<?php
/*
Plugin Name: Fixed Menu
Plugin URI: http://takeai.silverpigeon.jp/
Description: Making of fixed menu.
Author: AI.Takeuchi
Version: 1.7.1
Author URI: http://takeai.silverpigeon.jp/
*/

// -*- Encoding: utf8n -*-
// If you notice a my mistake(Program, English...), Please tell me.

/*  Copyright 2009 AI Takeuchi (email: takeai@silverpigeon.jp)

    This program is free software; you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation; either version 2 of the License, or
    (at your option) any later version.

    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with this program; if not, write to the Free Software
    Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
*/

//load_plugin_textdomain('fixed_menu');
load_plugin_textdomain( 'fixed_menu',
		'wp-content/plugins/fixed-menu/lang', 'fixed-menu/lang' );

if (is_admin()) {
    $wpFixedMenu = & new WpFixedMenu();
    // Registration of management screen header output function.
    add_action('admin_head', array(&$wpFixedMenu, 'addAdminHead'));
    // Registration of management screen function.
    add_action('admin_menu', array(&$wpFixedMenu, 'addAdminMenu'));
} else {
    wp_enqueue_script('jquery');
    //wp_enqueue_script('jQuery', WP_PLUGIN_URL . '/fixed-menu/module/jquery.js', null, '1.3.2');
    //wp_enqueue_script('jQuery.cookie', WP_PLUGIN_URL . '/fixed-menu/module/jquery.cookie.js', null, null);
    //wp_enqueue_script('jQuery.droppy', WP_PLUGIN_URL . '/fixed-menu/module/jquery.droppy.js', null, null);
    require_once('module/add_wp_head.php');
    add_action('wp_head', 'add_wp_head');
    require_once('module/function_fixed_menu.php');
    add_shortcode('fixed-menu', 'fixed_menu');
    // Can use the short-code in sidebar widget
    add_filter('widget_text', 'do_shortcode');
}

/* Data model */
class WpFixedItemModel {
    var $type_name = ''; // Page, Category, Post, Link
    var $type = ''; // page_id, cat, p, url
    var $id = 0;
    var $cssClass = '';
    var $str = '';
    var $img = '';
    var $url = '';

    // constructor
    function WpFixedItemModel() {
    }
    function toString() {
        return sprintf("%s<fs>%s<fs>%s<fs>%s<fs>%s<fs>%s<fs>%s<fs>%s",
                       $this->type_name, // Page, Category, Post, Link
                       $this->type, // page_id, cat, p, url
                       $this->id,
                       $this->cssClass,
                       $this->str,
                       $this->img,
                       $this->url,
                       $this->enable_item
                       );
    }
    function toModel($str) {
        $f = split('<fs>', $str);
        $this->type_name = $f[0]; // Page, Category, Post, Link
        $this->type = $f[1]; // page_id, cat, p, url
        $this->id = $f[2];
        $this->cssClass = $f[3];
        $this->str = $f[4];
        $this->img = $f[5];
        $this->url = $f[6];
        $this->enable_item = $f[7];
    }
}
class WpFixedMenuModel {
    // member variable
    var $version = '0.7';
    var $data = array();
    var $home_string = 'Home';
    var $home_image = '';
    
    // constructor
    function WpFixedMenuModel() {
        // default value
    }
    
    //
    function addNewMenu($menuName) {
        if (array_key_exists($menuName, ($this->data)) == true) {
            return false;
        } else {
            $this->data[$menuName] = array();
            $this->data[$menuName]['menu'] = array();
            $this->data[$menuName]['option'] = array();
            $this->data[$menuName]['option']['is_enable'] = 'enable';
            // QF_GetThumb
            $this->data[$menuName]['option']['qf_getthumb'] = 1;
            $this->data[$menuName]['option']['qf_getthumb_option'] = 'num=0&crop_w=40&width=80&crop_h=40&height=80';
            $this->data[$menuName]['option']['align'] = 'none';
            $this->data[$menuName]['option']['menu_title'] = '';
            $this->data[$menuName]['option']['change_publish_private_post'] = '';
            $this->data[$menuName]['option']['not_use_span_tag'] = '';
            $this->data[$menuName]['option']['do_not_show_uncategorized'] = '';
            $this->data[$menuName]['option']['exclude_pages'] = '';
            $this->data[$menuName]['option']['exclude_categories'] = '';
            $this->data[$menuName]['option']['child_cat_depth'] = 0;
            $this->data[$menuName]['option']['child_page_depth'] = 0;
            $this->data[$menuName]['option']['toggle_button'] = 'checked';
            return true;
        }
    }
    function toString($menuName) {
        $menu = $this->getMenu($menuName);
        if (!$menu) return '';
        foreach ($menu as $i => $rd) {
            if ($str) $str .= '<rs>';
            $str .= $rd->toString();
        }
        return $str;
    }
    function setStrToMenu($menuName, $str) {
        $this->data[$menuName]['menu'] = array();
        $records = split('<rs>', $str);
        foreach ($records as $num => $record) {
            $this->data[$menuName]['menu'][$num] = & new WpFixedItemModel();
            $this->data[$menuName]['menu'][$num]->toModel($record);
        }
    }
    
