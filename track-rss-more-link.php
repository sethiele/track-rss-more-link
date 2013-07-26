<?php
/*
Plugin Name: Track RSS More Link
Description: Adds Google Analytics tracking code for more link for RSS Feeds
Version: 0.1
Author: Sebastian Thiele
Author URI: http://www.sebastian-thiele.net
License: GPL2
*/


add_filter( 'the_excerpt_rss', 'rewrite_urls', 99 );

/**
  * Adds the RSS footer (or header) to the excerpt RSS feed item.
  *
  * @param string $content Feed item excerpt.
  * @return string
  */
function rewrite_urls( $content ) {
  global $post;
  if ( is_feed() ) {
    $post_url = get_permalink( $post->ID );
    $bloginfo = get_bloginfo('wpurl');
    $rss_tracker = get_option('rss_tracker');
    $tracking_para = "?utm_source=" . $rss_tracker['utm_source'] . "&utm_medium=" . $rss_tracker['utm_medium'] . "&utm_campaign=" . $post->post_name;
    //$content = str_replace($post_url, $post_url . $tracking_para, $content);  
    //$content = str_replace($bloginfo, $bloginfo . $tracking_para, $content);
    //$content = preg_replace('/href=\"http:\/\/lcl.iamyourfather.de(.+)\"/i', "href=\"" . $bloginfo . "$1" . $tracking_para . "\"", $content);
    preg_match_all('/<a href="(.*?)"/s', $content, $matches);
    $matches = array_unique($matches[1]);
    foreach($matches as $match){
      if( substr( $match, 0 , strlen( $bloginfo ) ) == $bloginfo ){
        if( $match != $bloginfo ){
          $content = str_replace( "href=\"" . $match . "\"", "href=\"" . $match . $tracking_para . "\"", $content );
        } else {
          $content = str_replace( "href=\"" . $bloginfo . "\"", "href=\"" . $bloginfo . $tracking_para . "\"", $content );
        }
      }
      //$content = str_replace( $match, $match . $tracking_para, $conent );
    }
    //$content = var_export($matches, true);
  }
  return $content;
}


/**
  * Create Option Page
  */
add_action( 'admin_menu', 'track_rss_menu' );

function track_rss_menu() {
  add_options_page( 
    'Track RSS Links with Google Analytics', 
    'Track RSS Links',
    'manage_options',
    'rss_tracker',
    'rss_tracker_options' );

  //call register settings function
  add_action( 'admin_init', 'register_rss_tracking_settings' );
}

function register_rss_tracking_settings() {
  register_setting( 'rss-settings-group', 'rss_tracker' );
}

function rss_tracker_options() {
  if ( !current_user_can( 'manage_options' ) )  {
    wp_die( __( 'You do not have sufficient permissions to access this page.' ) );
  }
?>
<div class="wrap">
  <form method="post" action="options.php">
    <?php settings_fields( 'rss-settings-group' ); ?> 
    <?php $rss_tracker = get_option('rss_tracker'); ?>

    <table class="form-table">
      <tr valign="top">
        <th scope="row">utm_source</th>
        <td><input type="text" name="rss_tracker[utm_source]" value="<?php echo $rss_tracker['utm_source']; ?>" /></td>
      </tr>
      <tr valign="top">
        <th scope="row">utm_medium</th>
        <td><input type="text" name="rss_tracker[utm_medium]" value="<?php echo $rss_tracker['utm_medium']; ?>" /></td>
      </tr>
    </table>

    <?php submit_button(); ?>
  </form>
</div>
<?php
}


function trackrssmorelink_activate() {
  $val = array(
                'utm_source' => 'rss',
                'utm_medium' => 'content'
              );
  add_option( 'rss_tracker', $val );
}
register_activation_hook( __FILE__, 'trackrssmorelink_activate' );


function trackrssmorelink_deactivate(){
  delete_option( 'rss_tracker' );
}

register_deactivation_hook( __FILE__, 'trackrssmorelink_deactivate' );
?>
