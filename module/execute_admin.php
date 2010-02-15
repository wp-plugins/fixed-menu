<?php
/*
 * Setting Screen
 * call: execute_admin($this);
 * -*- Encoding: utf8n -*-
 */
function execute_admin(&$obj) {
    //print_r($_REQUEST);
    //print_r($obj->model);
    //echo phpinfo();

    if (is_array($_REQUEST)) {
        // Array extract to variable
        extract($_REQUEST);
    }

    ?>
    <h2>Fixed Menu</h2>
    <form name="formFixedMenu" method="post">
      <input type="hidden" name="action" value="" />
        
    <?php
    if ($action === 'addnew') {
        echo '<input type="hidden" name="args" value="" />';
        $menuName = trim($menuName);
        if (!$menuName) {
            echoMenuList($obj, 'Input new menu name');
            return;
        }
        $msg = addnew($obj, $menuName);
        echoMenuList($obj, $msg);
    } else if ($action === 'save') {
        //echo $qf_getthumb;
        save($obj, $menuName);
    } else if ($action === 'edit') {
        $menuName = $args;
        $menuItem = $obj->model->getMenu($menuName);
        edit($obj, $menuName);
    } else if ($action === 'delete') {
        delete($obj, $menuName);
        echo '<input type="hidden" name="args" value="" />';
        echoMenuList($obj);
    } else {
        echo '<input type="hidden" name="args" value="" />';
        echoMenuList($obj);
    }
    
    echo '</form>';
}

function checkInput($str, $type) {
    if ($type === 'class') {
        if (preg_match('/^[0-9\-]|[_\-]$|[^a-zA-Z0-9_\-]/', $str)) return false;
    } else if ($type === 'remove_tag') {
        $str = str_replace('>', '&gt;', $str);
        $str = str_replace('<', '&lt;', $str);
    } else if ($type === 'int') {
        $str = preg_replace('/[^0-9]/', '', $str);
    } else if ($type === 'exclude') {
        $str = preg_replace('/[^0-9,]/', '', $str);
        $str = preg_replace('/,+/', ',', $str);
        $str = preg_replace('/^,/', '', $str);
        $str = preg_replace('/,$/', '', $str);
    } else if ($type === 'remove_tag_item') {
        $str = str_replace('>', '&gt;', $str);
        $str = str_replace('<', '&lt;', $str);
        $str = str_replace('&lt;rs&gt;', '<rs>', $str);
        $str = str_replace('&lt;fs&gt;', '<fs>', $str);
    }
    return $str;
}

function delete(&$obj, $menuName) {
    $obj->model->deleteMenu($menuName);
    $obj->updateWpOption($obj->model); // Save
}

function updateDB ($menu){
    //print_r($menu);

    //$wpdb = $GLOBALS['wpdb'];
    global $wpdb;

    foreach ($menu as $i => $item) {
        if ($item->type_name === 'home') continue;
        
        if ($item->enable_item === 'on') {
            $v = 'publish';
        } else if ($item->enable_item === 'off') {
            $v = 'private';
        } else {
            continue;
        }

        $sql = "UPDATE $wpdb->posts SET post_status = '$v' WHERE ID = $item->id;";
        $wpdb->query($sql);
        //echo '<p>' . $sql . '</p>';
        
        /*
        $table = $wpdb->posts;
        $data['post_status'] = $v;
        $where['ID'] = $item->id;
        $wpdb->update($table, $data, $where);
          */
    }
}

