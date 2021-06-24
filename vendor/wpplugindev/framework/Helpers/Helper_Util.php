<?php
namespace WPPluginsDev\Helpers;
/**
 * Utilities helper class
 *
 * @since 1.0.0
 */
class Helper_Util {
	
    /**
     * Get Rest Auth Error.
     *
     * @since 1.0.0
     *
     * @param string $redir_url The url to redirect to.
     * @return \WP_Error The error.
     */
    public static function get_auth_error( $redir_url = null ) {
        return new \WP_Error(
            403,
            'Entrar no site',
            array( 'login_url' => add_query_arg( 'redirect_to', $redir_url, '/wp-login.php' ) )
            );
    }
    
	/**
	 * Create nonce.
	 *
	 * @since 1.0.0
	 *
	 * @param string $action The action name to create nonce from.
	 * @return string The created nonce.
	 */
	public static function create_nonce( $action = 'wp_rest' ) {
		return wp_create_nonce( $action );
	}

	/**
	 * Verify nonce.
	 * 
	 * @param array $request_fields
	 * @param string $action The action to verify nonce
	 * @param string $request_method
	 * @param string $nonce_field
	 * @return boolean
	 */
	public static function verify_nonce( $request_fields, $action = null, $request_method = 'POST', $nonce_field = '_canonce' ) {
		$verified = false;
		
		if ( empty( $action ) ) {
			$action = !empty( $request_fields[ 'action' ] ) ? $request_fields[ 'action' ] : '';
		}
		if ( !empty( $request_fields[ $nonce_field ] ) && wp_verify_nonce( $request_fields[ $nonce_field ], $action ) ) {
			$verified = true;
		}
		
		return $verified;
	}

	/**
	 * Verify required fields aren't empty.
	 *
	 * @param string[] $required The array of fields to validate.
	 * @param string $request_fields fields to test required.
	 * @param bool $not_empty if true use empty method, else use isset method.
	 * @return bool True all fields are validated
	 */
	public static function validate_req_fields( $required, $request_fields = array(), $not_empty = true ) {
		$validated = true;
		
		foreach ( $required as $field ) {
			if ( $not_empty ) {
				if ( empty( $request_fields[ $field ] ) ) {
					$validated = false;
				}
			}
			else {
				if ( !isset( $request_fields[ $field ] ) ) {
					$validated = false;
				}
			}
		}
		
		return $validated;
	}

	/**
	 * Get the current page url.
	 *
	 * @return string The url.
	 */
	public static function get_current_page_url( $force_ssl = false, $domain_mapping = true ) {
		$current_page_url = 'http://';
		$server_name = !empty( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];
		
		if ( $force_ssl || 'on' == @$_SERVER[ 'HTTPS' ] ) {
			$current_page_url = 'https://';
		}
		
		if ( !in_array( $_SERVER[ 'SERVER_PORT' ], array( '80','433' ) ) ) {
			$current_page_url .= $server_name . ':' . $_SERVER[ 'SERVER_PORT' ] . $_SERVER[ 'REQUEST_URI' ];
		}
		else {
			$current_page_url .= $server_name . $_SERVER[ 'REQUEST_URI' ];
		}
		
		if ( $domain_mapping && function_exists( 'domain_mapping_post_content' ) ) {
			$current_page_url = domain_mapping_post_content( $current_page_url );
		}
		
		return $current_page_url;
	}

	public static function get_current_domain_url( $force_ssl = false, $domain_mapping = true ) {
		$url = 'http://';
		
		if ( $force_ssl || 'on' == @$_SERVER[ 'HTTPS' ] ) {
			$url = 'https://';
		}
		
		$url .= !empty( $_SERVER[ 'HTTP_HOST' ] ) ? $_SERVER[ 'HTTP_HOST' ] : $_SERVER[ 'SERVER_NAME' ];
		$url .= '/';
		
		if ( $domain_mapping && function_exists( 'domain_mapping_post_content' ) ) {
			$url = domain_mapping_post_content( $url );
		}
		
		return $url;
	}

	/**
	 * Get current domain.
	 *
	 * @return string domain name.
	 */
	public static function get_current_domain() {
		return @$_SERVER[ 'SERVER_NAME' ];
	}

	/**
	 * Get referrer url.
	 *
	 * @return string
	 */
	public static function get_referer() {
		return @$_SERVER[ 'HTTP_REFERER' ];
	}

	/**
	 * Replace http protocol to https
	 *
	 * @param string $url the original url
	 * @return string The changed url.
	 */
	public static function get_ssl_url( $url, $domain_mapping = true ) {
		if ( $domain_mapping && function_exists( 'domain_mapping_post_content' ) ) {
			$url = domain_mapping_post_content( $url );
		}
		
		$url = apply_filters( 'wd_helper_utility_get_ssl_url', preg_replace( '|^http://|', 'https://', $url ), $url );
		
		return $url;
	}

