<?php

namespace WPV_FV\Classes;

use Caldera_Forms_Entry_UI;
use calderawp\calderaforms\pro\api\keys;
use function PHPSTORM_META\type;
use WPV_FV\Integrations\Ninja;

class ApiEndpoint{

	private static $_instance = null;
    private static $filter_val;
    private static $countOfEntries = 0;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_action('init',[$this,'fv_export_csv']);



		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/getallforms/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_all_plugin_form',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/getallformsdeletable/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_all_plugin_form_deleteable',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/formdata/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_form_data',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_forms/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_forms',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/set_forms_option/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::set_forms_option',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_forms_option/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_forms_option',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_analytic_data/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_analytic_data',
				'permission_callback' => function () {
					return self::check_user_permission('analytics');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_plugins/', array(
				'methods' => 'GET',
				'callback' => __CLASS__ .'::get_available_plugin',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );

        add_action( 'rest_api_init', function () {
            register_rest_route( '/wts-fv/', '/delete_row_id/', array(
                'methods' => 'POST',
                'callback' => __CLASS__ .'::get_delete_row_id',
                'permission_callback' => function () {
	                return self::check_user_permission('delete');
                }
            ));
        } );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/set_dbpanel_settings/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::set_dbpanel_settings',
				'permission_callback' => function () {
					return self::check_user_permission('delete');
				}
			));
		} );

		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_dbpanel_settings/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_dbpanel_settings',
				'permission_callback' => function () {
					return self::check_user_permission('delete');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/set_ip_settings/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::set_ip_settings',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_ip_settings/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_ip_settings',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_gdpr_settings/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_gdpr_settings',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );
		add_action( 'rest_api_init', function () {
			register_rest_route( '/wts-fv/', '/get_entry_count/', array(
				'methods' => 'POST',
				'callback' => __CLASS__ .'::get_forms_entry_count',
				'permission_callback' => function () {
					return self::check_user_permission('view');
				}
			));
		} );

        add_action( 'rest_api_init', function () {
            register_rest_route( '/wts-fv/', '/get_values_for_filter/', array(
                'methods' => 'POST',
                'callback' => __CLASS__ .'::get_values_for_filter',
                'permission_callback' => function () {
	                return self::check_user_permission('view');
                }
            ));
        } );

        //Get Logs Table Data for Log Page
        add_action( 'rest_api_init', function () {
            register_rest_route( '/wts-fv/', '/get_logs_data/', array(
                'methods' => 'POST',
                'callback' => __CLASS__ .'::get_logs_data',
                'permission_callback' => function () {
                    return current_user_can( 'edit_others_posts' );
                }
            ));
        } );

	}

    public function get_logs_data(\WP_REST_Request $request){

        $gmt_offset =  get_option('gmt_offset');
        $hours   = (int) $gmt_offset;
        $minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

        if($hours >= 0 ){
            $time_zone = $hours.':'.$minutes;
        }
        else{
            $time_zone = $hours.':'.$minutes;
        }

        global $wpdb;
        $entry_query = "select l.id,event,l.user_id,description,u.user_login,DATE_FORMAT(ADDTIME(export_time_gmt,'".$time_zone."' ), '%Y/%m/%d %H:%i:%S') as export_time_gmt from {$wpdb->prefix}fv_logs l JOIN {$wpdb->prefix}users u on l.user_id=u.ID";

        $entry_result = $wpdb->get_results($entry_query, ARRAY_A);

        foreach ( $entry_result as $key => $value ) {
            $user_meta = get_user_meta($value['user_id']);

            $entry_result[$key]['user'] = $user_meta['first_name'][0].' '. $user_meta['last_name'][0];
            unset($entry_result[$key]['user_id']);
            unset($entry_result[$key]['user_login']);
        }


        return $entry_result;
    }


	static function get_all_plugin_form(\WP_REST_Request $request){
		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		$skipPlugin = $request->get_param('skipPlugin');

		if($skipPlugin === true){
			unset($data['forms_plugin']['caldera']);
			unset($data['forms_plugin']['ninja']);
		}
		$gdpr_settings = get_option( 'fv_gdpr_settings' );

		if(array_key_exists('debug_mode',$gdpr_settings)){
			$debugMode = $gdpr_settings['debug_mode'];
		}
		else{
			$debugMode = 'no';
		}

		//echo 'mode '. $debugMode;

		foreach ( $data['forms_plugin'] as $key => $value ) {
			$class = '\WPV_FV\Integrations\\'.ucfirst($key);

			$res = $class::get_forms($key);


			if($res == null ){
				$res['no_form'] = ['id' => 'no_form', 'name' => 'No Form'];
			}

			$pluginForms[$key] = $res;

		}

		$inserted_forms = get_option('fv_forms');
		$all_forms = [];
		foreach ($data['forms_plugin'] as $key => $value){
			if(array_key_exists($key, $inserted_forms)){
				$all_forms[$key] =  $pluginForms[$key] + $inserted_forms[$key];
			}
			else{
				$all_forms[$key] =  $pluginForms[$key];
			}

		}
		$allforms = [];
		if($debugMode === 'no'){
			foreach ($all_forms as $key => $value){
				$child = [];
				foreach ($all_forms[$key] as $childKey => $childValue){
					$child[]= [
						'id' => $childKey,
						'text' => $childValue['name']
					];
				}
				$allforms[] = [
					'text' => $key,
					'children' => $child
				];
			}
		}
		else{
			foreach ($all_forms as $key => $value){
				$child = [];
				foreach ($all_forms[$key] as $childKey => $childValue){
					$child[]= [
						'id' => $childKey,
						'text' => $childValue['name'].' ('.$childKey.')'
					];
				}
				$allforms[] = [
					'text' => $key,
					'children' => $child
				];
			}
		}
		$data['allForms'] = $allforms;
		return $data;
	}

	static function get_all_plugin_form_deleteable(\WP_REST_Request $request){
		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		$skipPlugin = $request->get_param('skipPlugin');

		if($skipPlugin === true){
			unset($data['forms_plugin']['caldera']);
			unset($data['forms_plugin']['ninja']);
		}

		foreach ( $data['forms_plugin'] as $key => $value ) {
			$class = '\WPV_FV\Integrations\\'.ucfirst($key);

			$res = $class::get_forms($key);


			if($res == null ){
				$res['no_form'] = ['id' => 'no_form', 'name' => 'No Form'];
			}

			$pluginForms[$key] = $res;

		}

		$inserted_forms = get_option('fv_forms');
		$all_forms = [];
		foreach ($data['forms_plugin'] as $key => $value){
			if(array_key_exists($key, $inserted_forms)){
				$all_forms[$key] =  $pluginForms[$key] + $inserted_forms[$key];
			}
			else{
				$all_forms[$key] =  $pluginForms[$key];
			}
		}

		foreach ( $all_forms as $pluginKey => $pluginValue ) {
			foreach ( $all_forms[$pluginKey] as $formKey => $formValue ) {
				//check form has entry or not in database
				//echo 'form '. $formKey.' val '. $pluginKey.PHP_EOL;
				$res = self::check_form_db_entry($formKey,$pluginKey);
				if($res > 0){
					$all_forms[$pluginKey][$formKey]['deletable'] = false;
				}
				else{
					$all_forms[$pluginKey][$formKey]['deletable'] = true;
				}

			}
		}

		$data['allForms'] = $all_forms;
		return $data;
	}

	static function check_form_db_entry($id,$plugin){
		global $wpdb;
		$entry_count_query = "SELECT * FROM {$wpdb->prefix}fv_enteries where form_plugin='".$plugin."' AND form_id='".$id."'";

		$entry_count_result = $wpdb->get_results($entry_count_query, ARRAY_A);
		$entry_count = count($entry_count_result);

		return $entry_count;
	}
	static function check_user_permission($param){
		$permissions = get_option( 'fv_user_role');

		$user = wp_get_current_user();
		if(is_user_logged_in()) {
			$user_role = $user->roles;
			$user_role = $user_role[0];
		}
		else{
			$user_role = 'subscriber';
		}

		if($user_role == 'administrator' || WPV_FV_PLAN === 'FREE'){
			return true;
		}

		if(array_key_exists($user_role,$permissions)){
			$user_permission = $permissions[$user_role];
		}
		else{
			return false;
		}

		if($user_permission[$param] === true || $user_permission[$param] === 'true')
		{
			return true;
		}
		else{
			return false;
		}
	}

	static function get_delete_row_id(\WP_REST_Request $request){
        global $wpdb;
	    $delete_id = $request->get_param( 'id' );
	    $deleteRowQuery1 = "Delete from {$wpdb->prefix}fv_enteries where id IN (".implode(",",$delete_id).")";
        $deleteRowQuery2 = "Delete from {$wpdb->prefix}fv_entry_meta where data_id IN (".implode(",",$delete_id).")";
        $wpdb->get_results($deleteRowQuery1, ARRAY_A);
        $wpdb->get_results($deleteRowQuery2, ARRAY_A);
	    return $delete_id;
    }

    static function get_values_for_filter( \WP_REST_Request $request) {
        $forms = [];
        $data['forms_plugin'] = apply_filters('fv_forms', $forms);

        $param = $request->get_param( 'data' );

        $per_page = $param['per_page'];
        $page_num = $param[ 'page_num'];
        $form_id = $param['formid'];
        $queryType = $param['query_type'];
        $filter = '';
        $filterValue = '';
        $filterOperator = '';

        if(WPV_FV_PLAN == 'PRO'){
	        $filter = array_key_exists('filter',$param) ? $param['filter'] : '';
	        $filterOperator = array_key_exists('filterOperator',$param) ? $param['filterOperator'] : '';
	        $filterValue =array_key_exists('filterValue',$param) ? $param['filterValue'] : '';
        }

        $gmt_offset =  get_option('gmt_offset');
        $hours   = (int) $gmt_offset;
        $minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

        if($hours >= 0 ){
            $time_zone = '+'.$hours.':'.$minutes;
        }
        else{
            $time_zone = $hours.':'.$minutes;
        }

        if($queryType !== 'Custom'){
            $dates = self::get_dates($queryType);

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

            $fromDate = new \DateTime($request->get_param( 'fromDate' ));
            $fromDate->setTimezone($tz);
            $toDate = new \DateTime($request->get_param( 'toDate' ));
            $toDate->setTimezone($tz);

            $fromDate = $fromDate->format('Y-m-d');
            $toDate = $toDate->format('Y-m-d');
        }
        if($page_num == '')
        {
            $page_num = 1;
        }

        $data = self::get_data($request,$data,$fromDate,$toDate,$form_id,$page_num,$per_page,$filter,$filterValue,$filterOperator);

        $plugin_name = $request->get_param('data')['plugin'];
        $col = $data['columns'];

        if (($key = array_search('Submission Date', $col)) !== false) {
            unset($col[$key]);
        }
        if (($key = array_search('captured', $col)) !== false) {
            unset($col[$key]);
        }
        if (($key = array_search('datestamp', $col)) !== false) {
            unset($col[$key]);
        }
        $col = array_values($col);

        if($plugin_name == 'ninja'){
	        $filterKeys = $data['filterColumnsKeys'];
            $final_cols = array($col,$filterKeys);
        }else{
            $final_cols = array($col,$col);
        }
        return $final_cols;
    }

	static function get_available_plugin( \WP_REST_Request $request ) {
		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		return $data['forms_plugin'];
	}
	static function get_form_data( \WP_REST_Request $request ) {

        //$executionStartTime = microtime(true);

		$param = $request->get_param('data');

		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		$per_page = $param['per_page'];
		$page_num = $param['page_num'];
		$form_id = $param['formid'];
		$queryType = $param['query_type'];
		$filter = '';
		$filterValue = '';
        $filterOperator = '';
		if(WPV_FV_PLAN == 'PRO'){
			$filter = array_key_exists('filter',$param) ? $param['filter'] : '';
			$filterOperator = array_key_exists('filterOperator',$param) ? $param['filterOperator'] : '';
			$filterValue =array_key_exists('filterValue',$param) ? $param['filterValue'] : '';
		}



		$gmt_offset =  get_option('gmt_offset');
		$hours   = (int) $gmt_offset;
		$minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if($hours >= 0 ){
			$time_zone = '+'.$hours.':'.$minutes;
		}
		else{
			$time_zone = $hours.':'.$minutes;
		}

		if($queryType !== 'Custom'){
			$dates = self::get_dates($queryType);

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
		if($page_num == '')
		{
			$page_num = 1;
		}

        $executionEndTime = microtime(true);

        //$seconds = $executionEndTime - $executionStartTime;

        //$time_elapsed_secs = microtime(true) - $executionStartTime;
        //echo $seconds;
        /*echo $time_elapsed_secs;
        die();*/

		$data = self::get_data($request,$data,$fromDate,$toDate,$form_id,$page_num,$per_page,$filter,$filterValue,$filterOperator);
		return $data;
	}

	static function get_data($request,$data,$fromDate,$toDate,$form_id,$page_num,$per_page,$filter,$filterValue,$filterOperator){

	    $plugin = $request->get_param('data')['plugin'];
		$export = false;

		if($plugin !== null && trim($plugin) !== '' ){
			if($plugin == 'caldera'){
				$data = self::get_caldera_form_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,'','',$filter,$filterValue,$filterOperator,'');
			}
            else if($plugin == 'ninja'){

                $data = Ninja::get_ninja_forms_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,'','',$filter,$filterValue,$filterOperator,'');
            }
			else{
				$data = self::get_tbl_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$export,$plugin,'','',$filter,$filterValue,$filterOperator,'');
			}
		}
		else{

			$forms_plugin = array_values($data['forms_plugin']);

			if(count($forms_plugin) == 0){
				return;
			}
			if($forms_plugin[0] == 'Caldera'){
				$data = self::get_caldera_form_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export, '','',$filter,$filterValue,$filterOperator,'');
			}
			else if($forms_plugin[0] == 'ninja'){
				$data = Ninja::get_ninja_forms_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,'','',$filter,$filterValue,$filterOperator,'');
			}
			else{
				$data = self::get_tbl_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$export,$plugin, '', '',$filter,$filterValue,$filterOperator,'');
			}
		}

		return $data;
	}

	static function get_tbl_data($request,$plugins,$fromDate,$toDate,$form_id,$page_num,$per_page,$export,$plugin,$customColumnsQuery,$colChoice,$filter,$filterValue,$filterOperator,$customRowsQuery){
		global $wpdb;


		$param = '';
		$param_count_query = '';

        $gdpr_setting = get_option( 'fv_gdpr_settings');

		$saveUserAgent = '';

        if($gdpr_setting === false){
            $saveIp = get_option('fv-ip-save');
        }
        else{
            $saveIp = $gdpr_setting['ip'];
            $saveUserAgent = $gdpr_setting['ua'];
        }

		if($plugin !== '' && $plugin !== null){
			$param_where[] = "form_plugin='".$plugin."'";
			$paramcount_where[] = "form_plugin='".$plugin."'";
			//$param .= "Where form_plugin='".$plugin."'";
			//$param_count_query .= "Where form_plugin='".$plugin."'";
			if($form_id !== '' && $form_id !== null){
				$param_where[] = "form_id='".$form_id."'";
				$paramcount_where[] = "form_id='".$form_id."'";
				//$param .= " and form_id='".$form_id."'";
				//$param_count_query .= " and form_id='".$form_id."'";
			}
		}
		else{
			if(count($plugins) == 0){
				return [];
			}
			$res = self::get_first_param($plugins);
			$param_where[] = "form_plugin='".$res['plugin']."'";
			$paramcount_where[] = "form_plugin='".$res['plugin']."'";
			//$param .= "Where form_plugin='".$res['plugin']."'";
			//$param_count_query .= "Where form_plugin='".$res['plugin']."'";
			if($form_id == '' || $form_id !== null){
				$form_id=$res['formid'];
				$param_where[] = "form_id='".$res['formid']."'";
				$paramcount_where[] = "form_id='".$res['formid']."'";
				//$param .= " and form_id='".$res['formid']."'";
				//$param_count_query .= " and form_id='".$res['formid']."'";
			}
		}

		if($fromDate !== '' && $fromDate !== null){
			$param_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
			$paramcount_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
			//$param .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
			//$param_count_query .= "and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
		}
		if($toDate !== '' && $toDate !== null){
			if($fromDate !== ''){
				$param_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				$paramcount_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				//$param .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				//$param_count_query .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
			}
			else{
				$param_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				$paramcount_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				//$param = " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
				//$param_count_query = " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
			}
		}

		if($filter == 'undefined' || $filter == ''){
			$filter_param[] = "meta_key like'%%'";
		}
		else{
			$filter_param[] = "meta_key='".$filter."'";
		}
		if($filterValue == 'undefined' || $filterValue == ''){
			$filter_param[] = "meta_value like'%%'";
		}
		else{
			if($filterOperator == 'equal'){
				$filter_param[] = "meta_value='".$filterValue."'";
			}
			else if($filterOperator == 'not_equal'){
				$filter_param[] = "meta_value != '".$filterValue."'";
			}
			else if($filterOperator == 'contain'){
				$filter_param[] = "meta_value LIKE '%".$filterValue."%'";
			}
			else if($filterOperator == 'not_contain'){
				$filter_param[] = "meta_value NOT LIKE '%".$filterValue."%'";
			}
		}

        $selectStatus= '';
        if(WPV_FV_PLAN == 'PRO') {
            $selectStatus = ",e.status";
        }else{
            $selectStatus = "";
        }

		$orderby[] = " order by captured desc";
		$orderby_count[] = " order by DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) desc";
		//$param .=" order by DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) desc";
		//$param_count_query .=" order by DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) desc";
		$limit = '';
		if($export == false) {
			if ( $page_num > 1 ) {
				$limit = ' limit ' . $per_page * ( $page_num - 1 ) . ',' . $per_page;
			} else {
				$limit = ' limit ' . $per_page;
			}
		}

        $customQuery = explode(',', $customColumnsQuery);

        if($saveUserAgent === 'yes'){
            $entry_query = "SELECT distinct e.id".$selectStatus.",e.url,e.user_agent,DATE_FORMAT(captured, '%Y/%m/%d %H:%i:%S') as captured,form_id,form_plugin FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where) .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
        }else{
            $entry_query = "SELECT distinct e.url,e.id".$selectStatus.",DATE_FORMAT(captured, '%Y/%m/%d %H:%i:%S') as captured,form_id,form_plugin FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where) .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
        }

        if($export == true && $customColumnsQuery!== ''){
            if($saveUserAgent === 'yes'){
                $filter_param[0] = 'meta_key like "%%"';
                $filter_param[1] = 'meta_value like "%%"';
            }

            $customTempQuery = "e.id,form_id";

            if($colChoice === 'custom'){
                if($customRowsQuery !== ''){
                    $rowsIds = "and e.id in (". implode(",",explode(',', $customRowsQuery)) .")";
                }else{
                    $rowsIds = '';
                }

                if (in_array('captured', $customQuery)) {
                    $customTempQuery .= ",DATE_FORMAT(captured, '%Y/%m/%d %H:%i:%S') as captured";
                }

                if (in_array('status', $customQuery)) {
                    $customTempQuery .= ",e.status";
                    //$entry_query = "SELECT distinct e.user_agent FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
                }

                if (in_array('url', $customQuery)) {
                    $customTempQuery .= ",e.url";
                    //$entry_query = "SELECT distinct e.url,e.id,form_id,e.user_agent FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
                }

                if (in_array('user_agent', $customQuery)) {
                    $customTempQuery .= ",e.user_agent";
                    //$entry_query = "SELECT distinct e.user_agent,e.id,form_id,e.user_agent FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
                }

                $entry_query = "SELECT distinct ".$customTempQuery." FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit;
            }else{
                if($customRowsQuery !== ''){
                    $rowsIds = "and e.id in (". implode(",",explode(',', $customRowsQuery)) .")";
                }else{
                    $rowsIds = '';
                }
                if($saveUserAgent === 'yes'){
                    $entry_query = "SELECT distinct e.id".$selectStatus.",e.url,e.user_agent,DATE_FORMAT(captured, '%Y/%m/%d %H:%i:%S') as captured,form_id,form_plugin FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id  where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit ;
                }else{
                    $entry_query = "SELECT distinct e.url,e.id".$selectStatus.",DATE_FORMAT(captured, '%Y/%m/%d %H:%i:%S') as captured,form_id,form_plugin FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id  where ". implode(' and ',$param_where).$rowsIds .' and ' . implode(' and ', $filter_param) . implode(' ',$orderby) . $limit ;
                }
            }
        }

		$entry_result = $wpdb->get_results($entry_query, ARRAY_A);

		$param_id = [];

		foreach ($entry_result as $key => $value){
			$param_id[] = $value['id'];
		}
		$res = [];
		$entry_count = 0;

		if(count($param_id) > 0){

			$entry_meta_query = "SELECT * FROM {$wpdb->prefix}fv_entry_meta where data_id IN (". implode(",",$param_id) . ") AND meta_key != 'fv_form_id' AND meta_key != 'fv_plugin'";


            if($export == true && $customColumnsQuery!== '' && $colChoice === 'custom'){
                $entry_meta_query .= " And meta_key IN('". implode("','",$customQuery) . "')";

            }
			if($saveIp === 'no'){
				$entry_meta_query .= " AND meta_key != 'fv_ip' AND meta_key != 'IP'";
			}
			$entry_metas = $wpdb->get_results($entry_meta_query, ARRAY_A);

			$entry_count_query = "SELECT * FROM {$wpdb->prefix}fv_enteries where ". implode(' and ',$paramcount_where). implode(' ',$orderby_count) ;

			$entry_count_result = $wpdb->get_results($entry_count_query, ARRAY_A);
			$entry_count = count($entry_count_result);
			$meta_data = [];


			foreach ($entry_metas as $entry_meta) {
				$meta_data[ $entry_meta['data_id'] ][ $entry_meta['meta_key'] ] = stripslashes($entry_meta['meta_value']);
			}

			$i = 0;
			foreach ($entry_result as $key => $value){
				$i++;
				//$res[$value['id']] = $value + $meta_data[$value['id']];
				$res[$i] = $meta_data[$value['id']] + $value;
			}

            $i=0;
            if($export){
                $temp_res = [];
                foreach ($res as $key => $val) {
                    $i++;
                    unset($val['id']);
                    unset($val['form_id']);
                    unset($val['form_plugin']);
                    $temp_res[$i] = $val;
                }
                $res = $temp_res;
            }

		}

		$distinct_cols_query = "select distinct meta_key from {$wpdb->prefix}fv_entry_meta em join {$wpdb->prefix}fv_enteries e on em.data_id=e.id where form_id='".$form_id."' AND meta_key != 'fv_form_id' AND meta_key != 'fv_plugin'";

		if($saveIp === 'no'){
                $distinct_cols_query .= " AND meta_key != 'fv_ip' AND meta_key != 'IP'";
		}
		if($export == true && $colChoice !== 'all'){
			$distinct_cols_query .= " And meta_key IN('". implode("','",$customQuery) . "')";
		}

		$distinct_cols_res = $wpdb->get_col($distinct_cols_query);


		if($export == true){
			//array_push($distinct_cols_res, '');
		}else{
		    if($entry_count > 0){
                array_push($distinct_cols_res, 'captured');
                array_push($distinct_cols_res, 'status');
                array_push($distinct_cols_res, 'url');
                //array_push($distinct_cols_res, 'status');
                if($saveUserAgent === 'yes'){
                    array_push($distinct_cols_res, 'user_agent');
                }
            }
		}


		if ( empty( $res ) ) {
			$data['lead_count'] = 0;
			$data['leads'] = [];
			$data['columns'] = $distinct_cols_res;
		}
		else{
			$data['lead_count'] = $entry_count;
			$data['leads'] = $res;
			$data['columns'] = $distinct_cols_res;
		}

		/*echo "<pre>";
		print_r($data);
        echo "</pre>";
        die();*/
		return $data;
	}

	static function get_caldera_form_data($request,$plugins,$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,$customColumnsQuery,$colChoice,$filter,$filterValue,$filterOperator,$customRowsQuery){

		global $wpdb;

		$param = '';
		$param_count_query = '';

		$param_where = [];
		$paramcount_where = [];

		if($plugin !== '' && $plugin !== null){
			if(trim($form_id) !== '' || $form_id !== null){
				$param_where[] = "form_id='".$form_id."' ";
				$paramcount_where[] = "form_id='".$form_id."'";
			}
		}
		else{
			if(count($plugins) == 0){
				return [];
			}
			$res = self::get_first_param($plugins);
			$form_id = $res['formid'];
			if($form_id == '' || $form_id !== null){
				$param_where[] = "form_id='".$res['formid']."'";
				$paramcount_where[] = "form_id='".$res['formid']."'";
			}
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
		if($filter == 'undefined' || $filter == ''){
			$filter_param[] = "slug like'%%'";
		}
		else{
			$filter_param[] = "slug='".$filter."'";
		}
		if($filterValue == 'undefined' || $filterValue == ''){
			$filter_param[] = "value like'%%'";
		}
		else{
			if($filterOperator == 'equal'){
				$filter_param[] = "value='".$filterValue."'";
			}
			else if($filterOperator == 'not_equal'){
				$filter_param[] = "value != '".$filterValue."'";
			}
			else if($filterOperator == 'contain'){
				$filter_param[] = "value LIKE '%".$filterValue."%'";
			}
			else if($filterOperator == 'not_contain'){
				$filter_param[] = "value NOT LIKE '%".$filterValue."%'";
			}
		}

		//$paramcount_where[] = "slug='".$filter."'";
		//$paramcount_where[] = "value like '%".$filterValue."%'";

		$orderby[] = "datestamp desc";
		$orderby_count[] = "DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) desc";

		$limit = '';
		if($export == false) {
            if ( $page_num > 1 ) {
	            $limit = ' limit ' . $per_page * ( $page_num - 1 ) . ',' . $per_page;
            } else {
            	$limit = ' limit ' . $per_page;
            }
        }
        // 1.
		$gmt_offset =  get_option('gmt_offset');
		$hours   = (int) $gmt_offset;
		$minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if($hours >= 0 ){
			$time_zone = $hours.':'.$minutes;
		}
		else{
			$time_zone = $hours.':'.$minutes;
		}

        $customQuery = explode(',', $customColumnsQuery);
        $entry_query = "SELECT distinct DATE_FORMAT(ADDTIME(datestamp,'".$time_zone."' ), '%Y/%m/%d %H:%i:%S') as datestamp,e.id,form_id,user_id,status FROM {$wpdb->prefix}cf_form_entries e left join {$wpdb->prefix}cf_form_entry_values ev on e.id=ev.entry_id where ". implode(' and ',$param_where) ." and ". implode(' and ',$filter_param) ." order by ".implode(' ',$orderby) . $limit;

        if($export == true && $customColumnsQuery !== ''){
                if($colChoice === 'custom'){
                    if($customRowsQuery !== ''){
                        $rowsIds = "and e.id in (". implode(",",explode(',', $customRowsQuery)) .")";
                    }else{
                        $rowsIds = '';
                    }
                    $entry_query = "SELECT distinct DATE_FORMAT(ADDTIME(datestamp,'".$time_zone."' ), '%Y/%m/%d %H:%i:%S') as datestamp,e.id,form_id FROM {$wpdb->prefix}cf_form_entries e left join {$wpdb->prefix}cf_form_entry_values ev on e.id=ev.entry_id  where ". implode(' and ',$param_where) . $rowsIds . " and ". implode(' and ',$filter_param) ." order by ".implode(' ',$orderby) . $limit;
                    if (!in_array('datestamp', $customQuery)) {
                        $entry_query = "SELECT e.id,form_id FROM {$wpdb->prefix}cf_form_entries e left join {$wpdb->prefix}cf_form_entry_values ev on e.id=ev.entry_id where ". implode(' and ',$param_where). $rowsIds ." and ". implode(' and ',$filter_param) ." order by ".implode(' ',$orderby) . $limit;
                    }
                }
                else{
                    if($customRowsQuery !== ''){
                        $rowsIds = "and e.id in (". implode(",",explode(',', $customRowsQuery)) .")";
                    }else{
                        $rowsIds = '';
                    }
                    $entry_query = "SELECT distinct DATE_FORMAT(ADDTIME(datestamp,'".$time_zone."' ), '%Y/%m/%d %H:%i:%S') as datestamp,e.id,e.form_id  FROM {$wpdb->prefix}cf_form_entries e left join {$wpdb->prefix}cf_form_entry_values ev on e.id=ev.entry_id where ". implode(' and ',$param_where). $rowsIds ." and ". implode(' and ',$filter_param) ." order by ".implode(' ',$orderby) . $limit;
                }
        }



		$entry_result = $wpdb->get_results($entry_query, ARRAY_A);
		$param_id = [];

		foreach ($entry_result as $key => $value){
			$param_id[] = $value['id'];
		}

		$res = [];
		$entry_count = 0;
		if(count($param_id) > 0){
            $entry_meta_query = "SELECT * FROM {$wpdb->prefix}cf_form_entry_values where entry_id IN (". implode(",",$param_id) . ") and slug not like '%.%'" ;
            if($export == true && $customColumnsQuery!== '' && $colChoice === 'custom'){
			    $entry_meta_query .= " And slug IN('". implode("','",$customQuery) . "')";
            }

			$entry_metas = $wpdb->get_results($entry_meta_query, ARRAY_A);

			$entry_count_query = "SELECT * FROM {$wpdb->prefix}cf_form_entries where ". implode(' and ',$paramcount_where) ." order by ".implode(' ',$orderby);

			$entry_count_result = $wpdb->get_results($entry_count_query, ARRAY_A);
			$entry_count = count($entry_count_result);

			$meta_data = [];

			foreach ($entry_metas as $entry_meta) {
				$meta_data[ $entry_meta['entry_id'] ][ str_replace('.','_',$entry_meta['slug']) ] = stripslashes($entry_meta['value']);
			}

			$i = 0;

			foreach ($entry_result as $key => $value){
				$i++;
                $res[$i] = $meta_data[$value['id']] + $value;
			}

            $i=0;
            if($export){
                $temp_res = [];
                foreach ($res as $key => $val) {
                    $i++;
                    unset($val['id']);
                    unset($val['form_id']);
                    $temp_res[$i] = $val;
                }
                $res = $temp_res;
            }
		}



		$distinct_cols_query = "select distinct REPLACE(slug,'.','_') from {$wpdb->prefix}cf_form_entry_values ev join {$wpdb->prefix}cf_form_entries e on ev.entry_id=e.id where form_id='".$form_id."' and slug not like '%.%'";

		if($export == true && $colChoice !== 'all'){
			$distinct_cols_query .= " And slug IN('". implode("','",$customQuery) . "')";
		}

		$distinct_cols_res = $wpdb->get_col($distinct_cols_query);


		if($export == true){
			//array_push($distinct_cols_res, '');
		}else{
		    if($entry_count > 0){
                array_push($distinct_cols_res, 'datestamp');
            }
		}

		if ( empty( $res ) ) {
			$data['lead_count'] = 0;
			$data['leads'] = [];
			$data['columns'] = $distinct_cols_res;
		}
		else{
			$data['lead_count'] = $entry_count;
			$data['leads'] = $res;
			$data['columns'] = $distinct_cols_res;
		}

		$data['forms_plugin'] = $plugins;

        return $data;
	}
	static function get_forms(\WP_REST_Request $request){

		$post_type = $request->get_param( 'post_type' );

		$class = '\WPV_FV\Integrations\\'.ucfirst($post_type);

		$res = $class::get_forms($post_type);

		if($res == null ){
			$res['no_form'] = ['id' => 'no_form', 'name' => 'No Form'];
		}
		return $res;
	}

	static function get_first_param($plugin){

		$plugin_name = array_keys($plugin);

		$res['plugin'] = $plugin_name[0];
		$class = '\WPV_FV\Integrations\\'.ucfirst($plugin_name[0]);

		$form_name = $class::get_forms($plugin_name[0]);


		if(is_array($form_name) && count($form_name) == 0 ){
			$form_name[] = ['id' => 'no_form', 'name' => 'No Form'];
		}
		else{
			$form_name[] = ['id' => 'no_form', 'name' => 'No Form'];
		}
		$form_key = array_keys($form_name);
		$res['formid'] = $form_name[$form_key[0]]['id'];

		return $res;
	}

	static function set_forms_option( \WP_REST_Request $request ){
		$forms_data = $request->get_param( 'columns' );
		$formName = $request->get_param( 'formName' );
		$pluginName = $request->get_param( 'pluginName' );

		$saved_data = get_option('fv-keys');

		$data = $saved_data;
		$data[$pluginName.'_'.$formName] = $forms_data;

		update_option( 'fv-keys', $data,false);
		return 'saved';
	}

	static function get_forms_option( \WP_REST_Request $request ){
		$formName = $request->get_param( 'formName' );
		$pluginName = $request->get_param( 'pluginName' );

		$saved_data = get_option('fv-keys');

		$data = [];
		if($formName !== ''){
			if(array_key_exists($pluginName.'_'.$formName,$saved_data)){
				$data = $saved_data[$pluginName.'_'.$formName];
			}
		}

		return $data;
	}

	static function get_dates($queryType){
		switch($queryType){
			case 'Today':
				$dates['fromDate'] = date("Y-m-d H:i:s");
				$dates['endDate'] = date("Y-m-d H:i:s");

				return $dates;
				break;

			case 'Yesterday':
				$dates['fromDate'] = date('Y-m-d H:i:s',strtotime("-1 days"));
				$dates['endDate'] = date('Y-m-d H:i:s',strtotime("-1 days"));

				return $dates;
				break;

			case 'Last_7_Days':
				$dates['fromDate'] = date('Y-m-d H:i:s',strtotime("-6 days"));
				$dates['endDate'] = date('Y-m-d H:i:s');

				return $dates;
				break;

			case 'This_Week' :
				$start_week = get_option('start_of_week');
				if($start_week != 0){
					if(date('D')!='Mon')
					{
						$staticstart = date('Y-m-d',strtotime('last Monday'));
					}else{
						$staticstart = date('Y-m-d');
					}

					if(date('D')!='Sat')
					{
						$staticfinish = date('Y-m-d',strtotime('next Sunday'));
					}else{

						$staticfinish = date('Y-m-d');
					}
				}
				else{
					if(date('D')!='Sun')
					{
						$staticstart = date('Y-m-d',strtotime('last Sunday'));
					}else{
						$staticstart = date('Y-m-d');
					}

					if(date('D')!='Sat')
					{
						$staticfinish = date('Y-m-d',strtotime('next Saturday'));
					}else{

						$staticfinish = date('Y-m-d');
					}
				}
				$dates['fromDate'] = $staticstart;
				$dates['endDate'] = $staticfinish;
				return $dates;
				break;

			case 'Last_Week':
				$start_week = get_option('start_of_week');
				if($start_week != 0) {
					$previous_week = strtotime( "-1 week +1 day" );
					$start_week    = strtotime( "last monday midnight", $previous_week );
					$end_week      = strtotime( "next sunday", $start_week );
				}
				else{
					$previous_week = strtotime( "-1 week +1 day" );
					$start_week    = strtotime( "last sunday midnight", $previous_week );
					$end_week      = strtotime( "next saturday", $start_week );
				}
				$start_week = date("Y-m-d",$start_week);
				$end_week = date("Y-m-d",$end_week);

				$dates['fromDate'] = $start_week;
				$dates['endDate'] = $end_week;

				return $dates;
				break;

			case 'Last_30_Days':
				$dates['fromDate'] = date('Y-m-d h:m:s',strtotime("-29 days"));
				$dates['endDate'] = date('Y-m-d h:m:s');

				return $dates;
				break;

			case 'This_Month':
				$dates['fromDate'] = date('Y-m-01');
				$dates['endDate'] = date('Y-m-t');

				return $dates;
				break;

			case 'Last_Month':
				//$dates['fromDate'] = date('Y-m-01',strtotime("-1 month"));
				//$dates['endDate'] = date('Y-m-t',strtotime("-1 month"));
				$dates['fromDate'] = date('Y-m-01',strtotime("first day of last month"));
				$dates['endDate'] = date('Y-m-t',strtotime("last day of last month"));

				return $dates;
				break;

			case 'This_Quarter' :
				$current_month = date('m');
				$current_year = date('Y');
				if($current_month>=1 && $current_month<=3)
				{
					$start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
					$end_date = strtotime('31-March-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
				}
				else  if($current_month>=4 && $current_month<=6)
				{
					$start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
					$end_date = strtotime('30-June-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
				}
				else  if($current_month>=7 && $current_month<=9)
				{
					$start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
					$end_date = strtotime('30-September-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
				}
				else  if($current_month>=10 && $current_month<=12)
				{
					$start_date = strtotime('1-October-'.$current_year);  // timestamp or 1-October 12:00:00 AM
					$end_date = strtotime('31-December-'.($current_year+1));  // timestamp or 1-January Next year 12:00:00 AM means end of 31 December this year
				}

				$dates['fromDate']= date('Y-m-d',$start_date);
				$dates['endDate']= date('Y-m-d',$end_date);
				return $dates;
				break;

			case 'Last_Quarter' :
				$current_month = date('m');
				$current_year = date('Y');

				if($current_month>=1 && $current_month<=3)
				{
					$start_date = strtotime('1-October-'.($current_year-1));  // timestamp or 1-October Last Year 12:00:00 AM
					$end_date = strtotime('31-December-'.($current_year-1));  // // timestamp or 1-January  12:00:00 AM means end of 31 December Last year
				}
				else if($current_month>=4 && $current_month<=6)
				{
					$start_date = strtotime('1-January-'.$current_year);  // timestamp or 1-Januray 12:00:00 AM
					$end_date = strtotime('31-March-'.$current_year);  // timestamp or 1-April 12:00:00 AM means end of 31 March
				}
				else  if($current_month>=7 && $current_month<=9)
				{
					$start_date = strtotime('1-April-'.$current_year);  // timestamp or 1-April 12:00:00 AM
					$end_date = strtotime('30-June-'.$current_year);  // timestamp or 1-July 12:00:00 AM means end of 30 June
				}
				else  if($current_month>=10 && $current_month<=12)
				{
					$start_date = strtotime('1-July-'.$current_year);  // timestamp or 1-July 12:00:00 AM
					$end_date = strtotime('30-September-'.$current_year);  // timestamp or 1-October 12:00:00 AM means end of 30 September
				}
				$dates['fromDate']= date('Y-m-d',$start_date);
				$dates['endDate']= date('Y-m-d',$end_date);
				return $dates;
				break;

			case 'This_Year':
				$dates['fromDate'] = date('Y-01-01');
				$dates['endDate'] = date('Y-12-t');

				return $dates;
				break;

			case 'Last_Year':
				$dates['fromDate'] = date('Y-01-01',strtotime("-1 year"));
				$dates['endDate'] = date('Y-12-t',strtotime("-1 year"));

				return $dates;
				break;
			}
	}
	static function get_analytic_data( \WP_REST_Request $request ){
		global $wpdb;
		$param = $request->get_param('data');

		$formName = $param['formName'];
		$pluginName = $param['pluginName'];
		$filterType = $param['filterType'];
		$queryType = $param['queryType'];
		$filter = '';

        //die();
		$gmt_offset =  get_option('gmt_offset');
		$hours   = (int) $gmt_offset;
		$minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if($hours >= 0 ){
			$time_zone = '+'.$hours.':'.$minutes;
		}
		else{
			$time_zone = $hours.':'.$minutes;
		}

		$data=[];

		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		if($queryType !== 'Custom'){
			$dates = self::get_dates($queryType);

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
			$toDate = new \DateTime($param['toDate']);
			$toDate->setTimezone($tz);

			$fromDate = $fromDate->format('Y-m-d');
			$toDate = $toDate->format('Y-m-d');
		}

		if($filterType == 'week'){
			$start_week = get_option('start_of_week');
			if($start_week == 0){
				$filter = '%U';
				$dayStart = 'Sunday';
				$weekNumber = '';
			}
			else{
				$filter = '%u';
				$dayStart = 'Monday';
				$weekNumber = '-1';
			}

			if($pluginName !== null && $pluginName !== '' ){
				if($pluginName == 'caldera'){
					$label = "STR_TO_DATE(CONCAT(DATE_FORMAT(datestamp, '%Y'),' ', DATE_FORMAT(datestamp, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
				}
                else if($pluginName == 'ninja'){
                    $label = "STR_TO_DATE(CONCAT(DATE_FORMAT(post_date, '%Y'),' ', DATE_FORMAT(post_date, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
                }
				else{
					$label = "STR_TO_DATE(CONCAT(DATE_FORMAT(`captured`, '%Y'),' ', DATE_FORMAT(`captured`, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
				}
			}
			else{
				$forms_plugin = array_values($data['forms_plugin']);
				if(count($forms_plugin) == 0){
					return;
				}
				$plugin_first = array_values($data['forms_plugin'])[0];
				if($plugin_first == 'Caldera'){
					$label = "STR_TO_DATE(CONCAT(DATE_FORMAT(datestamp, '%Y'),' ', DATE_FORMAT(datestamp, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
				}
                else if($plugin_first == 'ninja'){
                    $label = "STR_TO_DATE(CONCAT(DATE_FORMAT(post_date, '%Y'),' ', DATE_FORMAT(post_date, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
                }
				else{
					$label = "STR_TO_DATE(CONCAT(DATE_FORMAT(`captured`, '%Y'),' ', DATE_FORMAT(`captured`, '".$filter."')".$weekNumber.",' ', '".$dayStart."'), '%X %V %W')";
				}
			}
		}

		else if($filterType == 'month'){
			$filter = '%b';
			if($pluginName !== null && $pluginName !== '' ){
				if($pluginName == 'caldera'){
					$label = "concat(DATE_FORMAT(`datestamp`, '%b'),'(',DATE_FORMAT(`datestamp`, '%y'),')')";
				}
                else if($pluginName == 'ninja'){
                    $label = "concat(DATE_FORMAT(`post_date`, '%b'),'(',DATE_FORMAT(`post_date`, '%y'),')')";
                }
				else{
					$label = "concat(DATE_FORMAT(`captured`, '%b'),'(',DATE_FORMAT(`captured`, '%y'),')')";
				}

			}
			else{
				$plugin_first = array_values($data['forms_plugin'])[0];
				if($plugin_first == 'Caldera'){
					$label = "concat(DATE_FORMAT(datestamp, '%b'),'(',DATE_FORMAT(datestamp, '%y'),'))'";
				}
                else if($plugin_first == 'ninja'){
                    $label = "DATE_FORMAT(post_date, '%b')";
                }
				else{
					$label = "concat(DATE_FORMAT(captured, '%b'),'(',DATE_FORMAT(captured, '%y'),'))'";
				}
			}
		}
		else if($filterType == 'day'){
			$filter = '%j';
			if($pluginName !== null && $pluginName !== '' ){
				if($pluginName == 'caldera'){
					$label = "MAKEDATE(DATE_FORMAT(datestamp, '%Y'), DATE_FORMAT(datestamp, '%j'))";
				}
                else if($pluginName == 'ninja'){
                    $label = "MAKEDATE(DATE_FORMAT(post_date, '%Y'), DATE_FORMAT(post_date, '%j'))";
                }
				else{
					$label = "MAKEDATE(DATE_FORMAT(`captured`, '%Y'), DATE_FORMAT(`captured`, '%j'))";
				}
			}
			else{
				$plugin_first = array_values($data['forms_plugin'])[0];
				if($plugin_first == 'Caldera'){
					$label = "MAKEDATE(DATE_FORMAT(datestamp, '%Y'), DATE_FORMAT(datestamp, '%j'))";
				}
                else if($plugin_first == 'ninja'){
                    $label = "MAKEDATE(DATE_FORMAT(post_date, '%Y'), DATE_FORMAT(datestamp, '%j'))";
                }
				else{
					$label = "MAKEDATE(DATE_FORMAT(`captured`, '%Y'), DATE_FORMAT(`captured`, '%j'))";
				}
			}
		}

		if($filter == '%b'){
			$orderby = '%m';
		}
		else
		{
			$orderby = $filter;
		}

		if($pluginName !== null && $pluginName !== '' ){
			if($pluginName == 'caldera'){
				$res = self::get_caldera_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone,$data['forms_plugin']);
			}
            else if($pluginName == 'ninja'){
                $res = Ninja::get_ninjaforms_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone,$data['forms_plugin']);
            }
			else{
				//$data = self::get_tbl_data($request,$data['forms_plugin']);
				$res = self::fv_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone);
			}
		}
		else{
			$plugin_first = array_values($data['forms_plugin'])[0];
			if($plugin_first == 'Caldera'){
				$res = self::get_caldera_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone,$data['forms_plugin']);
			}
			else if($plugin_first == 'ninja'){
                $res = self::get_ninjaforms_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone,$data['forms_plugin']);
            }
			else{
				$res = self::fv_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone);
			}
		}

		if(count($res['data']) > 0){
			if($res['data'][0]['Label'] == null || $res['data'][0]['Label'] == ''){
				$first_date = $res['data'][1]['Label'];
				$res['data'][0]['Label'] = substr($first_date,0,4).'-01-01';
			}
		}

		return $res;
	}

	static function fv_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone){
		global $wpdb;
		$param = '';
		$param .= " Where DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
		$param .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";

		if($pluginName !== '' && $pluginName !== null){
			$param .= " and form_plugin='".$pluginName."'";

			if($formName !== '' && $formName !== null){
				$param .= " and form_id='".$formName."'";
			}
		}
		else{
			$res = self::get_first_param($data['forms_plugin']);
			$param .= " and form_plugin='".$res['plugin']."'";
			if($formName == '' || $formName == null){
				$param .= " and form_id='".$res['formid']."'";
			}
		}

		//$data_query = "SELECT DATE_FORMAT(`captured_gmt`, '".$filter."') as week, count(*) as count from {$wpdb->prefix}fv_enteries ".$param." GROUP BY DATE_FORMAT(`captured_gmt`, '".$filter."')";
		$data_query = "SELECT ".$label." as Label,CONCAT(DATE_FORMAT(`captured`, '".$filter."'),'(',DATE_FORMAT(`captured`, '%y'),')') as week, count(*) as count,CONCAT(DATE_FORMAT(`captured`, '%y'),'-',DATE_FORMAT(`captured`, '".$orderby."')) as ordering from {$wpdb->prefix}fv_enteries ".$param." GROUP BY DATE_FORMAT(`captured`, '".$orderby."'),ordering ORDER BY ordering";

		$data['data'] = $wpdb->get_results($data_query, ARRAY_A);

		return $data;
	}
	static function get_caldera_analytic_data($fromDate,$toDate,$formName,$pluginName,$filter,$data,$orderby,$label,$time_zone,$allplugin){
		global $wpdb;
		$param = '';

		$param .= " Where DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
		$param .= " and DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";

		if($pluginName !== '' && $pluginName !== null){
			if($formName !== '' && $formName !== null){
				$param .= " and form_id='".$formName."'";
			}
		}
		else{
			$res = self::get_first_param($allplugin);
			if($formName == '' || $formName == null){
				$param .= " and form_id='".$res['formid']."'";
			}
		}

		$data_query = "SELECT ".$label." as Label, CONCAT(DATE_FORMAT(datestamp, '".$filter."'),'(',DATE_FORMAT(datestamp, '%y'),')') as week, count(*) as count,CONCAT(DATE_FORMAT(datestamp, '%y'),'-',DATE_FORMAT(datestamp, '".$orderby."')) as ordering from {$wpdb->prefix}cf_form_entries ".$param." GROUP BY DATE_FORMAT(datestamp, '".$orderby."'),ordering ORDER BY ordering";


		$data['data'] = $wpdb->get_results($data_query, ARRAY_A);

		return $data;
	}

	function fv_export_csv(){
		if(isset($_POST['btnExport'])){
			$gdprSettings = get_option( 'fv_gdpr_settings' );

			global $wpdb;

			if($gdprSettings['export_reason'] === 'yes'){
				$wpdb->insert(
					$wpdb->prefix.'fv_logs',
					array(
						'user_id' => get_current_user_id(),
						'event' => 'export',
						'description' => $_REQUEST['exportDesctiption'],
						'export_time' => current_time( 'mysql', $gmt = 0 ),
						'export_time_gmt' => current_time( 'mysql', $gmt = 1 )
					)
				);
			}

			$forms = [];
			$data['forms_plugin'] = apply_filters('fv_forms', $forms);

			$per_page = '';
			$page_num = '';
			$customColumnsQuery = '';
			$customRowsQuery = '';
			$form_id = $_REQUEST['formName'];
			$plugin = $_REQUEST['PluginName'];
			$queryType = $_REQUEST['queryType'];
			$customColumns = $_REQUEST['slug'];
			$colChoice = $_REQUEST['ColumnsChoice'];
			$selectedRowsIds = $_REQUEST['selectedRowsIds'];

			$filter      = '';
			$filterValue = '';
			$filterOperator = '';
			if(WPV_FV_PLAN == 'PRO') {
				$filter      = $_REQUEST['filter'];
				$filterValue = $_REQUEST['filterValue'];
				$filterOperator = $_REQUEST['filterOperator'];
			}


			if($customColumns !== 'undefined'){
                $customColumnsQuery = $customColumns;
            }else{
			    $customColumnsQuery = '';
            }

            if($selectedRowsIds !== 'undefined'){
                $customRowsQuery = $selectedRowsIds;
            }else{
                $customRowsQuery = '';
            }
			$gmt_offset =  get_option('gmt_offset');
			$hours   = (int) $gmt_offset;
			$minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

			if($hours > 0 ){
				$time_zone = '+'.$hours.':'.$minutes;
			}
			else{
				$time_zone = '+'.$hours.':'.$minutes;
			}

			if($queryType !== 'Custom'){
				$dates = self::get_dates($queryType);

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

				$fromDate = new \DateTime($_REQUEST['from']);
				$fromDate->setTimezone($tz);

				$toDate = new \DateTime($_REQUEST['to']);
				$toDate->setTimezone($tz);

				$fromDate = $fromDate->format('Y-m-d');
				$toDate = $toDate->format('Y-m-d');
			}
			if($page_num == '')
			{
				$page_num = 1;
			}
			$request = '';
			$export = true;
			if($plugin === 'caldera'){
				$data = self::get_caldera_form_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,$customColumnsQuery,$colChoice,$filter,$filterValue,$filterOperator,$customRowsQuery);
			}
            else if($plugin == 'ninja'){
                $data = Ninja::get_ninja_forms_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$plugin,$export,$customColumnsQuery,$colChoice,$filter,$filterValue,$filterOperator,$customRowsQuery);
            }
			else{
				$data = self::get_tbl_data($request,$data['forms_plugin'],$fromDate,$toDate,$form_id,$page_num,$per_page,$export,$plugin,$customColumnsQuery,$colChoice,$filter,$filterValue,$filterOperator,$customRowsQuery);
            }

			if(empty($data['leads'])){
			    // will check soon
            }

            /*echo "<pre>";
			print_r($data);
            echo "</pre>";

            die()*/
			//ob_clean();
			ob_start();
			// plugin checker created cause ninja form leads starting index is 0.
			$plugin_checker = 1;
            if($plugin == 'ninja'){
                $plugin_checker = 0;
                $loop_out = count($data['leads']);
            }
            else{
	            $loop_out = count($data['leads']) + 1;
            }
			$final_leads = [];

            if($colChoice === 'all'){
                $colss = array_flip(explode(',',$_REQUEST['allColumns']));
            }else{
                $colss = array_flip(explode(',',$customColumns));
            }



			foreach ( $colss as $key => $value ) {
				$colss[$key] = '';
			}
			for($i = $plugin_checker; $i < $loop_out; $i++){
				$final_leads[] = array_merge($colss, $data['leads'][$i]);
			}


			$cols = array_merge(array_keys($final_leads[0]),$data['columns']);
			$cols = array_unique($cols);



            $default_vals = [];
			foreach ($cols as $key => $value){
				$default_vals[$value] = '';
			}

			$saved_keys_data = get_option('fv-keys');

			if($saved_keys_data === false){
				$saved_keys_data = [];
			}

			$key_exist = [];
			if($form_id !== ''){
				if(array_key_exists($plugin.'_'.$form_id,$saved_keys_data)){
					$key_exist = $saved_keys_data[$plugin.'_'.$form_id];
				}
			}
			$saved_keys = [];
			if(count($key_exist)>0){
				foreach ($key_exist as $key => $value){
					$saved_keys[$value['colKey']] = $value['alias'];
				}
				$o_keys = array_values($cols);
				$label = [];

				foreach ($o_keys as $key => $value){
					if(array_key_exists($value,$saved_keys)){
						$label[$value] = $saved_keys[$value];
					}
					else{
						$label[$value] = $value;
					}
				}
			}
			else{
			    if($final_leads[0] != '')
				$label = array_values($cols);
			    else
                $label = '';
			}

            if(count($key_exist) <= 0){
                for ($i=0; $i<count($label); $i++){
                    if($label[$i] === "captured"  || $label[$i] === "datestamp"){
                        $label[$i] = "Submission Date";
                    }
                }
            }
