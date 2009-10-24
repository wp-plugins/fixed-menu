<?php
/*
 * function_fixed_menu.php
 * -*- Encoding: utf8n -*-
 */
function fixed_menu($args, $is_echo = false) {
    //print_r($args);

    $menu = array();

    if (is_array($args)) {
        $menuName = $args['menuname'];
        $is_shortcode = true;
    } else {
        $menuName = $args;
        $is_shortcode = false;
    }

    // get data object
    $wpFixedMenu = & new WpFixedMenu();
    $model = $wpFixedMenu->model;
    //print_r($model);

    $menu = $model->getMenu($menuName);

    //print_r($menu);
    $option = $wpFixedMenu->model->getOption($menuName);
    $is_enable = $option['is_enable'];
    if ($is_enable === 'disable') {
        if ($is_echo) {
            return '';
        } else {
            echo '';
            return;
        }
    }
    
    if ($menu == false) {
        $msg = '<p>' . __('Fixed Menu: Menu not found or No menu item: ', 'fixed_menu') . $menuName . '</p>';
        if ($is_echo) {
            return $msg;
        } else {
            echo $msg;
            return;
        }
    }

    $align = $option['align'];
    if ($align === 'none') {
        $align = '';
    } else if ($align === 'left') {
        $align = 'style="float:left;"';
    } else if ($align === 'center') {
        $align = 'style="float:center;"';
    } else if ($align === 'right') {
        $align = 'style="float:right;"';
    }
    $menu_title = $option['menu_title'];
    if ($menu_title) {
        if ($is_shortcode) {
            $shortcode = " fixed_menu_title_shortcode";
        } else {
            $shortcode = " fixed_menu_title_phpcode";
        }
        $css_title = ' fixed_menu_title_' . $menuName;
        $menu_title = '<div class="fixed_menu_title' . $shortcode . $css_title . '"><span>' . $menu_title . '</span></div>';
    }
    // GF-GetThumb plug-in
    $qf_getthumb_option = $option['qf_getthumb_option'];
    $qf_getthumb_enable = $option['qf_getthumb'];
    if (!function_exists('the_qf_get_thumb_one')){
        // qf-getthumb plug-in is disabled or not installed
        $qf_getthumb_enable = 0;
    }
    
    $div_id_1 = $menuName;
    $div_id_2 = $menuName . '_sub';
    if ($is_shortcode) {
        $div_class_1 = 'fixed_menu_shortcode';
        $div_class_2 = 'fixed_menu_shortcode_sub';
    } else {
        $div_class_1 = 'fixed_menu';
        $div_class_2 = 'fixed_menu_sub';
    }
    
    // li.class: page-item-%
    //   0: % is string
    //   1: % is number
    $class_item_is_number = 0;

    global $current_name;
    global $post;
    $post_save = $post; // SAVE global value
    
    
    // get current page and category ID
    list($current_type, $current_id) = fixed_menu_get_current($post);

    $m = sprintf("<div id=\"%s\" class=\"%s\" %s>\n", $div_id_1, $div_class_1, $align);
    $m .= sprintf("  <div id=\"%s\" class=\"%s\">\n", $div_id_2, $div_class_2);
    $m .= $menu_title;
    $m .= "    <ul>\n";

    // make li tag
    foreach ($menu as $num => $item) {
        if ($item->enable_item === 'off') continue;
        
        //print_r($item);
        if ($class_item_is_number) {
            $num = $num + 1;
        } else {
            $num = $item->cssClass;
        }
        
        // Is menu item current page?
        if (fixed_menu_is_current($current_type, $current_id, $item->type, $item->id)) {
            $current = 'current_page_item';
            $current_a = $current . '_' . $num . '_a';
            $current_a .= ' ' . $current . '_a';
            $current_name = $num;
        } else if (is_single()) {
            $current = '';
            $current_a = '';
            $current_name = 'single_entry';
        } else {
            $current = '';
            $current_a = '';
            //$current_name = '';
        }

        // get post object
        list($p, $title) = fixed_menu_get_post($item->type, $item->id);
        
        if ($item->type === 'home') {
            if ($qf_getthumb_enable) {
                $home_image = $model->getHomeImage();
                // home_image use at in content image.
                //   example: page_id=4, cat=3, p=2
                if (preg_match("/^([a-z_]*)=([0-9]*)$/", $home_image, $match)) {
                    list($p, $title) = fixed_menu_get_post($match[1], $match[2]);
                    $post = $p; // global $post;
                    // call GF-GetThumb plug-in
                    $img = the_qf_get_thumb_one($qf_getthumb_option);
                } else {
                    // home_image is image location.
                    $img = sprintf("<img src=\"%s\" />", $home_image);
                }
            } else {
                $img = '';
            }
            $m .= sprintf("<li class=\"page_item page-item-home %s\">", $current);
            $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s/\" title=\"%s\">%s<span>%s</span></a></li>\n", $num, $current_a, get_bloginfo('siteurl'), $num, $img, $model->getHomeString());
        } else if ($item->type === 'url') {
            if ($qf_getthumb_enable) {
                // call GF-GetThumb plug-in
                $img = sprintf("<img src=\"%s\" />", $item->img);
            } else {
                $img = '';
            }
            $m .= sprintf("<li class=\"page_item page-item-%s %s\">", $num, $current);
            $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s\" title=\"%s\">%s<span>%s</span></a></li>\n", $num, $current_a, $item->url, $item->str, $img, $item->str);
        } else {
            if ($qf_getthumb_enable) {
                $post = $p; // global $post;
                // call GF-GetThumb plug-in
                $img = the_qf_get_thumb_one($qf_getthumb_option);
            } else {
                $img = '';
            }
            $m .= sprintf("<li class=\"page_item page-item-%s %s\">", $num, $current);
            $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s/?%s=%s\" title=\"%s\">%s<span>%s</span></a></li>\n", $num, $current_a, get_bloginfo('siteurl'), $item->type, $item->id, $title, $img, $title);
        }
    }
    $m .= "    </ul>\n";
    $m .= "  </div>\n";
    $m .= "</div>\n";

    $post = $post_save; // RESTORE --
    
    if ($is_echo) {
        return $m;
    } else {
        echo $m;
    }
}

