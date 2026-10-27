<div class="compact-filter-bar admin-panel">
	<form method="get" action="{$adminUrl}returns" id="admReturnsFilterForm" class="compact-filter-form">
		<select id="rt_status" name="status" class="compact-filter-select" aria-label="{'Status'|adminT}">
			<option value="0"{if $statusFilter == 0} selected{/if}>{'Status'|adminT}: {'All'|adminT}</option>
			{foreach $statusOptions as $statusId => $statusLabel}<option value="{$statusId}"{if $statusFilter == $statusId} selected{/if}>{'Status'|adminT}: {$statusLabel|escape}</option>{/foreach}
		</select>
		<button type="submit" class="compact-filter-submit">{'Filter'|adminT}</button>
		<a href="{$adminUrl}returns" class="compact-filter-clear">{'Clear'|adminT}</a>
		<span class="compact-filter-spacer"></span>
	</form>
</div>
{include file='admin/partials/compact-filter-styles.tpl'}

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>{'Returns'|adminT} {if $returnsTotal > 0}<span class="ps-panel__count">({$returnsTotal})</span>{/if}</h2>
	</div>
	<div class="ps-panel__body p-0">
		{if $returns|@count}
		<div class="table-responsive">
			<table class="table table-hover mb-0">
				<thead>
					<tr>
						<th>#</th>
						<th>{'Order'|adminT}</th>
						<th>{'Customer'|adminT}</th>
						<th>{'Date'|adminT}</th>
						<th>{'Status'|adminT}</th>
						<th></th>
					</tr>
				</thead>
				<tbody>
					{foreach $returns as $r}
					<tr>
						<td>{$r.id_return}</td>
						<td>
							<a href="{$adminUrl}order?id={$r.id_order}">#{$r.reference|escape}</a>
							<div class="small text-muted">{$r.total_formatted}</div>
						</td>
						<td>
							<div>{$r.customer_name|escape}</div>
							<div class="small text-muted">{$r.customer_phone|escape}</div>
						</td>
						<td>{$r.date_formatted}</td>
						<td><span class="badge {$r.status_badge}">{$r.status_label|escape}</span></td>
						<td class="text-end">
							<a href="{$adminUrl}return?id={$r.id_return}" class="btn btn-sm btn-outline-dark">{'Detail'|adminT}</a>
						</td>
					</tr>
					{/foreach}
				</tbody>
			</table>
		</div>
		{else}
		<p class="text-muted p-4 mb-0">{'No records found.'|adminT}</p>
		{/if}
	</div>
</div>

{if $pagination.total_pages > 1}
<div class="ps-pagination-wrap mt-3">
	{include file='admin/plugin/pagination.tpl'}
</div>
{/if}