/*
            echo "<pre>";
            print_r(array_values($label));
            echo "</pre>";

            die();*/

			if(isset($final_leads[0])) {
				$fp = fopen( 'php://output', 'w' );
				//$user_CSV[0] = array_keys($data['leads'][1]);
				fwrite($fp, "\xEF\xBB\xBF");
				fputcsv( $fp, array_values( $label ) );

				foreach ( $final_leads as $value ) {

					$value = wp_parse_args($value, $default_vals);
					fputcsv( $fp, $value,',', '"');
				}
				fclose( $fp );
			}
			$data = ob_get_contents();

			$form_name = $_REQUEST['formText'];
			$form_name = str_replace(' ','',$form_name);


			$datee = (new \DateTime());
			$datee->setTimezone($tz);

			$csv_date =  $datee->format('dmY_his');
			$csv_name = $plugin.'_'.$form_name.'_'.$csv_date;


			header('Pragma: public');
			header('Expires: 0');
			header('Cache-Control: must-revalidate, post-check=0, pre-check=0');
			header('Cache-Control: private', false);
			header('Content-Type: text/csv;charset=utf-8');
			header('Content-Disposition: attachment;filename='.$csv_name.'.csv');
			die();
		}
	}
	static function set_dbpanel_settings( \WP_REST_Request $request ){
		$dbPanel = $request->get_param( 'dbPanel' );
		$panelData = $request->get_param( 'panelData' );

		$data['panelNumber'] = $dbPanel;
		$data['panelData'] = $panelData;

		update_option( 'fv-db-settings', $data,false);

		return 'Saved';
	}
	static function get_dbpanel_settings( \WP_REST_Request $request ){
		$data = get_option( 'fv-db-settings');

		if($data == false){

			$forms = [];
			$plugins = apply_filters('fv_forms', $forms);

			$class = '\WPV_FV\Integrations\\'.ucfirst(array_keys($plugins)[0]);

			$plugin_forms = $class::get_forms(array_keys($plugins)[0]);
			$plugin = array_keys($plugins)[0];

			//$form = $plugin_forms[0];

			$data['panelNumber'] = '1';
			$data['panelData'][0] = [
				'queryType' => 'Last_30_Days',
				'formName' => $plugin_forms,
				'selectedPlugin' => $plugin,
				'selectedForm' => array_keys($plugin_forms)[0],
			];
		}
		return $data;
	}

	static function set_ip_settings(\WP_REST_Request $request){
		$saveIp = $request->get_param( 'saveIP' );
		update_option( 'fv-ip-save', $saveIp,false);

		return 'Saved';
	}

	static function get_ip_settings(\WP_REST_Request $request){
		$saveIp = get_option( 'fv-ip-save');

		return $saveIp;
	}
	static function get_gdpr_settings(\WP_REST_Request $request){
		$gdpr_setting = get_option( 'fv_gdpr_settings');

		return $gdpr_setting;
	}
	static function get_forms_entry_count(\WP_REST_Request $request){
		global $wpdb;
		$forms = [];
		$data['forms_plugin'] = apply_filters('fv_forms', $forms);

		$queryType = $request->get_param( 'duration' );
		$dates = self::get_dates($queryType);

		$gmt_offset =  get_option('gmt_offset');
		$hours   = (int) $gmt_offset;
		$minutes = ( $gmt_offset - floor( $gmt_offset ) ) * 60;

		if($hours >= 0 ){
			$time_zone = '+'.$hours.':'.$minutes;
		}
		else{
			$time_zone = $hours.':'.$minutes;
		}

		$tz = new \DateTimeZone($time_zone);

		$fromDate = new \DateTime($dates['fromDate']);
		$fromDate->setTimezone($tz);
		$toDate = new \DateTime($dates['endDate']);
		$toDate->setTimezone($tz);

		$fromDate = $fromDate->format('Y-m-d');
		$toDate = $toDate->format('Y-m-d');

		$entry_count = [];

		foreach ( $data['forms_plugin'] as $key => $value ) {

			$class = '\WPV_FV\Integrations\\'.ucfirst($key);

			$forms = $class::get_forms($key);

			if($forms == null ){
				$forms['no_form'] = ['id' => 'no_form', 'name' => 'No Form'];
			}


			if($key === 'caldera'){
				foreach ( $forms as $formKey => $formValue ) {
					$param = " where form_id='".$formKey."' and DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
					$param .= " and DATE_FORMAT(datestamp,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
					$data_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}cf_form_entries ".$param );

					$entry_count[$formKey]= [
						'plugin' =>  $value,
						'count' =>  $data_count,
						'formName' =>  $formValue['name']
					];
				}
			}
			else if($key === 'ninja'){
				foreach ( $forms as $formKey => $formValue ) {
					$param = " and DATE_FORMAT(post_date,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
					$param .= " and DATE_FORMAT(post_date,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
					$entry_count_query = "SELECT count(*) FROM {$wpdb->prefix}postmeta pm
						LEFT JOIN {$wpdb->prefix}posts p ON p.ID = pm.post_id
						WHERE meta_key='_form_id' and meta_value='" . $formValue['id'] . "'" . $param ;
					$data_count = $wpdb->get_var( $entry_count_query);

					$entry_count[$formKey]= [
						'plugin' =>  $value,
						'count' =>  $data_count,
						'formName' =>  $formValue['name']
					];
				}
			}
			else{
				foreach ( $forms as $formKey => $formValue ) {
					$param = " where form_id='".$formKey."' and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
					$param .= " and DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
					$data_count = $wpdb->get_var( "SELECT COUNT(*) FROM {$wpdb->prefix}fv_enteries ".$param );

					$entry_count[$formKey]= [
						'plugin' =>  $value,
						'count' =>  $data_count,
						'formName' =>  $formValue['name']
					];
				}
			}
		}
		$entry_count['fromDate'] = $fromDate;
		$entry_count['toDate'] = $toDate;

		return $entry_count;
	}
}

ApiEndpoint::instance();