// get content object
function fixed_menu_get_post($type, $id) {
    // page
    if ($type === 'page_id') {
        $p = get_post($id);
        $title = $p->post_title;
    }
    // category
    if ($type === 'cat') {
        // latest post of the category
        $preamble = get_posts('numberposts=1&category='.$id);
        $post0 = $preamble[0];
        $p = get_post($post0->ID);
        // category name
        $title = get_catname($id);
    }
    // post
    if ($type === 'p') {
        $preamble = get_posts('numberposts=50'); // get post max 50
        foreach ($preamble as $post0) {
            //print_r($post);
            if ($id != $post0->ID) { continue; }
            $p = get_post($post0->ID);
            $title = $post0->post_title;
            break;
        }
    }
    return array($p, $title);
}

// get current page type and id
function fixed_menu_get_current($post) {
    if (is_home()) {
        $current_type = 'home';
        $current_id = 0;
    } else if (is_page()) {
        $current_type = 'page_id';
        $current_id = $post->ID; // global $post;
    } else if (is_archive()) {
        $current_type = 'cat';
        $current_id = get_query_var('cat');
    } else if (is_single()) {
        // parent category
        //echo 'single';
        $current_type = 'cat';
        $current_id = get_query_var('cat');
        //echo 'current_type = ' . $current_type . ', current_id = ' . $current_id;
        //print_r(get_the_category($post->ID));
	$cats = get_the_category($post->ID);
        //print_r($cats[0]);
	$current_id = $cats[0]->cat_ID;
        //print_r(get_post_class());
    } else {
        $current_type = '';
        $current_id = 0;
    }
    return array($current_type, $current_id);
}

// Is this item current page?
function fixed_menu_is_current($current_type, $current_id, $type, $id) {
    //echo '<p>name = ' . $name . ' current_type = ' . $current_type . ' type = ' . $type . ' current_id = ' . $current_id . ' id = ' . $id . '</p>';
    if ($type === 'home') {
        if (is_home()) {
            return true;
        }
    } else if (is_page() && $current_type === $type && $current_id == $id) {
        return true;
    } else if (is_archive() && $current_type === $type && $current_id == $id) {
        return true;
    } else if (is_single() && $current_type === $type && $current_id == $id) {
        // single is to category
        //echo 'single';
        return true;
    }
    return false;
}

?>
