<?php
/*
Plugin Name: ODY SSO Login Plugin
Plugin URI: http://github.com/hepaestus/nsl_wordpress_plugin
Description: Simple Plugin Uses Nextend Social Login and Register and Enforces Specific Domain Only
Version: 1.0
Author: Pete Olsen III
Author URI: http://hepaestus.com
License: GPL
*/

add_action('init','sso_hello');
function sso_hello() {
    echo "SSO DOMAIN : " . get_option('ody_sso_data') . " ONE";
}

add_filter('nsl_registration_user_data', function ($user_data, $provider, $errors) {

    // echo "SSO DOMAIN 1 : " . get_option('ody_sso_data');	
    // List of ALLOWED domains. Multiple domains can be separated with commas e.g.: "gmail.com","yahoo.com"
    //$allowed_domains = array("odysseyconsult.com");

    $allowed_domains = array(get_option('ody_sso_data'));
    echo $allowed_domains[0];

    if (isset($user_data['email']) && !empty($user_data['email']) && is_email($user_data['email'])) {

        $email_parts = explode('@', $user_data['email']);

        if (! in_array($email_parts[1], $allowed_domains )) {
            // * The error message
            $errors->add('invalid_email', '' . __('ERROR') . ': ' . __('Sorry, registration with this email domain is not allowed!'));
            return $user_data;
        }
    } else {
        $errors->add('invalid_email', '' . __('ERROR') . ': ' . __('Sorry, email is missing or invalid!'));
    }

    return $user_data;
}, 10, 3);


/* Runs when plugin is activated */
register_activation_hook(__FILE__,'ody_sso_install'); 

function ody_sso_install() {
/* Creates new database field */
    $domainArr = explode('.', $_SERVER['HTTP_HOST']);
    $domain = $domainArr[1] . "." . $domainArr[2];
    add_option("ody_sso_data", $domain, '', '');
}

/* Runs on plugin deactivation*/
register_deactivation_hook( __FILE__, 'ody_sso_remove' );

function ody_sso_remove() {
    /* Deletes the database field */
    delete_option('ody_sso_data');
}

if ( is_admin() ){

	/* Call the html code */
	add_action('admin_menu', 'ody_sso_admin_menu');

	function ody_sso_admin_menu() {
		add_options_page('World Ody Sso', 'Ody Sso Options', 'administrator', 'ody_sso', 'ody_sso_html_page');
	}


	function ody_sso_html_page() {
	?>
		<div>
			<h2>SSO Login Options</h2>

			<form method="post" action="options.php">
			<?php wp_nonce_field('update-options'); ?>

			<table width="510">
				<tr valign="top">
					<th width="" scope="row">Domain to Allow to Join:</th>
					<td width="">
						<input name="ody_sso_data" type="text" id="ody_sso_data"
							value="<?php echo get_option('ody_sso_data'); ?>" />
								(ex. your-domain.com )
					</td>
				</tr>
			</table>

			<input type="hidden" name="action" value="update" />
			<input type="hidden" name="page_options" value="ody_sso_data" />

			<p>
				<input type="submit" value="<?php _e('Save Changes') ?>" />
			</p>

			</form>
		</div>
	<?php
	}
}

?>
