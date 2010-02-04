/*
 * fixed_menu.js
 * -*- Encoding: utf8n -*-
 *
 * Don't named 'class' and 'start' to variable at IE6.
 * Be an error when assign an object to not declared variable, Don't foget var.
 * Javascript Online Lint:
 *   http://www.javascriptlint.com/online_lint.php
 */

jQuery(document).ready(function(){
    //alert('jQuery.ready'); //test
});

// namespace
var FixedMenuJs;
if (typeof FixedMenuJs === 'undefined' || !FixedMenuJs) {
    FixedMenuJs = {};
}

// data object
FixedMenuJs.wpItems = new Array(); // wordpress contents
FixedMenuJs.meItems = new Array(); // menu data

FixedMenuJs.item = function () {
  var type_name; // 'Post'
  var type; // 'p'
  var id; // $post->ID
  var cssClass; // ''
  var str; // $post->post_title
  var img; // ''
  var url; // ''
  var enable_item; // ''
}; // Don't foget semicolon


FixedMenuJs.toItemObj = function (str) {
  //FixedMenuJs.items = new Array();
  if (!str) { return new Array(); }
  var items = new Array();
  var a = str.split('<rs>');
  for (var i in a) {
    var f = a[i].split('<fs>');
    var item = new FixedMenuJs.item();
    item.type_name = f[0];
    item.type = f[1];
    item.id = f[2];
    item.cssClass = f[3];
    item.str = f[4];
    item.img = f[5];
    item.url = f[6];
    item.enable_item = f[7];
    items[i] = item;
  }
  return items;
};

FixedMenuJs.toString = function (obj) {
  var s = '';
  for (var i in obj) {
    var c = obj[i];
    if (s.length) { s += '<rs>'; }
    s += c.type_name + '<fs>';
    s += c.type + '<fs>';
    s += c.id + '<fs>';
    s += c.cssClass + '<fs>';
    s += c.str + '<fs>';
    s += c.img + '<fs>';
    s += c.url + '<fs>';
    s += c.enable_item;
  }
  //alert(s);
  return s;
};

/*****************************************************************/

FixedMenuJs.toObj = function () {
  var f = document.formFixedMenu;

  var data;
  data = f.wpItem.value;
  FixedMenuJs.wpItems = FixedMenuJs.toItemObj(data);
  //alert(FixedMenuJs.wpItems[1].type_name);

  data = f.args.value;
  FixedMenuJs.meItems = FixedMenuJs.toItemObj(data);
  //alert(FixedMenuJs.meItems[1].type_name);
};

FixedMenuJs.toStr = function () {
  var f = document.formFixedMenu;

  f.wpItem.value = FixedMenuJs.toString(FixedMenuJs.wpItems);
  //alert(f.wpItem.value);

  f.args.value = FixedMenuJs.toString(FixedMenuJs.meItems);
  //alert(f.args.value);
};

/** general functions ************************************************/

FixedMenuJs.isBrowser = function () {
  if (document.all) { 
    ret = 'ie'; 
  } else if (document.layers) {
    ret = 'nn';
  } else if (document.getElementById) { 
    ret = 'n6';
  } else {
    alert('isBrowser: error: unknown browser');
    ret = 'unknown';
  }
  return ret;
};

FixedMenuJs.escape = function (str) {
  return String(str).replace(/[&<>"']/g, function ($0) {
    return {"&": "&amp;", "<": "&lt;", ">": "&gt;", '"': "&quot;", "'": "&#39;"}[$0];
  });
};

