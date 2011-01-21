<?php
/*
 * function_fixed_menu.php
 * -*- Encoding: utf8n -*-
 */

function fixed_menu_get_current_name() {
    // get data object
    $wpFixedMenu = & new WpFixedMenu();
    $model = $wpFixedMenu->model;
    //print_r($model->data['header_menu']['menu']);
    
    global $post;
    //print_r($post);
    $id = $post->ID;
    $cat_css = array();
    $cat_id = get_query_var('cat');
    $cats = get_the_category($id);
    
    //echo '<p>id = ' . $id . '</p>';
    
    foreach ($model->data as $menuName => $menu) {
        $menu = $model->getMenu($menuName);
        //echo $menuName;
        foreach ($menu as $num => $item) {
            //if ($item->enable_item === 'off') continue;
            //print_r($item);
            $css = $item->cssClass;
            
            $itemID = $item->id;
            $type = $item->type;
            if ($type === 'other_pages') { $type = 'page_id'; }
            if ($type === 'other_cats') { $type = 'cat'; }
            if ($type === 'home') { $type = 'home'; }
            //if ($type === 'rss_feed') { $type = 'rss_feed'; }
            
            
            if (is_home()) {
                $p = 'home';
                if ($type === $p) {
                    return $item->cssClass;
                }
            } else if (is_page()) {
                $p = 'page_id';
                if ($type === $p && $itemID == $id) {
                    return $item->cssClass;
                }
            } else if (is_single()) {
                $p = 'p';
                if ($type === $p && $itemID == $id) {
                    return $item->cssClass;
                }
                foreach ($cats as $cat) {
                    $c_id = $cat->category_parent;
                    if ($c_id == 0) {
                        $c_id = $cat->cat_ID;
                    }
                    //echo "<p> c_id= if ($c_id == $itemID) </p>";
                    if ($c_id == $itemID) {
                        array_push($cat_css, $item->cssClass);
                    }
                }
            } else if (is_archive()) {
                $p = 'cat';
                if ($type === $p && $itemID == $cat_id) {
                    return $item->cssClass;
                }
            } else {
            }
            
            //echo "<p>($type === $p && $itemID == $id)$cat_id</p>";
        }
    }
    return join(' ', $cat_css);
}






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

    $span = not_use_span_tag($option);
    
    if ($menu == false) {
        $msg = '<p>' . __('Fixed Menu: Menu not found or No menu item: ', 'fixed_menu') . $menuName . '</p>';
        if ($is_echo) {
            return $msg;
        } else {
            echo $msg;
            return;
        }
    }
    
    $align = get_align($option);
    
    $menu_title = $option['menu_title'];
    if ($menu_title) {
        if ($is_shortcode) {
            $shortcode = " fixed_menu_title_shortcode";
        } else {
            $shortcode = " fixed_menu_title_phpcode";
        }
        $css_title = ' fixed_menu_title_' . $menuName;
        $menu_title = '<div class="fixed_menu_title' . $shortcode . $css_title . '">' . $span[0] . $menu_title . $span[1] . '</div>';
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
    
    global $current_name;
    global $post;
    $post_save = $post; // SAVE global value
    
    
    // get current page and category ID
    list($current_type, $current_id) = fixed_menu_get_current($post);
    //print_r($post);
    //echo "list($current_type, $current_id) = fixed_menu_get_current(post);";

    $m = sprintf("<div id=\"%s\" class=\"%s\" %s>\n", $div_id_1, $div_class_1, $align);
    $m .= sprintf("  <div id=\"%s\" class=\"%s\">\n", $div_id_2, $div_class_2);
    $m .= $menu_title;
    //$m .= '    <ul id="nav">'."\n";
    $m .= '    <ul>'."\n";

    // make li tag
    foreach ($menu as $num => $item) {
        if ($item->enable_item === 'off') continue;
        
        //print_r($item);

        $num = $item->cssClass;
        
        // Is menu item current page?
        if (fixed_menu_is_current($current_type, $current_id, $item->type, $item->id)) {
            $current = 'current_page_item';
            $current_a = $current . '_' . $num . '_a';
            $current_a .= ' ' . $current . '_a';
            $current_name = $num;
        } else if (is_single()) {
            $current = '';
            $current_a = '';
            //$current_name = 'single_entry';
            $current_name = $num;
            /*
        } else if (is_archive()) {
            $current = '';
            $current_a = '';
            //$current_name = 'single_entry';
            $current_name = $num;*/
        } else {
            $current = '';
            $current_a = '';
            //$current_name = '';
        }

        //echo "<p>current_name0 = $current_name</p>";

        // get post object
        list($p, $title) = fixed_menu_get_post($item->type, $item->id);
        
        if ($item->type === 'home') {
            $m .= get_li_home($model, $option, $num, $current, $current_a, $span);
        } else if ($item->type === 'other_pages') {
            $exclude = get_exclude_page($menu, $option);
            // all page
            //$all_content = get_pages('sort_order=asc');
            $all_content = get_pages('sort_column=menu_order');
            $m .= get_li_others('page_id', $item->cssClass, $option, $p, $num, $current, $current_a, $title, $span, $exclude, $all_content, $current_type, $current_id);
        } else if ($item->type === 'other_cats') {
            $exclude = get_exclude_cat($menu, $option);
            // all category
            $all_content = get_categories();
            $m .= get_li_others('cat', $item->cssClass, $option, $p, $num, $current, $current_a, $title, $span, $exclude, $all_content, $current_type, $current_id);
        } else if ($item->type === 'rss_feed') {
            //$m .= '<li class="feed"><a title="RSS Feed of Posts" href="';
            $m .= '<li class="' . $item->cssClass . '"><a title="RSS Feed of Posts" href="';
            $m .= get_bloginfo('rss2_url');
            $m .= '">RSS Feed</a></li>';
            /*
            $exclude = get_exclude_cat($menu, $option);
            // all category
            $all_content = get_categories();
            $m .= get_li_others('cat', $item->cssClass, $option, $p, $num, $current, $current_a, $title, $span, $exclude, $all_content, $current_type, $current_id);*/
        } else if ($item->type === 'url') {
            $m .= get_li_url($item, $option, $num, $current, $current_a, $span);
        } else {
            $m .= get_li_else($item->type, $item->id, $option, $p, $num, $current, $current_a, $title, $span, 0);
        }
    }
    $m .= "    </ul>\n";
    $m .= "  </div>\n";
    $m .= "</div>\n";

    $post = $post_save; // RESTORE --

    //echo "<p>current_name1 = $current_name</p>";
    //global $current_name;
    //$current_name = $current_name;
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
        //$current_type = 'cat';
        //$current_id = get_query_var('cat');
        $current_type = 'p';
        $current_id = $post->ID; // global $post;
        //echo 'current_type = ' . $current_type . ', current_id = ' . $current_id;
        //print_r(get_the_category($post->ID));
	$cats = get_the_category($post->ID);
        //print_r($cats[0]);
        //$current_id = $cats[0]->cat_ID;
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

function not_use_span_tag($option) {
    if ($option['not_use_span_tag'] === 'checked') {
        $span[0] = '';
        $span[1] = '';
    } else {
        $span[0] = '<span>';
        $span[1] = '</span>';
    }
    return $span;
}

function get_align($option) {
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
    return $align;
}

function get_li_else($item_type, $item_id, $option, $p, $num, $current, $current_a, $title, $span, $nest_level) {
    global $post;
    
    $qf_getthumb_option = $option['qf_getthumb_option'];
    $qf_getthumb_enable = $option['qf_getthumb'];
    if (!function_exists('the_qf_get_thumb_one')){
        // qf-getthumb plug-in is disabled or not installed
        $qf_getthumb_enable = 0;
    }
    $child_cat_depth = $option['child_cat_depth'];
    $child_page_depth = $option['child_page_depth'];
    
    if ($qf_getthumb_enable) {
        $post = $p; // global $post;
        // call GF-GetThumb plug-in
        //$img = the_qf_get_thumb_one($qf_getthumb_option);
        // ie6 can't support padding for image, therefore add class
        $img = '<span class="qf-img">' . remove_class(the_qf_get_thumb_one($qf_getthumb_option)) . '</span>';
    } else {
        $img = '';
    }

    // have sub item ?
    //echo '<p>' . $nest_level . '</p>';
    $have_children = false;
    $sub_cats = array();
    $sub_pages = array();
    if ($item_type === 'cat') {    // sub category
        $sub_cats = get_categories('parent='.$item_id);
        if ($sub_cats) {
            $have_children = true;
        }
    } else if ($item_type === 'page_id') {    // sub page
        $sub_pages = get_pages('echo=1&sort_column=menu_order&depth=0&child_of='.$item_id);
        //if ($nest_level == 0) print_r($sub_pages);
        if ($sub_pages){
            $have_children = true;
        }
    }
    

    $m .= sprintf("<li class=\"page_item page-item-%s %s\">", $num, $current);
    if (!$option['toggle_button']) {
        if (($sub_cats && $nest_level + 1 <= $child_cat_depth) ||
            ($sub_pages && $nest_level + 1 <= $child_page_depth)) {
            $m .= '<div class="fixed-menu-toggle-button"></div>';
        } else {
            $m .= '<div class="fixed-menu-no-toggle-button"></div>';
        }
    }
    /*
    if ($img) {
        $m .= sprintf("<a href=\"%s/?%s=%s\" title=\"%s\">%s</a>", get_bloginfo('siteurl'), $item_type, $item_id, $title, $img);
    }
    $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s/?%s=%s\" title=\"%s\">%s%s%s</a>", $num, $current_a, get_bloginfo('siteurl'), $item_type, $item_id, $title, $span[0], $title, $span[1]);
      */
    $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s/?%s=%s\" title=\"%s\">%s%s%s%s</a>", $num, $current_a, get_bloginfo('siteurl'), $item_type, $item_id, $title, $img, $span[0], $title, $span[1]);
    //if ($have_children) $m .= "<ul>\n";

    // sub category/page
    if ($sub_cats && ++$nest_level <= $child_cat_depth) {
        $m .= "\n".'<ul class="' . $num . ' child fixed-menu-toggle">' . "\n";
        //print_r($sub_cats);
        foreach($sub_cats as $cat) {
            // WP2.8では型が一致しなくなっていたので修正
            if ($cat->category_parent != $item_id) continue;
            $m .= get_li_else($item_type, $cat->cat_ID, $option, $cat, $num, $current, $current_a, $cat->cat_name, $span, $nest_level);
        }
        $m .= "</ul>\n";
    } else if ($sub_pages && ++$nest_level <= $child_page_depth) {
        //$m .= '<ul class="' . $num . ' child">' . "\n";
        $m .= "\n".'<ul class="' . $num . ' child fixed-menu-toggle">' . "\n";
        foreach($sub_pages as $page) {
            // WP2.8では型が一致しなくなっていたので修正
            if ($page->post_parent != $item_id) continue;
            //print_r($sub_pages);
            //echo "<p>$title -> [$child->post_title]</p>";
            $m .= get_li_else($item_type, $page->ID, $option, $page, $num, $current, $current_a, $page->post_title, $span, $nest_level);
        }
        $m .= "</ul>\n";
    }
    $m .= "</li>\n";

    return $m;
}

