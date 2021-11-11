<?php
/*
Plugin Name: Email Confirmation for Restrict Content Pro
Plugin URI: https://github.com/angelvilches/emailconfirmation-rcp
Description: This plugin allow you to add a confirmation email field for Restrict Content Pro register form and use the email as username.
Version: 1.0.1
Author: Ángel Vilches
Author URI: https://www.angelvilches.com
License: GPLv2 or later
License URI:  https://www.gnu.org/licenses/gpl-2.0.html
Text Domain:  email-confirmation-for-restrict-content-pro
Domain Path:  /languages
*/

if (!defined('ABSPATH'))
	exit;

if (!defined('EMAILCONFIRMATION_RCP_TEXTDOMAIN'))
	define('EMAILCONFIRMATION_RCP_TEXTDOMAIN', 'email-confirmation-for-restrict-content-pro');


add_action( 'admin_init', 'av_ecrcp_child_plugin_has_parent_plugin' );
	function av_ecrcp_child_plugin_has_parent_plugin() {
		if ( is_admin() && current_user_can( 'activate_plugins' ) &&  !is_plugin_active( 'restrict-content-pro/restrict-content-pro.php' ) ) {
			add_action( 'admin_notices', 'av_ecrcp_author_admin_notice' );
	
			deactivate_plugins( plugin_basename( __FILE__ ) ); 
	
			if ( isset( $_GET['activate'] ) ) {
				unset( $_GET['activate'] );
			}
		}
	}
	
function av_ecrcp_author_admin_notice(){
	echo '<div class="notice notice-error is-dismissible">
			  <p>'.__('Sorry, but Email confirmation for Restrict Content Pro requires Restrict Content Pro plugin to be installed and active.','email-confirmation-for-restrict-content-pro').'</p>
			 </div>';
	}

//Archivo de idioma
//Language
add_action('plugins_loaded', 'av_ecrcp_load_textdomain');

	function av_ecrcp_load_textdomain() {
		
		$text_domain	= 'email-confirmation-for-restrict-content-pro';
		$path_languages = basename(dirname(__FILE__)).'/languages/';
	
		 load_plugin_textdomain($text_domain, false, $path_languages );
	}

//Carga el archivo con los estilos css
//Load the file with css.
add_action('wp_enqueue_scripts', 'av_ecrcp_cargacss');
function av_ecrcp_cargacss()
{
	wp_enqueue_style('av_cargacss', plugin_dir_url(__FILE__) . 'css/emailconfirmation-rcp.css');
}

//Añadimos un campo de email
// Add email field
function av_ecrcp_add_email() {

    $alternate_email = get_user_meta( get_current_user_id(), 'rcp_alt_email', true );
    ?>
<p id="rcp_user_email_wrap">
<label for="rcp_user_email2"><?php echo apply_filters ( 'rcp_registration_email_label', __( 'Email', 'email-confirmation-for-restrict-content-pro' ) ); ?></label>
<input name="rcp_user_email2" id="rcp_user_email2" class="required" type="text" <?php if( isset( $_POST['rcp_user_email2'] ) ) { echo 'value="' . esc_attr( $_POST['rcp_user_email2'] ) . '"'; } ?>/>
</p>

    <?php
}
add_action( 'rcp_before_register_form_fields', 'av_ecrcp_add_email' );

// Valida si ambos campos de email son idénticos
// Check if both email fields match
function av_ecrcp_validate_email( $posted ) {

    if ( is_user_logged_in() ) {
        return;
    }

    // Si no coincide con el email del campo rcp_user_email, da error
    if (  $posted['rcp_user_email2']  !== $posted['rcp_user_email'] ) {
		rcp_errors()->add( 'invalid_alt_email', __( 'Your emails do not match', 'email-confirmation-for-restrict-content-pro' ), 'register' );
    }

}
add_action( 'rcp_form_errors', 'av_ecrcp_validate_email',99);

// Quitamos la obligatoriedad de campo nombre de usuario en el formulario y usamos el email como nombre de usuario
// Remove  username requirement on the registration form and use the email address as the username.
function av_ecrcp_quitarusuario( $user ) {
	rcp_errors()->remove( 'username_empty' );
	$user['login'] = $user['email'];
	return $user;
}
add_filter( 'rcp_user_registration_data', 'av_ecrcp_quitarusuario' );