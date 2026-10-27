<div class="compact-filter-bar admin-panel">
	<form method="get" action="{$adminUrl}cancellations" id="admCancelFilterForm" class="compact-filter-form">
		<select id="cn_status" name="status" class="compact-filter-select" aria-label="{'Status'|adminT}">
			<option value="0"{if $statusFilter == 0} selected{/if}>{'Status'|adminT}: {'All'|adminT}</option>
			{foreach $statusOptions as $statusId => $statusLabel}<option value="{$statusId}"{if $statusFilter == $statusId} selected{/if}>{'Status'|adminT}: {$statusLabel|escape}</option>{/foreach}
		</select>
		<button type="submit" class="compact-filter-submit">{'Filter'|adminT}</button>
		<a href="{$adminUrl}cancellations" class="compact-filter-clear">{'Clear'|adminT}</a>
		<span class="compact-filter-spacer"></span>
	</form>
</div>
{include file='admin/partials/compact-filter-styles.tpl'}

<div class="ps-panel">
	<div class="ps-panel__head ps-panel__head--split">
		<h2>{'Cancellations'|adminT} {if $cancellationsTotal > 0}<span class="ps-panel__count">({$cancellationsTotal})</span>{/if}</h2>
	</div>
	<div class="ps-panel__body p-0">
		{if $cancellations|@count}
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
					{foreach $cancellations as $c}
					<tr>
						<td>{$c.id_cancel}</td>
						<td>
							<a href="{$adminUrl}order?id={$c.id_order}">#{$c.reference|escape}</a>
							<div class="small text-muted">{$c.total_formatted}</div>
						</td>
						<td>
							<div>{$c.customer_name|escape}</div>
							<div class="small text-muted">{$c.customer_phone|escape}</div>
						</td>
						<td>{$c.date_formatted}</td>
						<td><span class="badge {$c.status_badge}">{$c.status_label|escape}</span></td>
						<td class="text-end">
							<a href="{$adminUrl}cancel?id={$c.id_cancel}" class="btn btn-sm btn-outline-dark">{'Detail'|adminT}</a>
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
