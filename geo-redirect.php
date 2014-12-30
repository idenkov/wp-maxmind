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
//$surl = "http://freegeoip.net/xml/".$ipc;

//header('Location: http://reallusiondesign.com');
//exit;


//File for storing the IP's information
//reading the file
  $ipfile = $dir . "ipfile.txt";
  $iplist = file_get_contents($ipfile);
//File size
  $size = filesize($ipfile);
  //echo $ipfile;
  //echo $iplist;
  //echo $ipfile . " is " . $size . " bytes.";

//Adding the clinet IP to the cache file
file_put_contents($ipfile, $ipc . PHP_EOL, FILE_APPEND | LOCK_EX);

//Delete the ipfile if it is bigger than 50kb
  if ($size > 50000) {
    file_put_contents($ipfile, "");
    }

    function register_geosettings() {
      //register our settings
      register_setting( 'grm-settings-group', 'new_option_name' );
      register_setting( 'grm-settings-group', 'some_other_option' );
      register_setting( 'grm-settings-group', 'option_etc' );
    }

    function geo_settings_page() {
      ?>
      <div class="wrap">
        <h2>Geo Redirection</h2>
        <form method="post" action="options.php">
          <?php settings_fields( 'grm-settings-group' ); ?>
          <?php do_settings_sections( 'grm-settings-group' ); ?>
          <table class="form-table">
            <tr valign="top">
              <th scope="row">New Option Name|Your IP</th>
              <td><input type="text" name="new_option_name" value="<?php echo esc_attr( get_option('new_option_name') ); ?>" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">Some Other Option|MaxMind URL</th>
              <td><input type="text" name="some_other_option" value="<?php echo esc_attr( get_option('some_other_option') ); ?>" /></td>
            </tr>

            <tr valign="top">
              <th scope="row">Options, Etc.|MaxMind Credentials</th>
              <td><input type="text" name="option_etc" value="<?php echo esc_attr( get_option('option_etc') ); ?>" /></td>
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
