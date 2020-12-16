<?php

namespace WPV_FV;

use WPV_FV\Integrations\Cf7;
use WPV_FV\Integrations\Elementor;
use WPV_FV\Integrations\Caldera;
use WPV_FV\Classes\ApiEndpoint;
use WPV_FV\Classes\Settings;
use WPV_FV\pro\Bootstrap;
use WPV_FV\Integrations\BeaverBuilder;


class Plugin{

	private static $_instance = null;

	public static $_dashboard_widgets = 1;
	public static $_plan = 'FREE';
	public static $_role = [];

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	private function __construct() {

		// Init Freemius.
		//$this->wpv_fv();
		// Signal that SDK was initiated.
		//do_action( 'wpv_fv_loaded' );

		//add_action( 'activated_plugin', [$this,'fv_plugin_activation'], 10, 2 );
		add_action('admin_enqueue_scripts', [$this,'admin_scripts'], 10, 1);
		add_action('wp_dashboard_setup', [$this, 'add_dashboard_widgets']);
		$this->register_autoloader();


		add_action('plugins_loaded', [ 'WPV_FV\Classes\DbTables','fv_plugin_activated']);
		//register_activation_hook( WPV_FV_FILE, [ 'WPV_FV\Classes\DbTables','fv_plugin_activated'] );

		if(!function_exists('is_plugin_active')){
			include_once( ABSPATH . 'wp-admin/includes/plugin.php' );
		}

		if(is_plugin_active( 'caldera-forms/caldera-core.php' )){
			$caldera = new Caldera();
		}
		if(is_plugin_active( 'contact-form-7/wp-contact-form-7.php' )){
			$cf7 = new Cf7();
		}
		if(is_plugin_active( 'elementor-pro/elementor-pro.php' )){
			$ef = new Elementor();
		}
		if(is_plugin_active( 'bb-plugin/fl-builder.php' )){
			$bb = new BeaverBuilder();
		}
        // pro's bootstrap
		if(WPV_FV_PLAN === 'PRO'){
		    Bootstrap::instance();
		}


		self::$_dashboard_widgets = apply_filters('fv_widget_num', self::$_dashboard_widgets );
		self::$_plan = apply_filters('fv_plan', self::$_plan);
		self::$_role = apply_filters('fv_roles', self::$_role);

		ApiEndpoint::instance();
		Settings::instance();

		add_action('admin_menu', [$this,'my_menu_pages'] );

		add_filter( 'plugin_row_meta', [ $this, 'plugin_row_meta' ], 10, 2 );

		add_action('fv_reports', [$this,'do_this_hourly']);

		add_filter( 'manage_fv_data_display_posts_columns', [$this,'fv_set_custom_columns'] );
		add_action( 'manage_fv_data_display_posts_custom_column', [$this,'fv_set_custom_columns_data'], 10, 2 );
		if(is_admin()){
			add_filter( 'parse_query',[$this,'fv_d_type_filter'] );
		}
	}

	public function search_box( $text, $input_id ){} // Remove search box
	protected function pagination( $which ){}        // Remove pagination
	protected function display_tablenav( $which ){}  // Remove navigation


	function wpv_fv() {
		global $wpv_fv;
		if ( ! isset( $wpv_fv ) ) {
			// Include Freemius SDK.
			require_once WPV_FV_PATH . '/freemius/start.php';
			$wpv_fv = fs_dynamic_init( array(
				'id'                  => '4666',
				'slug'                => 'form-vibes',
				'type'                => 'plugin',
				'public_key'          => 'pk_321780b7f1d1ee45009cf6da38431',
				'is_premium'          => false,
				'has_addons'          => false,
				'has_paid_plans'      => false,
				'menu'                => array(
					'slug'           => 'fv-leads',
					'first-path'     => 'admin.php?page=fv-db-settings',
					'account'        => false,
					'contact'        => false,
					'support'        => false,
				),
			) );
		}
		return $wpv_fv;
	}

