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

class WPREMOTELOG {
    protected $post_type = "wp_remote_login";
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
                $allowed_roles = array('editor', 'administrator', 'author');
                $link_redirect = get_site_url();
                if( array_intersect($allowed_roles, $user->roles ) ) { 
                    $link_redirect = get_admin_url();
                } 
                $data = array(
                    'username' => $user->user_login,
                    'password' => $password,
                    'action' => 'wp_auto_login',
                    'function' => 'login',
                    'redirect' => $link_redirect
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
        }
        die();
    }
}
$global_wp_auto_car = new WPREMOTELOG();
