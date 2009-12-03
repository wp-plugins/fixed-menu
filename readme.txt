=== Fixed Menu ===
Contributors: AI.Takeuchi
Donate link: http://takeai.silverpigeon.jp/
Tags: menu, link, manually, content, html, image, performance, photo, picture, plugin, url, wordpress
Requires at least: 2.6
Tested up to: 2.8
Stable tag: 1.6.5

Fixed Menu is a plugin that The user can assemble the menu by himself.

Demonstration site is here!!
http://takeai.silverpigeon.jp/


== Description ==

Fixed Menu is plugin to place the assembled menu in theme, sidebar widget and content (shortcode).


== Installation ==

1. Upload the entire Fixed Menu folder to the `/wp-content/plugins/` directory.
2. Activate the plugin through the 'Plugins' menu in WordPress.
3. Visit Settings Fixed Menu.
4. Add New menu. Input new menu name, click 'new menu'.
5. Click menu name link.
6. Select menu item from contents list, and setting options. and save.
7. (a) Visit Post, Page or Appearance widget sidebar, and write shortcode in content.
   (b) Editting theme file, insert php code.
8. After can reassemble menu, that is re select menu item.


Fixed Menu can call QF-GetThumb plugin.
Thereby display image to menu top, that's beautifull.
Thank you! Q.F. QF-GetThumb plugin's Homepage: http://la-passeggiata.com/
QF-GetThumb donate link: http://la-passeggiata.com/sample/sp01/?lang=en
* Please don't contact creator of QF-GetThumb about Fixed Menu.

== Example ==

* Menu name is 'menu1'.
* Fixed Menu use global variable '$current_name'.

1. Write shortcode in content or widget sidebar text.

[fixed-menu menuname=menu1]

2. Editting theme.

(a) Simple code

<?php fixed_menu('menu1'); ?>

(b) Use css class of menu item. Part 1.

<?php fixed_menu('menu1'); ?>
<div class="<?php echo $current_name; ?>">
  Content code
</div>

(c) Use css class of menu item. part 2.

<?php $fm = fixed_menu('menu1', true); ?>
<div class="<?php echo $current_name; ?>">
  <?php echo $fm; ?>
  Content code
</div>


== Others ==

# If you notice a my mistake(Program, English...), Please tell me.
Web site: http://takeai.silverpigeon.jp/
AI.Takeuchi <takeai@silverpigeon.jp>

