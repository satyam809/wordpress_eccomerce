<?php

namespace WPV_FV\Integrations;

use WPV_FV\Classes\ApiEndpoint;
use WPV_FV\Classes\DbManager;

class Caldera extends DbManager{

	private static $_instance = null;

	public static function instance() {

		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}

		return self::$_instance;
	}

	/**
	 * Cf7 constructor.
	 */
	public function __construct() {
		$this->plugin_name = 'caldera';

		add_filter('fv_forms', [ $this, 'register_form' ]);
	}

	public function register_form($forms){
		$forms[$this->plugin_name] = 'Caldera';
		return $forms;
	}

	static function get_forms($param) {
		$post_type = $param;

		global $wpdb;

		$forms_query = "select * from {$wpdb->prefix}cf_forms where type='primary';";

		$form_result = $wpdb->get_results($forms_query);
		$data = [];
		foreach ( $form_result as $form ) {
			$form_name = unserialize($form->config);
			$data[$form_name['ID']] = [
				'id' => $form_name['ID'],
				'name' => $form_name['name']
			];
		}
		return $data;
	}

	static function get_submission_data($param){
        $slug = [];
        $cols = [];
        $filter_param = [];

        $gmt_offset =  get_option('gmt_offset');
        $hours   = (int) $gmt_offset;
        $minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

        if($hours >= 0 ){
            $time_zone = '+'.$hours.':'.$minutes;
        }
        else{
            $time_zone = $hours.':'.$minutes;
        }

        if($param['queryType'] !== 'Custom'){
            $dates = ApiEndpoint::get_dates($param['queryType']);

            $tz = new \DateTimeZone($time_zone);

            $fromDate = new \DateTime($dates['fromDate']);
            $fromDate->setTimezone($tz);
            $toDate = new \DateTime($dates['endDate']);
            $toDate->setTimezone($tz);

            $fromDate = $fromDate->format('Y-m-d');
            $toDate = $toDate->format('Y-m-d');
        }
        else{
            $tz = new \DateTimeZone($time_zone);

            $fromDate = new \DateTime($param['fromDate']);
            $fromDate->setTimezone($tz);
            $toDate = new \DateTime($param['toDate' ]);
            $toDate->setTimezone($tz);

            $fromDate = $fromDate->format('Y-m-d');
            $toDate = $toDate->format('Y-m-d');
        }

        if($fromDate !== '' && $fromDate !== null){
            $param_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) >= '".$fromDate."'";
            $paramcount_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) >= '".$fromDate."'";
        }
        if($toDate !== '' && $toDate !== null){
            if($fromDate !== ''){
                $param_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
                $paramcount_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
            }
            else{
                $param_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
                $paramcount_where[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
            }
        }

        if($param['selectedFilter'] == 'undefined' || $param['selectedFilter'] == '' || $param['filterValue'] == 'undefined' || $param['filterValue'] == ''){
            $filter_param[] = "slug like'%%'";
        }
        else{
            $filter_param[] = "slug='".$param['selectedFilter']."'";
        }
        if($param['filterValue'] == 'undefined' || $param['filterValue'] == ''){
            $filter_param[] = "value like'%%'";
        }
        else{
            if($param['filterOperator'] == 'equal'){
                $filter_param[] = "value='".$param['filterValue']."'";
            }
            else if($param['filterOperator'] == 'not_equal'){
                $filter_param[] = "value != '".$param['filterValue']."'";
            }
            else if($param['filterOperator'] == 'contain'){
                $filter_param[] = "value LIKE '%".$param['filterValue']."%'";
            }
            else if($param['filterOperator'] == 'not_contain'){
                $filter_param[] = "value NOT LIKE '%".$param['filterValue']."%'";
            }
        }

        foreach ($param['columns'] as $key => $val) {
            if($val->visible == 0 || $val->visible == ''){
                $slug[] = $val->colKey;
            }
            $cols[] = $val->colKey;
	    }

        global $wpdb;

        $filter_col_id = "select entry_id from {$wpdb->prefix}cf_form_entries e 
        left JOIN {$wpdb->prefix}cf_form_entry_values ev ON e.id=ev.entry_id where ". implode(' and ',$filter_param) ." and form_id = '".$param['form']."'";
        $filter_col_id_res = $wpdb->get_results($filter_col_id, ARRAY_A);
        $entry_id = [];
        foreach ($filter_col_id_res as $entryId) {
            $entry_id[] = $entryId['entry_id'];
        }

        $entry_query = "select * from {$wpdb->prefix}cf_form_entries e 
        left JOIN {$wpdb->prefix}cf_form_entry_values ev ON e.id=ev.entry_id
        where ". implode(' and ',$param_where) ." and ev.slug NOT IN ('".implode("','",$slug)."') and entry_id IN ('".implode("','",$entry_id)."') and form_id = '".$param['form']."'";
	    $entry_res = $wpdb->get_results($entry_query, ARRAY_A);

        $meta_data = [];
        foreach ($entry_res as $entry_meta) {
            $meta_data[ $entry_meta['entry_id'] ][ $entry_meta['slug'] ] = stripslashes($entry_meta['value']);
        }

        if (!in_array('datestamp', $slug)) {
            foreach ($entry_res as $entry_meta) {
                $meta_data[ $entry_meta['entry_id'] ][ 'datestamp' ] = stripslashes($entry_meta['datestamp']);
            }
        }

        $res = [];
        foreach ($meta_data as $key => $val) {
            $res[] = $val;
        }

        $final_array = [];
		$final_cols = array_flip(array_diff($cols, $slug));
		foreach ( $final_cols as $key => $value ) {
			$final_cols[$key] = '';
		}
        for($i = 0; $i<count($meta_data); $i++){
            $final_array[] = array_merge($final_cols, $res[$i]);
        }

	    return $final_array;
    }
}