	/**
	 * Returns the *correct* home-url for front-end pages of a given site.
	 *
	 * {@see description of home_url above for details}
	 *
	 * @param int $blog_id Blog-ID; by default the current blog is used.
	 * @param string $path Argument passed to the home_url() function.
	 * @return string The correct URL for a front-end page.
	 */
	public static function get_home_url( $blog_id = null, $path = '' ) {
		$schema = is_ssl() ? 'https' : 'http';
		$url = get_home_url( $blog_id, $path, $schema );
		
		return apply_filters( 'wd_helper_utility_get_home_url', $url, $blog_id, $path, $schema );
	}

	/**
	 * Returns user IP address.
	 *
	 * @return string Remote IP address on success, otherwise FALSE.
	 */
	public static function get_remote_ip() {
		$flag = !WP_DEBUG ? FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE : null;
		$keys = array( 'HTTP_CLIENT_IP','HTTP_X_FORWARDED_FOR','HTTP_X_FORWARDED','HTTP_X_CLUSTER_CLIENT_IP','HTTP_FORWARDED_FOR','HTTP_FORWARDED',
				'REMOTE_ADDR' );
		
		$remote_ip = false;
		foreach ( $keys as $key ) {
			if ( !empty( $_SERVER[ $key ] ) ) {
				foreach ( array_filter( array_map( 'trim', explode( ',', $_SERVER[ $key ] ) ) ) as $ip ) {
					if ( filter_var( $ip, FILTER_VALIDATE_IP, $flag ) !== false ) {
						$remote_ip = $ip;
						break 2;
					}
					if ( in_array( $ip, array( '127.0.0.1','::1' ) ) ) {
						$remote_ip = $ip;
						break 2;
					}
				}
			}
		}
		
		return $remote_ip;
	}

	/**
	 * Verify if is localhost.
	 * 
	 * @return boolean
	 */
	public static function is_localhost() {
		$localhost = array( '127.0.0.1','::1' );
		$ip = self::get_remote_ip();
		$is_localhost = false;
		
		if ( in_array( $ip, $localhost ) ) {
			$is_localhost = true;
		}
		
		if ( empty( $ip ) ) {
			$is_localhost = true;
		}
		
		return $is_localhost;
	}

	/**
	 * Check if a given ip is in a network
	 *
	 * @param string $ip IP to check in IPV4 format eg. 127.0.0.1
	 * @param string $range IP/CIDR netmask eg. 127.0.0.0/24, also 127.0.0.1 is accepted and /32 assumed
	 * @return boolean true if the ip is in this range / false if not.
	 */
	public static function ip_in_range( $ip, $range ) {
		$ip_in_range = false;
		
		if ( strpos( $range, '/' ) == false ) {
			$range .= '/32';
		}
		
		// $range is in IP/CIDR format eg 127.0.0.1/24
		list ( $range, $netmask ) = explode( '/', $range, 2 );
		$range_decimal = ip2long( $range );
		$ip_decimal = ip2long( $ip );
		$wildcard_decimal = pow( 2, ( 32 - $netmask ) ) - 1;
		$netmask_decimal = ~$wildcard_decimal;
		
		$ip_in_range = ( ( $ip_decimal & $netmask_decimal ) == ( $range_decimal & $netmask_decimal ) );
		
		return $ip_in_range;
	}

	/**
	 * Get browser user agent.
	 * 
	 * @return string
	 */
	public static function get_user_agent() {
		$user_agent = "";
		$user_agent = @$_SERVER[ 'HTTP_USER_AGENT' ];
		return $user_agent;
	}

	/**
	 * Verify if is a mobile request.
	 * 
	 * @return boolean
	 */
	public static function is_mobile() {
		$regexp = "/(android|avantgo|blackberry|bolt|boost|cricket|docomo|fone|hiptop|mini|mobi|palm|phone|pie|tablet|up\.browser|up\.link|webos|wos)/i";
		return preg_match( $regexp, $_SERVER[ "HTTP_USER_AGENT" ] );
	}

	/**
	 * Register custom post type.
	 * 
	 * @param string $post_type
	 * @param array $args
	 */
	public static function register_post_type( $post_type, $args = null ) {
		$defaults = array( 'public' => false,'has_archive' => false,'publicly_queryable' => false,'supports' => false,
				// 'capability_type' => apply_filters( $post_type, '_capability', 'page' ),
				'hierarchical' => false );
		
		$args = wp_parse_args( $args, $defaults );
		
		register_post_type( $post_type, $args );
	}

	/**
	 * Register custom taxonomy.
	 * 
	 * @param string $taxonomy
	 * @param string $post_type
	 * @param array $args
	 */
	public static function register_taxonomy( $taxonomy, $post_type, $args = null ) {
		$defaults = array( 'public' => true,'publicly_queryable' => true,'show_ui' => true,'show_in_menu' => true,'show_in_nav_menus' => true,
				'show_in_quick_edit' => true,'show_admin_column' => true,'hierarchical' => false,'labels' => array( 'name' => $taxonomy ) );
		
		$args = wp_parse_args( $args, $defaults );
		
		register_taxonomy( $taxonomy, $post_type, $args );
	}

