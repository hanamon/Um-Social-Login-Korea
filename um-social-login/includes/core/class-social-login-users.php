<?php 

namespace um_ext\um_social_login\core;

if ( ! defined( 'ABSPATH' ) ) exit;


/**
 * Class Social_Login_Users
 * @package um_ext\um_social_login\core
 */
class Social_Login_Users{

	var $networks;

	var $user_id = null;

	var $data = array();

	function __construct( $user_id = null ) {	

		if( $user_id ){
			$this->user_id = $user_id;

			if( ! isset( $this->data[ $user_id ] ) ){
				$this->data[ $user_id ] = get_user_meta( $user_id );

			}

		}
	}

	/**
	 * Get Display Name
	 * @param  string $provider 
	 * @return string         
	 */
	function getDisplayName( $provider = '' ){
		
		if( $value = $this->getValue("{$provider}_handle") ){

			return $value;
		}

		return '';

	}

	/**
	 * Get Profile Photo
	 * @param  string $provider 
	 * @return string         
	 */
	function getProfilePhoto( $provider = '' ){


		if( $value = $this->getValue("{$provider}_photo_url") ){

			if ( is_ssl() ) {
				$value = str_replace( 'http://', 'https://', $value );
			}

			return $value;
		}

		return '';

	}

	/**
	 * Get Profile Url
	 * @param  string $provider 
	 * @return string         
	 */
	function getProfileUrl( $provider = '' ){

		if( $value = $this->getValue("{$provider}_link") ){
			return $value;
		}

		return '';

	}

	/**
	 * Get Profile Url
	 * @param  string $provider 
	 * @return string         
	 */
	function getDateLinked( $provider = '' ){

		if( $value = $this->getValue("_um_sso_{$provider}_date_connected") ){
			return $value;
		}

		return '';

	}

	/**
	 * Get Raw Data
	 * @param  string $provider 
	 * @return array         
	 */
	function getRaw( $provider = '' ){

		if( $value = $this->getValue("{$provider}_raw") ){
			
			$this->data[ $user_id ]["{$provider}_raw"] = unserialize( $value );

			return $this->data[ $user_id ]["{$provider}_raw"];
		}

		return '';
		
	}

	/**
	 * Get Value
	 * @param  string $key 
	 * @return string  
	 */
	function getValue( $key = '' ){

		if( isset( $this->data[ $this->user_id ][ $key ] ) ){

			if( is_array(   $this->data[ $this->user_id ][ $key ] ) ){
				return current(  $this->data[ $this->user_id ][ $key ] );
			} 

			return $this->data[ $this->user_id ][ $key ];
		}

		return '';
	}

     /**
      * Check if user has linked avatar
      * @param  integer $user_id  
      * @param  string $provider 
      * @return boolean           
      */
    function has_avatar_linked( $provider ){
        
        $user_id = $this->user_id;

        $has_sso_avatar_linked = get_user_meta( $user_id, "_um_social_login_avatar_provider", true ); 

        if( empty( $has_sso_avatar_linked ) ){
        	return false;
        }

    	return true;
    }

	/**
	 * Jsdump browser console for debugging purposes
	 * @param  array $args
	 * @return json
	 */
	function jsdump( $args ){
		
		echo "<script>console.log(".json_encode( $args ).");</script>";
	}

}