{extends file='base.tpl'}
{block name='content'}
<div class="row">	
	<div class="col-md-4">
		{if (!empty($course->course_month))}
			<a href="{base_url()}home/course_month_detail/{$course->course_month}" class="btn btn-success"><< Na měsíc</a>
		{/if}
		<h2>Edit course</h2>
		
		{$edit_form}

		<hr>
	</div>
	<div class="col-md-8">
	<h2>Applications to course</h2>
		<a href="{base_url()}home/add/student_course?course={$course->id}&referer={current_url()}" class="btn add-new-btn pull-left btn-large btn-success">Add student to course</a>
		<table class="table model-table table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Status</th>
					<th>Tag</th>
					<th class="align-right">{t('actions', 'admin')}</th>
				</tr>
			</thead>

			<tbody>
			{foreach from=$course->relation('student_course') item=conn}
			{$student = $conn->relation('students')}
				<tr class="student-status-{$conn->status}">
					<td>
						{$student->id}
					</td>
					<td>
						{$student->get_unicode()}
					</td>
					<td>
						{if ( $opts = $this->basemodel->is_select_with_options($conn, 'status'))}
							{if (isset($opts[$conn->status]))}
								{$opts[$conn->status]}
							{/if}
						{/if}
			
					</td>
					<td>
						{if ( $opts = $this->basemodel->is_select_with_options($conn, 'tag'))}
							{if (isset($opts[$conn->tag]))}
								{$opts[$conn->tag]}
							{/if}
						{/if}
			
					</td>
					
					<td class="align-right">
					<div class="btn-group">
						<a class="btn btn-success" href="{base_url()}home/edit/{get_class($conn)|strtolower}/{$conn->id}?referer={current_url()}">
							Detail
						</a>
					  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
	
					    <span class="caret"></span>
					     <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu" role="menu">
						<li>
							<a href="{base_url()}home/edit/{get_class($conn)|strtolower}/{$conn->id}?referer={current_url()}">
								Detail
							</a>
						</li>
						<li class="divider"></li>
					    <li>
					    	<a class="confirm" href="{base_url()}home/delete/{get_class($conn)|strtolower}/{$conn->id}?referer={current_url()}">
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
	</div>
</div>
{/block}