{extends file='base.tpl'}
{block name='content'}
<form action="{base_url()}home/action/{$model_listing}" method="post">	
	<a href="{base_url()}home/add/{$model_listing}" class="btn add-new-btn pull-left btn-large btn-success">{t('add_new', 'admin')}</a>
	<div class="btn-group in-row">
		<span class="btn btn-danger">{t('selected_types_actions')}</span>
	  <button type="button" class="btn btn-danger dropdown-toggle" data-toggle="dropdown">

	    <span class="caret"></span>
	     <span class="sr-only">Toggle Dropdown</span>
	  </button>
	  <ul class="dropdown-menu" role="menu">
	  	<li>
	  		<a href="#"><input type="submit" class="as-link" name="copy_to_type" value="Zkopírovat do nového měsíce"></a>
	    </li>
	  </ul>
	</div>
	<table class="table model-table table-striped">

		<thead>
			<tr>
				<th class="align-center">
					<input type="checkbox" class="check-all">
				</th>
			{foreach from=$model['columns'] item=column}
				<th>{t($column, 'admin')}</th>
			{/foreach}
				<th class="align-center">Kurzy</th>
				<th class="align-right">{t('actions', 'admin')}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$model['instances'] item=course_type}
			<tr data-row-id="{$course_type->id}" class="state">
					<td class="align-center">
						<input type="checkbox" name="type[]" value="{$course_type->id}">
						
					</td>
					<td>
						{$course_type->id}
					</td>
					<td>
						{$course_type->title}
					</td>
					<td>
					{if ( $month = $course_type->relation('course_month'))}
						
						{$month->get_unicode()}
						
					{/if}
					</td>
					<td>
						{$course_type->from|date_format:'d.m.Y'}
					</td>
					<td>
						{$course_type->to|date_format:'d.m.Y'}
					</td>
					<td>
					
						<table class="table table-striped no-borders">
							<tbody>
								{foreach from=$course_type->relation('course') item=course}
									{if $course}
										<tr>
											<td>
												<a href="{base_url()}home/edit/course/{$course->id}">
													{$course->get_unicode()}
												</a>	
											</td>
										</tr>
									{/if}
								{/foreach}
							</tbody>	
						</table>
						
					</td>
					<td class="align-right">
						<div class="btn-group">
							<a class="btn btn-success" href="{base_url()}home/course_type_detail/{$course_type->id}">
								Detail
							</a>
						  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
		
						    <span class="caret"></span>
						     <span class="sr-only">Toggle Dropdown</span>
						  </button>
						  <ul class="dropdown-menu" role="menu">
						  	<li>
						  		<a href="{base_url()}home/edit/{get_class($course_type)|strtolower}/{$course_type->id}">
									{t('edit', 'admin')}
								</a>
						  	</li>
						  	<li class="divider"></li>
						    <li>
						    	<a class="confirm" href="{base_url()}home/delete/{get_class($course_type)|strtolower}/{$course_type->id}">
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
</form>
	<div class="pages align-center">
		<ul class="pagination">
		{foreach from=$pages key=ind item=page_num}
			<li class="{if ($ind == $page)}active{/if} page">
				<a href="{base_url()}home/listing/{$model_listing}/{$ind}">{$page_num}</a>
			</li>
		{/foreach}
		</ul>
	</div>

{/block}