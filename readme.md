# WordPressFreeScout

This is a [FreeScout](https://freescout.net/) module which integrates FreeScout with WordPress, Easy Digital Downloads, and <a href="https://wpfusion.com">WP Fusion</a>.

Features:

- Lookup is performed using all of the customer's email addresses, with a fallback to first + last name
- Link to edit the WordPress user
- Registration date
- Active CRM (read from usermeta)
- Last EDD license check (read from usermeta)
- Current version of plugin installed on their site (green if up to date, red if outdated)
- The customer's active plugin integrations (read out of usermeta)
- The customer's tags in your CRM
- EDD order history including date, amount, status, and payment method
- EDD licenses including active sites

![CleanShot 2023-11-14 at 12 45 06@2x](https://github.com/verygoodplugins/WordPressFreeScout/assets/13076544/10afec8b-14d5-4049-ba88-b17a16a96535)

## Wish list

- [ ] Connect multiple mailboxes to multiple sites
- [ ] EDD subscriptions
- [ ] Link to CRM contact record
- [ ] Settings in the FreeScout admin to toggle individual fields
- [ ] Collapsible section headers
- [ ] EDD upgrade links

--------------------

## Changelog

### 1.0.3 on Feb 21st 2024 
* Fixed broken EDD license ID link
* Improved error handling for invalid or empty responses
* Cleaned up settings page

### 1.0.2 on November 17, 2023

* Made CRM section conditional on that field being present
* Added View in CRM link under CRM tags

### 1.0.1 on November 10, 2023

* Fixed edit user link going to CRM contact record, not user record
* Better 404 handling


### 1.0.0 on November 10, 2023

- Initial release

--------------------

## Installation

These instructions assume you installed FreeScout using the [recommended process](https://github.com/freescout-helpdesk/freescout/wiki/Installation-Guide), the "one-click install" or the "interactive installation bash-script", and you are viewing this page using a macOS or Ubuntu system.

Other installations are possible, but not supported here.

1. Download the [latest release of WordPressFreeScout](https://github.com/verygoodplugins/WordPressFreeScout/releases).

2. Unzip the file locally.

3. Copy the folder into your server using SFTP.

   ```sh
   scp -r ~/Desktop/freehelp-root@freescout.example.com:/var/www/html/Modules/WordPressFreeScout/
   ```

4. SSH into the server and update permissions on that folder.

   ```sh
   chown -R www-data:www-data /var/www/html/Modules/WordPressFreeScout/
   ```

5. Access your admin modules page like https://freescout.example.com/modules/list.

6. Find **WordPressFreeScout** and click ACTIVATE.

7. Copy the included WordPress helper plugin from `/WordPress-Plugin/freescout-wordpress` to your `/wp-content/plugins/` directory on the WordPress site.

8. Activate the WordPress plugin.

9. In the WordPress admin, go to your user profile and create a new application password.

10. In FreeScout, go to Settings >> WordPress and add the URL to your Wordpress site, your admin username, and application password generated in step #9.

11. Save the settings and the connection should show as Active.
