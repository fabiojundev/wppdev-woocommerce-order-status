<?php
namespace WPPluginsDev\Helpers;
/**
 * Period and frequency helper class
 * 
 * @since 1.0.0
 */
class Helper_Period {
	const FREQ_TYPE_WEEKLY = 'semanal';
	const FREQ_TYPE_MONTHLY = 'mensal';
	const FREQ_TYPE_BIMONTHLY = 'bimestral';
	const FREQ_TYPE_QUARTERLY = 'trimestral';
	const FREQ_TYPE_SEMESTERLY = 'semestral';
	const FREQ_TYPE_ANNUALLY = 'anual';
	const PERIOD_TYPE_DAYS = 'days';
	const PERIOD_TYPE_WEEKS = 'weeks';
	const PERIOD_TYPE_MONTHS = 'months';
	const PERIOD_TYPE_YEARS = 'years';
	const PERIOD_FORMAT = 'Y-m-d';
	const DATE_TIME_FORMAT = 'Y-m-d H:i';
	const DATE_TIME_FORMAT_BR = 'd/m/y H:i';
	const DATE_FORMAT_SHORT = 'y-m-d';
	const DATE_FORMAT = 'Y-m-d';
	const DATE_FORMAT_BR = 'd/m/Y';
	const DATE_FORMAT_BR_SHORT = 'd/m/y';
	const DATE_INVOICE = 'm/Y';

	/**
	 * Get frequency types.
	 * 
	 * @since 1.0.0
	 */
	public static function get_frequency_types() {
		return apply_filters( 
				'wppdev_model_plan_get_frequency_types', 
				array( 
						self::FREQ_TYPE_WEEKLY => __( 'Semanal', WPPDEV_TXT_DM ), 
						self::FREQ_TYPE_MONTHLY => __( 'Mensal', WPPDEV_TXT_DM ), 
						self::FREQ_TYPE_BIMONTHLY => __( 'Bimestral', WPPDEV_TXT_DM ), 
						self::FREQ_TYPE_QUARTERLY => __( 'Trimestral', WPPDEV_TXT_DM ), 
						self::FREQ_TYPE_SEMESTERLY => __( 'Semestral', WPPDEV_TXT_DM ), 
						self::FREQ_TYPE_ANNUALLY => __( 'Anual', WPPDEV_TXT_DM ) ) );
	}

	/**
	 * Return the existing period types.
	 *
	 * @since 1.0.0
	 *       
	 * @return array The period types and descriptions.
	 */
	public static function get_period_types() {
		return apply_filters( 
				'wppdev_helper_period_get_period_types', 
				array( 
						self::PERIOD_TYPE_DAYS => __( 'dias', WPPDEV_TXT_DM ), 
						self::PERIOD_TYPE_WEEKS => __( 'semanas', WPPDEV_TXT_DM ), 
						self::PERIOD_TYPE_MONTHS => __( 'meses', WPPDEV_TXT_DM ), 
						self::PERIOD_TYPE_YEARS => __( 'anos', WPPDEV_TXT_DM ) ) );
	}

	/**
	 * Convert frequency to days.
	 * 
	 * @since 1.0.0
	 * 
	 * @param string $frequency
	 */
	public static function freq2days( $frequency ) {
		return self::get_period_in_days( self::freq2period( $frequency ) );
	}

	/**
	 * Convert frequency unit to period.
	 *
	 * @since 1.0.0
	 * @param unknown $frequency
	 * @return number[]|NULL[]|string[]
	 */
	public static function freq2period( $frequency ) {
		$period_unit = 0;
		$period_type = null;
		$frequency_types = self::get_frequency_types();
		
		if ( array_key_exists( $frequency, $frequency_types ) ) {
			switch ( $frequency ) {
				case self::FREQ_TYPE_WEEKLY:
					$period_unit = 1;
					$period_type = self::PERIOD_TYPE_WEEKS;
					break;
				case self::FREQ_TYPE_MONTHLY:
					$period_unit = 1;
					$period_type = self::PERIOD_TYPE_MONTHS;
					break;
				case self::FREQ_TYPE_BIMONTHLY:
					$period_unit = 2;
					$period_type = self::PERIOD_TYPE_MONTHS;
					break;
				case self::FREQ_TYPE_QUARTERLY:
					$period_unit = 3;
					$period_type = self::PERIOD_TYPE_MONTHS;
					break;
				case self::FREQ_TYPE_SEMESTERLY:
					$period_unit = 6;
					$period_type = self::PERIOD_TYPE_MONTHS;
					break;
				case self::FREQ_TYPE_ANNUALLY:
					$period_unit = 12;
					$period_type = self::PERIOD_TYPE_MONTHS;
					break;
			}
		}
		return array( 'period_unit' => $period_unit, 'period_type' => $period_type );
	}

