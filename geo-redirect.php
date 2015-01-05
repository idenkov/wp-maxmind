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


//File for storing the IP's information
//reading the file
  $ipfile = $dir . "ipfile.txt";
  $cali_list = $dir . "cal_locations.txt";
  $iplist = file_get_contents($ipfile);
//File size
  $size = filesize($ipfile);

//Delete the ipfile content if it is bigger than 50kb
  if ($size > 50000) {
    file_put_contents($ipfile, "");
  }

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

  //Adding the visitor IP to the cache file
  if (!$cached_ip){
    file_put_contents($ipfile, $ipc . PHP_EOL, FILE_APPEND | LOCK_EX);

    //HTTP Basic authentication and MaxMind Request
    //Need if/else or function somewhere here to execute the request only if the IP is not in the cache file.
    $maxurl = get_option('maxmind_service_url') . "46.10.117.238";
    $ch = curl_init($maxurl);
    $headers = array(
      'Content-Type:application/json',
      'Authorization: Basic '. base64_encode(get_option('maxmind_userid') . ":" . get_option('maxmind_license_key') ) );
      curl_setopt($ch, CURLOPT_TIMEOUT, 5);
      curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 5);
      curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
      curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
      $response = curl_exec($ch);
      curl_close($ch);
      echo $response;

      //Get the visitor town
      //Need to check if the town it is within the file!!!
      $mmcity = json_decode($response, true);
      $city = $mmcity['city']['names']['en'];

      //Check if the town is within the file
      $in_cali = FALSE;
      $searchip = $city;
      $handle_town = fopen($cali_list, 'r');
      while (($buffer = fgets($handle_town)) !== false) {
        if (strpos($buffer, $searchip) !== false) {
          $in_cali = TRUE;
          break; // Once we find the string, we break out the loop.
        }
      }
      fclose($handle);
  }


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


  //header('Location:' . get_option('maxmind_userid'));
  //exit;
  //list all the variable to check for redirect here
  //$cached_ip
  //$wp_exist
  //$ip_exist
  //$in_cali

  if (strpos($response, "error") !== false || empty($response)) {
    $mmstatus = "<p style=\"color:red;\">There is error in the response from MaxMind. Redirects wont be executed!</p>";
  } else {
    $mmstatus = "<p style=\"color:green;\">Connection OK</p>";
  }
  function mmstatus() {
    global $mmstatus;
    echo $mmstatus;
  }

    function register_geosettings() {
      register_setting( 'grm-settings-group', 'redirect_url');
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
              <th scope="row">Redirect to:</th>
              <td>
                <input type="text" name="your_ip" value="<?php echo esc_attr( get_option('redirect_url') ); ?>" />
                <p>This is the URL where you visitors will be redirected to. It should be complete, for example <i>http://domain.com/page-name.html</i></p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">Your IP address:</th>
              <td>
                <input type="text" name="your_ip" value="<?php echo esc_attr( get_option('your_ip') ); ?>" />
                <p>This is where you can put IP addresses that you dont want be redirected, to use multiple addresses separate them with comma or space</p>
              </td>
            </tr>

            <tr valign="top">
              <th scope="row">MaxMind URL:</th>
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