	/**
	 * Verify required fields aren't empty.
	 *
	 * @param string[] $fields The array of fields to validate.
	 * @param string $request_method POST or GET
	 * @param bool $not_empty if true use empty method, else use isset method.
	 * @return bool True all fields are validated
	 */
	public static function validate_required( $fields, $request_method = 'POST', $not_empty = true, $request_fields = null ) {
		$validated = true;
		
		if ( empty( $request_fields ) ) {
			switch ( $request_method ) {
				case 'GET' :
					$request_fields = $_GET;
					break;
				
				case 'REQUEST' :
					$request_fields = $_REQUEST;
					break;
				
				default :
				case 'POST' :
					$request_fields = $_POST;
					break;
			}
		}
		
		foreach ( $fields as $field ) {
			if ( $not_empty ) {
				if ( empty( $request_fields[ $field ] ) ) {
					$validated = false;
				}
			}
			else {
				if ( !isset( $request_fields[ $field ] ) ) {
					$validated = false;
				}
			}
		}
		
		return $validated;
	}

	/**
	 * Get field from request parameters.
	 *
	 * @param string $id The field ID
	 * @param mixed $default The default value of the field.
	 * @param string $request_method POST or GET
	 * @return mixed The value of the request field.
	 */
	public static function get_request_field( $id, $default = '', $request_method = 'POST' ) {
		$value = $default;
		$request_fields = null;
		
		switch ( $request_method ) {
			case 'GET' :
				$request_fields = $_GET;
				break;
			
			case 'REQUEST' :
				$request_fields = $_REQUEST;
				break;
			
			default :
			case 'POST' :
				$request_fields = $_POST;
				break;
		}
		
		if ( isset( $request_fields[ $id ] ) ) {
			$value = $request_fields[ $id ];
		}
		
		return apply_filters( 'wd_helper_get_request_field', $value, $id, $default );
	}

	/**
	 * Get field from GET request.
	 *
	 * @param string $id The request field param id.
	 * @param string $default The default value.
	 * @return string
	 */
	public static function get_request_param( $id, $default = '' ) {
		return self::get_request_field( $id, $default, 'GET' );
	}

	/**
	 * Search files in folder.
	 * 
	 * @param unknown $search_dir The search folder.
	 * @param unknown $base_dir The base dir 
	 * @param string $mask
	 * @param string $recursive
	 * @return string[]
	 */
	public static function search_files( $search_dir, $base_dir, $mask = '*.*', $recursive = true ) {
		static $stack = array();
		$files = array();
		$ignore = array( '.svn','.git','.DS_Store','CVS','Thumbs.db','desktop.ini' );
		
		$dir = @opendir( $search_dir );
		
		if ( $dir ) {
			while ( ( $entry = @readdir( $dir ) ) !== false ) {
				if ( $entry != '.' && $entry != '..' && !in_array( $entry, $ignore ) ) {
					$path = $search_dir . '/' . $entry;
					
					if ( @is_dir( $path ) && $recursive ) {
						array_push( $stack, $entry );
						$files = array_merge( $files, self::search_files( $path, $base_dir, $mask, $recursive ) );
						array_pop( $stack );
					}
					else {
						$regexp = '~^(' . self::get_regexp_by_mask( $mask ) . ')$~i';
						
						if ( preg_match( $regexp, $entry ) ) {
							$files[] = ( $base_dir != '' ? $base_dir . '/' : '' ) . ( ( $p = implode( '/', $stack ) ) != '' ? $p . '/' : '' ) . $entry;
						}
					}
				}
			}
			
			@closedir( $dir );
		}
		
		return $files;
	}

	/**
	 * Default price format BR.
	 * 
	 * @param float $price The price to format.
	 * @param string $currency The currency symbol.
	 * @return string
	 */
	public static function format_price( $price, $currency = 'R$' ) {
		return apply_filters( 'wd_helper_util_format_price', sprintf( '%s %s', $currency, number_format_i18n( $price, 2, ',', '' ) ) );
	}

	/**
	 * Default price format BR.
	 *
	 * @param float $price The price to format.
	 * @param string $currency The currency symbol.
	 * @return string
	 */
	public static function format_price_freq( $price, $frequency, $currency = 'R$' ) {
		$period = __( 'mês', WPPDEV_TXT_DM );
		
		switch ( $frequency ) {
			case Helper_Period::FREQ_TYPE_WEEKLY:
				$period = __( 'semana', WPPDEV_TXT_DM );
				break;
			case Helper_Period::FREQ_TYPE_MONTHLY:
				$period = __( 'mês', WPPDEV_TXT_DM );
				break;
			case Helper_Period::FREQ_TYPE_BIMONTHLY:
				$period = __( 'bimestre', WPPDEV_TXT_DM );
				break;
			case Helper_Period::FREQ_TYPE_QUARTERLY:
				$period = __( 'trimestre', WPPDEV_TXT_DM );
				break;
			case Helper_Period::FREQ_TYPE_SEMESTERLY:
				$period = __( 'semestre', WPPDEV_TXT_DM );
				break;
			case Helper_Period::FREQ_TYPE_ANNUALLY:
				$period = __( 'ano', WPPDEV_TXT_DM );
				break;
		}
		
		$price = self::format_price( $price, $currency ) . ' / ' . $period;
		return apply_filters( 'wd_helper_util_format_price_freq', $price );
	}
	
