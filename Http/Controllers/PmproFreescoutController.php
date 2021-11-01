<?php

namespace Modules\PmproFreescout\Http\Controllers;

use App\Mailbox;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Routing\Controller;

class PmproFreescoutController extends Controller
{

    /**
     * Function to show the settings page for Paid Memberships Pro authentication.
     */
    public function mailboxSettings($id) {
        // Find the mailbox ID.
        $mailbox = Mailbox::findOrFail($id);

       $settings = \PMProFreescout::getMailboxSettings($mailbox);

        return view('pmprofreescout::mailbox_settings', [
            'settings' => [
                'pmpro.url' => $settings['url'] ?? '',
                'pmpro.username' => $settings['username'] ?? '',
                'pmpro.password' => $settings['password'] ?? '',
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
                $settings[str_replace('pmpro.', '', $key)] = $value;
                unset($settings[$key]);
            }
        }

        $mailbox->setMetaParam('pmpro', $settings);
        $mailbox->save();

        return redirect()->route('mailboxes.pmprofreescout', ['id' => $id]);
    }

    /**
     * Ajax function to get updated orders from API.
     * TODO
     */
    

} //End of Class
