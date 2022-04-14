<?php 
/**
 * Plugin Name: Qiwi Investment Stock Intrument Manager
 * Plugin URI: http://wabamedia.co
 * Description: Create plugin to manage Qiwi Investment Intrument
 * Version: 1.0
 * Author: Jermaine Byfield
 * Author URI: http://wabamedia.co
 */

include(plugin_dir_path( __FILE__  ) . 'controller/qiwicontroller.php');
//Inititiate function
$qwiwi  =  new qiwicontroller();
function init_posttype(){
    $qwiwi  =  new qiwicontroller();
    $qwiwi->postType_stock_instrument();
}
function init_submenu(){
    
    $qwiwi  =  new qiwicontroller(); 
    $qwiwi->add_import_submenu();
}
function qwi_shortcode($att){
    ob_start();
    $qwiwi  =  new qiwicontroller(); 
    $qwiwi->generate_chart();
    $output = ob_get_contents();
    ob_end_clean();
    return $output;
} 
add_action( 'init', 'my_script_enqueuer' );
function my_script_enqueuer() {
  wp_enqueue_script( 'jquery' );
  # before enqueue,if not register then register  
  //wp_enqueue_script( 'qiwi-chart' );
  //wp_localize_script( 'qiwi-chart', 'myAjax', array( 'ajaxurl' => admin_url( 'admin-ajax.php' )));  
}
function JSE(){
 
    $qwiwi  =  new qiwicontroller(); 
  // $qwiwi->getJSE();
  // wp_mail( "jermaine@wabamedia.co", "QWI JSE CRON was run at ".date("Y-m-d h:i A"), "JSE Cron was run successfully \n Data Source: \n https://www.jamstockex.com/wp-json/jse-api/v1/instruments/latest/qwi");

   
} 
//
function netAssetValue(){ 
      $qwiwi  =  new qiwicontroller(); 
      $data = $qwiwi->getNetAssetValue();
} 


function qwi_get_net_asset_value($att){
    // $qwiwi  =  new qiwicontroller(); 
    // $output = $qwiwi->getNetAssetValue();
}

add_action( 'getJSE', 'JSE'); 
add_action( 'getNetAssetValue', 'netAssetValue'); 
add_action( 'init', "init_posttype" ); 
add_action( 'admin_menu', "init_submenu" );
add_action( 'wp_enqueue_scripts', function(){ $qwiwi  =  new qiwicontroller();  $qwiwi->enqueue_scripts();}); 
add_action( 'wp_enqueue_scripts', function(){ $qwiwi  =  new qiwicontroller();  $qwiwi->enqueue_style();}); 
add_shortcode('qwi_chart', 'qwi_shortcode');
//Test shortcode for fetching records
add_shortcode('get_net_asset_value', 'qwi_get_net_asset_value');
//Test shortcode for fetching records
add_action( 'wp_ajax_chart_data',  function(){ $qwiwi  =  new qiwicontroller();  $qwiwi->generate_chart_data_json();});
add_action( 'wp_ajax_nopriv_chart_data',  function(){ $qwiwi  =  new qiwicontroller();  $qwiwi->generate_chart_data_json();});
//Disable the add new button
add_action( 'admin_head', function(){
    ob_start(); ?>
    <style>
         /* #wp-admin-bar-new-content {
            display: none;
        } */
      
        a[href*='stock_instrument'].page-title-action{
            display: none !important;
        } 
        #menu-posts-stock_instrument > ul > li:nth-child(3) > a{
            display:none;
        }
        li[class*='yoast_cornerstone']{ display:none}
       
    </style>
<?php ob_end_flush();
});