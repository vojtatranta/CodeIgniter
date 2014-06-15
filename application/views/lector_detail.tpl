{extends file='base.tpl'}
{block name='content'}
<div class="row">	
	<div class="col-md-4 hidden">
		{if (!empty($lector->course_month))}
			<a href="{base_url()}home/listing/course" class="btn btn-success"><< Na kurzy</a>
		{/if}
		<h2>Edit lector</h2>
		
		{$edit_form}

		<hr>
	</div>
	<div class="col-md-12">
	<h2>Applications to course</h2>
		<a href="{base_url()}home/add/student_course?course={$lector->id}&referer={current_url()}" class="btn add-new-btn pull-left btn-large btn-success">Add student to course</a>
		<table class="table lector-schedule table-bordered table-striped">
			<thead>
				<tr>
					<th class="time-column">Time</th>
				{for $i=1 to 5}
					<th class="align-center">
						{$day_names[($i % 7)]}
						<br>
					</th>
				{/for}
				</tr>
			</thead>
			<tbody>
			{foreach from=$day_times item=time}
				<tr>
					<td class="align-center">{$time}</td>
				{for $i=1 to 5}
					{if (isset($lectors_schedule[($i % 7)][$time]))}
						<td class="day-{$i} time-{$time} {if (date('d') == $i AND date('H:i') == $time )}now{/if} {if (date('d') == $i)}today{/if}">
							<div class="td-inside">
							{foreach from=$lectors_schedule[($i % 7)][$time] key=ind item=course}
								<div class="course course-ind-{$ind} dur-{$course->course_time->get_duration()}" style="height: {$course->course_time->get_duration() * 30 - (45 * $ind)}px; margin: {45 * $ind}px 0 0 {5 * $ind}px; top: -5px ">
									{$course->relation('lector')->name}
									<br>
									{$course->course_time->time} - {$course->course_time->to}&nbsp;({$course->course_time->get_duration() * 5} min)
									<br>
									<a href="{base_url()}home/course_detail/{$course->id}">{$course->title}</a>
									
								</div>
							{/foreach}
							</div>
						</td>
					{else}
						<td class="day-{$i} time-{$time} {if (date('d') == $i AND date('H:i') == $time )}now{/if} {if (date('d') == $i)}today{/if}">
							<div class="td-inside"></div>
						</td>
					{/if}
				{/for}
				</tr>
			{/foreach}
			</tbody>
		</table>
	</div>
</div>
{/block}