function save(&$obj, $menuName) {
    //print_r($_REQUEST);
    if (is_array($_REQUEST)) {
        // Array extract to variable
        extract($_REQUEST);
    }

    $model = &$obj->model;
    $menu = &$model->getMenu($menuName);
    $menu_str = $model->toString($menuName);

    // option
    $option = &$model->getOption($menuName);
    $option['qf_getthumb'] = checkInput($qf_getthumb, 'remove_tag');
    $option['qf_getthumb_option'] = checkInput($qf_getthumb_option, 'remove_tag');
    $option['is_enable'] = checkInput($is_enable, 'remove_tag');
    $option['menu_title'] = checkInput($fixed_menu_title, 'remove_tag');
    $option['align'] = checkInput($fixed_menu_align, 'remove_tag');
    $option['change_publish_private_post'] = checkInput($change_publish_private_post, 'remove_tag');
    $option['not_use_span_tag'] = checkInput($not_use_span_tag, 'remove_tag');
    $option['do_not_show_uncategorized'] = checkInput($do_not_show_uncategorized, 'remove_tag');
    $option['exclude_pages'] = checkInput($exclude_pages, 'exclude');
    $option['exclude_categories'] = checkInput($exclude_categories, 'exclude');
    $option['child_cat_depth'] = checkInput($child_cat_depth, 'int');
    $option['child_page_depth'] = checkInput($child_page_depth, 'int');
    $option['toggle_button'] = checkInput($toggle_button, 'remove_tag');
    $model->setOption($menuName, $option);

    // home
    $home_string = checkInput($home_string, 'remove_tag');
    $model->setHomeString($home_string);
    $home_image = checkInput($home_image, 'remove_tag');
    $model->setHomeImage($home_image);

    // menu
    $args = checkInput($args, 'remove_tag_item');
    //echo $args;
    $model->setStrToMenu($menuName, $args);
    if ($change_publish_private_post === 'checked') {
        $msg .= updateDB($model->getMenu($menuName));
    }
    $obj->updateWpOption($model); // Save database-model
    $msg .= __('Saved', 'fixed_menu');
    edit($obj, $menuName, $msg);
}

