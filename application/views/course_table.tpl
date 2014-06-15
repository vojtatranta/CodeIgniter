{extends file='base.tpl'}
{block name='content'}
	
	<a href="{base_url()}home/add/{$model_listing}" class="btn add-new-btn pull-left btn-large btn-success">{t('add_new', 'admin')}</a>
	


	<table class="table model-table courses-table table-striped">

		<thead>
			<tr>
				<th>Title</th>
				<th>Study month</th>
				<th>Course type</th>
				<th>Lector</th>
				<th>Place</th>
				<th>Classroom</th>
				<th>Textbook</th>
				<th>Status</th>
				<th>Course times</th>
				<th>Actions</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$model['instances'] item=course}
			<tr data-row-id="{$course->id}" class="state students-count-{$course->count_students()}">
				<td>{$course->title}</td>
				<td>
					{if ( $month = $course->relation('course_month'))}
						
						<a href="{base_url()}home/course_month_detail/{$month->id}">
							{$month->get_unicode()}
						</a>
						
					{/if}
				</td>
				<td>
					{if ( $type = $course->relation('course_type'))}
						
						<a href="{base_url()}home/edit/course_type/{$type->id}">
							{$type->get_unicode()}
						</a>
						
					{/if}
				</td>
				<td>
					{if ( $lector = $course->relation('lector'))}
						<a href="{base_url()}home/edit/lector/{$lector->id}">
							{$lector->get_unicode()}
						</a>
						
					{/if}
				</td>
				<td>{$course->place}</td>
				<td>
					{if ( $classroom = $course->relation('classroom'))}
						
						<a href="{base_url()}home/edit/classroom/{$classroom->id}">
							{$classroom->get_unicode()}
						</a>
						
					{/if}
				</td>
				
				<td>
					{if ( $textbook = $course->relation('textbook'))}
						<a href="{base_url()}home/edit/textbook/{$textbook->id}">
							{$textbook->get_unicode()}
						</a>
						
					{/if}
				</td>
				<td>
					{$course->status}
				</td>
				<td>
					{if ($times = $course->relation('course_time', 4))}
					<table class="table no-borders">
						<tbody>
						{foreach from=$times item=time}
							<tr>
								<td>
									<a href="{base_url()}home/edit/time/{$time->id}">
										{$time->get_unicode()}
									</a>
								</td>
							</tr>
						{/foreach}
						</tbody>
					</table>
					{/if}
				</td>
				<td class="align-right">
					<div class="btn-group">
						<a class="btn btn-success" href="{base_url()}home/course_detail/{$course->id}">
							Detail
						</a>
					  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
	
					    <span class="caret"></span>
					     <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu" role="menu">
						<li>
						  	<a href="{base_url()}home/course_detail/{$course->id}">
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