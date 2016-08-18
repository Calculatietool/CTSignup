<?php

class CalculatieTool {
	const API_ENDPOINT = 'http://localhost';

	private static $initiated = false;

	/**
	 * Initialize the hooks if class is not instanciated yet.
	 */
	public static function init() {
		if ( ! self::$initiated ) {
			self::init_hooks();
		}
	}

	/**
	 * Initializes WordPress hooks
	 */
	private static function init_hooks() {
		self::$initiated = true;

		add_shortcode('ctsignup-form-signup', array( 'CalculatieTool', 'signup_form_register' ) );
		add_shortcode('ctsignup-form-mail', array( 'CalculatieTool', 'signup_form_mail' ) );

		wp_enqueue_script( 'script', plugins_url( '/js/jquery.form-validator.min.js', __FILE__ ), array ( 'jquery' ) );

		add_action('wp_footer', array( 'CalculatieTool', 'jq_validator' ) );

		if ( is_admin() ) {
			add_action( 'admin_init', array( 'CalculatieTool', 'admin_init' ) );
			add_action( 'admin_menu', array( 'CalculatieTool', 'admin_menu' ) );
		}
	}

	/**
	 * Register the admin settings and validation callbacks.
	 */
	public static function admin_init() {
		register_setting( 'ctsignup-settings-group', 'client_id', array( 'CalculatieTool', 'setting_size_check' ) );
		register_setting( 'ctsignup-settings-group', 'client_secret', array( 'CalculatieTool', 'setting_size_check' ) );
	}

	/**
	 * Register the admin menu callback.
	 */
	public static function admin_menu() {
		add_options_page( __('CTSignup', 'ctsignup'), __('CTSignup', 'ctsignup'), 'manage_options', 'ctsignup-config', array( 'CalculatieTool', 'load_options_page' ) );
	}

	/**
	 * Print admin settings page.
	 */
	public static function load_options_page() {
		?>
			<div class="wrap">
			<h1>CTSignup settings</h1>

			<form method="post" action="options.php">
			    <?php settings_fields( 'ctsignup-settings-group' ); ?>
			    <?php do_settings_sections( 'ctsignup-settings-group' ); ?>
			    <table class="form-table">
			        <tr valign="top">
			        <th scope="row">Client ID</th>
			        <td><input type="text" name="client_id" value="<?php echo esc_attr( get_option( 'client_id') ); ?>" /></td>
			        </tr>
			         
			        <tr valign="top">
			        <th scope="row">Client secret</th>
			        <td><input type="password" name="client_secret" value="<?php echo esc_attr( get_option( 'client_secret' ) ); ?>" /></td>
			        </tr>
			    </table>
			    <?php submit_button(); ?>
			</form>
			
			<h2>Verify settings</h2>
			<a href="<?php _e(add_query_arg( 'verify', true )); ?>" class="button button-primary">Verify</a>
			</div>
		<?php
	}

	/**
	 * Validate the length of the client keys.
	 *
	 * @param string $input Key to validate.
	 * @return string The key on success, empty on failure.
	 */
	public static function setting_size_check( $input ) {
		if ( 40 != strlen( $input ) ) {
			add_settings_error( 'ctsignup-settings-keylength', esc_attr( 'settings-update' ), 'Invalid key provided' );
			return;
		}

		return $input;
	}

	/**
	 * Retrieve the client id from either the wp settings or
	 * defined config file.
	 *
	 * @return string The client id.
	 */
	public static function get_client_id() {
		return defined('CTSIGNUP_CLIENT_ID') ? constant('CTSIGNUP_CLIENT_ID') : get_option('client_id');
	}

	/**
	 * Retrieve the client secret from either the wp settings or
	 * defined config file.
	 *
	 * @return string The client secret.
	 */
	public static function get_client_secret() {
		return defined('CTSIGNUP_CLIENT_SECRET') ? constant('CTSIGNUP_CLIENT_SECRET') : get_option('client_secret');
	}

	/**
	 * Build the access token URL.
	 *
	 * @return string The resulting URL.
	 */
	public static function get_token_url() {
		return self::API_ENDPOINT . '/oauth2/access_token';
	}

