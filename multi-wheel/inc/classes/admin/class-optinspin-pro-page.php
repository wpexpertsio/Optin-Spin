<?php

Class Optinspin_Get_Pro {

    function __construct() {
        add_action( 'admin_menu', array(&$this, 'opt_get_pro_menu') );
    }

    /**
     * Register submenu
     * @return void
     */
    public function opt_get_pro_menu() {
        add_submenu_page(
            'edit.php?post_type=optin-wheels', '<span style="background-color: orange;color: black;padding: 3px 8px;border-radius: 9px;">Get Pro</span>', '<span style="background-color: orange;color: black;padding: 3px 8px;border-radius: 9px;">Get Pro</span>', 'manage_options', 'optin-pro-page', array(&$this, 'optinpsin_get_pro_page')
        );
    }

    /**
     * Render submenu
     * @return void
     */
    public function optinpsin_get_pro_page() {
        ?>
        <style>
            #wpwrap {
                background-color: white;
            }
        </style>
        <script>
            jQuery(document).ready(function() {
               window.open('https://goo.gl/Lmrtfb','_self');
            });
        </script>
        <?php
        echo '<h2>Redirecting you....</h2>';
    }
}