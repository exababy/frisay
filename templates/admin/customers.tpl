<div class="customer-filter-bar admin-panel">
	<form method="get" action="{$adminUrl}customers" id="admCustomersFilterForm" class="customer-filter-form">
		<label class="customer-filter-search" for="cst_q"><svg xmlns="http://www.w3.org/2000/svg" width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true"><circle cx="11" cy="11" r="8"/><path d="m21 21-4.35-4.35"/></svg><input type="search" id="cst_q" name="q" placeholder="{'Search name, phone or email...'|adminT}" value="{$searchQuery|escape}" aria-label="{'Search name, phone or email...'|adminT}"></label>
		<button type="submit" class="btn customer-filter-submit">{'Search'|adminT}</button>
		{if $searchQuery}<a href="{$adminUrl}customers" class="customer-filter-clear">Temizle</a>{/if}
		<span class="customer-filter-spacer"></span>
		<button type="button" class="btn btn-primary customer-filter-add" data-bs-toggle="modal" data-bs-target="#createCustomerModal">{'Add customer'|adminT}</button>
	</form>
</div>
<style>
{literal}
.customer-filter-bar{padding:14px 16px;border:1px solid #e2e5df;border-radius:15px;margin-bottom:14px}.customer-filter-form{display:flex;align-items:center;gap:10px;width:100%}.customer-filter-search{height:44px;flex:1 1 360px;min-width:220px;display:flex;align-items:center;gap:10px;padding:0 14px;border:1px solid #aeb5ad;border-radius:11px;color:#626b78}.customer-filter-search input{width:100%;min-width:0;border:0;outline:0;background:transparent;font-size:14px}.customer-filter-search:focus-within{border-color:#287b55;box-shadow:0 0 0 3px rgba(40,123,85,.12)}.customer-filter-search input::placeholder{color:#788292}.customer-filter-submit{height:44px;padding:0 18px;border:1px solid #c5cbc2;border-radius:11px;background:#fff;color:#152235;font-weight:600}.customer-filter-submit:hover{background:#f5f7f4;border-color:#98a197}.customer-filter-clear{padding:0 4px;color:#697386;font-size:13px;text-decoration:none}.customer-filter-clear:hover{color:#182230;text-decoration:underline}.customer-filter-spacer{flex:1}.customer-filter-add{height:44px;display:inline-flex;align-items:center;justify-content:center;padding:0 18px;border-radius:11px;white-space:nowrap}@media(max-width:700px){.customer-filter-form{flex-wrap:wrap}.customer-filter-search{flex:1 1 100%}.customer-filter-spacer{display:none}.customer-filter-add{margin-left:auto}}@media(max-width:560px){.customer-filter-submit{flex:1}.customer-filter-clear{order:5}.customer-filter-add{order:6;width:100%}}
{/literal}
</style>

{if $customerFlash}
	<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index:9999;">
		<div class="toast frisay-toast {$customerFlashType|default:'dark'} show" role="alert">
			<div class="toast-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>
			</div>
			<div class="toast-body p-0">
				<div class="toast-message">
					{$customerFlash|escape}
				</div>
			</div>
			<button class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
		</div>
	</div>
{/if}

<div class="admin-panel p-0">
	<div class="table-responsive">
		<table class="table table-sm align-middle mb-0">
			<thead>
				<tr>
					<th>ID</th>
					<th>{'Full name'|adminT}</th>
					<th>{'Phone'|adminT}</th>
					<th>{'Email'|adminT}</th>
					<th>{'Order'|adminT}</th>
					<th>{'Total'|adminT}</th>
					<th>{'Registered'|adminT}</th>
					<th>{'Status'|adminT}</th>
					<th></th>
				</tr>
			</thead>
			<tbody>
				{if $customers|@count}
				{foreach $customers as $row}
				<tr>
					<td>{$row.id_user}</td>
					<td>{$row.user_full_name|escape}</td>
					<td>{$row.phone|escape}</td>
					<td>{if $row.email}{$row.email|escape}{else}<span class="text-muted">—</span>{/if}</td>
					<td>{$row.order_count}</td>
					<td>{$row.order_total_formatted}</td>
					<td>{$row.date_formatted}</td>
					<td>{if $row.active}{'Active'|adminT}{else}<span class="text-danger">{'Inactive'|adminT}</span>{/if}</td>
					<td class="text-end"><a href="{$adminUrl}customer?id={$row.id_user}" class="btn btn-sm btn-outline-dark">{'Detail'|adminT}</a></td>
				</tr>
				{/foreach}
				{else}
				<tr><td colspan="9" class="text-muted">{'No customers found.'|adminT}</td></tr>
				{/if}
			</tbody>
		</table>
	</div>
</div>

{include file='admin/plugin/pagination.tpl'}

<div class="modal fade" id="createCustomerModal" tabindex="-1" aria-labelledby="createCustomerModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post" action="{$adminUrl}customers">
				<input type="hidden" name="token" value="{$adminToken|escape}">
				<input type="hidden" name="createCustomer" value="1">
				<div class="modal-header">
					<h5 class="modal-title" id="createCustomerModalLabel">{'Add customer'|adminT}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
				</div>
				<div class="modal-body">
					<div class="mb-3">
						<label class="form-label" for="new_customer_name">{'Full name'|adminT}</label>
						<input type="text" class="form-control" id="new_customer_name" name="user_full_name" required maxlength="128" autocomplete="off">
					</div>
					<div class="mb-3">
						<label class="form-label" for="new_customer_phone">{'Phone'|adminT}</label>
						<input type="text" class="form-control" id="new_customer_phone" name="phone" required placeholder="{'e.g. +90 532… or +1 202…'|adminT}" autocomplete="off">
					</div>
					<div class="mb-0">
						<label class="form-label" for="new_customer_email">{'Email'|adminT}</label>
						<input type="email" class="form-control" id="new_customer_email" name="email" maxlength="128" autocomplete="off">
						<div class="form-text">{'Optional. A random password is generated automatically.'|adminT}</div>
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
					<button type="submit" class="btn btn-primary btn-sm">{'Create customer'|adminT}</button>
				</div>
			</form>
		</div>
	</div>
</div>