	/**
	 * Add a period interval to a date.
	 *
	 * @since 1.0.0
	 *       
	 * @param int $period_unit The period unit to add.
	 * @param string $period_type The period type to add.
	 * @param string $start_date The start date to add to.
	 * @throws Exception
	 * @return string The added date.
	 */
	public static function add_interval( $period_unit, $period_type, $start_date = null, $format = self::PERIOD_FORMAT ) {
		if ( empty( $start_date ) ) {
			$start_date = date( self::PERIOD_FORMAT );
		}
		if ( self::PERIOD_TYPE_YEARS == $period_type ) {
			$period_unit *= 365;
			$period_type = self::PERIOD_TYPE_DAYS;
		}

		if ( self::PERIOD_TYPE_MONTHS == $period_type ) {
			$end_dt = self::add_months( $start_date, $period_unit, $format );
		}
		else {
			$end_dt = strtotime( '+' . $period_unit . $period_type, strtotime( $start_date ) );
			$end_dt = date( $format, $end_dt );
		}
		if ( $end_dt === false ) {
			throw new \Exception( 'error add_interval' );
		}

		return apply_filters( 'wppdev_helper_period_add_interval', $end_dt );
	}
	
	/**
	 * Add months interval.
	 * 
	 * Handle non existing dates.
	 * 
	 * @param string $start_date
	 * @param int $months
	 * @param string $format
	 * @return string
	 */
	public static function add_months( $start_date, $months, $format ) {
		$start_dt = new \DateTime( $start_date );
		$interval = new \DateInterval( 'P' . $months . 'M' );
		
		//correct 30/feb cases
		$last_day_of_month = clone $start_dt;
		$last_day_of_month->modify( 'last day of +' . $months . ' month' );
		if ( $start_dt->format( 'd' ) > $last_day_of_month->format( 'd' ) ) {
			$interval = $start_dt->diff( $last_day_of_month );
		}
		$end_date = $start_dt->add( $interval );
		$end_date = $end_date->format( $format );
		
		return $end_date;
	}
	
	/**
	 * Subtract a period interval to a date.
	 *
	 * @since 1.0.0
	 *       
	 * @param int $period_unit The period unit to subtract.
	 * @param string $period_type The period type to subtract.
	 * @param string $start_date The start date to subtract to.
	 * @throws Exception
	 * @return string The subtracted date.
	 */
	public static function subtract_interval( $period_unit, $period_type, $start_date = null ) {
		if ( empty( $start_date ) ) {
			$start_date = date( self::PERIOD_FORMAT );
		}
		
		$end_dt = strtotime( '-' . $period_unit . $period_type, strtotime( $start_date ) );
		if ( $end_dt === false ) {
			throw new \Exception( 'error subtract_interval' );
		}
		return apply_filters( 'wppdev_helper_period_subtract_interval', date( self::PERIOD_FORMAT, $end_dt ) );
	}

	/**
	 * Subtract dates.
	 *
	 * Return (end_date - start_date) in period_type format
	 *
	 * @since 1.0.0
	 *       
	 * @param Date $end_date The end date to subtract from in the format yyyy-mm-dd
	 * @param Date $start_date The start date to subtraction the format yyyy-mm-dd
	 * @return string The resulting days of the date subtraction.
	 */
	public static function subtract_dates( $start_date, $end_date = null, $format = self::DATE_FORMAT ) {

		if( $end_date ) {
			$end_date = \DateTime::createFromFormat( $format, $end_date );
		}
		else {
			$end_date = new \DateTime( $end_date );
		}
		$start_date = \DateTime::createFromFormat( $format, $start_date );
		
		$days = round( ( $end_date->format( 'U' ) - $start_date->format( 'U' ) ) / ( 60 * 60 * 24 ) );
		
		return apply_filters( 'wppdev_helper_period_subtract_dates', $days );
	}

