<?php

namespace WPV_FV\Classes;


class Settings{

	private static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {
		add_action( 'wp_ajax_fv_save_config', [ $this, 'fv_save_config' ] );
        add_action( 'wp_ajax_fv_save_status', [ $this, 'fv_save_status' ] );
		add_action( 'wp_ajax_fv_save_role_config', [ $this, 'fv_save_role_config' ] );
		add_action( 'wp_ajax_fv_save_exclude_forms', [ $this, 'fv_save_exclude_forms' ] );
		add_action( 'wp_ajax_nopriv_fv_save_exclude_forms', [ $this, 'fv_save_exclude_forms' ] );

		add_action( 'wp_ajax_fv_delete_forms', [ $this, 'fv_delete_forms' ] );
		add_action( 'wp_ajax_nopriv_fv_delete_forms', [ $this, 'fv_delete_forms' ] );
	}

	public function fv_save_status(){

        global $wpdb;

        $wpdb->update('wp_fv_enteries', array('status'=> $_REQUEST['value']), array('id' => $_REQUEST['id']));
    }

	public function fv_save_config(){
		$data['panelNumber'] = $_REQUEST['dbPanel'];
		$data['panelData'] = $_REQUEST['panelData'];

		update_option( 'fv-db-settings', $data,false);

		$saveIp = $_REQUEST['saveIP'];
		$saveUA = $_REQUEST['saveUA'];
		$debug_mode = $_REQUEST['debugMode'];
		$export_reason = $_REQUEST['exportReason'];
		//update_option( 'fv-ip-save', $saveIp,false);
		$gdpr['ip'] = $saveIp;
		$gdpr['ua'] = $saveUA;
		$gdpr['debug_mode'] = $debug_mode;
		$gdpr['export_reason'] = $export_reason;
		update_option( 'fv_gdpr_settings', $gdpr,false);

	}

	public function fv_save_exclude_forms(){
		$forms = $_REQUEST['myForms'];

		update_option( 'fv_exclude_forms', $forms,false);

	}

	public function fv_save_role_config(){
		$data = $_REQUEST['role_data'];

		update_option( 'fv_user_role', $data,false);
	}

	public function fv_delete_forms(){
		$formID = $_REQUEST['formId'];
		$plugin = $_REQUEST['plugin'];

		$inserted_forms = get_option('fv_forms');
		unset($inserted_forms[$plugin][$formID]);

		update_option('fv_forms',$inserted_forms);
	}
}