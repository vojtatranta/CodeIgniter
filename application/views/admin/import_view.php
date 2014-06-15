<?php $this->load->view('admin/common_header'); ?>
	<div id="content">
		<div class="row">
			<form action="<?= base_url() ?>admin/import_ingrediences" method="post">
				<textarea name="import" id="" cols="30" rows="10"></textarea>
				<input type="submit" value="importovat">
			</form>
		</div>
	</div><!--/#content-->
		<div id="footer">
			<div class="footer-inner">
			</div>
		</div>
	</div><!--/#container-->
<?php $this->load->view('admin/common_footer'); ?>