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
