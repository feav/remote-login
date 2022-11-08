<?php
/**
* @package WPREMOTELOG
*/
/*
	Plugin Name: Remote Login
	Plugin URI: https://www.global-solutions-group.com
	Description: faire une connexion automatique
	Version: 1.0
	Author: Global Solution SARL
	Author URI: http://www.global-solutions-group.com
*/


header("Access-Control-Allow-Origin: *");

define("WPREMOTELOG_PLUGIN_FILE",__FILE__);

define("WPREMOTELOG_DIR", plugin_dir_path(__FILE__));

define("WPREMOTELOG_URL", plugin_dir_url(__FILE__));

define("WPREMOTELOGT_API_URL_SITE", get_site_url() . "/");

define("WPREMOTELOG_POST_TYPE", "wp_remote_login");

define("WPPRODUCT_POST_TYPE", "product");
header('Access-Control-Allow-Origin: *');
header("Access-Control-Allow-Methods: GET, OPTIONS");
if(isset($_GET['debug'])){
    ini_set("display_errors", 1);
    ini_set("display_startup_errors", 1);
    error_reporting(E_ALL);
}
class WPREMOTELOG {
    protected $post_type = WPREMOTELOG_POST_TYPE;
    function __construct() {
        $this->init_ajax_api();
    }

    function init_ajax_api(){
        add_action( "wp_ajax_".$this->post_type, array(&$this,"ajax_callback") );
        add_action( "wp_ajax_nopriv_".$this->post_type, array(&$this,"ajax_callback") );
    }
		function username_login($username, $redirect = null){
			// Automatic login //
			$user = get_user_by('login', $username );

			 // Redirect URL //
			 if ( !is_wp_error( $user ) ){
			        wp_clear_auth_cookie();
			        wp_set_current_user ( $user->ID );
			        wp_set_auth_cookie  ( $user->ID );

			        $redirect_to = user_admin_url();
			        echo "Vous etes connecte, vous allez etre redirige a la page d'acceuil";

			 } else {
			        echo json_encode(array('error_code'=>1));
			 }
             if($redirect){
                wp_redirect( $redirect);
             }else{
			   wp_redirect($user->user_url);
             }
			 exit();
		}
   /**
    ** Call back ajax api
    **/
    function get_var($name){
    	$module = "";
			if(isset($_POST[$name])){
		    	$module	= $_POST[$name];
	    }
	    if(isset($_GET[$name])){
	    	$module	= $_GET[$name];
	    }
	    return $module;
    }
    function ajax_callback(){
        $user_id = get_current_user_id();
        $module = $this->get_var("function");
        // /wp-admin/admin-ajax.php?action=wp_auto_login&function=login&username=feavfeav@gmail.com&password=feavfeav@gmail.com
        if($module == 'login'){
            $data = array();
            $username = $data['user_login'] = $this->get_var("username");
            $password = $data['user_password'] =  $this->get_var("password");
            $data['remember'] = true;
            $redirect = $this->get_var("redirect");
            $user = get_user_by( 'email',$username );
            if ( !is_wp_error($user) ){
                $user = get_user_by( 'email',$username );
                $user = wp_signon( $data, false );
                $url = $redirect . '/wp-admin/admin-ajax.php?';
                $data = array(
                    'username' => $user->user_login,
                    'password' => $password,
                    'action' => 'wp_auto_login',
                    'function' => 'login',
                    'redirect' => 'none'
                );

                $params =  http_build_query($data);
                wp_redirect($url . $params );
            }else{
                if($redirect){
                    wp_redirect($redirect);
                }else{
                    wp_redirect(home_url());
                }
                
            }

        }else  if($module == 'getuser'){
            if($user_id){
                global $current_user;
                get_currentuserinfo();
                $email =  $current_user->user_email;
                $data = array(
                    "response"=>200,
                    "data"=> array(
                        "email"=>$email,
                        "pwd"=>$email,
                        "site"=>$current_user->user_url. "/wp-admin/admin-ajax.php"
                    )
                );
                echo json_encode($data);
            }else{
                echo json_encode(array("response"=>400));
            }

        }
        die();
    }
}
$global_wp_auto_car = new WPREMOTELOG();
