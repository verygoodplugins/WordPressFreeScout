<?php

namespace Modules\PmproFreescout\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Database\Eloquent\Factory;

//Module Alias
define( 'PMPRO_MODULE', 'pmpro' );

class PmproFreescoutServiceProvider extends ServiceProvider
{
    
    const MAX_ORDERS = 3;

    /**
     * Indicates if loading of the provider is deferred.
     *
     * @var bool
     */
    protected $defer = false;

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
        
        //Add Mailbox Menu Items
        \Eventy::addAction('mailboxes.settings.menu', function($mailbox) {
            if (auth()->user()->isAdmin()) {
                echo \View::make('pmprofreescout::partials/settings_menu', ['mailbox' => $mailbox])->render();
            }
        }, 34);

        // Section parameters.
        \Eventy::addFilter('settings.section_params', function($params, $section) {
           
            if ($section != PMPRO_MODULE) {
                return $params;
            }

            $params['settings'] = [
                'pmpro.url' => [
                    'env' => 'PMPRO_URL',
                ],
                'pmpro.username' => [
                    'env' => 'PMPRO_USERNAME',
                ],
                'pmpro.password' => [
                    'env' => 'PMPRO_PASSWORD',
                ],
            ];

            return $params;
        }, 20, 2);

         // Section settings.
         \Eventy::addFilter('settings.section_settings', function($settings, $section) {
           
            if ($section != PMPRO_MODULE) {
                return $settings;
            }

            $settings['pmpro.url'] = config('pmpro.url');
            $settings['pmpro.username'] = config('pmpro.username');
            $settings['pmpro.password'] = config('pmpro.password');

            return $settings;
        }, 20, 2);

        \Eventy::addAction('conversation.after_prev_convs', function($customer, $conversation, $mailbox) { 

            $results = [];
            $load = false;
            
            $customer_email = $customer->getMainEmail();

            if (!$customer_email) {
                return;
            }

            // Make sure that we have settings for authentication.
            if (!\PMProFreescout::isMailboxApiEnabled($mailbox)) {
                return;
            }

            $settings = \PMProFreescout::getMailboxSettings($mailbox);

            $results = self::apiGetMemberInfo($customer_email, $mailbox );

            echo \View::make('pmprofreescout::partials/orders', [
                'results'        => $results['data'],
                'customer_email' => $customer_email,
                'load'           => $load,
                'url'            => \PMProFreescout::getSanitizedUrl( $settings['url']),
            ])->render();
        }, 12, 3 );
    }

    /**
     * Get Mailbox settings we need for authenticating.
     */
    public static function getMailboxSettings($mailbox) {
        return [
            'url' => $mailbox->meta['pmpro']['url'] ?? '',
            'username' => $mailbox->meta['pmpro']['username'] ?? '',
            'password' => $mailbox->meta['pmpro']['password'] ?? '',
        ];
    }

    /**
     * Get customer information for the customer based off their email address
     */
    public static function apiGetMemberInfo($customer_email, $mailbox = null) {
        $response = [
            'error' => '',
            'data' => [],
        ];


        // Get settings from database or from config.
        if ( $mailbox && self::isMailboxApiEnabled( $mailbox ) ) {
            $settings = self::getMailboxSettings( $mailbox );

            $url = self::getSanitizedUrl( $settings['url'] );
            $username = $settings['username'];
            $password = $settings['password'];

        } else {
            $url = self::getSanitizedUrl( config('pmpro.url') );
            $username = config('pmpro.username');
            $password = config('pmpro.password');
        }

        $request_url = $url . 'wp-json/pmpro-support/v1/get-customer-info/';

        // Get data via REST API and return it.
        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $request_url. '?user_email=' . $customer_email );
            curl_setopt($ch, CURLOPT_TIMEOUT, 10);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

            $results = curl_exec($ch);
            $status_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

            curl_close($ch);

            // If the request was okay, get data otherwise let's get an error yo!
            if ( $status_code == 200 ) {
                $response['data'] = json_decode( $results );
            } else {
                $response['error'] = json_decode( $results );
            }
        } catch ( Exception $e ) {
            $response['error'] = $e->getMessage();
        }          

        return $response;
    }

    /**
     * Check if credentials are saved and working.
     * @return boolean Returns true if settings are stored.
     */
    public static function isMailboxApiEnabled($mailbox) {
        
        if (empty($mailbox) || empty($mailbox->meta['pmpro'])) {
            return false;
        }

        $settings = self::getMailboxSettings($mailbox);

        return (!empty($settings['url']) && !empty($settings['username']) && !empty($settings['password']));
    }

    /**
     * Sanitize the URL submitted to ensure it's always correct format.
     * @return string sanitized URL with trailing /.
     */
    public static function getSanitizedUrl($url = '') {
        if (empty($url)) {
            $url = config('pmpro.url');
        }

       $url = preg_replace("/https?:\/\//i", '', $url);

        if (substr($url, -1) != '/') {
            $url .= '/';
        }

        return 'https://'.$url;
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
            __DIR__.'/../Config/config.php' => config_path('pmprofreescout.php'),
        ], 'config');
        $this->mergeConfigFrom(
            __DIR__.'/../Config/config.php', 'pmpro'
        );
    }

    /**
     * Register views.
     *
     * @return void
     */
    public function registerViews()
    {
        $viewPath = resource_path('views/modules/pmprofreescout');

        $sourcePath = __DIR__.'/../Resources/views';

        $this->publishes([
            $sourcePath => $viewPath
        ],'views');

        $this->loadViewsFrom(array_merge(array_map(function ($path) {
            return $path . '/modules/pmprofreescout';
        }, \Config::get('view.paths')), [$sourcePath]), 'pmprofreescout');
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