	function do_this_hourly() {
		$this->write_log("=============Cron Job Executed Time ===================". current_time('Y-m-d H:i:s',0));

	}

	function write_log ( $log )  {
		if ( is_array( $log ) || is_object( $log ) ) {
			error_log( print_r( $log, true ) );
		} else {
			error_log( $log );
		}
	}
	function fv_plugin_activation( $plugin, $network_activation ) {
		$url = admin_url().'admin.php?page=fv-db-settings';
		if($plugin == 'form-vibes/form-vibes.php'){
			header('Location: ' . $url);
			die();
		}
	}

	private function register_autoloader() {

		spl_autoload_register( [ __CLASS__, 'autoload' ] );

	}

	public function autoload( $class ) {

		if ( 0 !== strpos( $class, __NAMESPACE__ ) ) {
			return;
		}

		if ( ! class_exists( $class ) ) {

			$filename = strtolower(
				preg_replace(
					[ '/^' . __NAMESPACE__ . '\\\/', '/([a-z])([A-Z])/', '/_/', '/\\\/' ],
					[ '', '$1-$2', '-', DIRECTORY_SEPARATOR ],
					$class
				)
			);

			$filename = WPV_FV_PATH .'/inc/'. $filename . '.php';
			if ( is_readable( $filename ) ) {
				include( $filename );
			}
		}
	}
	private $panelNumber;
	private $data;
	function add_dashboard_widgets() {
		$this->data = get_option('fv-db-settings');
		$this->panelNumber = $this->data['panelNumber'];

		if($this->data == false || WPV_FV_PLAN === 'FREE' || $this->data == ''){
			if($this->panelNumber > 1 || $this->panelNumber == ''){
			    add_meta_box('form_vibes_widget-0','Form Vibes Analytics',[$this, 'dashboard_widget'],null,'normal','high',0);
            }
			else{
				for($i=0; $i<$this->panelNumber; $i++){
					add_meta_box('form_vibes_widget-'.$i,'Form Vibes - Analytics',[$this, 'dashboard_widget'],'','normal','high',$i);
				}
            }
			return;
        }

		$user = wp_get_current_user();
		$user_role = $user->roles;
		$user_role = $user_role[0];

		$data = get_option( 'fv_user_role');

        if($user_role === 'administrator' || $data[$user_role]['dashboard'] === true || $data[$user_role]['dashboard'] === 'true' ){

	        for($i=0; $i<$this->panelNumber; $i++){
		        add_meta_box('form_vibes_widget-'.$i,'Form Vibes - Analytics',[$this, 'dashboard_widget'],'','normal','high',$i);
	        }
        }
	}

	function dashboard_widget($vars, $i) {
		echo '<div name="dashboard-widget" id="fv-dashboard-widgets-'.$i['args'].'">
				<span class="fv-toggle-controls">

                </span></div>';
	}