    function deleteMenu($menuName) {
        unset($this->data[$menuName]);
    }
    
    function getMenu($menuName) {
        if (!array_key_exists($menuName, $this->data)) {
            return false;
        }
        return $this->data[$menuName]['menu'];
    }
    function setMenu($menuName, $items) {
        $this->data[$menuName]['menu'] = $items;
    }
    
    function getOption($menuName) {
        return $this->data[$menuName]['option'];
    }
    function setOption($menuName, $option) {
        $this->data[$menuName]['option'] = $option;
    }
    
    function setHomeString($str) {
        $this->home_string = $str;
    }
    function getHomeString() {
        return $this->home_string;
    }
    
    function setHomeImage($str) {
        $this->home_image = $str;
    }
    function getHomeImage() {
        return $this->home_image;
    }
    
    function setToggleButton($str) {
        $this->data[$menuName]['toggle_button'] = $str;
    }
    function getToggleButton() {
        return $this->data[$menuName]['toggle_button'];
    }
}

/* main class */
class WpFixedMenu {
    var $view;
    var $model;
    var $request;
    var $plugin_name;
    var $plugin_uri;

    // constructor
    function WpFixedMenu() {
        $this->plugin_name = 'fixed_menu';
        
        $this->plugin_uri  = get_settings('siteurl');
        $this->plugin_uri .= '/wp-content/plugins/fixed-menu/';

        $this->model = $this->getModelObject();
    }
    
    // create model object
    function getModelObject() {
        $data_clear = 0; // Debug: 1: Be empty to data
        
        // get option from Wordpress
        $option = $this->getWpOption();
        
        //printf("<p>Debug[%s, %s]</p>", strtolower(get_class($option)), strtolower('WpFixedMenuModel'));
        
        // Restore the model object if it is registered
        if (strtolower(get_class($option)) === strtolower('WpFixedMenuModel') && $data_clear == 0) {
            $model = $option;
        } else {
            // create model instance if it is not registered,
            // register it to Wordpress
            $model = & new WpFixedMenuModel();
            $this->addWpOption($model);
        }
        return $model;
    }
    
    function getWpOption() {
        $option = get_option($this->plugin_name);
        
        if(!$option == false) {
            $OptionValue = $option;
        } else {
            $OptionValue = false;
        }
        return $OptionValue;
    }

    /* be add plug-in data to Wordpresss */
    function addWpOption(&$model) {
        $option_description = $this->plugin_name . " Options";
        $OptionValue = $model;
        //print_r($OptionValue);
        add_option(
            $this->plugin_name,
            $OptionValue,
            $option_description);
    }

    /* update plug-in data */
    function updateWpOption(&$OptionValue) {
        $option_description = $this->plugin_name . " Options";
        $OptionValue = $OptionValue;
        //$OptionValue = $this->model;
        
        update_option(
            $this->plugin_name,
            $OptionValue,
            $option_description);
    }
    
    /*
     * management screen header output
     * reading javascript and css
     */
    function addAdminHead() {
        echo '<link type="text/css" rel="stylesheet" href="';
        echo $this->plugin_uri . 'fixed_menu.css" />' . "\n";;
        
        //echo '<script type="text/javascript" src="';
        //echo $this->plugin_uri . 'js/fixed_menu.js">';
        //echo '</script>'  . "\n";

        echo '<script type="text/javascript">';
        require_once('module/js.php');
        //echo 'window.onload = function() { FixedMenuJs.onLoad(); }';
        echo '</script>';
    }

    function addAdminMenu() {
        add_options_page(
            'Fixed Menu Options',
            'Fixed Menu',
            8,
            'fixed_menu.php',
            array(&$this, 'executeAdmin')
            );
    }

    function executeAdmin() {
        require_once('module/execute_admin.php');
        execute_admin($this);
    }
}
?>
