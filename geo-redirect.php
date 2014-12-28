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

//Git testing, delete later. LOL

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
echo $ipfile;
echo $iplist;
//File size
$size = filesize($ipfile);
echo $iplist . " is " . $size . " bytes.";


file_put_contents($ipfile, $ipc . PHP_EOL, FILE_APPEND | LOCK_EX);

//Delete the ipfile if it is bigger than 50kb
if ($size > 50000) {
  file_put_contents($ipfile, "");
  }
