<?php
/*
Plugin Name: Top Bar Notification
Description: Free notification plugin, that allows you to add a notification to the top of your website.
Version: 1.12
Author: Minnek Digital Studio
Author URI: https://minnekdigital.com
License: Free, for more info read the license.
 */

defined('ABSPATH') or die("Exit");

if (!isset($wpdb)) {
	$wpdb = $GLOBALS['wpdb'];
}

//use the postmeta to add a key because the proporse is add to specific page.
register_activation_hook(__FILE__, 'tbn_Install');

function tbn_Install() {
	global $wpdb;
	$charset_collate = $wpdb->get_charset_collate();
	$tableName = $wpdb->prefix . 'notification_setting';
	$sql = "CREATE TABLE $tableName (
		id mediumint(9) NOT NULL AUTO_INCREMENT,
		activate INT(9),
		color VARCHAR(255) DEFAULT '#000000' NOT NULL,
		page_id INT(12) DEFAULT 23445 NOT NULL,
		message VARCHAR(500) DEFAULT 'This is a test',
		PRIMARY KEY (id)
	) $charset_collate;";

	require_once ABSPATH . 'wp-admin/includes/upgrade.php';
	dbDelta($sql);

}

//  adding menu to the dashboard setting
add_action('admin_menu', 'tbn_notification_bar_menu');

function tbn_notification_bar_menu() {
	add_options_page('Notification Settings', 'Notification Settings', 'manage_options', 'notification', 'tbn_notification_option');
}

// adding script to head on Front-End
function tbn_scriptDisplay() {
	wp_enqueue_style('style', plugins_url('/assets/css/style.css', __File__));
	wp_enqueue_style('materialize-icon', plugins_url('/assets/css/materialize-icon.css', __File__));
	wp_enqueue_style('animate.min', plugins_url('/assets/css/animate.min.css', __File__));
	// wp_enqueue_script('jquery');
	wp_enqueue_script('ajaxRequest', plugins_url('/assets/js/ajaxRequest.js', __File__));
}

// Add hook for admin <head></head>
add_action('wp_head', 'tbn_scriptDisplay');

function tbn_notification_option() {
	if (!current_user_can('manage_options')) {
		wp_die(__("Your are not an Admin"));
	}
	tbn_authorized();
}
function tbn_script_js_ccs_backend() {
	wp_enqueue_style('style', plugins_url('/assets/css/style.css', __File__));
	wp_enqueue_style('spectrum', plugins_url('/assets/css/spectrum.css', __File__));
	wp_enqueue_script('tbnjscolor', plugins_url('assets/js/tbnjscolor.js', __FILE__));
	wp_enqueue_script('ajaxAdd', plugins_url('assets/js/ajaxAdd.js', __FILE__));
	wp_enqueue_script('color_init', plugins_url('assets/js/color_init.js', __FILE__));

}

add_action('wp_ajax_nopriv_tbn_ajax_input', 'tbn_ajax_input', 15);
add_action('wp_ajax_tbn_ajax_input', 'tbn_ajax_input', 15);
//adding function ajax to add and update the info
add_action('wp_ajax_tbn_ajax_add', 'tbn_ajax_add', 15);
function tbn_ajax_input() {
	global $wpdb;

	if ($_POST['id']) {
		$id = sanitize_text_field($_POST['id']);
		$tableName = $wpdb->prefix . 'notification_setting';
		$settingResult = $wpdb->get_results(
			"SELECT activate , color, page_id as page, message as notification FROM $tableName WHERE (page_id = $id) OR (page_id = 0)"
		);
		if ($settingResult) {
			if ($settingResult[0]->activate != 0) {
				$data = array(
					'html' => '<div class="mc-notification"><div class="containers"><div class="notification-content">' . $settingResult[0]->notification . '<div class="mc-close"><div class="cls"><i class="material-icons close">close</i></div></div></div></div></div>',
					'color' => $settingResult[0]->color,
				);
				echo (json_encode($data));
				die();
			} else {
				header('HTTP/1.0 204 No Content', true, 404);die();
			}
		} else {
			header('HTTP/1.0 204 No Content', true, 404);die();
		}
	}
}