function edit(&$obj, $menuName, $msg = '') {
    if ($msg) {
        printf("<div class=\"msg\">%s</div>", $msg);
    }

    $model = $obj->model;
    $menu = $model->getMenu($menuName);
    //
    foreach ($menu as $i => $value) {
        if (!$menu[$i]->enable_item) {
            $menu[$i]->enable_item = 'on';
        }
    }
    //print_r($menu);
    $menu_str = $model->toString($menuName);
    //print 'menu_str = ' . $menu_str;
    $option = $model->getOption($menuName);
    
    printf("<input type=\"hidden\" name=\"menuName\" value=\"%s\" />", $menuName);
    printf("<input type=\"hidden\" name=\"menuItem\" value=\"%s\" />", $menu_str);
    printf("<input type=\"hidden\" name=\"args\" value=\"%s\" />", $menu_str);

    $qf_getthumb = $option['qf_getthumb'];
    $is_enable = $option['is_enable'];

    ?>
    <div id="fixed_menu_info">
      <ul>
        <li><?php _e('Be sure to save after editing please.', 'fixed_menu')?></li>
        <li><?php _e('Text box, enter the css class to use the menu item.', 'fixed_menu')?></li>
      </ul>
      <ul>
    </div>
    <?php

    $im = array();//new wpFixedMenuItemModel();
    $ic = 0;
    // Home
    $im[$ic] = new WpFixedItemModel();
    $im[$ic]->type_name = 'home';
    $im[$ic]->type = 'home';
    $im[$ic]->id = 0;
    $im[$ic]->cssClass = 'home';
    $im[$ic]->str = 'Home';
    $im[$ic]->img = '';
    $im[$ic]->url = '';
    $im[$ic]->enable_item = 'on';
    $wpItem = $im[$ic]->toString();
    $ic++;
    // Other pages
    $im[$ic] = new WpFixedItemModel();
    $im[$ic]->type_name = 'other_pages';
    $im[$ic]->type = 'other_pages';
    $im[$ic]->id = 0;
    $im[$ic]->cssClass = 'other_pages';
    $im[$ic]->str = 'Other pages';
    $im[$ic]->img = '';
    $im[$ic]->url = '';
    $im[$ic]->enable_item = 'on';
    $wpItem = $im[$ic]->toString();
    $ic++;
    // Other categories
    $im[$ic] = new WpFixedItemModel();
    $im[$ic]->type_name = 'other_cats';
    $im[$ic]->type = 'other_cats';
    $im[$ic]->id = 0;
    $im[$ic]->cssClass = 'other_cats';
    $im[$ic]->str = 'Other categories';
    $im[$ic]->img = '';
    $im[$ic]->url = '';
    $im[$ic]->enable_item = 'on';
    $wpItem = $im[$ic]->toString();
    $ic++;
    // RSS feed
    $im[$ic] = new WpFixedItemModel();
    $im[$ic]->type_name = 'rss_feed';
    $im[$ic]->type = 'rss_feed';
    $im[$ic]->id = 0;
    $im[$ic]->cssClass = 'rss_feed';
    $im[$ic]->str = 'RSS Feed';
    $im[$ic]->img = '';
    $im[$ic]->url = '';
    $im[$ic]->enable_item = 'on';
    $wpItem = $im[$ic]->toString();
    $ic++;
    
    // all page
    $pages = get_pages();
    foreach ($pages as $post) {
        //print_r($post);
        $im[$ic] = new WpFixedItemModel();
        $im[$ic]->type_name = 'Page';
        $im[$ic]->type = 'page_id';
        $im[$ic]->id = $post->ID;
        $im[$ic]->cssClass = '';
        $im[$ic]->str = $post->post_title;
        $im[$ic]->img = '';
        $im[$ic]->url = '';
        if ($wpItem) { $wpItem .= '<rs>'; }
        $wpItem .= $im[$ic]->toString();
        $ic++;
    }

    // all category
    $categories = get_categories();
    foreach ($categories as $num => $cat) {
        $im[$ic] = new WpFixedItemModel();
        $im[$ic]->type_name = 'Category';
        $im[$ic]->type = 'cat';
        $im[$ic]->id = $cat->cat_ID;
        $im[$ic]->cssClass = '';
        $im[$ic]->str = $cat->cat_name;
        $im[$ic]->img = '';
        $im[$ic]->url = '';
        if ($wpItem) { $wpItem .= '<rs>'; }
        $wpItem .= $im[$ic]->toString();
        $ic++;
    }

    // latest 50 posts
    $preamble = get_posts('numberposts=50'); // get contents max 50
    foreach ($preamble as $post) {
        //print_r($post);
        $im[$ic] = new WpFixedItemModel();
        $im[$ic]->type_name = 'Post';
        $im[$ic]->type = 'p';
        $im[$ic]->id = $post->ID;
        $im[$ic]->cssClass = '';
        $im[$ic]->str = $post->post_title;
        $im[$ic]->img = '';
        $im[$ic]->url = '';
        if ($wpItem) { $wpItem .= '<rs>'; }
        $wpItem .= $im[$ic]->toString();
        $ic++;
    }

    // remove the item exist in menu from collected Wordpress contents 
    $wpItem = '';
    foreach ($im as $num => $w) {
        $is_menu_item = 0;
        foreach ($menu as $n => $m) {
            if (($w->type === $m->type && $m->type === 'home') ||
                ($w->type_name === $m->type_name &&
                 $w->type === $m->type && $w->id === $m->id)) {
                $is_menu_item = 1;
                break;
            }
        }
        if ($is_menu_item) {
            unset($im[$num]);
            continue;
        }
        if ($wpItem) { $wpItem .= '<rs>'; }
        $wpItem .= $w->toString();
    }
    
    
    // make contents list
    $s = makeContentsHtml($im);
    printf("<fieldset id=\"contents_html2\"><legend>Site contents</legend><div id=\"contents_html\">%s</div></fieldset>", $s);
    
    // make menu item list
    $s = makeMenuHtml($menu);
    
    printf("<fieldset id=\"menu_html2\"><legend>Menu items</legend><div id=\"menu_html\">%s</div></fieldset>", $s);
    printf("<input type=\"hidden\" name=\"wpItem\" value=\"%s\" />", $wpItem);

    ?>
    <fieldset id="item_link_f"><legend><?php _e('Add link to menu', 'fixed_menu');?></legend>
      <div class="infield">
        URL <input type="text" name="link_uri" id="link_uri" value="http://" size="20" /> 
        String <input type="text" name="link_string" id="link_string" value="" size="20" /> 
        <input type="button" name="addUrl" value="<?php _e('Add', 'fixed_menu')?>" onClick="FixedMenuJs.addMenuList_url()" />
        <br />css class name <input type="text" name="link_cssClass" id="link_cssClass" value="link" />
        Image (use to QF-GetThumb plugin) <input type="text" name="link_image" id="link_image" value="http://" /> 
      </div>
    </fieldset>

      
    <fieldset id="common_setting_f"><legend><?php _e('Others Setting', 'fixed_menu');?></legend>
      <div class="infield">
        <input type="checkbox" name="change_publish_private_post" value="checked" <?php echo $option['change_publish_private_post'];?> /><?php _e('Menu item enable/disable changed to the post change publish/private.','fixed_menu');?>
        <br /><input type="checkbox" name="toggle_button" value="checked" <?php echo $option['toggle_button'];?> /><?php _e('Not use Toggle Button Div.','fixed_menu');?>
        <br /><input type="checkbox" name="not_use_span_tag" value="checked" <?php echo $option['not_use_span_tag'];?> /><?php _e('Not use span tag.','fixed_menu');?>
        <br /><input type="checkbox" name="do_not_show_uncategorized" value="checked" <?php echo $option['do_not_show_uncategorized'];?> /><?php _e('Don\'t show Uncategorized in Other categories.','fixed_menu');?>
        <br /><?php _e('Exclude pages','fixed_menu'); ?> <input type="text" name="exclude_pages" id="exclude_pages" value="<?php echo $option['exclude_pages'];?>" size="20" /> <?php _e('(Comma separated)','fixed_menu'); ?>
        <br /><?php _e('Exclude categories','fixed_menu'); ?> <input type="text" name="exclude_categories" id="exclude_categories" value="<?php echo $option['exclude_categories'];?>" size="20" /> <?php _e('(Comma separated)','fixed_menu'); ?>
      </div>
    </fieldset>
      
    <fieldset id="qf_getthumb_f"><legend><?php _e('Sort order','fixed_menu'); ?></legend>
      <div class="infield">
      </div>
    </fieldset>
      
    <fieldset id="qf_getthumb_f"><legend><?php _e('Sub category/page','fixed_menu'); ?></legend>
      <div class="infield">
        <?php _e('Number for limited child category depth','fixed_menu'); ?> <input type="text" name="child_cat_depth" id="child_cat_depth" value="<?php echo $option['child_cat_depth'];?>" size="20" /> <?php _e('(0 to 99)','fixed_menu'); ?>
        <br /><?php _e('Number for limited child page depth','fixed_menu'); ?> <input type="text" name="child_page_depth" id="child_page_depth" value="<?php echo $option['child_page_depth'];?>" size="20" /> <?php _e('(0 to 99)','fixed_menu'); ?>
      </div>
    </fieldset>

      
    <fieldset id="qf_getthumb_f"><legend>QF-GetThumb plug-in</legend>
      <div class="infield">
        Option <input type="text" name="qf_getthumb_option" id="qf_getthumb_option" value="<?php echo $option['qf_getthumb_option'] ?>" size="80" /> 
      </div>
    </fieldset>

      
    <div id="fixed_menu_ctrl">
    <?php _e('Menu title', 'fixed_menu')?> <input type="text" name="fixed_menu_title" id="fixed_menu_titld" value="<?php echo $option['menu_title'] ?>" size="40" />
    <?php _e('Align', 'fixed_menu')?> 
    <select name="fixed_menu_align">
    <option value="none" <?php if ($option['align'] === 'none') { echo 'selected'; } ?> ><?php _e('None', 'fixed_menu')?></option>
    <option value="left" <?php if ($option['align'] === 'left') { echo 'selected'; } ?> ><?php _e('Left', 'fixed_menu')?></option>
    <option value="center" <?php if ($option['align'] === 'center') { echo 'selected'; } ?> ><?php _e('Center', 'fixed_menu')?></option>
    <option value="right" <?php if ($option['align'] === 'right') { echo 'selected'; } ?> ><?php _e('Right', 'fixed_menu')?></option>
    </select>
    <br />
    <input type="button" name="delete" value="<?php _e('Delete this menu', 'fixed_menu')?>" onClick="FixedMenuJs.deleteMenu()" />
    <select name="is_enable">
      <option value="enable" <?php if ($is_enable === 'enable') { echo 'selected'; } ?> ><?php _e('Enabled', 'fixed_menu')?></option>
    <option value="disable" <?php if ($is_enable !== 'enable') { echo 'selected'; } ?>><?php _e('Disabled', 'fixed_menu')?></option>
    </select>
    <input type="checkbox" name="qf_getthumb" value="1" <? if ($qf_getthumb) { echo 'checked'; } ?> /> <?php _e('Use QF-GetThumb plug-in', 'fixed_menu')?>
      <?php if (!function_exists('the_qf_get_thumb_one')){ _e('(Can not use plug-in now)', 'fixed_menu'); }?>
    <input type="button" name="save" value="<?php _e('Save', 'fixed_menu')?>" onClick="FixedMenuJs.do_submit('save')" />
    </div>

    <fieldset id="common_setting_f"><legend><?php _e('Common Setting', 'fixed_menu');?></legend>
      <div class="infield">
        <?php _e('Home String','fixed_menu');?> <input type="text" name="home_string" id="home_string" value="<?php echo $model->home_string ?>" />
        <br /><?php _e('Home Image (use to QF-GetThumb plugin)','fixed_menu');?> <input type="text" name="home_image" id="home_image" value="<?php echo $model->home_image ?>" />
        <br /><?php _e('How to specify the image of Home item', 'fixed_menu')?><br /><?php _e('Input image location. example: http://example/example.jpg', 'fixed_menu')?> <br /><?php _e('Input contents number. example: page_id=2 or cat=3 or p=4 ...', 'fixed_menu')?>
      </div>
    </fieldset>
      
    <?php
    $doc = getDoc();
    $doc = str_replace('_menuName_', $menuName, $doc);
    echo $doc;
}

