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
			<label for="ctsignup_user_account"><?php _e('Gebruikersnaam (verplicht)'); ?></label>
			<input name="ctsignup_user_account" id="ctsignup_user_account" class="required" type="text" value="<?php isset($_POST["ctsignup_user_account"]) ? _e($_POST["ctsignup_user_account"]) : null ?>" data-sanitize="trim lower" data-validation="server" data-validation-url="<?php _e(add_query_arg( 'usercheck', true )); ?>"/>
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