	function admin_scripts() {
		$screen = get_current_screen();
        if ( $screen->id === 'fv_data_display' || $screen->id === 'edit-fv_data_display' || $screen->id == 'toplevel_page_fv-leads' || $screen->id == 'form-vibes_page_fv-analytics' || $screen->id == 'form-vibes_page_fv-db-settings' || $screen->id == 'form-vibes_page_db-manager' || $screen->id == 'fv_data_display' || $screen->id == 'dashboard' || $screen->id == 'form-vibes_page_fv-logs' || $screen->id == 'form-vibes_page_fv_submission_dashboard' ) {
            wp_enqueue_style( 'fv-css', WPV_FV_URL . 'assets/css/fv-style.css', '', WPV_FV_VERSION );
			wp_enqueue_style( 'component-css', WPV_FV_URL . 'assets/css/style.css', '', WPV_FV_VERSION );
	        wp_enqueue_style('fv-select-css', WPV_FV_URL. 'assets/css/select2.min.css',[],WPV_FV_VERSION);
			wp_enqueue_script( 'fv-js', WPV_FV_URL . 'assets/js/script.js', [ 'jquery-ui-datepicker' ], WPV_FV_VERSION, true );
			$user          = wp_get_current_user();
			$user_role     = $user->roles;
			$user_role     = $user_role[0];
			$gdpr_settings = get_option( 'fv_gdpr_settings' );

			if ( isset( $_REQUEST['post'] ) ) {
				$postID   = $_REQUEST['post'];
				$postType = get_post_type( $postID );
				$postMeta = '';
				if ( $postType === 'fv_data_display' ) {
					$postMeta      = get_post_meta( $postID, 'fv_sc_data', true );
					$postMetaStyle = get_post_meta( $postID, 'fv_sc_style_data', true );
					$postKey       = get_post_meta( $postID, 'fv_data_key', true );
					$d_type        = get_post_meta( $postID, 'fv_data_type', true );
				}
			} else {
				$postID        = '';
				$postMeta      = '';
				$d_type        = '';
				$postKey       = '';
				$postMetaStyle = '';
			}


			wp_localize_script( 'fv-js', 'fvGlobalVar', array(
				'site_url'        => site_url(),
				'ajax_url'        => admin_url( 'admin-ajax.php' ),
				'admin_url'       => admin_url(),
				'rest_url'        => get_rest_url(),
				'fv_version'      => WPV_FV_VERSION,
				'fv_plan'         => self::$_plan,
				'widget_num'      => self::$_dashboard_widgets,
				'user'            => $user_role,
				'post_id'         => $postID,
				'post_meta'       => maybe_unserialize( $postMeta ),
				'post_meta_style' => maybe_unserialize( $postMetaStyle ),
				'post_d_type'     => $d_type,
				'post_key'        => $postKey,
				'gdpr_settings'   => $gdpr_settings,
				'roles'           => self::$_role,
				'nonce'           => wp_create_nonce( 'wp_rest' )
			) );
			add_action( 'admin_print_scripts', [ $this, 'fv_disable_admin_notices' ] );
			wp_enqueue_style( 'wp-components' );
		}

		if ( $screen->id == 'dashboard') {
			wp_enqueue_script( 'dashboard-js', WPV_FV_URL . 'assets/js/dashboard.js', [ 'wp-components' ], WPV_FV_VERSION, true );
		}
		if ( $screen->id == 'form-vibes_page_fv-db-settings') {
			wp_enqueue_script( 'dashboard-setting-js', WPV_FV_URL . 'assets/js/dashboardSettings.js', [ 'wp-components' ], WPV_FV_VERSION, true );
		}
		if ( $screen->id == 'form-vibes_page_fv-analytics') {
			wp_enqueue_script( 'dashboard-analytics-js', WPV_FV_URL . 'assets/js/analytics.js', [ 'wp-components' ], WPV_FV_VERSION, true );
		}
		if ( $screen->id === 'fv_data_display' || $screen->id === 'edit-fv_data_display' || $screen->id == 'toplevel_page_fv-leads' || $screen->id == 'form-vibes_page_db-manager' || $screen->id == 'fv_data_display' ) {
			wp_enqueue_script( 'admin-js', WPV_FV_URL . 'assets/js/admin.js', [ 'wp-components' ], WPV_FV_VERSION, true );
			//wp_enqueue_style('fv-pro-dynatable-js', WPV_FV_URL. 'assets/js/jquery.dynatable.js',[],WPV_FV_VERSION);
		}

		if ( $screen->id == 'form-vibes_page_fv-logs') {
			wp_enqueue_script( 'logs-js', WPV_FV_URL . 'assets/js/fv-logs.js', [ 'wp-components' ], WPV_FV_VERSION, true );
		}
	}

