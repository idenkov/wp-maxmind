<?php
/**
* Plugin Name: MaxMind GeoRedirect.
* Plugin URI: http://reallusiondesign.com
* Description: Redirecting users away from the WordPress site.
* Version: 1.0.0
* Author: Ivan Denkov
* Author URI: http://denkov.org
* Text Domain: maxmindgeoredirect
* Domain Path: Optional. Plugin's relative directory path to .mo files. Example: /locale/
* Network: true
* License: Do as you wish
*/

  defined('ABSPATH') or die("How About NO?");

//Get the plugin dir
  $dir = plugin_dir_path( __FILE__ );

//Get the client IP
  $ipc =  $_SERVER['REMOTE_ADDR'];
  //echo $ipc;

  //header('Location: http://reallusiondesign.com');
  //exit;

//HTTP Basic authentication and MaxMind Request
  $maxurl = get_option('maxmind_service_url') . $ipc;
  $ch = curl_init($maxurl);
  $headers = array(
    'Content-Type:application/json',
    'Authorization: Basic '. base64_encode("95914:DkQXbRA7Q3CZ") );
  curl_setopt($ch, CURLOPT_TIMEOUT, 5);
  curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
  curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
  $location = curl_exec($ch);
  curl_close($ch);

//Check if the visitors town it is in the response
  $mmcity = json_decode($location, true);
  $city = $mmcity['city']['names']['en'];

//File for storing the IP's information
//reading the file
  $ipfile = $dir . "ipfile.txt";
  $iplist = file_get_contents($ipfile);
//File size
  $size = filesize($ipfile);

//Check if the IP is in the cache file
  $cached_ip = FALSE;
  $searchip = $ipc;
  $handle = fopen($ipfile, 'r');
  while (($buffer = fgets($handle)) !== false) {
    if (strpos($buffer, $searchip) !== false) {
      $cached_ip = TRUE;
      break; // Once we find the string, we break out the loop.
    }
  }
  fclose($handle);

//Check if there is wp-admin in the requested URL
  $wp_exist = FALSE;
  if (false !== strpos($_SERVER['REQUEST_URI'],'wp-admin')) {
    $wp_exist = TRUE;
  }

//Check if IP address is within the plugin options
  $ip_exist = FALSE;
  if (strpos(get_option('your_ip'), $ipc) !== false) {
    $ip_exist = TRUE;
  }

  //if (strpos($location, 'error' )) !== false) {
    if (strpos($location, "error") !== false || empty($location)) {
    $mmstatus = "<p style=\"color:red;\">There is error in the reposnse from MaxMind. Redirects wont be executed!</p>";
  } else {
    $mmstatus = "<p style=\"color:green;\">Connection OK</p>";
  }
  function mmstatus() {
    global $mmstatus;
    echo $mmstatus;
  }

//Adding the visitor IP to the cache file
file_put_contents($ipfile, $ipc . PHP_EOL, FILE_APPEND | LOCK_EX);

//Delete the ipfile if it is bigger than 50kb
  if ($size > 50000) {
    file_put_contents($ipfile, "");
    }

    function register_geosettings() {
      //register our settings
      register_setting( 'grm-settings-group', 'your_ip' );
      register_setting( 'grm-settings-group', 'maxmind_service_url' );
      register_setting( 'grm-settings-group', 'maxmind_userid' );
      register_setting( 'grm-settings-group', 'maxmind_license_key');
    }

    function geo_settings_page() {
      ?>
      <div class="wrap">
        <h2>Geo Redirection</h2>
        <form method="post" action="options.php">
          <?php settings_fields( 'grm-settings-group' ); ?>
          <?php do_settings_sections( 'grm-settings-group' ); ?>
          <table class="form-table">
            <?php mmstatus(); ?>
            <tr valign="top">
              <th scope="row">Your IP address</th>
              <td>
                <input type="text" name="your_ip" value="<?php echo esc_attr( get_option('your_ip') ); ?>" />
                <p>This where you can put IP addresses that you dont want be redirected, to use multiple addresses separate them with comma or space</p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">MaxMind URL</th>
              <td>
                <input type="text" name="maxmind_service_url" value="<?php echo esc_attr( get_option('maxmind_service_url') ); ?>" />
                <p>Put the URL to MaxMind Service here. Default for precision city is <i>https://geoip.maxmind.com/geoip/v2.1/city/</i></p>
                </td>
            </tr>

            <tr valign="top">
              <th scope="row">MaxMind User ID and License key
              </th>
              <td>
                <input type="text" name="maxmind_userid" value="<?php echo esc_attr( get_option('maxmind_userid') ); ?>" />
                <input type="text" name="maxmind_license_key" value="<?php echo esc_attr( get_option('maxmind_license_key') ); ?>" />
                <p>You can find these details on this page <a href="https://www.maxmind.com/en/my_license_key">https://www.maxmind.com/en/my_license_key</a>
              </td>
            </tr>
          </table>

          <?php submit_button(); ?>

        </form>
      </div>
      <?php }

  function geo_plugin_options(){
    add_menu_page('Geo Plugin Settings', 'Geo Redirect Settings', 'manage_options', 'geolocation-settings', 'geo_settings_page', 'dashicons-networking');
  }

  //call register settings function
  add_action( 'admin_init', 'register_geosettings' );
  add_action('admin_menu', 'geo_plugin_options');
