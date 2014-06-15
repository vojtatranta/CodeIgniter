{extends file='base.tpl'}
{block name='content'}
	
	<a href="{base_url()}home/add/{$model_listing}" class="btn add-new-btn pull-left btn-large btn-success">{t('add_new', 'admin')}</a>

	<table class="table model-table table-striped">

		<thead>
			<tr>
			{foreach from=$model['columns'] item=column}
				<th>{t($column, 'admin')}</th>
			{/foreach}
				<th class="align-right">{t('actions', 'admin')}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$model['instances'] item=course}
			<tr data-row-id="{$course->id}">
				{foreach from=$model['columns'] item=column}
					{$val = $course->$column}
					<td class="column-for-{$column}">
					{if ( $type = $this->basemodel->is_class_or_type($course, $column) )}
						{$instance = $course->relation($type)}
						{if ( is_array($instance) )}
							{foreach from=$instance item=inst}
								<a href="{base_url()}home/edit/{$type}/{$inst->id}">{$inst->get_unicode()|ucfirst}, </a>
							{/foreach}
						{elseif ($instance)}

							<a href="{base_url()}home/edit/{$type}/{$instance->id}">{$instance->get_unicode()}</a>
						{/if}
					{elseif ( $opts = $this->basemodel->is_select_with_options($course, $column))}
						{if (isset($opts[$val]))}
							{$opts[$val]}
						{/if}
					{else}
						{$val}
					{/if}
					</td>
				{/foreach}
					<td class="align-right">
						<div class="btn-group">
							<a class="btn btn-success" href="{base_url()}home/edit/{get_class($course)|strtolower}/{$course->id}">
								Detail
							</a>
						  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
		
						    <span class="caret"></span>
						     <span class="sr-only">Toggle Dropdown</span>
						  </button>
						  <ul class="dropdown-menu" role="menu">
						  	<li>
						  		<a href="{base_url()}home/edit/{get_class($course)|strtolower}/{$course->id}">
									Detail
								</a>
						  	</li>
						 	<li class="divider"></li>
						    <li>
						    	<a class="confirm" href="{base_url()}home/delete/{get_class($course)|strtolower}/{$course->id}">
						    		{t('delete', 'admin')}
						    	</a>
						    </li>
						  </ul>
						</div>
					</td>
			</tr>
		{/foreach}
		</tbody>
	</table>
	<!--<div class="pages align-center">
		<ul class="pagination">
		{foreach from=$pages key=ind item=page_num}
			<li class="{if ($ind == $page)}active{/if} page">
				<a href="{base_url()}home/listing/{$model_listing}/{$ind}">{$page_num}</a>
			</li>
		{/foreach}
		</ul>
	</div>-->

{/block}