function get_li_others($item_type, $cssClass, $option, $p, $num, $current, $current_a, $title, $span, $exclude, $all_content, $current_type, $current_id) {
    $oIDs = array();
    $nest_level = 0;

    global $current_name;

    foreach ($all_content as $num => $content) {
        if ($item_type === 'cat') {
            if ($content->category_parent) continue;
            $oID = $content->cat_ID;
            //$num2 = 'other_cats_' . $oID;
            $num = $cssClass;
        } else if ($item_type === 'page_id') {
            //print_r($content);
            if ($content->post_parent) continue;
            $oID = $content->ID;
            //$num2 = 'other_pages_' . $oID;
            $num = $cssClass;
        } else {
            return;
        }
        //$oTitle = $cat->cat_name;
        if (array_key_exists($oID, $exclude)) { continue; }
        // get post object
        list($p, $title) = fixed_menu_get_post($item_type, $oID);
        //print_r($p);
        //echo $current_type . ', ' . $current_id;
        if ($current_type === $item_type && $current_id == $oID) {
            $current = 'current_page_item';
            $current_a = $current . '_' . $num . '_a';
            $current_a .= ' ' . $current . '_a';
            $current_name = $num;
        } else {
            $current = '';
            $current_a = '';
        }
        $m .= get_li_else($item_type, $oID, $option, $p, $num, $current, $current_a, $title, $span, $nest_level);
    }
    return $m;
}

