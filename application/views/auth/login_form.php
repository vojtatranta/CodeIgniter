<div class="login-box">
	<div class="login-box-inner">
		<?php if ( !$this->appuser->is_logged_in() ): ?>
			<form action="<?= base_url() ?>auth/process_login" class="login-form-box" method="POST">
				<div class="login-left float">
					<div class="input-wrap">
						<input type="text" placeholder="<?= t('email') ?>" name="email">
					</div>
				</div><!--/.login-left-->
				<div class="login-right float">
					<div class="input-wrap">
						<input type="password" placeholder="<?= t('password') ?>" name="password">
						<input type="hidden" name="back_uri" value="<?= base_url().$this->uri->uri_string() ?>">
					</div>
				</div><!--/.login-right-->
				<div class="login-texts">
					<?= a('auth/register', t('register'), array('class' => 'register-link login-box-links')) ?>&nbsp;
					<?= a('auth/forgot', t('forgotten_password'), array('class' => 'login-box-links')) ?>
					<input type="submit" class="btn btn-primary login-submit" value="<?= t('login') ?>">
				</div>
			</form>
		<?php else: ?>
			<?= a('profile', t('my_profile'), 'btn btn-profile profile-link btn-success') ?>
			<?= a('auth/logout', t('logout'), 'btn btn-danger btn-logout'); ?>
		<?php endif; ?>
	</div>
</div><!--/.login-box-->