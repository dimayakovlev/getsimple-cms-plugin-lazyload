<?php
/*
Plugin Name: Lazy Load
Description: Delay loading of images in long web pages. Images outside of viewport won't be loaded before user scrolls to them.
Version: 1.0
Author: Dmitry Yakovlev
Author URI: http://dimayakovlev.ru/
*/

# get correct id for plugin
$thisfile = basename(__FILE__, '.php');

# register plugin
register_plugin(
	$thisfile, //Plugin id
	'Lazy Load', 	//Plugin name
	'1.0', 		//Plugin version
	'Dmitry Yakovlev',  //Plugin author
	'http://dimayakovlev.ru/', //author website
	'Delay loading of images in long web pages', //Plugin description
	'', //page type - on which admin tab to display
	''  //main function (administration)
);

# activate filter 
add_action('index-pretemplate', 'lazyload_add_action');

function lazyload_add_action() {
  global $LAZYLOAD, $LAZYLOADEMBED, $LAZYLOADHEAD, $SITEURL;
  
  if (!isset($LAZYLOAD)) $LAZYLOAD = true;
  if (!$LAZYLOAD) return false;
  if (!isset($LAZYLOADEMBED)) $LAZYLOADEMBED = false;
  if (!isset($LAZYLOADHEAD)) $LAZYLOADHEAD = false;
  
  add_filter('content', 'lazyload_filter');
  if ($LAZYLOADEMBED) {
    function lazyload_get_script() {
      echo '<script>'.@file_get_contents(GSPLUGINPATH.'lazyload/js/lazysizes.min.js').'</script>';
    }
    add_action($LAZYLOADHEAD ? 'theme-header' : 'theme-footer', 'lazyload_get_script');
  } else {
    register_script('lazysizes', $SITEURL.'plugins/lazyload/js/lazysizes.min.js', '4.0.0-rc3', !$LAZYLOADHEAD);
    queue_script('lazysizes', GSFRONT);
  }
}

/**
 * Filter Content
 *
 * This will replace attributes src to data-src 
 * and add lazyload class to all images in given HTML code
 *
 * @param string Content HTML code
 * @return string Filtered HTML code
 */
function lazyload_filter($content) {
  global $LAZYLOADIMAGE;  
  if (!isset($LAZYLOADIMAGE)) $LAZYLOADIMAGE = '#';  
  $doc = new DOMDocument();
  $doc->loadHTML('<meta http-equiv="Content-Type" content="text/html; charset=utf-8">'.$content);
  foreach ($doc->getElementsByTagName('img') as $img) {
    $img->setAttribute('class', trim($img->getAttribute('class').' lazyload'));
    $img->setAttribute('data-src', $img->getAttribute('src'));
    $img->setAttribute('src', $LAZYLOADIMAGE);
  }
  return preg_replace('/<\/?(?:!DOCTYPE.+?|meta.+?|html|body|head)>/i', '', $doc->saveHTML());
}
