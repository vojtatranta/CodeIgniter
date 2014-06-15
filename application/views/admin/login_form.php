<div class="row">
		
	<form action="<?= base_url() ?>auth/process_login" method="post" class="form-signin">
		<input type="text" name="email" class="email" placeholder="<?= t('email') ?>">
		<input type="password" name="password" placeholder="<?= t('password') ?>">
		<input type="submit" class="btn btn-large btn-success" value="<?= t('login') ?>" type="submit">
	</form>				
	
</div>