	/**
	 * compressing the file with the bzip2-extension
	 *
	 * @return bool
	 * @param string $in
	 * @param string $out
	 */
	function bzip2( $in, $out ) {
		if ( !file_exists( $in ) || !is_readable( $in ) )
			return false;
		if ( ( !file_exists( $out ) && !is_writeable( dirname( $out ) ) || ( file_exists( $out ) && !is_writable( $out ) ) ) )
			return false;
		
		$in_file = fopen( $in, "rb" );
		$out_file = bzopen( $out, "wb" );
		
		while ( !feof( $in_file ) ) {
			$buffer = fgets( $in_file, 4096 );
			bzwrite( $out_file, $buffer, 4096 );
		}
		
		fclose( $in_file );
		bzclose( $out_file );
		
		return true;
	}

	/**
	 * uncompressing the file with the bzip2-extension
	 *
	 * @return bool
	 * @param string $in
	 * @param string $out
	 */
	function bunzip2( $in, $out ) {
		if ( !file_exists( $in ) || !is_readable( $in ) )
			return false;
		if ( ( !file_exists( $out ) && !is_writeable( dirname( $out ) ) || ( file_exists( $out ) && !is_writable( $out ) ) ) )
			return false;
		
		if ( function_exists( 'bzopen' ) ) {
			$in_file = bzopen( $in, "r" );
			$out_file = fopen( $out, "w" );
			
			while ( $buffer = bzread( $in_file, 4096 ) ) {
				fwrite( $out_file, $buffer, 4096 );
			}
			
			bzclose( $in_file );
			fclose( $out_file );
			return true;
		}
		else {
			return false;
		}
	}

	/**
	 * Unzip file.
	 * @param string $in The full filename path to read from.
	 * @param unknown $out The full filename path to output.
	 * @return boolean
	 */
	public function unzip( $in, $out ) {
		$zip = new ZipArchive();
		if ( $zip->open( $in ) === TRUE ) {
			$zip->extractTo( $out );
			$zip->close();
			return true;
		}
		return false;
	}

	/**
	 * Create a xml from an array.
	 * 
	 * @param array $data
	 * @param string $xml_data
	 * @return unknown
	 */
	public static function array_to_xml( $data, $xml_data ) {
		foreach ( $data as $key => $value ) {
			if ( is_numeric( $key ) ) {
				$key = 'item' . $key; // dealing with <0/>..<n/> issues
			}
			if ( is_array( $value ) ) {
				$subnode = $xml_data->addChild( $key );
				$subnode = self::array_to_xml( $value, $subnode );
			}
			else {
				$xml_data->addChild( "$key", htmlspecialchars( "$value" ) );
			}
		}
		return $xml_data;
	}

	public static function insert_into_array( 
		$array, 
		$search_key, 
		$insert_key, 
		$insert_value, 
		$insert_after_found_key = true, 
		$append_if_not_found = false ) 
		{

        $new_array = array();

        foreach( $array as $key => $value ){

            // INSERT BEFORE THE CURRENT KEY? 
            // ONLY IF CURRENT KEY IS THE KEY WE ARE SEARCHING FOR, AND WE WANT TO INSERT BEFORE THAT FOUNDED KEY
            if( $key === $search_key && ! $insert_after_found_key ) {
                $new_array[ $insert_key ] = $insert_value;
			}

            // COPY THE CURRENT KEY/VALUE FROM OLD ARRAY TO A NEW ARRAY
            $new_array[ $key ] = $value;

            // INSERT AFTER THE CURRENT KEY? 
            // ONLY IF CURRENT KEY IS THE KEY WE ARE SEARCHING FOR, AND WE WANT TO INSERT AFTER THAT FOUNDED KEY
            if( $key === $search_key && $insert_after_found_key ) {
                $new_array[ $insert_key ] = $insert_value;
			}

        }

        // APPEND IF KEY ISNT FOUND
        if( $append_if_not_found && count( $array ) == count( $new_array ) ) {
            $new_array[ $insert_key ] = $insert_value;
		}

        return $new_array;

    }
	
