{extends file='base.tpl'}
{block name='content'}
	
	<a href="{base_url()}home/add/{$model_listing}" class="btn add-new-btn pull-left btn-large btn-success">{t('add_new', 'admin')}</a>



	<table class="table course-month-table table-striped">

		<thead>
			<tr>
			{foreach from=$model['columns'] item=column}
				<th>{t($column, 'admin')}</th>
			{/foreach}
				<th>Courses</th>
				<th class="align-right">{t('actions', 'admin')}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$model['instances'] item=course_month}
			<tr data-row-id="{$course_month->id}" class="state">
				<td>{$course_month->id}</td>
				<td>
				{if ( $opts = $this->basemodel->is_select_with_options($course_month, 'month'))}
					{if (isset($opts[$course_month->month]))}
						{$opts[$course_month->month]}
					{/if}
				{/if}
				</td>
				<td>
					{$course_month->year}
				</td>
				<td>
					<table class="table table-striped no-borders">
						<tbody>
						{foreach from=$course_month->relation('course') item=course}
							<tr>
								<td>
									{if ($course)}
										<a href="{base_url()}home/course_detail/{$course->id}">
											{$course->get_unicode()}
										</a>	
									{/if}
								</td>
							</tr>
						{/foreach}
						</tbody>	
					</table>
				</td>
				<td class="align-right">
					<div class="btn-group">
						<a class="btn btn-success" href="{base_url()}home/course_month_detail/{$course_month->id}?referer={current_url()}">
							Detail
						</a>
					  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
	
					    <span class="caret"></span>
					     <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu" role="menu">
					    <li>
					    	<a href="{base_url()}home/course_month_detail/{$course_month->id}?referer={current_url()}">Detail</a>
					    </li>
					    <li>
					    	<a href="{base_url()}home/add/course?course_month={$course_month->id}&referer={current_url()}">Add courses to this month</a>
					    </li>
					    <li>
					    	<a href="{base_url()}home/add/course/?course_month={$course_month->id}">
					    		Add course
					    	</a>
					    </li>
						<li class="divider"></li>
					    <li>
					    	<a class="confirm" href="{base_url()}home/delete/{get_class($course_month)|strtolower}/{$course_month->id}">
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