	public function plugin_row_meta( $plugin_meta, $plugin_file ) {
		if ( WPV_FV__PLUGIN_BASE === $plugin_file ) {
			$row_meta = [
				'docs' => '<a href="https://wpvibes.link/go/fv-all-docs-pp/" aria-label="' . esc_attr( __( 'View Documentation', 'wpv-fv' ) ) . '" target="_blank">' . __( 'Read Docs', 'wpv-fv' ) . '</a>',
				'support' => '<a href="https://wpvibes.link/go/fv-support-wp/" aria-label="' . esc_attr( __( 'Support', 'wpv-fv' ) ) . '" target="_blank">' . __( 'Need Support', 'wpv-fv' ) . '</a>',
			];

			$plugin_meta = array_merge( $plugin_meta, $row_meta );
		}

		return $plugin_meta;
	}

	function my_menu_pages(){

		//add_menu_page('Form Vibes Leads', 'Form Vibes', 'edit_others_posts', '', "" );
		add_menu_page('Form Vibes Leads', 'Form Vibes', 'publish_posts', 'fv-leads', [$this,'display_react_table'], 'dashicons-analytics', 30 );
		add_submenu_page('fv-leads','Form Vibes Submissions', 'Submissions', 'publish_posts', 'fv-leads', [$this,'display_react_table'] );
		add_submenu_page('fv-leads','Form Vibes Analytics', 'Analytics', 'publish_posts', 'fv-analytics', [$this,'fv_analytics'] );
		add_submenu_page('fv-leads','Form Vibes Logs', 'Event Logs', 'publish_posts', 'fv-logs', [$this,'fv_logs'] );
		add_submenu_page('fv-leads','Form Vibes Dashboard Settings', 'Settings', 'manage_options', 'fv-db-settings', [$this,'fv_db_settings'] );
		//add_submenu_page('my-menu', 'Submenu Page Title', 'Whatever You Want', 'manage_options', 'my-menu' );
	}

	function fv_disable_admin_notices() {
		global $wp_filter;
		if ( is_user_admin() ) {
			if ( isset( $wp_filter['user_admin_notices'] ) ) {
				unset( $wp_filter['user_admin_notices'] );
			}
		} elseif ( isset( $wp_filter['admin_notices'] ) ) {
			unset( $wp_filter['admin_notices'] );
		}
		if ( isset( $wp_filter['all_admin_notices'] ) ) {
			unset( $wp_filter['all_admin_notices'] );
		}

		if ( isset( $_GET['remind_later'] ) ) {
			add_action( 'admin_notices', [ $this,'fv_remind_later'] );
		}
		else if ( isset( $_GET['review_done'] ) ) {
			add_action( 'admin_notices', [ $this,'fv_review_done'] );
		}
		else{
			add_action( 'admin_notices', [ $this,'fv_review'] );
		}


	}

	function fv_review(){
		$show_review = get_transient('fv_remind_later');
		$review_added = get_transient('fv_review_done');

		$review_status = get_option( 'fv-review');
		if($review_status !== 'done'){
			if($show_review == '' && $review_added == ''){
				global $wpdb;

				$rowcount = $wpdb->get_var("SELECT COUNT(*) FROM {$wpdb->prefix}fv_enteries");

				if($rowcount>9){
				?>
                <div class="notice notice-success is-dismissible">
                    <p><?php _e( 'Thank you for using <b>Form Vibes</b>! <br/> How was your Experience with us ?', 'wpv-fv' ); ?></p>

                    <p>
                        <a class="fv-notice-link" target="_blank" href="https://wordpress.org/support/plugin/form-vibes/reviews/#new-post" class="button button-primary"><span class="dashicons dashicons-heart"></span><?php _e( 'Ok, you deserve it!', 'wpv-fv' ); ?></a>
                        <a class="fv-notice-link" href="<?php echo add_query_arg( 'remind_later', 'later'); ?>"><span class="dashicons dashicons-schedule"></span><?php _e( 'May Be Later', 'wpv-fv' ); ?></a>
                        <a class="fv-notice-link" href="<?php echo add_query_arg( 'review_done', 'done'); ?>"><span class="dashicons dashicons-smiley"></span><?php _e( 'Already Done', 'wpv-fv' ); ?></a>
                    </p>
                </div>
				<?php
				}
			}
		}
	}
	function fv_remind_later(){
		set_transient( 'fv_remind_later', 'show again', WEEK_IN_SECONDS );
	}

