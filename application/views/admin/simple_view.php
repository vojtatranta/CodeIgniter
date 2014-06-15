<?php $this->load->view('admin/common_header'); ?>
	<div id="content">
		<div class="row">
			<?php if (is_array($content)): ?>
				<?php foreach ($content as $k => $item): ?>
					<div class="item-print">
						<?= filter_out($item) ?>
					</div>
				<?php endforeach ?>
			<?php else: ?>
				<?= $content ?>
			<?php endif; ?>
		</div>
	</div><!--/#content-->
		<div id="footer">
			<div class="footer-inner">
			</div>
		</div>
	</div><!--/#container-->
<?php $this->load->view('admin/common_footer'); ?>