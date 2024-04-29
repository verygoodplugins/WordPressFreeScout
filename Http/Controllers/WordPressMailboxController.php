<?php

namespace Modules\WordPressFreeScout\Http\Controllers;

use App\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class WordPressMailboxController extends Controller
{
    /**
     * Function to show the settings page for WordPress authentication.
     */
    public function mailboxSettings($id) {
        // Find the mailbox ID.
        $mailbox = Mailbox::findOrFail($id);

       $settings = \WordPressFreeScout::getMailboxSettings($mailbox);

        return view('wordpressfreescout::mailbox_settings', [
            'settings' => [
                'wordpress.wordpress_active'    =>  $settings['wordpress_active'] ?? false,
                'wordpress.url'                 =>  $settings['url'] ?? '',
                'wordpress.username'            =>  $settings['username'] ?? '',
                'wordpress.password'            =>  $settings['password'] ?? '',
            ],
            'mailbox' => $mailbox
        ]);
    }

    /**
     * Save the settings and reload the page.
     */
    public function mailboxSettingsSave($id, Request $request) {
        // Find the mailbox ID.
        $mailbox = Mailbox::findOrFail($id);

        $settings = $request->settings ?: [];

        if (!empty($settings)) {
            foreach ($settings as $key => $value) {
                $settings[str_replace('wordpress.', '', $key)] = $value;
                unset($settings[$key]);
            }
        }

        config(['wordpress.url'      => $settings['url']]);
        config(['wordpress.username' => $settings['username']]);
        config(['wordpress.password' => $settings['password']]);

        $test_response = \WordPressFreeScout::apiWordPressCall( 'wp/v2/users/me', [], \WordPressFreeScout::API_METHOD_GET );

        if (!empty($test_response['message'])) {
            \Helper::log('feature_requests_errors', 'Error occurred checking API credentials: '.json_encode($test_response) ?? '');
        }

        $wordpress_auth_error = '';

        if (!isset($test_response['code']) && (!isset($test_response['status']) || $test_response['status'] != 'error')) {
            $wordpress_auth_error = '';
            $settings['wordpress_active'] = true;
        } else {

            if (!empty($test_response['message'])) {
                $wordpress_auth_error = $test_response['message'];
            } else {
                $wordpress_auth_error = __('Unknown API error occurred.');
            }
        }


        $mailbox->setMetaParam('wordpress', $settings);
        $mailbox->save();

        return redirect()->route('mailboxes.wordpress.settings', ['id' => $id]);
    }
}