function get_li_url($item, $option, $num, $current, $current_a, $span) {
    $qf_getthumb_option = $option['qf_getthumb_option'];
    $qf_getthumb_enable = $option['qf_getthumb'];
    if (!function_exists('the_qf_get_thumb_one')){
        // qf-getthumb plug-in is disabled or not installed
        $qf_getthumb_enable = 0;
    }

    if ($qf_getthumb_enable) {
        // call GF-GetThumb plug-in
        // use external site image
        $img = the_qf_get_thumb_one($qf_getthumb_option, '', $item->url);
        //$img = sprintf("<img src=\"%s\" />", $item->img);
    } else {
        $img = '';
    }
    $m .= sprintf("<li class=\"page_item page-item-%s %s\">", $num, $current);
    $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s\" title=\"%s\">%s%s%s%s</a></li>\n", $num, $current_a, $item->url, $item->str, $img, $span[0], $item->str, $span[1]);
    return $m;
}

function get_li_home($model, $option, $num, $current, $current_a, $span) {
    $qf_getthumb_option = $option['qf_getthumb_option'];
    $qf_getthumb_enable = $option['qf_getthumb'];
    if (!function_exists('the_qf_get_thumb_one')){
        // qf-getthumb plug-in is disabled or not installed
        $qf_getthumb_enable = 0;
    }

    if ($qf_getthumb_enable) {
        $home_image = $model->getHomeImage();
        // home_image use at in content image.
        //   example: page_id=4, cat=3, p=2
        if (preg_match("/^([a-z_]*)=([0-9]*)$/", $home_image, $match)) {
            list($p, $title) = fixed_menu_get_post($match[1], $match[2]);
            $post = $p; // global $post;
            // call GF-GetThumb plug-in
            $img = '<span class="qf-img">' . the_qf_get_thumb_one($qf_getthumb_option) . '</span>';
        } else {
            // home_image is image location.
            //$img = sprintf("<img src=\"%s\" />", $home_image);
            $img = sprintf("<span class=\"qf-img\"><img src=\"%s\" /></span>", $home_image);
        }
    } else {
        $img = '';
    }
    $m .= sprintf("<li class=\"page_item page-item-home %s\">", $current);
    if (!$option['toggle_button']) {
        $m .= '<div class="fixed-menu-no-toggle-button"></div>';
    }
    $m .= sprintf("<a class=\"page_item_a page-item-%s-a %s\" href=\"%s/\" title=\"%s\">%s%s%s%s</a></li>\n", $num, $current_a, get_bloginfo('siteurl'), $num, $img, $span[0], $model->getHomeString(), $span[1]);
    return $m;
}

