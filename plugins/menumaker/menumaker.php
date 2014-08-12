<?php
/*
Plugin Name: Menu Maker
Plugin URI: http://troidus.com/wordpress/menu-maker
Description: Make a menu from pages and posts for navigation. Use <code>&lt;?php id_menu_maker(); ?&gt;</code> anywhere on your site to display your menu.
Version: 0.6
Author: Indranil Dasgupta
Author URI: http://troidus.com/
*/

/*  Copyright 2009  Indranil Dasgupta  (email : indranild at gmail dot com)

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
    Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
*/

function menu_maker_activate() {
	add_option('id_before_menu','<ul>');
	add_option('id_menumaker_pullpage','1');
	add_option('id_before_item','<li>');
	add_option('id_after_item','</li>');
	add_option('id_before_active_item','<li class="active">');
	add_option('id_after_active_item','</li>');
	add_option('id_after_menu','</ul>');
	add_option('id_menumaker_number','1');
	add_option('id_menumaker_maxnum','1');
	add_option('id_menumaker_menu_1',array());
}

function menu_maker_menu() {
  add_options_page('Menu Maker', 'Menu Maker', 8, __FILE__, 'menu_maker_options');
}

function menu_maker_options() {
	$number = get_option('id_menumaker_number');
	if($number > get_option('id_menumaker_maxnum')) {
		update_option('id_menumaker_maxnum',$number);
	} elseif($number < get_option('id_menumaker_maxnum')) {
		$max = get_option('id_menumaker_maxnum');
		for($i = $number + 1; $i <= $max; $i++) {
			$menu = "id_menumaker_menu_{$i}";
			delete_option($menu);
		}
		update_option('id_menumaker_maxnum',$number);
	}
	for($i=1;$i<=$number;$i++) {
		$menu = "id_menumaker_menu_{$i}";
		if(!get_option($menu))
			add_option($menu, array());
	}
?>
	<div class="wrap">
		<div id="icon-options-general" class="icon32"><br /></div>
		<h2>Menu Maker</h2>
		<h3>Menu Options</h3>
		<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>
			<p>Options for your menu</p>
			<table class="form-table">
				<tr valign="top">
					<th scope="row">Before menu</th>
					<td><input type="text" name="id_before_menu" value="<?php echo htmlspecialchars(get_option('id_before_menu')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Before item</th>
					<td><input type="text" name="id_before_item" value="<?php echo htmlspecialchars(get_option('id_before_item')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">After item</th>
					<td><input type="text" name="id_after_item" value="<?php echo htmlspecialchars(get_option('id_after_item')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Before <em>active</em> item</th>
					<td><input type="text" name="id_before_active_item" value="<?php echo htmlspecialchars(get_option('id_before_active_item')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">After <em>active</em> item</th>
					<td><input type="text" name="id_after_active_item" value="<?php echo htmlspecialchars(get_option('id_after_active_item')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">After menu</th>
					<td><input type="text" name="id_after_menu" value="<?php echo htmlspecialchars(get_option('id_after_menu')); ?>" /></td>
				</tr>
				<tr valign="top">
					<th scope="row">Number of items</th>
					<td><input type="text" name="id_menumaker_number" value="<?php echo htmlspecialchars(get_option('id_menumaker_number')); ?>" /></td>
				</tr>
			</table>
			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="id_before_menu,id_before_item,id_after_item,id_before_active_item,id_after_active_item,id_after_menu,id_menumaker_number" />
			<p class="submit">
				<input type="submit" name="Submit" value="<?php _e('Save Changes') ?>" />
			</p>
		</form>
		<div>
			<h3>Menu</h3>
			<form method="post" action="options.php">
				<?php wp_nonce_field('update-options'); ?>
				<table class="form-table">
					<?php $menu_up_num = ''; ?>
					<?php for($i=1;$i<=$number;$i++) { ?>
						<?php $menu_num_name = "id_menumaker_menu_{$i}"; ?>
						<?php $saved_menu = get_option($menu_num_name); ?>
						<tr valign="top">
							<th scope="row">Menu item <?php echo $i; ?> (title, type, link)</th>
							<td>
								<input type="text" name="<?php echo $menu_num_name; ?>[]" value="<?php echo htmlspecialchars($saved_menu[0]); ?>" /> 
								<select name="<?php echo $menu_num_name; ?>[]">
									<option value="post"<?php if($saved_menu[1] == 'post') echo ' selected'; ?>>Post / Page</option>
									<option value="external"<?php if($saved_menu[1] == 'external') echo ' selected'; ?>>External</option>
									<option value="home"<?php if($saved_menu[1] == 'home') echo ' selected'; ?>>Home</option>
									<option value="category"<?php if($saved_menu[1] == 'category') echo ' selected'; ?>>Category</option>
								</select>
								<input type="text" name="<?php echo $menu_num_name; ?>[]" value="<?php echo htmlspecialchars($saved_menu[2]); ?>" />
							</td>
						</tr>
						<?php $menu_up_num .= "id_menumaker_menu_{$i},"; ?>
					<?php } ?>
					<?php $menu_up_num = rtrim($menu_up_num,","); ?>
				</table>
				<input type="hidden" name="action" value="update" />
				<input type="hidden" name="page_options" value="<?php echo $menu_up_num; ?>" />
				<p class="submit">
					<input type="submit" name="Submit" value="<?php _e('Update Menu') ?>" />
				</p>
			</form>
		</div>
	</div>
<?php
}

function id_menu_maker($name = 'default') {
	$output = '';
	
	$b_item = get_option('id_before_item');
	$a_item = get_option('id_after_item');
	$b_act_item = (get_option('id_before_active_item')) ? get_option('id_before_active_item') : $b_item;
	$a_act_item = (get_option('id_after_active_item')) ? get_option('id_after_active_item') : $a_item;
	$num = get_option('id_menumaker_number');
	
	$output .= get_option('id_before_menu');
	for($i=1;$i<=$num;$i++) {
		$menu_val = "id_menumaker_menu_{$i}";
		$menu = get_option($menu_val);
		if($menu[1] == 'post') {
			$link = get_permalink($menu[2]);
		} elseif($menu[1] == 'external') {
			$link = $menu[2];
		} elseif($menu[1] == 'home') {
			$link = get_option('home');
		} elseif($menu[1] == 'category') {
			$link = get_category_link($menu[2]);
		}
		$title = $menu[0];
		if($menu[1] != 'home' && (is_single($menu[2]) || is_category($menu[2]) || is_page($menu[2]))) {
			if($menu[1] == 'category') {
				if(is_single()) {
					$output .= "\n" . $b_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_item;
				} else {
					$output .= "\n" . $b_act_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_act_item;
				}
			} elseif($menu[1] == 'post') {
				if(is_single($menu[2]) || is_page($menu[2])) {
					$output .= "\n" . $b_act_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_act_item;
				} else {
					$output .= "\n" . $b_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_item;
				}
			}
		} elseif($menu[1] == 'home' && is_front_page()) {
			$output .= "\n" . $b_act_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_act_item;
		} else {
			$output .= "\n" . $b_item . '<a href="'. $link .'" title = "' . $title . '">' . $title . '</a>' . $a_item;
		}
	}
	$output .= "\n" . get_option('id_after_menu') . "\n";
	
	echo $output;
}

register_activation_hook(__FILE__,'menu_maker_activate');

add_action('admin_menu', 'menu_maker_menu');

?>