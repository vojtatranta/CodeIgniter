<?php $this->load->view('admin/common_header'); ?>
	
	<div id="content">
		<div class="row">
			<div class="span12">
				
			<?php foreach ($models as $model_name => $model): ?>
			<h3><?= a( 'admin/list_records/'.$model_name , t('list_'.$model_name, 'admin')) ?></h3>
			
					<?= a('admin/add/'.$model_name, t('add_'.$model_name, 'admin'), array('class' => 'add-button block button'))  ?>
				
				<table id="instances-table" class="table table-striped table-full-width" >
					<thead>
						<tr>
							<?php foreach ($model['fields'] as $k => $column_name): ?>
								<th><?= t($column_name, 'admin') ?></th>
							<?php endforeach ?>
								<th><?= t('actions', 'admin') ?></th>
						</tr>
					</thead>
					<tbody>
					<?php foreach ($model['instances'] as $k => $instance): ?>
						<tr>
							<?php foreach ($model['fields'] as $k => $column_name): ?>
									<?php //prer($instance) ?>
								<?php if ( $this->config->item('show_model_links') AND $type = $this->basemodel->is_class_or_type($instance, $column_name) ): ?>
									<td><?= a('admin/edit/'.$instance->get_type_of($column_name).'/'.$instance->$column_name, $instance->relation($column_name)->get_unicode() ) ?></td>
								<?php elseif (!is_array($instance->$column_name)): ?>
									<?php if ( $options = $instance->is_select($column_name) ): ?>
										<td><?= filter_out($options[$instance->$column_name]) ?></td>
									<?php else: ?>
										<td><?= filter_out($instance->$column_name) ?></td>
									<?php endif; ?>
								<?php endif; ?>
							<?php endforeach ?>
							<td class="align-right">
								<div class="btn-group">
								<?= a('admin/edit/'.$model_name.'/'.$instance->id, t('edit', 'admin'), 'btn btn-success')  ?>
								  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
				
								    <span class="caret"></span>
								     <span class="sr-only">Toggle Dropdown</span>
								  </button>
								  <ul class="dropdown-menu" role="menu">
								    <li><?= a('admin/translate/'.$model_name.'/'.$instance->id, t('translate', 'admin'))  ?></li>
								    <li class="divider"></li>
								    <li><?= a('admin/delete/'.$model_name.'/'.$instance->id, t('delete', 'admin'))  ?></li>
								  </ul>
								</div>
							</td>
						</tr>
					<?php endforeach ?>
					</tbody>
				</table>
			<?php endforeach ?>
			
			</div>
		</div>
	</div><!--/#content-->

		<div id="footer">
			<div class="footer-inner">
			</div>
		</div>
	</div><!--/#container-->
	
<?php $this->load->view('admin/common_footer'); ?>