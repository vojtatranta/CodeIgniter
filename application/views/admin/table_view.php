<?php $this->load->view('admin/common_header'); ?>
	<div id="content">
		<div class="row">
			<div class="spap12">
			<?= a('admin/add/'.$model_name, t('add_'.$model_name), array('class' => 'add-button block button'))  ?>
				<table id="instances-table" class="table-full-width">
					<thead>
						<tr>
							<?php foreach ($columns as $k => $column_name): ?>
								<th><?= t($column_name) ?></th>
							<?php endforeach ?>
							<th><?= t('edit') ?></th>
							<th><?= t('delete_entity') ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($content as $k => $instance): ?>
						<tr>
							<?php foreach ($columns as $k => $column_name): ?>
									<?php //prer($instance) ?>
								<?php if ( class_exists($column_name) ): ?>
									<td><?= a('admin/edit/'.$column_name.'/'.$instance->$column_name, filter_out($instance->relation($column_name)->get_unicode()) ) ?></td>
								<?php else: ?>
									<td><?= filter_out($instance->$column_name) ?></td>
								<?php endif; ?>
							<?php endforeach ?>
							<td><?= a('admin/edit/'.$model_name.'/'.$instance->id, t('edit'))  ?></td>
							<td><?= a('admin/delete/'.$model_name.'/'.$instance->id, t('delete_entity'))  ?></td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			</div>
		</div>
	</div><!--/#content-->

		<div id="footer">
			<div class="footer-inner">
			</div>
		</div>
	</div><!--/#container-->
	
<?php $this->load->view('admin/common_footer'); ?>