function tbn_ajax_add() {
	global $wpdb;

	$tbnDataAllow = array(
		'width' => array(),
		'height' => array(),
		'style' => array(),
		'src' => array(),
		'href' => array(),
	);

	$arr = array(
		'br' => $tbnDataAllow,
		'p' => $tbnDataAllow,
		'strong' => $tbnDataAllow,
		'a' => $tbnDataAllow,
		'h1' => $tbnDataAllow,
		'button' => $tbnDataAllow,
		'span' => $tbnDataAllow,
		'h2' => $tbnDataAllow,
		'ul' => $tbnDataAllow,
		'li' => $tbnDataAllow,
		'input' => $tbnDataAllow,
	);

	$message = wp_kses($_POST['message'], $arr);

	$activate = "";
	if ($_POST['activate']) {
		$activate = sanitize_text_field($_POST['activate']);
	} else {
		$activate = 0;
	}
	$color = sanitize_hex_color($_POST['color']);
	$page = sanitize_text_field($_POST['page_id']);
	$id = sanitize_text_field(explode("-2", $_POST['data'])[0]);

	$table_name = $wpdb->prefix . 'notification_setting';
	if ($id != "") {
		$wpdb->update($table_name, array(
			'id' => $id,
			'activate' => $activate,
			'color' => $color,
			'page_id' => $page,
			'message' => $message,
		),
			array('id' => $id)
		);
	} else {
		$wpdb->insert($table_name, array(
			'color' => $color,
			'activate' => $activate,
			'page_id' => $page,
			'message' => $message,
		)
		);
	}
	echo "Changes Updated";
	die;
}

function tbn_authorized() {
	global $wpdb;
	$tableName = $wpdb->prefix . 'notification_setting';
	$settingResult = $wpdb->get_results(
		"SELECT id,activate ,color as color, page_id as page, message as notification FROM $tableName"
	);
	$tableName = $wpdb->prefix . 'posts';
	$page = $wpdb->get_results(
		"SELECT ID, post_title as page FROM $tableName WHERE post_type = 'page' AND post_status = 'publish'");

	//calling function to add script and style
	tbn_script_js_ccs_backend();

	?>
	<div class="Setting wrap">
		<form method="POST" action="" accept-charset="utf-8">
			<input name="data" style="display:none;" value="<?php echo count($settingResult) ? $settingResult[0]->id : ""; ?>" >
			<h1>General Setting</h1>
			<table class="form-table">
				<tbody>
                    <tr>
                        <th>
                            <input type="checkbox" name="activate" class="activate" value="1" <?php if (sizeof($settingResult) > 0) {echo ($settingResult[0]->activate == 1) ? 'checked' : "";}?> > Activate
                        </th>
                    </tr>
					<tr>
						<th> Bar Color</th>
						<td>
							<input hidden id="color" name="color" value="<?php echo count($settingResult) > 0 ? $settingResult[0]->color : ""; ?>">
		                    <input type="text" name="color_palette" class="jscolor" id="custom" value="<?php echo count($settingResult) > 0 ? $settingResult[0]->color : ""; ?>">
						</td>
					</tr>
					<tr>
						<th>Page</th>
						<td>
							<select name="page_id">
							<option value="0">All Pages</option>
							<?php foreach ($page as $key => $value) {
								if ($value->ID == intval($settingResult[0]->page)) {
								?>
									<option value="<?php echo $value->ID ?>" selected>
										<?php echo $value->page ?> </option>
										<?php continue;?>
								<?php
								}
								?>
								<option value="<?php echo $value->ID ?>"><?php echo $value->page ?></option>
							<?php
							} ?>
							</select>
						</td>
					</tr>
					<tr>
						<th>Message</th>
						<td>
                            <?php
$content = count($settingResult) ? preg_replace("/\s+/", " ", $settingResult[0]->notification) : "";
	$setting = array(
		'tinymce' => array(
			'toolbar2' => 'forecolor, fontsizeselect',
		),
		'teeny' => false,
		'textarea_rows' => 15,
		'tabindex' => 1,
		'textarea_name' => 'message',
	);
	$editor_id = "tbnmessage";
	wp_editor($content, $editor_id, $setting);
	?>
						</td>
					</tr>
				</tbody>
			</table>
			<input type="submit" value="Save Changes" class="submit button button-primary">
        </form>
	</div>

    <style>
        #tbnAlert{
            width: 200px;
            height: 60px;
            position: fixed;
            right: 0px;
            background-color: #70f576cc;
            text-align: center;
            border: 1px solid #7575754d;
            border-radius: 0.3rem;
            box-shadow: 13px -1px 20px black;
            z-index: 999;
        }
        #tbnAlert span{
            position: relative;
            top: 16px;
            color: white;
            font-size: 18px;
            text-shadow: 1px 2px 8px black;
            font-weight: 600;
        }

    </style>
<?php }
