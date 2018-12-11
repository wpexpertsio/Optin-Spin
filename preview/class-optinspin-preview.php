<?php

add_action('init','optinspin_preview');

function optinspin_preview() {
    if( isset( $_GET['optinspin_preview'] ) ) {
        echo do_shortcode('[optinspin wheeL_id='.$_GET['optinspin_preview'].' slide=1]');
    }
}

//add_action( 'post_submitbox_misc_actions', 'optinspin_preview_btn' );
function optinspin_preview_btn() {
    if( isset( $_GET['post'] ) ) {
        $post_id = $_GET['post'];
        echo '<a href="'.get_home_url().'?optinspin_preview='.$post_id.'" style="float: right;margin: 10px;" id="optinspin_preview_btn" class="button optinspin_preview_btn"">Preview</a>';
    }
}

//add_action('admin_head','optinspin_hide_default_preview');

function optinspin_hide_default_preview() {
    ?>
    <style>
        a#post-preview {
            display: none;
        }
    </style>
    <?php
}

add_action('wp_head','optinspin_trigger_wheel_preview');

function optinspin_trigger_wheel_preview() {
    if( isset( $_GET['optinspin_preview'] ) ) {
        ?>
        <script>
            jQuery(document).ready(function () {
                setTimeout(function() {
                    open_wheel_slide();
                },2000);
            });
        </script>
        <?php
    }
}