function makeContentsHtml ($im) {
    if (!count($im)) {
        return 'No item';
    }
    $s = '<table class="fixedmenu_t1">';
    $n = 0;
    foreach ($im as $num => $w) {
        //$s .= '<tr><td>' . $w->type_name . '</td><td>' . $w->type . '</td><td>' . $w->id . '</td><td>' . $w->cssClass . '</td><td>' . $w->str . '</td><td><input type="text" name="menutext_' . $n . '" id="menutext_' . $n . '" value="' . $w->cssClass . '"/></td><td><input type="button" name="addMenuList" value="' . __('Add', 'fixed_menu') . '&gt;" onClick="FixedMenuJs.addMenuList(\'' . $n . '\')" /></td><tr>';
        $s .= '<tr><td>' . $w->type_name . '</td><td>' . $w->type . '</td><td>' . $w->id . '</td><td>' . $w->str . '</td><td><input type="text" name="menutext_' . $n . '" id="menutext_' . $n . '" value="' . $w->cssClass . '"/></td><td><input type="button" name="addMenuList" value="' . __('Add', 'fixed_menu') . '&gt;" onClick="FixedMenuJs.addMenuList(\'' . $n . '\')" /></td><tr>';
        $n++;
    }
    $s .= '</table>';
    return $s;
}