FixedMenuJs.makeLink = function (str) {
  return str.replace(/https?:\/\/[\w\-.!~*'();\/?:@&=+$,%#]+/g, '<a href="$&" target="_blank">$&</a>');
};

// Rewrite inner html.
FixedMenuJs.setInnerHtml = function (id, a) {
  //a = FixedMenuJs.escape(a);
  ib = FixedMenuJs.isBrowser();
  if (ib == 'ie') {
    document.all(id).innerHTML = a; 
  //} else if (IE) {
  //  document.layers[id].innerHTML = a; 
  } else if (ib == 'nn') { 
    document.layers[id].innerHTML = a; 
  } else if (ib == 'n6') {
    document.getElementById(id).innerHTML = a; 
  }
};

FixedMenuJs.strcmp = function (str1, str2) {
    var ct;
    var cmp;

    if((cmp = str1.length - str2.length) !== 0){
        return cmp;
    }

    for(ct = 0; ct < str1.length; ct++) {
        var c1 = str1.charCodeAt(ct);
        var c2 = str2.charCodeAt(ct);
        if((cmp = (c1 - c2)) !== 0){
            break;
        }
    }
    return cmp;
};

/*****************************************************************/


FixedMenuJs.inputCheck = function () {
  var msg = '';
  f = document.formFixedMenu;
  if (FixedMenuJs.strcmp(f.action.value, 'addnew') === 0) {
    if (f.menuName.value.length === 0) {
      msg += "<?php _e('Input new menu name.', 'fixed_menu'); ?>";
    }
  }

  return msg;
};

FixedMenuJs.deleteMenu = function () {
  var msg = '';
  f = document.formFixedMenu;
  menuName = f.menuName.value;

  // Confirm delete menu
  if (!window.confirm('<?php _e('Remove menu', 'fixed_menu')?> ' + menuName + '<?php _e(', Are you sure?', 'fixed_menu')?>')) {
    return; //cancel
  }
  FixedMenuJs.do_submit('delete');
};


FixedMenuJs.do_submit = function (str) {
    var f = document.formFixedMenu;
    f.action.value = str;
    msg = FixedMenuJs.inputCheck();
    if (msg.length > 0) {
      alert(msg);
      return;
    }
    f.submit();
};

FixedMenuJs.do_edit = function (str) {
  var f = document.formFixedMenu;
  f.args.value = str;
  FixedMenuJs.do_submit('edit');
};


FixedMenuJs.addMenuList = function (num) {
  var f = document.formFixedMenu;

  var mtid = 'menutext_' + num;
  //alert(mtid);
  var cssClass = document.getElementById(mtid).value;
  if (!cssClass) {
     alert('<?php _e('Input menu css class name.', 'fixed_menu')?>');
     return;
  }

  FixedMenuJs.toObj();
  var item = FixedMenuJs.wpItems[num];
  //alert(FixedMenuJs.wpItems.length);
  FixedMenuJs.wpItems.splice(num, 1); // remove
  item.cssClass = cssClass;
  //alert(FixedMenuJs.wpItems.length);
  FixedMenuJs.meItems.push(item); // add
  //alert(FixedMenuJs.meItems.length);
  FixedMenuJs.toStr();
  //alert(FixedMenuJs.wpItems.length);

  html = FixedMenuJs.getWpItemHtml();
  FixedMenuJs.setInnerHtml('contents_html', html);
  html = FixedMenuJs.getMenuHtml();
  FixedMenuJs.setInnerHtml('menu_html', html);
};

FixedMenuJs.cancelItem = function (num) {
  var f = document.formFixedMenu;

  FixedMenuJs.toObj();
  var item = FixedMenuJs.meItems[num];
  FixedMenuJs.meItems.splice(num, 1); // remove
  FixedMenuJs.wpItems.push(item); // add
  FixedMenuJs.toStr();

  html = FixedMenuJs.getWpItemHtml();
  FixedMenuJs.setInnerHtml('contents_html', html);
  html = FixedMenuJs.getMenuHtml();
  FixedMenuJs.setInnerHtml('menu_html', html);
};

FixedMenuJs.addMenuList_url = function () {
  var f = document.formFixedMenu;

  var uri = document.getElementById('link_uri').value;
  var str = document.getElementById('link_string').value;
  var img = document.getElementById('link_image').value;
  var cls = document.getElementById('link_cssClass').value;

  var msg = '';
  if (!uri) {
     msg = msg + "<?php _e('Input URL', 'fixed_menu')?>\n";
  }
  if (!str) {
     msg = msg + "<?php _e('Input link string', 'fixed_menu')?>\n";
  }
  if (msg) {
     alert(msg);
     return;
  }

  d = new Date();
  localTime = d.getTime();

  FixedMenuJs.toObj();
  var o = new FixedMenuJs.item();
  o.type_name = 'Link'; // 'Post';
  o.type = 'url'; // 'p';
  o.id = localTime; // $post->ID;
  o.cssClass = cls; // 'link'; // ''
  o.str = str; // $post->post_title;
  o.img = img; // '';
  o.url = uri; // '';

  FixedMenuJs.meItems.push(o);
  FixedMenuJs.toStr();

  html = FixedMenuJs.getMenuHtml();
  FixedMenuJs.setInnerHtml('menu_html', html);
};


FixedMenuJs.checked = function (num) {
  var f = document.formFixedMenu;
  var i = num;

  FixedMenuJs.toObj();
  var items = FixedMenuJs.meItems;

  if (FixedMenuJs.strcmp(items[i].enable_item, 'off') === 0) {
    items[i].enable_item = 'on';
  } else {
    items[i].enable_item = 'off';
  }

  FixedMenuJs.toStr();
  html = FixedMenuJs.getWpItemHtml();
  FixedMenuJs.setInnerHtml('contents_html', html);
  html = FixedMenuJs.getMenuHtml();
  FixedMenuJs.setInnerHtml('menu_html', html);
  
};


FixedMenuJs.upItem = function (num) {
  FixedMenuJs.moveItem('up', num);
};
FixedMenuJs.downItem = function (num) {
  FixedMenuJs.moveItem('down', num);
};
FixedMenuJs.moveItem = function (cmd, num) {
  var f = document.formFixedMenu;

  var i = num;

  FixedMenuJs.toObj();
  var items = FixedMenuJs.meItems;

  if (FixedMenuJs.strcmp(cmd, 'up') === 0) {
    if (i === 0) { return; }
    j = parseInt(num, 10) - 1;
    a = items[i];
    b = items[j];
    items[i] = b;
    items[j] = a;
  } else {
    if (i >= (items.length - 1)) { return; }
    j = parseInt(i, 10) + 1;
    a = items[i];
    b = items[j];
    items[i] = b;
    items[j] = a;
  }
  FixedMenuJs.toStr();

  html = FixedMenuJs.getWpItemHtml();
  FixedMenuJs.setInnerHtml('contents_html', html);
  html = FixedMenuJs.getMenuHtml();
  FixedMenuJs.setInnerHtml('menu_html', html);
};

FixedMenuJs.getMenuHtml = function() {
  var f = document.formFixedMenu;
  var checked;

  FixedMenuJs.toObj();
  var items = FixedMenuJs.meItems;
  if (!items.length) { return 'No item'; }

  t = '<table class="fixedmenu_t1">';
  for (var i in items) {
    var o = items[i];
    if (FixedMenuJs.strcmp(o.enable_item, 'off') === 0) {
      checked = '';
    } else {
      checked = 'checked';
    }

    t = t + '<tr><td><input type="button" value="&lt; <?php _e('Return', 'fixed_menu')?>" onClick="FixedMenuJs.cancelItem(\'' + i + '\')" /></td><td>' + o.type_name + '</td><td>' + o.type + '</td><td>' + o.id + '</td><td>' + o.cssClass + '</td><td>' + o.str + '</td><td><input type="button" value="Up" onClick="FixedMenuJs.upItem(\'' + i + '\')" /></td><td><input type="button" value="Down" onClick="FixedMenuJs.downItem(\'' + i + '\')" /></td><td><input type="checkbox" name="item_on_off_' + i + '" value="on" onClick="FixedMenuJs.checked(\'' + i + '\')" ' + checked + ' /></td></tr>';
  }
  t = t + '</table>';
  //alert(t);
  return t;
};

FixedMenuJs.getWpItemHtml = function () {
  var f = document.formFixedMenu;

  FixedMenuJs.toObj();
  var items = FixedMenuJs.wpItems;
  //alert(FixedMenuJs.wpItems[0].type_name);
  //alert(items[0].type_name);
  if (!items.length) { return 'No item'; }

  t = '<table class="fixedmenu_t1">';
  for (var i in items) {
    var o = items[i];
    //alert(items[i].type);
    //alert(o.type);

    t = t + '<tr><td>' + o.type_name + '</td><td>' + o.type + '</td><td>' + o.id + '</td><td>' + o.str + '</td><td><input type="text" name="menutext_' + i + '" id="menutext_' + i + '" value="' + o.cssClass + '" /></td><td><input type="button" name="addMenuList" value="<?php _e('Add', 'fixed_menu')?> &gt;" onClick="FixedMenuJs.addMenuList(\'' + i + '\')" /></td><tr>';

  }
  t = t + '</table>';
  //alert(t);
  return t;
};