	function fv_review_done(){
		//set_transient( 'fv_review_done', 'Already Reviewed !', 3 * MONTH_IN_SECONDS );

		update_option( 'fv-review', 'done',false);
	}

	function display_react_table()
	{
	    ?>
        <div id="fv-leads"></div>

		<?php
        /*$url = "https://randomuser.me/api/?results=50";
        $data = json_decode(file_get_contents($url), true);
        //echo '<pre>';print_r($data['results'][0]);echo '</pre>';
        //print_r($data['results']);
	    global $wpdb;
	    $my_id = $wpdb->insert_id;
	    $table = $wpdb->prefix.'fv_enteries';
	    $table1 = $wpdb->prefix.'fv_entry_meta';
	    $num = count($data['results']);
		$Date = date("Y-m-j h:i:s");
	    for($i =0; $i < $num; $i++){
		    $b =  rand(1,100) . ' days';
			$date1 = date('Y-m-d h:i:s', strtotime($Date. ' + ' . $b));
			echo 'date 1 '. $date1;
			$tbl_data = array( 'form_plugin' => 'cf7','form_id' => 5,'captured' => $date1, 'captured_gmt'=> current_time( mysql, $gmt = 1 ));
		    //$format = array('%s','%d');
            //echo 'Id '. $i .' ' ; print_r($data);
            $wpdb->insert($table,$tbl_data);
		    //echo 'data '; print_r($tbl_data); echo '<br/>';
            for($j=1; $j <= 7; $j++){
                if($j == 1){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'your-name','meta_value' => $data['results'][$i]['name']['first'] . ' ' . $data[$i]['name']['last']);
                }
                else if($j == 2){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'your-email','meta_value' => $data['results'][$i]['email']);
                }
                else if($j == 3){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'your-subject','meta_value' => $data['results'][$i]['location']['state']);
                }
                else if($j == 4){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'your-message','meta_value' => $data['results'][$i]['login']['uuid']);
                }
                else if($j == 5){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'fv_plugin','meta_value' => 'cf7');
                }
                else if($j == 6){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'fv_form_id','meta_value' => 5);
                }
                else if($j == 7){
	                $data1 = array('data_id' => $i+1, 'meta_key' => 'fv_ip','meta_value' => '192.168.95.1');
                }
	            //echo 'data 1 '; print_r($data1); echo '<br/>';
	            $wpdb->insert($table1,$data1);
            }
        }*/
	}

	function fv_logs(){
		?>
        <div id="fv-logs" class="fv-logs"></div>
		<?php
	}

	function fv_analytics(){
    ?>
        <div id="fv-analytics" class="fv-analytics"></div>
    <?php
    }
	function fv_db_settings(){
    ?>
        <div id="fv-db-settings" class="fv-db-settings-wrapper"></div>
    <?php
    }

    function fv_set_custom_columns($columns){
	    $columns = [
		    'cb' => __( 'cb' ),
		    'title' => __( 'Title' ),
            'fv_d_type' => __( 'Data Type', 'wpfv' ),
            'date' => __( 'Date' )
        ];


	    return $columns;
    }

    function fv_set_custom_columns_data($column, $post_id){
	    switch ( $column ) {

		    case 'fv_d_type' :
		        $d_type = get_post_meta($post_id,'fv_data_type',true);
			    echo $d_type;
			    break;
	    }
    }

    function fv_d_type_filter($wp_query){
	    if($wp_query->query['post_type'] === 'undefined'){
            return;
        }
	    if($wp_query->query['post_type'] === 'fv_data_display' && isset($_REQUEST['type'])){
		    $wp_query->query_vars['meta_key'] = 'fv_data_type';
		    $wp_query->query_vars['meta_value'] = $_REQUEST['type'];
        }

    }
}

Plugin::instance();