function makeMenuHtml ($menu) {
    if (!count($menu)) {
        return 'No item';
    }
    $s = '<table class="fixedmenu_t1">';
    $n = 0;
    foreach ($menu as $num => $m) {
        //echo 'enable_item = [' . $m->enable_item . ']';
        //if (!$m->enable_item) { $m->enable_item = 'on'; }
        if ($m->enable_item === 'off') {
            $checked = '';
        } else {
            $checked = 'checked';
        }
        $s .= '<tr><td><input type="button" value="&lt; ' . __('Return', 'fixed_menu') . '" onClick="FixedMenuJs.cancelItem(\'' . $n . '\')" /></td><td>' . $m->type_name . '</td><td>' . $m->type . '</td><td>' . $m->id . '</td><td>' . $m->cssClass . '</td><td>' . $m->str . '</td><td><input type="button" value="Up" onClick="FixedMenuJs.upItem(\'' . $n . '\')" /></td><td><input type="button" value="Down" onClick="FixedMenuJs.downItem(\'' . $n . '\')" /></td><td><input type="checkbox" name="item_on_off_' . $n . '" value="on" onClick="FixedMenuJs.checked(\'' . $n . '\')" '. $checked . ' /></td></tr>';
        $n++;
    }
    $s .= '</table>';
    return $s;
}

