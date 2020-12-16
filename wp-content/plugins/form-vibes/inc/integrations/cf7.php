<?php

namespace WPV_FV\Integrations;

use WPV_FV\Classes\DbManager;
use WPV_FV\Classes\ApiEndpoint;
class Cf7 extends DbManager{

    private static $_instance = null;

    // array for skipping fields or unwanted data from the form data.
    protected $skip_fields = [];

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
	    $this->plugin_name = 'cf7';

	    $this->set_skip_fields();



        add_action('wpcf7_before_send_mail', [$this, 'before_send_mail']);

        add_filter('fv_forms', [ $this, 'register_form' ]);
	}

	public function register_form($forms){
		$forms[$this->plugin_name] = 'Contact Form 7';
		return $forms;
	}

	protected function set_skip_fields(){
	    // name of all fields which should not be stored in our database.
	    $this->skip_fields = ['g-recaptcha-response','_wpcf7','_wpcf7_version', '_wpcf7_locale', '_wpcf7_unit_tag', '_wpcf7_container_post'];
	}

	public function before_send_mail( $contact_form ){
        $data = [];

	    $submission = \WPCF7_Submission::get_instance();
	    // getting all the fields or data from the form.
	    $posted_data = $submission->get_posted_data();


	    // File Upload
		/*
		$files = $submission->uploaded_files();

		$upload_dir    = wp_upload_dir();
		$fv_dirname = $upload_dir['basedir'].'/fv_uploads';
		$time_now = time();
		if ( ! file_exists( $fv_dirname ) ) {
			wp_mkdir_p( $fv_dirname );
		}

		foreach ($files as $file_key => $file) {
			$posted_data[$file_key] = $time_now.'-'.basename($file);
			//array_push($uploaded_files, $time_now.'-'.basename($file));
			copy($file, $fv_dirname.'/'.$time_now.'-'.basename($file));
		}

		*/

		//End File Upload Code

	    //loop for skipping fields from the posted_data.
	    foreach ( $posted_data as $key => $value){
	        if(in_array( $key, $this->skip_fields)){
	            // unset will destroy the skip's fields.
	            unset($posted_data[$key]);
            }
	        else if(gettype($value) === 'array' ){

				$posted_data[$key] = implode(', ',$value);
	        }
        }

	    if($submission){

            $data['plugin_name']    =   $this->plugin_name;
            $data['id']             =   $contact_form->id();
            $data['captured']       =   current_time( 'mysql', $gmt = 0 );
            $data['captured_gmt']   =   current_time( 'mysql', $gmt = 1 );

	        $data['title']          =   $contact_form->title();
		    $data['url']            =   $submission->get_meta('url');

            $posted_data['fv_plugin']       =   $this->plugin_name;
            $posted_data['fv_form_id']      =   $contact_form->id();

		    if(get_option('fv_gdpr_settings') !== false){
			    $gdpr = get_option( 'fv_gdpr_settings');
			    $saveIp = $gdpr['ip'];
		    }
		    else{
			    $saveIp = get_option( 'fv-ip-save');
		    }

            if($saveIp === 'yes'){
			    $posted_data['IP']              = $this->set_user_ip();
		    }

	        $data['posted_data']    =   $posted_data;

        }
        $this->insert_enteries($data);
    }

    static function get_forms($param) {
	    $post_type = $param;

	    if($post_type == 'cf7'){
		    $post_type = 'wpcf7_contact_form';
	    }
	    $args = array(
		    'post_type'   => $post_type,
		    'order'       => 'ASC',
	    );

	    $forms = get_posts( $args );

	    $data = [];
	    foreach ( $forms as $form ) {
		    $data[$form->ID] = [
		    	                 'id' => $form->ID,
			                     'name' => $form->post_title
		                       ];
		}
	    return $data;
    }

    static function get_submission_data($param){

        $meta_key = [];
        $cols = [];

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
            $param_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '". $fromDate . "'";
            $paramcount_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) >= '".$fromDate."'";
        }
        if($toDate !== '' && $toDate !== null){
            if($fromDate !== ''){
                $param_where[] = " DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
                $paramcount_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
            }
            else{
                $param_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '". $toDate . "'";
                $paramcount_where[] = "DATE_FORMAT(captured,GET_FORMAT(DATE,'JIS')) <= '".$toDate."'";
            }
        }

        if($param['selectedFilter'] == 'undefined' || $param['selectedFilter'] == '' || $param['filterValue'] == 'undefined' || $param['filterValue'] == ''){
            $filter_param[] = "meta_key like'%%'";
        }
        else{
            $filter_param[] = "meta_key='".$param['selectedFilter']."'";
        }
        if($param['filterValue'] == 'undefined' || $param['filterValue'] == ''){
            $filter_param[] = "meta_value like'%%'";
        }
        else{
            if($param['filterOperator'] == 'equal'){
                $filter_param[] = "meta_value='".$param['filterValue']."'";
            }
            else if($param['filterOperator'] == 'not_equal'){
                $filter_param[] = "meta_value != '".$param['filterValue']."'";
            }
            else if($param['filterOperator'] == 'contain'){
                $filter_param[] = "meta_value LIKE '%".$param['filterValue']."%'";
            }
            else if($param['filterOperator'] == 'not_contain'){
                $filter_param[] = "meta_value NOT LIKE '%".$param['filterValue']."%'";
            }
        }

        foreach ($param['columns'] as $key => $val) {
            if($val->visible == 0 || $val->visible == ''){
                $meta_key[] = $val->colKey;
            }
            $cols[] = $val->colKey;
        }

        global $wpdb;
        $gdpr_settings = get_option('fv_gdpr_settings');

        $filter_col_id = "select data_id FROM {$wpdb->prefix}fv_enteries e left join {$wpdb->prefix}fv_entry_meta em on e.id=em.data_id where 
        ". implode(' and ',$filter_param)." and form_id = '".$param['form']."'";
        $filter_col_id_res = $wpdb->get_results($filter_col_id, ARRAY_A);
        $entry_id = [];
        foreach ($filter_col_id_res as $entryId) {
            $entry_id[] = $entryId['data_id'];
        }


        $entry_query = "select * from {$wpdb->prefix}fv_enteries e 
        left JOIN {$wpdb->prefix}fv_entry_meta ev ON e.id=ev.data_id
        where ". implode(' and ',$param_where) ." and ev.meta_key NOT IN ('".implode("','",$meta_key)."') and data_id IN ('".implode("','",$entry_id)."') and form_id = '".$param['form']."'";

        $entry_res = $wpdb->get_results($entry_query, ARRAY_A);

        $meta_data = [];
        $ipChecker = '';
        if($gdpr_settings['ip'] === 'yes'){
            $ipChecker = "";
        }else{
            $ipChecker = "IP";
        }

        foreach ($entry_res as $entry_meta) {

            if($entry_meta['meta_key'] == 'fv_plugin' || $entry_meta['meta_key'] == 'fv_form_id' || $entry_meta['meta_key'] == $ipChecker ){
                continue;
            }
            $meta_data[ $entry_meta['data_id'] ][ $entry_meta['meta_key'] ] = stripslashes($entry_meta['meta_value']);
        }


        if (!in_array('captured', $meta_key)) {
            foreach ($entry_res as $entry_meta) {
                $meta_data[ $entry_meta['data_id'] ][ 'captured' ] = stripslashes($entry_meta['captured']);
            }
        }

        if($gdpr_settings['ua'] !== 'no'){
            if (!in_array('user_agent', $meta_key)) {
                foreach ($entry_res as $entry_meta) {
                    $meta_data[ $entry_meta['data_id'] ][ 'user_agent' ] = stripslashes($entry_meta['user_agent']);
                }
            }
        }

        $res = [];
        foreach ($meta_data as $key => $val) {
            $res[] = $val;
        }

        $final_array = [];
        $final_cols = array_flip(array_diff($cols, $meta_key));
	    foreach ( $final_cols as $key => $value ) {
		    $final_cols[$key] = '';
        }
        for($i = 0; $i<count($res); $i++){
            $final_array[] = array_merge($final_cols, $res[$i]);
        }

	    return $final_array;
    }

}