	public static function get_state_options() {
		return array(
				"" => "UF", "AC" => "AC","AL" => "AL","AM" => "AM","AP" => "AP","BA" => "BA","CE" => "CE", "DF" => "DF","ES" => "ES","GO" => "GO","MA" => "MA",
				"MG" => "MG","MS" => "MS","MT" => "MT","PA" => "PA","PB" => "PB","PE" => "PE","PI" => "PI", "PR" => "PR","RJ" => "RJ","RN" => "RN",
				"RO" => "RO","RR" => "RR","RS" => "RS","SC" => "SC","SE" => "SE","SP" => "SP","TO" => "TO");
	}
	
	public static function get_month_options() {
		
		$months = array( '' => 'mês' );
		
		for ( $i = 1; $i <= 12; $i++ ) {
			$months[ $i ] = sprintf( "%02d", $i );
		}
		
		return $months;
	}
	
	public static function get_year_options() {
		$years = array( '' => 'ano' );
		
		$current_year = date( 'Y' );
		for ( $i = $current_year; $i <= ( $current_year + 20 ); $i++ ) {
			$years[ $i ] = $i;
		}
		
		return $years;
	}
	
	public static function get_array_keys( $array ) {
		$keys = array_keys( $array );
		return array_combine( $keys, $keys );
	}
	
	/**
	 * Appends an item to the beginning of an associative array while preserving
	 * the array keys.
	 *
	 * @since 1.0.3
	 * @param array $arr
	 * @param scalar $key
	 * @param mixed $val
	 * @return array
	 */
	public function array_unshift_assoc( &$arr, $key, $val ) {
		$arr = array_reverse( $arr, true );
		$arr[ $key ] = $val;
		return array_reverse( $arr, true );
	}
	