function addnew(&$obj, $menuName) {
    if (!checkInput($menuName, 'class')){
        return __('Can be used to menu name characters from a-z, A-Z, 0-9, minus and underbar. Can not used underbar and numeric at top, last character minus and underbar too.', 'fixed_menu');
    }
    //print_r($obj->model);
    if ($obj->model->addNewMenu($menuName) == false) {
        return __('It menu name is already used', 'fixed_menu');
    }
    $obj->updateWpOption($obj->model); // Save
    return '';
}

function echoMenuList(&$obj, $msg = '') {
    if ($msg) {
        printf("<div class=\"msg\">%s</div>", $msg);
    }
    printf("<ul>\n");
    ?>
    <li><?php _e('New menu', 'fixed_menu')?> <input type="text" name="menuName" />
    <input type="button" name="addnew" value="<?php _e('Add', 'fixed_menu')?>" onClick="FixedMenuJs.do_submit('addnew')" /></li>
    <?php
    foreach ($obj->model->data as $menuName => $record) {
        //$menuName = $record['menuName'];
        // void(0) and onClick: ie6 will not work
        //printf("<li><a href=\"javascript:void(0)\" onClick=\"FixedMenuJs.do_edit('%s')\">%s</a></li>\n", $menuName, $menuName);
        printf("<li><a href=\"javascript:FixedMenuJs.do_edit('%s')\" onClick=\"FixedMenuJs.do_edit('%s')\">%s</a></li>\n", $menuName, $menuName, $menuName);
    }
    printf("</ul>\n");
}


function getDoc() {
    $str = "";
    $str .= "<h3>" . __('Embed shortcode in editting contents', 'fixed_menu') . "</h3>";
    $str .= "<p>[fixed-menu menuname=_menuName_]</p>";
    
    $str .= "<h3>" . __('Embed theme, part1', 'fixed_menu') . "</h3>";
    $str .= "<p>&lt;?php fixed_menu('_menuName_'); ?&gt;<br>";
    $str .= "&lt;div class=&quot;&lt;?php echo fixed_menu_get_current_name(); ?&gt;&quot;&gt;<br>";
    $str .= "&nbsp;&nbsp; " . __('Content', 'fixed_menu') . "<br>";
    $str .= "  &lt;/div&gt; </p>";
    $str .= "<p>" . __('* Is also a good without div tag', 'fixed_menu') . "</p>";
    
    $str .= "<h3>" . __('Embed theme, part2', 'fixed_menu') . "</h3>";
    $str .= "<p>&lt;div class=&quot;&lt;?php echo fixed_menu_get_current_name(); ?&gt;&quot;&gt;<br>";
    $str .= "&lt;?php \$fm = fixed_menu('_menuName_', true); ?&gt;<br>";
    $str .= " &nbsp; &nbsp; &lt;?php echo \$fm; ?&gt;<br>";
    $str .= " &nbsp; &nbsp; " . __('Content', 'fixed_menu') . "<br>";
    $str .= " &lt;/div&gt;</p>";
    $str .= "<p>" . __('* Is also a good without div tag', 'fixed_menu') . "</p>";
    
    $str .= "<h3>" . __('The menu is printed as follows:', 'fixed_menu') . "</h3>";
    $str .= "<p>&lt;div id=&quot;_menuName_&quot;&gt;<br>";
    $str .= "&nbsp; &nbsp; &lt;div id=&quot;_menuName__sub&quot;&gt;<br>";
    $str .= "&nbsp; &nbsp; &nbsp; &nbsp; &lt;ul&gt;<br>";
    $str .= "&nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &nbsp; &lt;li&gt;" . __('Menu items','fixed_menu') . "&lt;/li&gt;<br>";
    $str .= "&nbsp; &nbsp; &nbsp; &nbsp; &lt;/ul&gt;<br>";
    $str .= "&nbsp; &nbsp; &lt;/div&gt;<br>";
    $str .= "&lt;/div&gt;<br>";
    $str .= "<p>" . __('* For more information, please refer to the actual output', 'fixed_menu') . "</p>";
    $str .= '<p>Thank you! Q.F. <a href="http://la-passeggiata.com/" target="_blank">QF-GetThumb plugin\'s Homepage</a></p>';
    $str .= "<p># If you notice a my mistake(Program, English...), Please tell me.</p>";
    
    return $str;
}
?>
