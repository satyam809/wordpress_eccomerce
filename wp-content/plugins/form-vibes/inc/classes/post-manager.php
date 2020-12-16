<?php

namespace WPV_FV\Classes;


class PostManager{

    private static $_instance = null;

    public static function instance() {

        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }

        return self::$_instance;
    }

	public function __construct() {
    }

   /* public function get(){
        ?>
        <select name="field-name" id="field-id" style="margin-left: 200px">
        <option value="">--Select--</option><?php
        $dbValue = get_option('field-name'); //example!
        $posts = get_posts(array(
            'post_type'     => 'wpcf7_contact_form',
            'numberposts'   => -1
        ));
        foreach ( $posts as $p ) {
            echo '<option value="'.$p->ID.'"'.selected($p->ID,$dbValue,false).'>'.$p->post_title.' ('.$p->ID.')</option>';
        } ?>
    </select>
        <?php
    }*/
}