	/**
	 * Build the request URL towards the service.
	 *
	 * @param string $uri Part of the URl specific for the request.
	 * @return string The resulting URL.
	 */
	public static function build_api_url( $uri ) {
		return self::API_ENDPOINT . $uri;
	}

	/**
	 * Print script component. Validator is called and
	 * bound to the HTML form.
	 */
	public static function jq_validator() {
		?>
		<script type='text/javascript'>
		jQuery(document).ready(function() {
			jQuery.validate({
				form : '#ctsignup_registration_form',
				lang: 'nl',
				modules: [ 'security', 'sanitize' ]
			});
			jQuery.validate({
				form : '#ctsignup_email_form',
				lang: 'nl',
				modules: [ 'sanitize' ]
			});
		});
		</script>
		<?php
	}

	/**
	 * Print the admin verification success banner.
	 */
	public static function ctsignup_admin_verify_ok() {
	    ?>
	    <div class="updated notice">
	        <p><?php _e( 'Connection succeeded, keys are verified.' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Print the admin verification error banner.
	 */
	public static function ctsignup_admin_verify_error() {
	    ?>
	    <div class="error notice">
	        <p><?php _e( 'Connection failed, check the keys.' ); ?></p>
	    </div>
	    <?php
	}

	/**
	 * Catch any errors that occur during the request.
	 *
	 * @return class WP_Error The wordpress error object.
	 */
	public static function ctsignup_errors() {
    	static $wp_error;
    	return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error( null, null, null ) );
	}

	/**
	 * print the HTML error view.
	 */
	public static function signup_form_error_messages() {
		if ( $codes = self::ctsignup_errors()->get_error_codes() ) {
			echo '<div class="ctsignup_errors">';
			echo '<span>Oeps:</span><ul>';
			foreach ( $codes as $code ) {
				$message = self::ctsignup_errors()->get_error_message( $code );
				echo '<li class="ctsignup_error">' . $message . '</span><br/>';
			}
			echo '</ul></div>';
		}	
	}

	/**
	 * Return the HTML view.
	 *
	 * @param string|array $attrs Supplied shortcode attributes.
	 * @return string The HTML page.
	 */
	public static function signup_form_register( $attrs ) {
		$redirect = "/";
		if ( isset( $attrs['success'] ) ) {
			$redirect = $attrs['success'];
		}

		ob_start(); ?>	
			<?php CalculatieTool::signup_form_error_messages(); ?>
	 
			<div class="ctsignup_signup">
				<form id="ctsignup_registration_form" class="ctsignup_form" action="" method="post">
					<p>
						<label for="ctsignup_user_first"><?php _e('Voornaam (verplicht)'); ?></label>
						<input name="ctsignup_user_first" id="ctsignup_user_first" type="text" value="<?php isset($_POST["ctsignup_user_first"]) ? _e($_POST["ctsignup_user_first"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_last"><?php _e('Achternaam (verplicht)'); ?></label>
						<input name="ctsignup_user_last" id="ctsignup_user_last" type="text" value="<?php isset($_POST["ctsignup_user_last"]) ? _e($_POST["ctsignup_user_last"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_phone"><?php _e('Telefoonnummer'); ?></label>
						<input name="ctsignup_user_phone" id="ctsignup_user_phone" type="text" value="<?php isset($_POST["ctsignup_user_phone"]) ? _e($_POST["ctsignup_user_phone"]) : null ?>"/>
					</p>
					<p>
						<label for="ctsignup_user_company"><?php _e('Bedrijfsnaam (verplicht)'); ?></label>
						<input name="ctsignup_user_company" id="ctsignup_user_company" class="required" type="text" value="<?php isset($_POST["ctsignup_user_company"]) ? _e($_POST["ctsignup_user_company"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_account"><?php _e('Username (verplicht)'); ?></label>
						<input name="ctsignup_user_account" id="ctsignup_user_account" class="required" type="text" value="<?php isset($_POST["ctsignup_user_account"]) ? _e($_POST["ctsignup_user_account"]) : null ?>" data-validation="alphanumeric" data-validation-allowing="-_" data-sanitize="trim lower"/>
					</p>
					<p>
						<label for="ctsignup_user_email"><?php _e('Email (verplicht)'); ?></label>
						<input name="ctsignup_user_email" id="ctsignup_user_email" class="required" type="email" value="<?php isset($_POST["ctsignup_user_email"]) ? _e($_POST["ctsignup_user_email"]) : null ?>" required data-validation="email"/>
					</p>
					<p>
						<label for="password"><?php _e('Wachtwoord (verplicht)'); ?></label>
						<input name="ctsignup_user_pass" id="password" class="required" type="password" data-validation="length" data-validation-length="min5"/>
					</p>
					<p>
						<label for="password_again"><?php _e('Herhaal wachtwoord (verplicht)'); ?></label>
						<input name="ctsignup_user_pass_confirm" id="password_again" class="required" type="password" data-validation="confirmation" data-validation-confirm="ctsignup_user_pass"/>
					</p>
					<p>
						<label for="ctsignup_agreement"><?php _e('Ga akkoord met de algemene voorwaarde'); ?> *</label>
						<input name="ctsignup_agreement" type="checkbox" data-validation="required">
					</p>
					<p>
						<input type="hidden" name="signup_redirect" value="<?php _e( $redirect ) ?>"/>
						<input type="submit" name="signup_form_save" value="<?php _e('Registreer account'); ?>"/>
					</p>
				</form>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Return the HTML view.
	 *
	 * @param string|array $attrs Supplied shortcode attributes.
	 * @return string The HTML page.
	 */
	public static function signup_form_mail( $attrs ) {
		$redirect = "/";
		if ( isset( $attrs['success'] ) ) {
			$redirect = $attrs['success'];
		}

		ob_start(); ?>	
			<?php CalculatieTool::signup_form_error_messages(); ?>
	 
			<div class="ctsignup_mail">
				<form id="ctsignup_email_form" class="mail_form" action="" method="post">
					<p>
						<label for="ctsignup_user_first"><?php _e('Voornaam (verplicht)'); ?></label>
						<input name="ctsignup_user_first" id="ctsignup_user_first" type="text" value="<?php isset($_POST["ctsignup_user_first"]) ? _e($_POST["ctsignup_user_first"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_last"><?php _e('Achternaam (verplicht)'); ?></label>
						<input name="ctsignup_user_last" id="ctsignup_user_last" type="text" value="<?php isset($_POST["ctsignup_user_last"]) ? _e($_POST["ctsignup_user_last"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_email"><?php _e('Email (verplicht)'); ?></label>
						<input name="ctsignup_user_email" id="ctsignup_user_email" class="required" type="email" value="<?php isset($_POST["ctsignup_user_email"]) ? _e($_POST["ctsignup_user_email"]) : null ?>" required data-validation="email"/>
					</p>
					<p>
						<label for="ctsignup_user_phone"><?php _e('Telefoonnummer (verplicht)'); ?></label>
						<input name="ctsignup_user_phone" id="ctsignup_user_phone" type="text" value="<?php isset($_POST["ctsignup_user_phone"]) ? _e($_POST["ctsignup_user_phone"]) : null ?>" data-validation="required"/>
					</p>
					<p>
						<label for="ctsignup_user_comment"><?php _e('Opmerking'); ?></label>
						<textarea name="ctsignup_user_comment" id="ctsignup_user_comment"><?php isset($_POST["ctsignup_user_comment"]) ? _e($_POST["ctsignup_user_comment"]) : null ?></textarea>
					</p>
					<p>
						<input type="hidden" name="mail_redirect" value="<?php _e( $redirect ) ?>"/>
						<input type="submit" name="mail_form_save" value="<?php _e('Versturen'); ?>"/>
					</p>
				</form>
			</div>
		<?php
		return ob_get_clean();
	}

	/**
	 * Handle the form POST request from the frontend. Perform
	 * basic validation to ease the service and gain faster feedback.
	 */
	public static function process_signup_form() {
		$first_name    = sanitize_text_field( $_POST["ctsignup_user_first"] );
		$last_name     = sanitize_text_field( $_POST["ctsignup_user_last"] );
		$company       = sanitize_text_field( $_POST["ctsignup_user_company"] );
		$account       = sanitize_text_field( $_POST["ctsignup_user_account"] );
		$email         = sanitize_email( $_POST["ctsignup_user_email"] );
		$password      = sanitize_text_field( $_POST["ctsignup_user_pass"] );
		$password2     = sanitize_text_field( $_POST["ctsignup_user_pass_confirm"] );
		$redirect      = sanitize_text_field( $_POST["signup_redirect"] );

		if ( !$first_name || !$last_name ) {
			self::ctsignup_errors()->add('empty_names', __('Voor en achternaam zijn verplicht') );
		}

		if( !$account ) {
			self::ctsignup_errors()->add('empty_account', __('Gebruikersnaam is verplicht') );
		}

		if( !$email ) {
			self::ctsignup_errors()->add('empty_email', __('Email is verplicht') );
		}

		if( !$password || !$password2 ) {
			self::ctsignup_errors()->add('empty_password', __('Wachtwoord is verplicht') );
		}

		if( strlen( $password ) < 5 ) {
			self::ctsignup_errors()->add('short_password', __('Wachtwoord moet minimaal 5 characters bevatten') );
		}

		if( $password != $password2 ) {
			self::ctsignup_errors()->add('no_match_password', __('Wachtwoorden komen niet overeen') );
		}

		if( empty( self::ctsignup_errors()->get_error_messages() ) ) {
 			if ( CalculatieTool::api_external_signup( compact( 'first_name', 'last_name', 'company', 'account', 'email', 'password' ) ) ) {
				wp_redirect( $redirect ); exit;
			}
		}
	}

	/**
	 * Handle the form POST request from the frontend. Perform
	 * basic validation to ease the service and gain faster feedback. Then
	 * send the form per mail.
	 */
	public static function process_mail_form() {
		$first_name    = sanitize_text_field( $_POST["ctsignup_user_first"] );
		$last_name     = sanitize_text_field( $_POST["ctsignup_user_last"] );
		$email         = sanitize_email( $_POST["ctsignup_user_email"] );
		$phone         = sanitize_text_field( $_POST["ctsignup_user_phone"] );
		$redirect      = sanitize_text_field( $_POST["mail_redirect"] );

		if ( ! $first_name || ! $last_name ) {
			self::ctsignup_errors()->add('empty_names', __('Voor en achternaam zijn verplicht') );
		}

		if( ! $email ) {
			self::ctsignup_errors()->add('empty_email', __('Email is verplicht') );
		}

		if( ! $phone ) {
			self::ctsignup_errors()->add('empty_email', __('Telefoonnummer is verplicht') );
		}

		$mail_content = "New user: ";

		if( empty( self::ctsignup_errors()->get_error_messages() ) ) {
			if ( wp_mail( get_bloginfo( 'admin_email' ), 'Nieuwe', $mail_content ) ) {
				wp_redirect( $redirect ); exit;
			}
		}
	}

	/**
	 * Catch the incomming request, and send it to the
	 * designated callback.
	 */
	public static function helper() {
		if ( isset( $_POST['signup_form_save'] ) ) {
			self::process_signup_form();
		}

		if ( isset( $_POST['mail_form_save'] ) ) {
			self::process_mail_form();
		}

		if ( isset( $_GET['verify'] ) && is_admin() ) {

			if ( self::api_external_verification() ) {
				add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_verify_ok') );
			} else {
				add_action( 'admin_notices',  array( 'CalculatieTool', 'ctsignup_admin_verify_error') );
			}	
		}
	}

	/**
	 * Return the access token if already present. If not
	 * request the token via the client settings. The token
	 * is stored in the application cache while it is valid.
	 *
	 * @return string Return the access token or false on failure.
	 */
	public static function get_access_token() {
		$access_token = get_transient( 'ctsignup_access_token' );

		if( false === $access_token ) {
			$body = array(
				'client_id' => self::get_client_id(),
				'client_secret' => self::get_client_secret(),
				'redirect_uri' => get_site_url(),
				'grant_type' => 'client_credentials',
			);

			$response = self::http_post( $body , self::get_token_url() );

			if ( !$response ) {
				CalculatieTool::log( 'Service returned empty response' );
				
				return false;
			}

			if ( property_exists( $response, 'error' ) ) {
				CalculatieTool::log( compact( 'response' ) );

				return false;
			}

			set_transient( 'ctsignup_access_token', $response->access_token, $response->expires_in );

			$access_token = $response->access_token;
		}

		return $access_token;
	}

	/**
	 * Verify the connection, keys and service.
	 *
	 * @return bool True on success, false on failure.
	 */
	public static function api_external_verification() {
		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return false;
		}

		$response = self::http_get( self::build_api_url( '/oauth2/rest/internal/verify' ), $access_token );
		if ( ! $response ) {
			CalculatieTool::log( 'Service returned empty response' );
			
			return false;
		}

		if ( 0 === $response->success ) {
			return false;
		}

		return true;
	}

	/**
	 * Send the signup request.
	 *
	 * @param array $data User data.
	 * @return bool True on success, false on failure.
	 */
	public static function api_external_signup( $data ) {
		$access_token = self::get_access_token();
		if ( ! $access_token ) {
			return false;
		}

		$response = self::http_post( $data, self::build_api_url( '/oauth2/rest/internal/user_signup' ), $access_token );
		if ( ! $response ) {
			CalculatieTool::log( 'Service returned empty response' );
			
			return false;
		}

		if ( property_exists( $response, 'error' ) ) {
			print_r($response); exit;
		}

		if ( 0 === $response->success ) {
			foreach ( $response->errors as $error ) {
				self::ctsignup_errors()->add( 'backend_error', $error );
			}

			return false;
		}

		return true;
	}

	/**
	 * Log debugging info to the error log.
	 *
	 * Enabled when WP_DEBUG_LOG is enabled (and WP_DEBUG, since according to
	 * core, "WP_DEBUG_DISPLAY and WP_DEBUG_LOG perform no function unless
	 * WP_DEBUG is true), but can be disabled via the akismet_debug_log filter.
	 *
	 * @param mixed $message The data to log.
	 */
	public static function log( $message ) {
		error_log(  'CTSignup: an error occured: ' . print_r( compact( 'message' ), true ) );
	}

	/**
	 * Make a POST request to the service.
	 *
	 * @param array $request Data to send to the service.
	 * @param string $url The URL for the request.
	 * @param string $token Access token to identify the client.
	 * @return object Resulting object, empty on failure.
	 */
	public static function http_post( $request, $url, $token = null ) {
		$request_ua = sprintf( 'WordPress/%s | CTSignup/%s', $GLOBALS['wp_version'], constant( 'CTSINGUP_VERSION' ) );

		$http_args = array(
			'body' => $request,
			'headers' => array(
				'User-Agent' => $request_ua,
			),
			'timeout' => 15
		);

		if ( isset( $token ) ) {
			$http_args[ 'headers' ][ 'Authorization' ] = "Bearer " . $token;
		}

		$response = wp_remote_post( $url, $http_args );
		if ( is_wp_error( $response ) ) {
			do_action( 'http_request_failure', $response );

			CalculatieTool::log( compact( 'url', 'http_args', 'response' ) );

			return;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

	/**
	 * Make a GET request to the service.
	 *
	 * @param string $url The URL for the request.
	 * @param string $token Access token to identify the client.
	 * @return object Resulting object, empty on failure.
	 */
	public static function http_get( $url, $token = null ) {
		$request_ua = sprintf( 'WordPress/%s | CTSignup/%s', $GLOBALS['wp_version'], constant( 'CTSINGUP_VERSION' ) );

		$http_args = array(
			'headers' => array(
				'User-Agent' => $request_ua,
			),
			'timeout' => 15
		);

		if ( isset( $token ) ) {
			$http_args[ 'headers' ][ 'Authorization' ] = "Bearer " . $token;
		}

		$response = wp_remote_get( $url, $http_args );
		if ( is_wp_error( $response ) ) {
			do_action( 'http_request_failure', $response );

			CalculatieTool::log( compact( 'url', 'http_args', 'response' ) );

			return;
		}

		return json_decode(wp_remote_retrieve_body($response));
	}

}
