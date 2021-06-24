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
		$period = __( 'mês', WD_TEXT_DOMAIN );
		
		switch ( $frequency ) {
			case Helper_Period::FREQ_TYPE_WEEKLY:
				$period = __( 'semana', WD_TEXT_DOMAIN );
				break;
			case Helper_Period::FREQ_TYPE_MONTHLY:
				$period = __( 'mês', WD_TEXT_DOMAIN );
				break;
			case Helper_Period::FREQ_TYPE_BIMONTHLY:
				$period = __( 'bimestre', WD_TEXT_DOMAIN );
				break;
			case Helper_Period::FREQ_TYPE_QUARTERLY:
				$period = __( 'trimestre', WD_TEXT_DOMAIN );
				break;
			case Helper_Period::FREQ_TYPE_SEMESTERLY:
				$period = __( 'semestre', WD_TEXT_DOMAIN );
				break;
			case Helper_Period::FREQ_TYPE_ANNUALLY:
				$period = __( 'ano', WD_TEXT_DOMAIN );
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
				'' => __( 'Select country', WD_TEXT_DOMAIN ),
				'AX' => __( 'ÃLAND ISLANDS', WD_TEXT_DOMAIN ),
				'AL' => __( 'ALBANIA', WD_TEXT_DOMAIN ),
				'DZ' => __( 'ALGERIA', WD_TEXT_DOMAIN ),
				'AS' => __( 'AMERICAN SAMOA', WD_TEXT_DOMAIN ),
				'AD' => __( 'ANDORRA', WD_TEXT_DOMAIN ),
				'AI' => __( 'ANGUILLA', WD_TEXT_DOMAIN ),
				'AQ' => __( 'ANTARCTICA', WD_TEXT_DOMAIN ),
				'AG' => __( 'ANTIGUA AND BARBUDA', WD_TEXT_DOMAIN ),
				'AR' => __( 'ARGENTINA', WD_TEXT_DOMAIN ),
				'AM' => __( 'ARMENIA', WD_TEXT_DOMAIN ),
				'AW' => __( 'ARUBA', WD_TEXT_DOMAIN ),
				'AU' => __( 'AUSTRALIA', WD_TEXT_DOMAIN ),
				'AT' => __( 'AUSTRIA', WD_TEXT_DOMAIN ),
				'AZ' => __( 'AZERBAIJAN', WD_TEXT_DOMAIN ),
				'BS' => __( 'BAHAMAS', WD_TEXT_DOMAIN ),
				'BH' => __( 'BAHRAIN', WD_TEXT_DOMAIN ),
				'BD' => __( 'BANGLADESH', WD_TEXT_DOMAIN ),
				'BB' => __( 'BARBADOS', WD_TEXT_DOMAIN ),
				'BE' => __( 'BELGIUM', WD_TEXT_DOMAIN ),
				'BZ' => __( 'BELIZE', WD_TEXT_DOMAIN ),
				'BJ' => __( 'BENIN', WD_TEXT_DOMAIN ),
				'BM' => __( 'BERMUDA', WD_TEXT_DOMAIN ),
				'BT' => __( 'BHUTAN', WD_TEXT_DOMAIN ),
				'BA' => __( 'BOSNIA-HERZEGOVINA', WD_TEXT_DOMAIN ),
				'BW' => __( 'BOTSWANA', WD_TEXT_DOMAIN ),
				'BV' => __( 'BOUVET ISLAND', WD_TEXT_DOMAIN ),
				'BR' => __( 'BRAZIL', WD_TEXT_DOMAIN ),
				'IO' => __( 'BRITISH INDIAN OCEAN TERRITORY', WD_TEXT_DOMAIN ),
				'BN' => __( 'BRUNEI DARUSSALAM', WD_TEXT_DOMAIN ),
				'BG' => __( 'BULGARIA', WD_TEXT_DOMAIN ),
				'BF' => __( 'BURKINA FASO', WD_TEXT_DOMAIN ),
				'CA' => __( 'CANADA', WD_TEXT_DOMAIN ),
				'CV' => __( 'CAPE VERDE', WD_TEXT_DOMAIN ),
				'KY' => __( 'CAYMAN ISLANDS', WD_TEXT_DOMAIN ),
				'CF' => __( 'CENTRAL AFRICAN REPUBLIC', WD_TEXT_DOMAIN ),
				'CL' => __( 'CHILE', WD_TEXT_DOMAIN ),
				'CN' => __( 'CHINA', WD_TEXT_DOMAIN ),
				'CX' => __( 'CHRISTMAS ISLAND', WD_TEXT_DOMAIN ),
				'CC' => __( 'COCOS (KEELING) ISLANDS', WD_TEXT_DOMAIN ),
				'CO' => __( 'COLOMBIA', WD_TEXT_DOMAIN ),
				'CK' => __( 'COOK ISLANDS', WD_TEXT_DOMAIN ),
				'CR' => __( 'COSTA RICA', WD_TEXT_DOMAIN ),
				'CY' => __( 'CYPRUS', WD_TEXT_DOMAIN ),
				'CZ' => __( 'CZECH REPUBLIC', WD_TEXT_DOMAIN ),
				'DK' => __( 'DENMARK', WD_TEXT_DOMAIN ),
				'DJ' => __( 'DJIBOUTI', WD_TEXT_DOMAIN ),
				'DM' => __( 'DOMINICA', WD_TEXT_DOMAIN ),
				'DO' => __( 'DOMINICAN REPUBLIC', WD_TEXT_DOMAIN ),
				'EC' => __( 'ECUADOR', WD_TEXT_DOMAIN ),
				'EG' => __( 'EGYPT', WD_TEXT_DOMAIN ),
				'SV' => __( 'EL SALVADOR', WD_TEXT_DOMAIN ),
				'EE' => __( 'ESTONIA', WD_TEXT_DOMAIN ),
				'FK' => __( 'FALKLAND ISLANDS (MALVINAS)', WD_TEXT_DOMAIN ),
				'FO' => __( 'FAROE ISLANDS', WD_TEXT_DOMAIN ),
				'FJ' => __( 'FIJI', WD_TEXT_DOMAIN ),
				'FI' => __( 'FINLAND', WD_TEXT_DOMAIN ),
				'FR' => __( 'FRANCE', WD_TEXT_DOMAIN ),
				'GF' => __( 'FRENCH GUIANA', WD_TEXT_DOMAIN ),
				'PF' => __( 'FRENCH POLYNESIA', WD_TEXT_DOMAIN ),
				'TF' => __( 'FRENCH SOUTHERN TERRITORIES', WD_TEXT_DOMAIN ),
				'GA' => __( 'GABON', WD_TEXT_DOMAIN ),
				'GM' => __( 'GAMBIA', WD_TEXT_DOMAIN ),
				'GE' => __( 'GEORGIA', WD_TEXT_DOMAIN ),
				'DE' => __( 'GERMANY', WD_TEXT_DOMAIN ),
				'GH' => __( 'GHANA', WD_TEXT_DOMAIN ),
				'GI' => __( 'GIBRALTAR', WD_TEXT_DOMAIN ),
				'GR' => __( 'GREECE', WD_TEXT_DOMAIN ),
				'GL' => __( 'GREENLAND', WD_TEXT_DOMAIN ),
				'GD' => __( 'GRENADA', WD_TEXT_DOMAIN ),
				'GP' => __( 'GUADELOUPE', WD_TEXT_DOMAIN ),
				'GU' => __( 'GUAM', WD_TEXT_DOMAIN ),
				'GG' => __( 'GUERNSEY', WD_TEXT_DOMAIN ),
				'GY' => __( 'GUYANA', WD_TEXT_DOMAIN ),
				'HM' => __( 'HEARD ISLAND AND MCDONALD ISLANDS', WD_TEXT_DOMAIN ),
				'VA' => __( 'HOLY SEE (VATICAN CITY STATE)', WD_TEXT_DOMAIN ),
				'HN' => __( 'HONDURAS', WD_TEXT_DOMAIN ),
				'HK' => __( 'HONG KONG', WD_TEXT_DOMAIN ),
				'HU' => __( 'HUNGARY', WD_TEXT_DOMAIN ),
				'IS' => __( 'ICELAND', WD_TEXT_DOMAIN ),
				'IN' => __( 'INDIA', WD_TEXT_DOMAIN ),
				'ID' => __( 'INDONESIA', WD_TEXT_DOMAIN ),
				'IE' => __( 'IRELAND', WD_TEXT_DOMAIN ),
				'IM' => __( 'ISLE OF MAN', WD_TEXT_DOMAIN ),
				'IL' => __( 'ISRAEL', WD_TEXT_DOMAIN ),
				'IT' => __( 'ITALY', WD_TEXT_DOMAIN ),
				'JM' => __( 'JAMAICA', WD_TEXT_DOMAIN ),
				'JP' => __( 'JAPAN', WD_TEXT_DOMAIN ),
				'JE' => __( 'JERSEY', WD_TEXT_DOMAIN ),
				'JO' => __( 'JORDAN', WD_TEXT_DOMAIN ),
				'KZ' => __( 'KAZAKHSTAN', WD_TEXT_DOMAIN ),
				'KI' => __( 'KIRIBATI', WD_TEXT_DOMAIN ),
				'KR' => __( 'KOREA, REPUBLIC OF', WD_TEXT_DOMAIN ),
				'KW' => __( 'KUWAIT', WD_TEXT_DOMAIN ),
				'KG' => __( 'KYRGYZSTAN', WD_TEXT_DOMAIN ),
				'LV' => __( 'LATVIA', WD_TEXT_DOMAIN ),
				'LS' => __( 'LESOTHO', WD_TEXT_DOMAIN ),
				'LI' => __( 'LIECHTENSTEIN', WD_TEXT_DOMAIN ),
				'LT' => __( 'LITHUANIA', WD_TEXT_DOMAIN ),
				'LU' => __( 'LUXEMBOURG', WD_TEXT_DOMAIN ),
				'MO' => __( 'MACAO', WD_TEXT_DOMAIN ),
				'MK' => __( 'MACEDONIA', WD_TEXT_DOMAIN ),
				'MG' => __( 'MADAGASCAR', WD_TEXT_DOMAIN ),
				'MW' => __( 'MALAWI', WD_TEXT_DOMAIN ),
				'MY' => __( 'MALAYSIA', WD_TEXT_DOMAIN ),
				'MT' => __( 'MALTA', WD_TEXT_DOMAIN ),
				'MH' => __( 'MARSHALL ISLANDS', WD_TEXT_DOMAIN ),
				'MQ' => __( 'MARTINIQUE', WD_TEXT_DOMAIN ),
				'MR' => __( 'MAURITANIA', WD_TEXT_DOMAIN ),
				'MU' => __( 'MAURITIUS', WD_TEXT_DOMAIN ),
				'YT' => __( 'MAYOTTE', WD_TEXT_DOMAIN ),
				'MX' => __( 'MEXICO', WD_TEXT_DOMAIN ),
				'FM' => __( 'MICRONESIA, FEDERATED STATES OF', WD_TEXT_DOMAIN ),
				'MD' => __( 'MOLDOVA, REPUBLIC OF', WD_TEXT_DOMAIN ),
				'MC' => __( 'MONACO', WD_TEXT_DOMAIN ),
				'MN' => __( 'MONGOLIA', WD_TEXT_DOMAIN ),
				'ME' => __( 'MONTENEGRO', WD_TEXT_DOMAIN ),
				'MS' => __( 'MONTSERRAT', WD_TEXT_DOMAIN ),
				'MA' => __( 'MOROCCO', WD_TEXT_DOMAIN ),
				'MZ' => __( 'MOZAMBIQUE', WD_TEXT_DOMAIN ),
				'NA' => __( 'NAMIBIA', WD_TEXT_DOMAIN ),
				'NR' => __( 'NAURU', WD_TEXT_DOMAIN ),
				'NP' => __( 'NEPAL', WD_TEXT_DOMAIN ),
				'NL' => __( 'NETHERLANDS', WD_TEXT_DOMAIN ),
				'AN' => __( 'NETHERLANDS ANTILLES', WD_TEXT_DOMAIN ),
				'NC' => __( 'NEW CALEDONIA', WD_TEXT_DOMAIN ),
				'NZ' => __( 'NEW ZEALAND', WD_TEXT_DOMAIN ),
				'NI' => __( 'NICARAGUA', WD_TEXT_DOMAIN ),
				'NE' => __( 'NIGER', WD_TEXT_DOMAIN ),
				'NU' => __( 'NIUE', WD_TEXT_DOMAIN ),
				'NF' => __( 'NORFOLK ISLAND', WD_TEXT_DOMAIN ),
				'MP' => __( 'NORTHERN MARIANA ISLANDS', WD_TEXT_DOMAIN ),
				'NO' => __( 'NORWAY', WD_TEXT_DOMAIN ),
				'OM' => __( 'OMAN', WD_TEXT_DOMAIN ),
				'PW' => __( 'PALAU', WD_TEXT_DOMAIN ),
				'PS' => __( 'PALESTINE', WD_TEXT_DOMAIN ),
				'PA' => __( 'PANAMA', WD_TEXT_DOMAIN ),
				'PY' => __( 'PARAGUAY', WD_TEXT_DOMAIN ),
				'PE' => __( 'PERU', WD_TEXT_DOMAIN ),
				'PH' => __( 'PHILIPPINES', WD_TEXT_DOMAIN ),
				'PN' => __( 'PITCAIRN', WD_TEXT_DOMAIN ),
				'PL' => __( 'POLAND', WD_TEXT_DOMAIN ),
				'PT' => __( 'PORTUGAL', WD_TEXT_DOMAIN ),
				'PR' => __( 'PUERTO RICO', WD_TEXT_DOMAIN ),
				'QA' => __( 'QATAR', WD_TEXT_DOMAIN ),
				'RE' => __( 'REUNION', WD_TEXT_DOMAIN ),
				'RO' => __( 'ROMANIA', WD_TEXT_DOMAIN ),
				'RU' => __( 'RUSSIAN FEDERATION', WD_TEXT_DOMAIN ),
				'RW' => __( 'RWANDA', WD_TEXT_DOMAIN ),
				'SH' => __( 'SAINT HELENA', WD_TEXT_DOMAIN ),
				'KN' => __( 'SAINT KITTS AND NEVIS', WD_TEXT_DOMAIN ),
				'LC' => __( 'SAINT LUCIA', WD_TEXT_DOMAIN ),
				'PM' => __( 'SAINT PIERRE AND MIQUELON', WD_TEXT_DOMAIN ),
				'VC' => __( 'SAINT VINCENT AND THE GRENADINES', WD_TEXT_DOMAIN ),
				'WS' => __( 'SAMOA', WD_TEXT_DOMAIN ),
				'SM' => __( 'SAN MARINO', WD_TEXT_DOMAIN ),
				'ST' => __( 'SAO TOME AND PRINCIPE', WD_TEXT_DOMAIN ),
				'SA' => __( 'SAUDI ARABIA', WD_TEXT_DOMAIN ),
				'SN' => __( 'SENEGAL', WD_TEXT_DOMAIN ),
				'RS' => __( 'SERBIA', WD_TEXT_DOMAIN ),
				'SC' => __( 'SEYCHELLES', WD_TEXT_DOMAIN ),
				'SG' => __( 'SINGAPORE', WD_TEXT_DOMAIN ),
				'SK' => __( 'SLOVAKIA', WD_TEXT_DOMAIN ),
				'SI' => __( 'SLOVENIA', WD_TEXT_DOMAIN ),
				'SB' => __( 'SOLOMON ISLANDS', WD_TEXT_DOMAIN ),
				'ZA' => __( 'SOUTH AFRICA', WD_TEXT_DOMAIN ),
				'GS' => __( 'SOUTH GEORGIA AND THE SOUTH SANDWICH ISLANDS', WD_TEXT_DOMAIN ),
				'ES' => __( 'SPAIN', WD_TEXT_DOMAIN ),
				'SR' => __( 'SURINAME', WD_TEXT_DOMAIN ),
				'SJ' => __( 'SVALBARD AND JAN MAYEN', WD_TEXT_DOMAIN ),
				'SZ' => __( 'SWAZILAND', WD_TEXT_DOMAIN ),
				'SE' => __( 'SWEDEN', WD_TEXT_DOMAIN ),
				'CH' => __( 'SWITZERLAND', WD_TEXT_DOMAIN ),
				'TW' => __( 'TAIWAN, PROVINCE OF CHINA', WD_TEXT_DOMAIN ),
				'TZ' => __( 'TANZANIA, UNITED REPUBLIC OF', WD_TEXT_DOMAIN ),
				'TH' => __( 'THAILAND', WD_TEXT_DOMAIN ),
				'TL' => __( 'TIMOR-LESTE', WD_TEXT_DOMAIN ),
				'TG' => __( 'TOGO', WD_TEXT_DOMAIN ),
				'TK' => __( 'TOKELAU', WD_TEXT_DOMAIN ),
				'TO' => __( 'TONGA', WD_TEXT_DOMAIN ),
				'TT' => __( 'TRINIDAD AND TOBAGO', WD_TEXT_DOMAIN ),
				'TN' => __( 'TUNISIA', WD_TEXT_DOMAIN ),
				'TR' => __( 'TURKEY', WD_TEXT_DOMAIN ),
				'TM' => __( 'TURKMENISTAN', WD_TEXT_DOMAIN ),
				'TC' => __( 'TURKS AND CAICOS ISLANDS', WD_TEXT_DOMAIN ),
				'TV' => __( 'TUVALU', WD_TEXT_DOMAIN ),
				'UG' => __( 'UGANDA', WD_TEXT_DOMAIN ),
				'UA' => __( 'UKRAINE', WD_TEXT_DOMAIN ),
				'AE' => __( 'UNITED ARAB EMIRATES', WD_TEXT_DOMAIN ),
				'GB' => __( 'UNITED KINGDOM', WD_TEXT_DOMAIN ),
				'US' => __( 'UNITED STATES', WD_TEXT_DOMAIN ),
				'UM' => __( 'UNITED STATES MINOR OUTLYING ISLANDS', WD_TEXT_DOMAIN ),
				'UY' => __( 'URUGUAY', WD_TEXT_DOMAIN ),
				'UZ' => __( 'UZBEKISTAN', WD_TEXT_DOMAIN ),
				'VU' => __( 'VANUATU', WD_TEXT_DOMAIN ),
				'VE' => __( 'VENEZUELA', WD_TEXT_DOMAIN ),
				'VN' => __( 'VIET NAM', WD_TEXT_DOMAIN ),
				'VG' => __( 'VIRGIN ISLANDS, BRITISH', WD_TEXT_DOMAIN ),
				'VI' => __( 'VIRGIN ISLANDS, U.S.', WD_TEXT_DOMAIN ),
				'WF' => __( 'WALLIS AND FUTUNA', WD_TEXT_DOMAIN ),
				'EH' => __( 'WESTERN SAHARA', WD_TEXT_DOMAIN ),
				'ZM' => __( 'ZAMBIA', WD_TEXT_DOMAIN ) );
		
		return apply_filters( 'wd_helper_util_get_country_codes', $countries );
	}
}
