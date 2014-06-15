{extends file='base.tpl'}
{block name='content'}
<div class="row">	
	<div class="col-md-4">
		
		<h2>Edit study month</h2>	
		{$edit_form}

		<hr>
		{$copy_form}
	</div>
	<div class="col-md-8">
	<h2>Courses</h2>
		<a href="{base_url()}home/add/course?course_month={$course_month->id}&referer={current_url()}" class="btn add-new-btn pull-left btn-large btn-success">
		Add new course to this month
		</a>
		<table class="table model-table table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>Name</th>
					<th>Number of students</th>
					<th>Status</th>
					<th class="align-right">Action</th>
				</tr>
			</thead>

			<tbody>
			
			{foreach from=$course_month->relation('course') item=course}
				<tr class="students-count-{$course->count_students()}">
					<td>
						{$course->id}
					</td>
					<td>
						{$course->get_unicode()}
					</td>
					<td class="align-center">
						{$course->count_students()}
					</td>
					<td>
						{$course->status|ucfirst}
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
					  		<a href="{base_url()}home/course_detail/{$course->id}?referer={current_url()}">
							Detail
						</a>
					  	</li>
					    <li>
					    	<a href="{base_url()}home/add/student_course?course={$course->id}&referer={current_url()}">
					    		Add students to course
					    	</a>
					    </li>		
					    <li class="divider"></li>	
					    <li>
					    	<a class="confirm" href="{base_url()}home/delete/{get_class($course)|strtolower}/{$course->id}?referer={current_url()}">
					    		Delete
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