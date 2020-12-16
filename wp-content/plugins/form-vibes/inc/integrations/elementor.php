<?php

namespace WPV_FV\Integrations;

use WPV_FV\Classes\DbManager;

class Elementor extends DbManager{

	private static $_instance = null;
	static $forms = [];

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

    public function __construct()
    {
        $this->plugin_name = 'elementor';

        add_action('elementor_pro/forms/new_record', [$this, 'form_new_record'], 10, 2);

	    add_filter('fv_forms', [ $this, 'register_form' ]);

	    if(WPV_FV_PLAN === 'PRO'){
		    add_action( 'elementor/widgets/widgets_registered', [$this, 'elementor_widget_registered']);
	    }
    }

	public function register_form($forms){
		$forms[$this->plugin_name] = 'Elementor Forms';
		return $forms;
	}


    public function form_new_record( $record , $handler ){
	    $data = [];

        $data['plugin_name']    =   $this->plugin_name;
        $data['id']             =   $record->get_form_settings( 'id' );
        $data['captured']       =   current_time( 'mysql', $gmt = 0 );
        $data['captured_gmt']   =   current_time( 'mysql', $gmt = 1 );

        $data['title']          =   $record->get_form_settings( 'form_name' );
        $data['url']            =   $_POST['referrer'];

        $posted_data['fv_plugin']      =   $this->plugin_name;
	    $posted_data                    = $this->field_processor($record);

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
	    $posted_data['fv_form_id']      = $record->get_form_settings( 'id' );
        $data['posted_data']            = $posted_data;

		$this->field_processor($record);
        $this->insert_enteries($data);

    }

    public function field_processor($record){
	    $data = $record->get('fields');

		$save_data = [];
	    foreach ( $data as $key => $value ) {
		    $save_data[$key] = $value['value'];
	    }
		return $save_data;

    }
	static function get_forms($param) {
		global $wpdb;

		$sql_query = "SELECT *  FROM {$wpdb->prefix}postmeta
		WHERE meta_key LIKE '_elementor_data'
		AND meta_value LIKE '%\"widgetType\":\"form\"%'
		AND post_id IN (
			SELECT id FROM {$wpdb->prefix}posts
			WHERE post_status LIKE 'publish'
		)";

		$results = $wpdb->get_results($sql_query);


		if (!count($results)){
			return;
		}
		foreach($results as $result){
			$post_id = $result->post_id;
			$data = $result->meta_value;
			$json = json_decode($data,true);

			if($json){
				foreach($json as $j){
					self::find_form($j,$post_id,$json);
				}
			}
		}
		return self::$forms;

	}
	static function find_form($element_data,$post_id,$original_data){
		if(!$element_data['elType']){
			return;
		}

		if ( 'widget' === $element_data['elType'] && ('form' === $element_data['widgetType'] || 'global' === $element_data['widgetType'] )) {
			$id = self::check_global($post_id);


			if('form' === $element_data['widgetType'] ){
				if($id == null || $id === 'NULL'){
					self::$forms[$element_data['id']] = [
						'id' => $element_data['id'],
						'name' => $element_data['settings']['form_name'],
					];
				}
				else{
					self::$forms[$id] = [
						'id' => $id,
						'name' => $element_data['settings']['form_name'],
					];
				}
			}
		}

		if ( ! empty( $element_data['elements'] ) ) {
			foreach ( $element_data['elements'] as $element ) {
				self::find_form( $element,$post_id,$original_data);
			}
		}
	}

	static function check_global($post_id){
		global $wpdb;
		//check global key exist in meta key
		$sql_query1 = "SELECT *  FROM {$wpdb->prefix}postmeta
		WHERE meta_key LIKE '_elementor_global_widget_included_posts'
		AND post_id={$post_id}";

		$results1 = $wpdb->get_results($sql_query1);

		if (!count($results1)){
			//not global widget
			return;
		}

		foreach($results1 as $result1){
			$global_id = $result1->post_id;
			$data1 = $result1->meta_value;
			$json1 = maybe_unserialize($data1);

			$posts = implode(",",array_keys($json1)) ;

			$qry = "SELECT id FROM {$wpdb->prefix}posts where ID IN ({$posts}) ";

			$res = $wpdb->get_results($qry);
			if( !count($res)){
				return;
			}

			$id = $res[0]->id;

			$sql_query = "SELECT *  FROM {$wpdb->prefix}postmeta
							WHERE meta_key LIKE '_elementor_data' AND post_id = {$id}";

			$results = $wpdb->get_results($sql_query);

			if (!count($results)){
				return;
			}

			foreach($results as $result){
				$post_id = $result->post_id;
				$data = $result->meta_value;
				$json = json_decode($data,true);

				if($json){
					foreach($json as $j){
						$abc = self::get_global_widget_id($j,$post_id,$global_id);

						if($abc !== null && trim($abc) !== ''){
							return $abc;
						}
					}
				}
			}
		}
	}

	static function get_global_widget_id($element_data,$post_id,$global_id){
		if(!$element_data['elType']){
			return;
		}

		if ( 'widget' === $element_data['elType'] && 'global' === $element_data['widgetType'] ) {
			if($global_id == $element_data['templateID']){
				return $element_data['id'];
			}
		}

		if ( ! empty( $element_data['elements'] ) ) {
			foreach ( $element_data['elements'] as $element ) {
				$a = self::get_global_widget_id( $element,$post_id,$global_id);
				if($a !== '' && $a !== null){
					return $a;
				}
			}
		}
	}
    static function get_submission_data($param){
        $class = '\WPV_FV\Integrations\\'.ucfirst('cf7');
        $data = $class::get_submission_data($param);

        return $data;
    }

    public function elementor_widget_registered(){
    	require_once WPV_FV_PATH.'inc/pro/fv-data-table.php';
    }
}
