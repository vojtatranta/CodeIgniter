{extends file='base.tpl'}
{block name='content'}
<div class="row">	
	<div class="col-md-4">
		<div class="btn-group">
			<a href="{base_url()}home/add/{$model_listing}" class="btn add-new-btn right-large btn-success">{t('add_new', 'admin')}</a>
		  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">

		    <span class="caret"></span>
		     <span class="sr-only">Toggle Dropdown</span>
		  </button>
		  <ul class="dropdown-menu" role="menu">

		    <li>
		    	<a class="confirm" href="{base_url()}home/delete/{get_class($course_type)|strtolower}/{$course_type->id}">
		    		{t('delete', 'admin')}
		    	</a>
		    </li>
		  </ul>
		</div>
		
		{$edit_form}
	</div>
	<div class="col-md-8">
	<h2>Kurzy</h2>
		<table class="table model-table table-striped">
			<thead>
				<tr>
					<th>ID</th>
					<th>Název</th>
					<th>Lektor</th>
					<th class="align-right">{t('actions', 'admin')}</th>
				</tr>
			</thead>

			<tbody>
			{foreach from=$course_type->relation('course') item=course}
				<tr>
					<td>
						{$course->id}
					</td>
					<td>
						{$course->get_unicode()}
					</td>
					<td>
						{if ($lector = $course->relation('lector'))}{$lector->get_unicode()}{/if}
					</td>
					<td class="align-right">
					<div class="btn-group">
						<a class="btn btn-success" href="{base_url()}home/edit/{get_class($course)|strtolower}/{$course->id}">
							{t('edit', 'admin')}
						</a>
					  <button type="button" class="btn btn-success dropdown-toggle" data-toggle="dropdown">
	
					    <span class="caret"></span>
					     <span class="sr-only">Toggle Dropdown</span>
					  </button>
					  <ul class="dropdown-menu" role="menu">
			
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
	</div>
</div>
{/block}