	/**
	 * Return current date.
	 *
	 * @since 1.0.0
	 *       
	 * @return string The current date.
	 */
	public static function current_date( $format = null, $ignore_filters = false ) {
		if ( empty( $format ) ) {
			$format = self::PERIOD_FORMAT;
		}
		
		$format = apply_filters( 'wppdev_helper_period_current_date_format', $format );

		$timezone = ini_get( 'date.timezone' );
		$timezone = apply_filters( 'wppdev_helper_period_current_date_timezone', $timezone );
		if( empty( $timezone ) ) {
			$timezone = date_default_timezone_get();
		}

		date_default_timezone_set( $timezone );
		
		$timestamp = time();
		$dt = new \DateTime( "now", new \DateTimeZone( $timezone ) ); //first argument "must" be a string
		$dt->setTimestamp( $timestamp ); //adjust the object to correct timestamp
		
		$date = $dt->format( $format );
		if ( ! $ignore_filters ) {
			$date = apply_filters( 'wppdev_helper_period_current_date', $date );
		}
		
		return $date;
	}

	/**
	 * Return current timestamp.
	 *
	 * @since 1.0.0
	 *       
	 * @return string The current date.
	 */
	public static function current_time( $type = 'mysql' ) {
		return apply_filters( 'wppdev_helper_period_current_time', current_time( $type, true ) );
	}

	/**
	 * Get period in days.
	 *
	 * Convert period in week, month, years to days.
	 *
	 * @since 1.0.0
	 *       
	 * @param $period The period to convert.
	 *       
	 * @return int The calculated days.
	 */
	public static function get_period_in_days( $period ) {
		$days = 0;
		switch ( $period[ 'period_type' ] ) {
			case self::PERIOD_TYPE_DAYS:
				$days = $period[ 'period_unit' ];
				break;
			case self::PERIOD_TYPE_WEEKS:
				$days = $period[ 'period_unit' ] * 7;
				break;
			case self::PERIOD_TYPE_MONTHS:
				$days = $period[ 'period_unit' ] * 30;
				break;
			case self::PERIOD_TYPE_YEARS:
				$days = $period[ 'period_unit' ] * 365;
				break;
		}
		return apply_filters( 'wppdev_helper_period_get_period_in_days', $days, $period );
	}

	/**
	 * Get period value.
	 *
	 * @since 1.0.0
	 *       
	 * @param array $period
	 * @param string $field
	 * @return string
	 */
	public static function get_period_value( $period, $field ) {
		$value = null;
		if ( isset( $period[ $field ] ) ) {
			$value = $period[ $field ];
		}
		elseif ( 'period_unit' == $field ) {
			$value = 1;
		}
		elseif ( 'period_type' == $field ) {
			$value = self::PERIOD_TYPE_DAYS;
		}
		return apply_filters( 'wppdev_helper_period_get_period_value', $value );
	}

	/**
	 * Get period description.
	 *
	 * @since 1.0.0
	 *       
	 * @param array $period
	 * @return string
	 */
	public static function get_period_desc( $period ) {
		$period_unit = self::get_period_value( $period, 'period_unit' );
		$period_type = self::get_period_value( $period, 'period_type' );
		if ( abs( $period_unit < 2 ) ) {
			$period_type = preg_replace( '/s$/', '', $period_type );
		}
		$desc = sprintf( '%s %s', $period_unit, $period_type );
		
		return apply_filters( 'wppdev_helper_period_get_period_desc', $desc );
	}

	/**
	 * Format a date to another format.
	 *
	 * @since 1.0.0
	 *       
	 * @param unknown $date The date in yyyy-mm-dd format.
	 * @param unknown $format The resulting format.
	 *       
	 * @return string The formatted date.
	 */
	public static function format( $date, $format = self::DATE_FORMAT ) {
		$date = new \DateTime( $date );
		return apply_filters( 'wppdev_helper_period_format', $date->format( $format ), $date, $format );
	}
}