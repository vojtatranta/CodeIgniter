{extends file='base.tpl'}
{block name='content'}
	<table class="table table-striped">

		<thead>
			<tr>
			{foreach from=$courses['columns'] item=column}
				<th>{t($column, 'admin')}</th>
			{/foreach}
				<th>{t('actions', 'admin')}</th>
			</tr>
		</thead>
		<tbody>
		{foreach from=$courses['instances'] item=course}
			<tr data-row-id="{$course->id}">
				{foreach from=$courses['columns'] item=column}
					{$val = $course->$column}
					<td class="column-for-{$column}">
					{if ( $type = $this->basemodel->is_class_or_type($course, $column) )}
						{$instance = $course->relation($type)}
						{if ( is_array($instance) )}
							{foreach from=$instance item=inst}
								<a href="home/edit/{$type}/{$inst->id}">{$inst->get_unicode()|ucfirst}, </a>
							{/foreach}
						{else}
							<a href="home/edit/{$type}/{$instance->id}">{$instance->get_unicode()}</a>
						{/if}
					{else}
						{$val}
					{/if}
					</td>
				{/foreach}
					<td></td>
			</tr>
		{/foreach}
		</tbody>
	</table>

{/block}