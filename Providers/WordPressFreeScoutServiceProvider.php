<?php

namespace Modules\WordPressFreeScout\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;
use Illuminate\Support\Facades\Auth;

//Module Alias
define( 'WP_MODULE', 'wordpressfreescout' );

class WordPressFreeScoutServiceProvider extends ServiceProvider
{

	/**
	 * Indicates if loading of the provider is deferred.
	 *
	 * @var bool
	 */
	protected $defer = false;

	const API_METHOD_GET = 'GET';
	const API_METHOD_POST = 'POST';
	const API_METHOD_DELETE = 'DELETE';

	/**
	 * Boot the application events.
	 *
	 * @return void
	 */
	public function boot()
	{
		$this->registerConfig();
		$this->registerViews();
		$this->registerFactories();
		$this->loadMigrationsFrom(__DIR__ . '/../Database/Migrations');
		$this->hooks();
	}

	/**
	 * Module hooks.
	 */
	public function hooks()
	{
		// Add module's JS file to the application layout.
		\Eventy::addFilter('javascripts', function($javascripts) {
			$javascripts[] = \Module::getPublicPath(WP_MODULE).'/js/laroute.js';
			$javascripts[] = \Module::getPublicPath(WP_MODULE).'/js/module.js';
				return $javascripts;
		});

		// Add module's CSS file to the application layout.
		\Eventy::addFilter('stylesheets', function($styles) {
			$styles[] = \Module::getPublicPath(WP_MODULE).'/css/module.css';
				return $styles;
		});

		// Add item to settings sections.
		\Eventy::addFilter('settings.sections', function($sections) {
			$sections[WP_MODULE] = ['title' => __('WordPress'), 'icon' => 'list-alt', 'order' => 500];

			return $sections;
		}, 30);

		// Section settings
		\Eventy::addFilter('settings.section_settings', function($settings, $section) {

			if ($section != WP_MODULE) {
				return $settings;
			}
			$settings['wordpress.url']                 = config('wordpress.url');
			$settings['wordpress.username']            = config('wordpress.username');
			$settings['wordpress.password']            = config('wordpress.password');
			$settings['wordpress.helpscout_api_token'] = config('wordpress.helpscout_api_token');

			return $settings;
		}, 20, 2);

		// Section parameters.
		\Eventy::addFilter( 'settings.section_params', function($params, $section) {

			if ($section != WP_MODULE) {
				return $params;
			}

			$wordpress_auth_error = '';

			// Get rooms and test API credentials.
			if (config('wordpress.url') && config('wordpress.username') && config('wordpress.password')) {

				// Check credentials.
				$test_response = self::apiWordPressCall( 'wp/v2/users/me', [], self::API_METHOD_GET );

				if (!empty($test_response['message'])) {
					\Helper::log('feature_requests_errors', 'Error occurred checking API credentials: '.json_encode($test_response) ?? '');
				}

				if (!isset($test_response['code']) && (!isset($test_response['status']) || $test_response['status'] != 'error')) {
					\Option::set('wordpress.wordpress_active', true);
					$wordpress_auth_error = '';
				} else {
					\Option::set('wordpress.wordpress_active', false);
					if (!empty($test_response['message'])) {
						$wordpress_auth_error = $test_response['message'];
					} else {
						$wordpress_auth_error = __('Unknown API error occurred.');
					}
				}
			} elseif (\Option::get('wordpress.wordpress_active')) {
				\Option::set('wordpress.wordpress_active', false);
			}

			$params['template_vars'] = [
				'wordpress_auth_error' => $wordpress_auth_error
			];

			$params['settings'] = [
				'wordpress.url' => [
					'env' => 'WP_URL',
				],
				'wordpress.username' => [
					'env' => 'WP_USERNAME',
				],
				'wordpress.password' => [
					'env' => 'WP_PASSWORD',
				]
			];

			return $params;
		}, 20, 2);

		// Settings view name.
		\Eventy::addFilter('settings.view', function($view, $section) {
			if ($section != WP_MODULE) {
				return $view;
			} else {
				return 'wordpressfreescout::settings';
			}
		}, 20, 2);

		// After saving settings.
		\Eventy::addFilter('settings.after_save', function($response, $request, $section, $settings) {
			if ($section != WP_MODULE) {
				return $response;
			}
			return $response;

		}, 20, 4);

		\Eventy::addAction('conversation.after_prev_convs', function($customer, $conversation, $mailbox) {

			$results = [
				"data" => [],
				"error" => []
			];

			$emails = $customer->emails()->pluck('email')->toArray();

			if ( ! $emails ) {
				return;
			}

			$results = \Cache::get( 'wp_user_' . $emails[0] );

            $settings = \WordPressFreeScout::getMailboxSettings($mailbox);

            $results = null;

			if ( ! $results ) {

				$customer_data = array(
					'emails'     => $emails,
					'first_name' => $customer->first_name,
					'last_name'  => $customer->last_name,
				);


				// Get the data.
				$results = self::apiWordPressCall( 'freescout/v1/email', $customer_data, self::API_METHOD_GET, $settings);

				// Cache it for an hour.
				\Cache::put( 'wp_user_' . $emails[0], $results, now()->addMinutes( 60 ) );

			}

			if ( ! isset( $results['data'] ) ) {
				echo 'Uknown error occurred.';
				return;
			}

			echo \View::make('wordpressfreescout::partials/orders', [
				'results'        => $results['data'] ?? false,
				'error'          => $results['error'] ?? '',
				'customer_email' => $emails[0],
				'load'           => false,
				'url'            => \WordPressFreeScout::getSanitizedUrl( $settings['url'] ),
			])->render();

		}, 12, 3 );


        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            echo \View::make('wordpressfreescout::partials/mailbox_settings_menu', ['mailbox' => $mailbox])->render();
        }, 40);
	}

	/**
	 * Get Mailbox settings we need for authenticating.
	 */
	public static function getMailboxSettings($mailbox) {
		return [
            'wordpress_active'  =>  $mailbox->meta['wordpress']['wordpress_active'] ?? false,
			'url'      => $mailbox->meta['wordpress']['url'] ?? '',
			'username' => $mailbox->meta['wordpress']['username'] ?? '',
			'password' => $mailbox->meta['wordpress']['password'] ?? '',
		];
	}

	public static function apiWordPressCall( $url, $params, $http_method = self::API_METHOD_GET, $settings = [] ) {

		$response = array();

		$wordpress_url = (isset($settings['url']) && !empty($settings['url'])) ? $settings['url'] : config( 'wordpress.url' );

		if ( false === strpos( $wordpress_url, 'http' ) ) {
			$wordpress_url = 'https://' . $wordpress_url;
		}

		$api_url = $wordpress_url . '/wp-json/' . $url;

		if ( ( $http_method == self::API_METHOD_GET || $http_method == self::API_METHOD_DELETE ) && ! empty( $params ) ) {
			$api_url .= '?' . http_build_query( $params );
		}

		try {
			$ch = curl_init( $api_url );

            $wpUsername = (isset($settings['username']) && !empty($settings['username'])) ? $settings['username'] : config( 'wordpress.username' );

            $wpPassword = (isset($settings['password']) && !empty($settings['password'])) ? $settings['password'] : config( 'wordpress.password' );

			$headers = array(
				'Accept: application/json',
				'Content-Type: application/json',
				'Authorization: Basic ' . base64_encode( $wpUsername . ':' . $wpPassword ),
			);

			curl_setopt($ch, CURLOPT_CUSTOMREQUEST, $http_method);

			if ( $http_method == self::API_METHOD_POST ) {
				curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($params));
			}

			curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
			curl_setopt($ch, CURLOPT_TIMEOUT, 40);

			$json_response = curl_exec($ch);

			$response = json_decode($json_response);

			$status = curl_getinfo($ch, CURLINFO_HTTP_CODE);

			if (curl_errno($ch)) {
				throw new \Exception(curl_error($ch), 1);
			}

			curl_close($ch);

			if (empty($response) && $status != 204 && $status != 200 && $status != 201) {
				throw new \Exception(__('Empty API response. Check your credentials. HTTP status code: :status. JSON response: :json. URL: :url', ['status' => $status, 'json' => $json_response, 'url' => $api_url ]), 1);
			} elseif ($status == 404) {
				return [
					'data'   => false,
					'status' => 'error',
					'error'  => __('No matching user found.'),
				];
			} elseif ($status == 204) {
				return [
					'status' => 'success',
				];
			}

		} catch (\Exception $e) {

			\Helper::log('feature_requests_errors', 'API error: '.$e->getMessage().'; Response: '.json_encode($response).'; Method: '.$url.'; Parameters: '.json_encode($params));

			return [
				'status' => 'error',
				'error' => __('API call error.').' '.$e->getMessage()
			];
		}

		return array(
			'status' => 'success',
			'error'  => false,
			'data'   => $response,
		);

	}

	/**
	 * Sanitize the URL submitted to ensure it's always correct format.
	 * @return string sanitized URL with trailing /.
	 */
	public static function getSanitizedUrl($url = '') {
		if (empty($url)) {
			$url = config('wordpress.url');
		}

		$url = preg_replace("/https?:\/\//i", '', $url);

		if (substr($url, -1) != '/') {
			$url .= '/';
		}

		return 'https://'.$url;
	}

	/**
	 * Function to decode REST API response codes and output an error for us.
	 *
	 * @return string Returns human readable error message if status isn't 200 for the API Request.
	 */
	public static function errorCodeDescr($code) {

		switch ($code) {
			case 400:
				$descr = __('Bad request');
				break;
			case 401:
			case 403:
				$descr = __('Authentication or permission error, e.g. incorrect API keys or your store is protected with Basic HTTP Authentication');
				break;
			case 0:
			case 404:
				$descr = __('Store not found at the specified URL');
				break;
			case 500:
				$descr = __('Internal store error');
				break;
			default:
				$descr = __('Unknown error');
				break;
		}

		return $descr;
	}


	/**
	 * Register the service provider.
	 *
	 * @return void
	 */
	public function register()
	{
		$this->registerTranslations();
	}

	/**
	 * Register config.
	 *
	 * @return void
	 */
	protected function registerConfig()
	{
		$this->publishes([
			__DIR__.'/../Config/config.php' => config_path('wordpressfreescout.php'),
		], 'config');
		$this->mergeConfigFrom(
			__DIR__.'/../Config/config.php', 'wordpress'
		);
	}

	/**
	 * Register views.
	 *
	 * @return void
	 */
	public function registerViews()
	{
		$viewPath = resource_path('views/modules/wordpressfreescout');

		$sourcePath = __DIR__.'/../Resources/views';

		$this->publishes([
			$sourcePath => $viewPath
		],'views');

		$this->loadViewsFrom(array_merge(array_map(function ($path) {
			return $path . '/modules/wordpressfreescout';
		}, \Config::get('view.paths')), [$sourcePath]), 'wordpressfreescout');
	}

	/**
	 * Register translations.
	 *
	 * @return void
	 */
	public function registerTranslations()
	{
		$this->loadJsonTranslationsFrom(__DIR__ .'/../Resources/lang');
	}

	/**
	 * Register an additional directory of factories.
	 * @source https://github.com/sebastiaanluca/laravel-resource-flow/blob/develop/src/Modules/ModuleServiceProvider.php#L66
	 */
	public function registerFactories()
	{
		if (! app()->environment('production')) {
			app(Factory::class)->load(__DIR__ . '/../Database/factories');
		}
	}

	/**
	 * Get the services provided by the provider.
	 *
	 * @return array
	 */
	public function provides()
	{
		return [];
	}

}