	/**
	 * Get countries code and names.
	 *
	 * @since 1.0.0
	 *
	 * @return array {
	 *         Returns array of ( $code => $name ).
	 *         @type string $code The country code.
	 *         @type string $name The country name.
	 *         }
	 */
	public function get_country_codes() {
		$countries = array(
				'' => __( 'Select country', WPPDEV_TXT_DM ),
				'AX' => __( 'ÃLAND ISLANDS', WPPDEV_TXT_DM ),
				'AL' => __( 'ALBANIA', WPPDEV_TXT_DM ),
				'DZ' => __( 'ALGERIA', WPPDEV_TXT_DM ),
				'AS' => __( 'AMERICAN SAMOA', WPPDEV_TXT_DM ),
				'AD' => __( 'ANDORRA', WPPDEV_TXT_DM ),
				'AI' => __( 'ANGUILLA', WPPDEV_TXT_DM ),
				'AQ' => __( 'ANTARCTICA', WPPDEV_TXT_DM ),
				'AG' => __( 'ANTIGUA AND BARBUDA', WPPDEV_TXT_DM ),
				'AR' => __( 'ARGENTINA', WPPDEV_TXT_DM ),
				'AM' => __( 'ARMENIA', WPPDEV_TXT_DM ),
				'AW' => __( 'ARUBA', WPPDEV_TXT_DM ),
				'AU' => __( 'AUSTRALIA', WPPDEV_TXT_DM ),
				'AT' => __( 'AUSTRIA', WPPDEV_TXT_DM ),
				'AZ' => __( 'AZERBAIJAN', WPPDEV_TXT_DM ),
				'BS' => __( 'BAHAMAS', WPPDEV_TXT_DM ),
				'BH' => __( 'BAHRAIN', WPPDEV_TXT_DM ),
				'BD' => __( 'BANGLADESH', WPPDEV_TXT_DM ),
				'BB' => __( 'BARBADOS', WPPDEV_TXT_DM ),
				'BE' => __( 'BELGIUM', WPPDEV_TXT_DM ),
				'BZ' => __( 'BELIZE', WPPDEV_TXT_DM ),
				'BJ' => __( 'BENIN', WPPDEV_TXT_DM ),
				'BM' => __( 'BERMUDA', WPPDEV_TXT_DM ),
				'BT' => __( 'BHUTAN', WPPDEV_TXT_DM ),
				'BA' => __( 'BOSNIA-HERZEGOVINA', WPPDEV_TXT_DM ),
				'BW' => __( 'BOTSWANA', WPPDEV_TXT_DM ),
				'BV' => __( 'BOUVET ISLAND', WPPDEV_TXT_DM ),
				'BR' => __( 'BRAZIL', WPPDEV_TXT_DM ),
				'IO' => __( 'BRITISH INDIAN OCEAN TERRITORY', WPPDEV_TXT_DM ),
				'BN' => __( 'BRUNEI DARUSSALAM', WPPDEV_TXT_DM ),
				'BG' => __( 'BULGARIA', WPPDEV_TXT_DM ),
				'BF' => __( 'BURKINA FASO', WPPDEV_TXT_DM ),
				'CA' => __( 'CANADA', WPPDEV_TXT_DM ),
				'CV' => __( 'CAPE VERDE', WPPDEV_TXT_DM ),
				'KY' => __( 'CAYMAN ISLANDS', WPPDEV_TXT_DM ),
				'CF' => __( 'CENTRAL AFRICAN REPUBLIC', WPPDEV_TXT_DM ),
				'CL' => __( 'CHILE', WPPDEV_TXT_DM ),
				'CN' => __( 'CHINA', WPPDEV_TXT_DM ),
				'CX' => __( 'CHRISTMAS ISLAND', WPPDEV_TXT_DM ),
				'CC' => __( 'COCOS (KEELING) ISLANDS', WPPDEV_TXT_DM ),
				'CO' => __( 'COLOMBIA', WPPDEV_TXT_DM ),
				'CK' => __( 'COOK ISLANDS', WPPDEV_TXT_DM ),
				'CR' => __( 'COSTA RICA', WPPDEV_TXT_DM ),
				'CY' => __( 'CYPRUS', WPPDEV_TXT_DM ),
				'CZ' => __( 'CZECH REPUBLIC', WPPDEV_TXT_DM ),
				'DK' => __( 'DENMARK', WPPDEV_TXT_DM ),
				'DJ' => __( 'DJIBOUTI', WPPDEV_TXT_DM ),
				'DM' => __( 'DOMINICA', WPPDEV_TXT_DM ),
				'DO' => __( 'DOMINICAN REPUBLIC', WPPDEV_TXT_DM ),
				'EC' => __( 'ECUADOR', WPPDEV_TXT_DM ),
				'EG' => __( 'EGYPT', WPPDEV_TXT_DM ),
				'SV' => __( 'EL SALVADOR', WPPDEV_TXT_DM ),
				'EE' => __( 'ESTONIA', WPPDEV_TXT_DM ),
				'FK' => __( 'FALKLAND ISLANDS (MALVINAS)', WPPDEV_TXT_DM ),
				'FO' => __( 'FAROE ISLANDS', WPPDEV_TXT_DM ),
				'FJ' => __( 'FIJI', WPPDEV_TXT_DM ),
				'FI' => __( 'FINLAND', WPPDEV_TXT_DM ),
				'FR' => __( 'FRANCE', WPPDEV_TXT_DM ),
				'GF' => __( 'FRENCH GUIANA', WPPDEV_TXT_DM ),
				'PF' => __( 'FRENCH POLYNESIA', WPPDEV_TXT_DM ),
				'TF' => __( 'FRENCH SOUTHERN TERRITORIES', WPPDEV_TXT_DM ),
				'GA' => __( 'GABON', WPPDEV_TXT_DM ),
				'GM' => __( 'GAMBIA', WPPDEV_TXT_DM ),
				'GE' => __( 'GEORGIA', WPPDEV_TXT_DM ),
				'DE' => __( 'GERMANY', WPPDEV_TXT_DM ),
				'GH' => __( 'GHANA', WPPDEV_TXT_DM ),
				'GI' => __( 'GIBRALTAR', WPPDEV_TXT_DM ),
				'GR' => __( 'GREECE', WPPDEV_TXT_DM ),
				'GL' => __( 'GREENLAND', WPPDEV_TXT_DM ),
				'GD' => __( 'GRENADA', WPPDEV_TXT_DM ),
				'GP' => __( 'GUADELOUPE', WPPDEV_TXT_DM ),
				'GU' => __( 'GUAM', WPPDEV_TXT_DM ),
				'GG' => __( 'GUERNSEY', WPPDEV_TXT_DM ),
				'GY' => __( 'GUYANA', WPPDEV_TXT_DM ),
				'HM' => __( 'HEARD ISLAND AND MCDONALD ISLANDS', WPPDEV_TXT_DM ),
				'VA' => __( 'HOLY SEE (VATICAN CITY STATE)', WPPDEV_TXT_DM ),
				'HN' => __( 'HONDURAS', WPPDEV_TXT_DM ),
				'HK' => __( 'HONG KONG', WPPDEV_TXT_DM ),
				'HU' => __( 'HUNGARY', WPPDEV_TXT_DM ),
				'IS' => __( 'ICELAND', WPPDEV_TXT_DM ),
				'IN' => __( 'INDIA', WPPDEV_TXT_DM ),
				'ID' => __( 'INDONESIA', WPPDEV_TXT_DM ),
				'IE' => __( 'IRELAND', WPPDEV_TXT_DM ),
				'IM' => __( 'ISLE OF MAN', WPPDEV_TXT_DM ),
				'IL' => __( 'ISRAEL', WPPDEV_TXT_DM ),
				'IT' => __( 'ITALY', WPPDEV_TXT_DM ),
				'JM' => __( 'JAMAICA', WPPDEV_TXT_DM ),
				'JP' => __( 'JAPAN', WPPDEV_TXT_DM ),
				'JE' => __( 'JERSEY', WPPDEV_TXT_DM ),
				'JO' => __( 'JORDAN', WPPDEV_TXT_DM ),
				'KZ' => __( 'KAZAKHSTAN', WPPDEV_TXT_DM ),
				'KI' => __( 'KIRIBATI', WPPDEV_TXT_DM ),
				'KR' => __( 'KOREA, REPUBLIC OF', WPPDEV_TXT_DM ),
				'KW' => __( 'KUWAIT', WPPDEV_TXT_DM ),
				'KG' => __( 'KYRGYZSTAN', WPPDEV_TXT_DM ),
				'LV' => __( 'LATVIA', WPPDEV_TXT_DM ),
				'LS' => __( 'LESOTHO', WPPDEV_TXT_DM ),
				'LI' => __( 'LIECHTENSTEIN', WPPDEV_TXT_DM ),
				'LT' => __( 'LITHUANIA', WPPDEV_TXT_DM ),
				'LU' => __( 'LUXEMBOURG', WPPDEV_TXT_DM ),
				'MO' => __( 'MACAO', WPPDEV_TXT_DM ),
				'MK' => __( 'MACEDONIA', WPPDEV_TXT_DM ),
				'MG' => __( 'MADAGASCAR', WPPDEV_TXT_DM ),
				'MW' => __( 'MALAWI', WPPDEV_TXT_DM ),
				'MY' => __( 'MALAYSIA', WPPDEV_TXT_DM ),
				'MT' => __( 'MALTA', WPPDEV_TXT_DM ),
				'MH' => __( 'MARSHALL ISLANDS', WPPDEV_TXT_DM ),
				'MQ' => __( 'MARTINIQUE', WPPDEV_TXT_DM ),
				'MR' => __( 'MAURITANIA', WPPDEV_TXT_DM ),
				'MU' => __( 'MAURITIUS', WPPDEV_TXT_DM ),
				'YT' => __( 'MAYOTTE', WPPDEV_TXT_DM ),
				'MX' => __( 'MEXICO', WPPDEV_TXT_DM ),
				'FM' => __( 'MICRONESIA, FEDERATED STATES OF', WPPDEV_TXT_DM ),
				'MD' => __( 'MOLDOVA, REPUBLIC OF', WPPDEV_TXT_DM ),
				'MC' => __( 'MONACO', WPPDEV_TXT_DM ),
				'MN' => __( 'MONGOLIA', WPPDEV_TXT_DM ),
				'ME' => __( 'MONTENEGRO', WPPDEV_TXT_DM ),
				'MS' => __( 'MONTSERRAT', WPPDEV_TXT_DM ),
				'MA' => __( 'MOROCCO', WPPDEV_TXT_DM ),
				'MZ' => __( 'MOZAMBIQUE', WPPDEV_TXT_DM ),
				'NA' => __( 'NAMIBIA', WPPDEV_TXT_DM ),
				'NR' => __( 'NAURU', WPPDEV_TXT_DM ),
				'NP' => __( 'NEPAL', WPPDEV_TXT_DM ),
				'NL' => __( 'NETHERLANDS', WPPDEV_TXT_DM ),
				'AN' => __( 'NETHERLANDS ANTILLES', WPPDEV_TXT_DM ),
				'NC' => __( 'NEW CALEDONIA', WPPDEV_TXT_DM ),
				'NZ' => __( 'NEW ZEALAND', WPPDEV_TXT_DM ),
				'NI' => __( 'NICARAGUA', WPPDEV_TXT_DM ),
				'NE' => __( 'NIGER', WPPDEV_TXT_DM ),
				'NU' => __( 'NIUE', WPPDEV_TXT_DM ),
				'NF' => __( 'NORFOLK ISLAND', WPPDEV_TXT_DM ),
				'MP' => __( 'NORTHERN MARIANA ISLANDS', WPPDEV_TXT_DM ),
				'NO' => __( 'NORWAY', WPPDEV_TXT_DM ),
				'OM' => __( 'OMAN', WPPDEV_TXT_DM ),
				'PW' => __( 'PALAU', WPPDEV_TXT_DM ),
				'PS' => __( 'PALESTINE', WPPDEV_TXT_DM ),
				'PA' => __( 'PANAMA', WPPDEV_TXT_DM ),
				'PY' => __( 'PARAGUAY', WPPDEV_TXT_DM ),
				'PE' => __( 'PERU', WPPDEV_TXT_DM ),
				'PH' => __( 'PHILIPPINES', WPPDEV_TXT_DM ),
				'PN' => __( 'PITCAIRN', WPPDEV_TXT_DM ),
				'PL' => __( 'POLAND', WPPDEV_TXT_DM ),
				'PT' => __( 'PORTUGAL', WPPDEV_TXT_DM ),
				'PR' => __( 'PUERTO RICO', WPPDEV_TXT_DM ),
				'QA' => __( 'QATAR', WPPDEV_TXT_DM ),
				'RE' => __( 'REUNION', WPPDEV_TXT_DM ),
				'RO' => __( 'ROMANIA', WPPDEV_TXT_DM ),
				'RU' => __( 'RUSSIAN FEDERATION', WPPDEV_TXT_DM ),
				'RW' => __( 'RWANDA', WPPDEV_TXT_DM ),
				'SH' => __( 'SAINT HELENA', WPPDEV_TXT_DM ),
				'KN' => __( 'SAINT KITTS AND NEVIS', WPPDEV_TXT_DM ),
				'LC' => __( 'SAINT LUCIA', WPPDEV_TXT_DM ),
				'PM' => __( 'SAINT PIERRE AND MIQUELON', WPPDEV_TXT_DM ),
				'VC' => __( 'SAINT VINCENT AND THE GRENADINES', WPPDEV_TXT_DM ),
				'WS' => __( 'SAMOA', WPPDEV_TXT_DM ),
				'SM' => __( 'SAN MARINO', WPPDEV_TXT_DM ),
				'ST' => __( 'SAO TOME AND PRINCIPE', WPPDEV_TXT_DM ),
				'SA' => __( 'SAUDI ARABIA', WPPDEV_TXT_DM ),
				'SN' => __( 'SENEGAL', WPPDEV_TXT_DM ),
				'RS' => __( 'SERBIA', WPPDEV_TXT_DM ),
				'SC' => __( 'SEYCHELLES', WPPDEV_TXT_DM ),
				'SG' => __( 'SINGAPORE', WPPDEV_TXT_DM ),
				'SK' => __( 'SLOVAKIA', WPPDEV_TXT_DM ),
				'SI' => __( 'SLOVENIA', WPPDEV_TXT_DM ),
				'SB' => __( 'SOLOMON ISLANDS', WPPDEV_TXT_DM ),
				'ZA' => __( 'SOUTH AFRICA', WPPDEV_TXT_DM ),
				'GS' => __( 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', WPPDEV_TXT_DM ),
				'ES' => __( 'SPAIN', WPPDEV_TXT_DM ),
				'SR' => __( 'SURINAME', WPPDEV_TXT_DM ),
				'SJ' => __( 'SVALBARD AND JAN MAYEN', WPPDEV_TXT_DM ),
				'SZ' => __( 'SWAZILAND', WPPDEV_TXT_DM ),
				'SE' => __( 'SWEDEN', WPPDEV_TXT_DM ),
				'CH' => __( 'SWITZERLAND', WPPDEV_TXT_DM ),
				'TW' => __( 'TAIWAN, PROVINCE OF CHINA', WPPDEV_TXT_DM ),
				'TZ' => __( 'TANZANIA, UNITED REPUBLIC OF', WPPDEV_TXT_DM ),
				'TH' => __( 'THAILAND', WPPDEV_TXT_DM ),
				'TL' => __( 'TIMOR-LESTE', WPPDEV_TXT_DM ),
				'TG' => __( 'TOGO', WPPDEV_TXT_DM ),
				'TK' => __( 'TOKELAU', WPPDEV_TXT_DM ),
				'TO' => __( 'TONGA', WPPDEV_TXT_DM ),
				'TT' => __( 'TRINIDAD AND TOBAGO', WPPDEV_TXT_DM ),
				'TN' => __( 'TUNISIA', WPPDEV_TXT_DM ),
				'TR' => __( 'TURKEY', WPPDEV_TXT_DM ),
				'TM' => __( 'TURKMENISTAN', WPPDEV_TXT_DM ),
				'TC' => __( 'TURKS AND CAICOS ISLANDS', WPPDEV_TXT_DM ),
				'TV' => __( 'TUVALU', WPPDEV_TXT_DM ),
				'UG' => __( 'UGANDA', WPPDEV_TXT_DM ),
				'UA' => __( 'UKRAINE', WPPDEV_TXT_DM ),
				'AE' => __( 'UNITED ARAB EMIRATES', WPPDEV_TXT_DM ),
				'GB' => __( 'UNITED KINGDOM', WPPDEV_TXT_DM ),
				'US' => __( 'UNITED STATES', WPPDEV_TXT_DM ),
				'UM' => __( 'UNITED STATES MINOR OUTLYING ISLANDS', WPPDEV_TXT_DM ),
				'UY' => __( 'URUGUAY', WPPDEV_TXT_DM ),
				'UZ' => __( 'UZBEKISTAN', WPPDEV_TXT_DM ),
				'VU' => __( 'VANUATU', WPPDEV_TXT_DM ),
				'VE' => __( 'VENEZUELA', WPPDEV_TXT_DM ),
				'VN' => __( 'VIET NAM', WPPDEV_TXT_DM ),
				'VG' => __( 'VIRGIN ISLANDS, BRITISH', WPPDEV_TXT_DM ),
				'VI' => __( 'VIRGIN ISLANDS, U.S.', WPPDEV_TXT_DM ),
				'WF' => __( 'WALLIS AND FUTUNA', WPPDEV_TXT_DM ),
				'EH' => __( 'WESTERN SAHARA', WPPDEV_TXT_DM ),
				'ZM' => __( 'ZAMBIA', WPPDEV_TXT_DM ) );
		
		return apply_filters( 'wd_helper_util_get_country_codes', $countries );
	}
}