function get_exclude_page($menu, $option) {
    $exclude = array();
    $exclude_pages = split(',', $option['exclude_pages']);
    foreach ($exclude_pages as $num => $item) {
        $exclude[$item] = 1;
    }
    foreach ($menu as $num => $item) {
        if ($item->type === 'page_id') {
            $exclude[$item->id] = 1;
        }
    }
    return $exclude;
}

function get_exclude_cat($menu, $option) {
    $exclude = array();
    $exclude_cat = split(',', $option['exclude_categories']);
    foreach ($exclude_cat as $num => $item) {
        $exclude[$item] = 1;
    }
    if ($option['do_not_show_uncategorized'] === 'checked') {
        $exclude[1] = 1; // Uncategorized
    }
    foreach ($menu as $num => $item) {
        if ($item->type === 'cat') {
            $exclude[$item->id] = 1;
        }
    }
    return $exclude;
}

function remove_class($tag) {
    //$tag = preg_replace('/class=\"[^\"]*\"/i', '', $tag);
    //$tag = preg_replace('/title=\"[^\"]*\"/i', '', $tag);
    //$tag = preg_replace('/alt=\"[^\"]*\"/i', '', $tag);
    //$tag = preg_replace('/[ ]+/', ' ', $tag);
    $tag = preg_replace('/.jpg+/', '.png', $tag);
    return $tag;
}
?>
