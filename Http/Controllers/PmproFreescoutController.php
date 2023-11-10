<?php

namespace Modules\WordPressFreeScout\Http\Controllers;

use App\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class WordPressFreeScoutController extends Controller
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
                'wordpress.url' => $settings['url'] ?? '',
                'wordpress.username' => $settings['username'] ?? '',
                'wordpress.password' => $settings['password'] ?? '',
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

        $mailbox->setMetaParam('wordpress', $settings);
        $mailbox->save();

        return redirect()->route('mailboxes.wordpressfreescout', ['id' => $id]);
    }

    /**
     * Ajax function to get updated orders from API.
     */
    public function ajax(Request $request) {

        $response = [
            'status' => 'error',
            'msg'    => '', // this is error message
        ];

        switch( $request->action ) {
            case 'orders':
                // Get orders and stuff.
                // Get the Mailbox
                $mailbox = null;
                if ( $request->mailbox_id ) {
                    $mailbox = Mailbox::find( $request->mailbox_id );
                }

                $settings = \WordPressFreeScout::getMailboxSettings( $mailbox );
                $results = \WordPressFreeScout::apiGetMemberInfo( $request->customer_email, $mailbox, true ); //Force to get uncached data!

                $response['html'] = \View::make('wordpressfreescout::partials/orders', [
                    'results'        => $results['data'],
                    'error'          => $results['error'],
                    'customer_email' => $request->customer_email,
                    'load'           => false,
                    'url'            => \WordPressFreeScout::getSanitizedUrl( $settings['url'] ),
                ])->render();

                $response['status'] = 'success';
                break;
        }
        return \Response::json($response);
    }

} //End of Class
