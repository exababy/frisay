{if $flash}
	<div class="toast-container position-fixed bottom-0 end-0 p-4" style="z-index:9999;">
		<div class="toast frisay-toast {$flashType|default:'success'} show" role="alert">
			<div class="toast-icon">
				<svg xmlns="http://www.w3.org/2000/svg" width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="lucide lucide-check-icon lucide-check"><path d="M20 6 9 17l-5-5"/></svg>
			</div>
			<div class="toast-body p-0">
				<div class="toast-message">
					{$flash|escape}
				</div>
			</div>
			<button class="btn-close btn-close-white" data-bs-dismiss="toast"></button>
		</div>
	</div>
{/if}
<style>
{literal}
.customer-profile{border:1px solid #e5e9e6;border-radius:16px;background:#fff;padding:24px;margin-bottom:22px;box-shadow:0 5px 18px rgba(25,42,36,.04)}
.customer-profile__top{display:flex;align-items:center;gap:16px;flex-wrap:wrap}.customer-profile__avatar{width:62px;height:62px;border-radius:18px;background:#e8f3ec;color:#287b55;display:grid;place-items:center;font-size:22px;font-weight:700}.customer-profile__identity{flex:1;min-width:210px}.customer-profile__identity h1{font-size:22px;margin:0 0 5px;color:#20352b}.customer-profile__identity p{margin:0;color:#69776e;font-size:13px}.customer-profile__actions{display:flex;gap:8px;flex-wrap:wrap}.customer-profile__stats{display:grid;grid-template-columns:repeat(3,minmax(0,1fr));gap:12px;margin-top:22px}.customer-profile__stat{background:#f7f9f7;border:1px solid #edf0ed;border-radius:12px;padding:13px 16px}.customer-profile__stat small{display:block;color:#748078;margin-bottom:4px}.customer-profile__stat strong{color:#20352b;font-size:17px}.customer-detail-card{border:1px solid #e5e9e6;border-radius:14px;background:#fff;padding:20px;margin-bottom:16px}.customer-detail-card h2{font-size:15px;font-weight:650;margin:0 0 18px;color:#263a30}.customer-detail-card .form-label{font-size:13px;color:#58675e}.customer-order-table thead th{font-size:12px;color:#718077;font-weight:600;border-bottom-color:#e8ece9}.customer-order-table td{padding-top:12px;padding-bottom:12px}@media(max-width:700px){.customer-profile__stats{grid-template-columns:1fr}.customer-profile{padding:18px}.customer-profile__actions{width:100%}}
{/literal}
</style>
<section class="customer-profile">
	<div class="customer-profile__top">
		<div class="customer-profile__avatar">{$customerInitials|escape}</div>
		<div class="customer-profile__identity">
			<h1>{$customer.user_full_name|escape}</h1>
			<p>Müşteri #{$customer.id_user} · {$customer.email|default:'E-posta yok'|escape} · {$customer.phone|default:'Telefon yok'|escape}</p>
		</div>
		<div class="customer-profile__actions">
			<a class="btn btn-dark" href="{$adminUrl}order-create?customer_id={$customer.id_user}">Bu müşteriye sipariş girişi yap</a>
			<form method="post" action="{$adminUrl}customer?id={$customer.id_user}" target="_blank">
				<input type="hidden" name="impersonateCustomer" value="1"><input type="hidden" name="token" value="{$adminToken}">
				<button class="btn btn-outline-dark" type="submit">Müşteri olarak giriş yap</button>
			</form>
			<button type="button" class="btn btn-outline-primary" data-bs-toggle="modal" data-bs-target="#customerContactModal">Müşteriyle iletişim</button>
		</div>
	</div>
	<div class="customer-profile__stats">
		<div class="customer-profile__stat"><small>Sipariş sayısı</small><strong>{$customerOrderCount|default:0}</strong></div>
		<div class="customer-profile__stat"><small>Tamamlanan sipariş tutarı</small><strong>{$customerOrdersTotalFormatted|default:'₺0,00'|escape}</strong></div>
		<div class="customer-profile__stat"><small>Son sipariş</small><strong>{$customerLastOrderDate|default:'Henüz sipariş yok'|escape}</strong></div>
	</div>
</section>
<div class="row g-4">
	<div class="col-lg-4">
		<form method="post" class="customer-detail-card">
			<input type="hidden" name="saveCustomer" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Customer information'|adminT}</h2>

			<div class="mb-3">
				<label class="form-label">{'Full name'|adminT}</label>
				<input type="text" name="user_full_name" class="form-control" required
					value="{$customer.user_full_name|escape}">
			</div>
			<div class="mb-3">
				<label class="form-label">{'Phone'|adminT}</label>
				<input type="text" name="phone" class="form-control" required
					value="{$customer.phone|escape}" placeholder="{'e.g. +90 532… or +1 202…'|adminT}">
			</div>
			<div class="mb-3">
				<label class="form-label">{'Email'|adminT}</label>
				<input type="email" name="email" class="form-control"
					value="{$customer.email|escape}" placeholder="ornek@mail.com">
			</div>
			<div class="mb-3">
				<label class="form-label">{'Customer group'|adminT}</label>
				<select name="id_group" class="form-select">
					{foreach $customerGroups as $group}
					<option value="{$group.id_group}"{if $customer.id_group == $group.id_group} selected{/if}>{$group.name|escape}{if $group.discount_percent > 0} (−{$group.discount_percent|escape}%){/if}</option>
					{/foreach}
				</select>
				<div class="form-text"><a href="{$adminUrl}customer-groups">{'Manage groups'|adminT}</a></div>
			</div>

			<p class="mb-1 text-muted small">{'Registration date'|adminT}</p>
			<p class="mb-3">{$customer.date_formatted}</p>
			<p class="mb-3">
				{if $customer.active}
				<span class="badge bg-success">{'Active'|adminT}</span>
				{else}
				<span class="badge bg-danger">{'Inactive'|adminT}</span>
				{/if}
			</p>

			<button type="submit" class="btn btn-dark btn-sm">{'Save information'|adminT}</button>
		</form>

		<form method="post" class="customer-detail-card">
			<input type="hidden" name="saveCustomerPassword" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Change password'|adminT}</h2>
			<p class="text-muted small mb-3">{'The new password is saved directly to the customer account. At least 8 characters (e.g. <code>12345678</code>). The customer signs in with their registered phone number.'|adminT}</p>
			<div class="mb-3">
				<label class="form-label">{'New password'|adminT}</label>
				<input type="password" name="password" class="form-control" required minlength="8" autocomplete="new-password">
			</div>
			<div class="mb-3">
				<label class="form-label">{'New password (confirm)'|adminT}</label>
				<input type="password" name="password2" class="form-control" required minlength="8" autocomplete="new-password">
			</div>
			<button type="submit" class="btn btn-outline-dark btn-sm">{'Update password'|adminT}</button>
		</form>

		<form method="post" class="customer-detail-card">
			<input type="hidden" name="toggleActive" value="1">
			<input type="hidden" name="token" value="{$adminToken}">
			<h2 class="h6 mb-3">{'Account status'|adminT}</h2>
			<button type="submit" class="btn btn-sm {if $customer.active}btn-outline-danger{else}btn-outline-success{/if}">
				{if $customer.active}{'Deactivate account'|adminT}{else}{'Activate account'|adminT}{/if}
			</button>
		</form>
	</div>

	<div class="col-lg-8">
		<div class="customer-detail-card">
			<h2 class="h6 mb-3">{'Order history'|adminT}</h2>
			{if $customer.orders|@count}
			<div class="table-responsive">
				<table class="table table-sm align-middle mb-0 customer-order-table">
					<thead>
						<tr>
							<th>{'Reference'|adminT}</th>
							<th>{'Status'|adminT}</th>
							<th>{'Total'|adminT}</th>
							<th>{'Date'|adminT}</th>
							<th></th>
						</tr>
					</thead>
					<tbody>
						{foreach $customer.orders as $order}
						<tr>
							<td>{$order.reference|escape}</td>
							<td>{$order.status_label|escape}</td>
							<td>{$order.total_formatted}</td>
							<td>{$order.date_formatted}</td>
							<td class="text-end"><a href="{$adminUrl}order?id={$order.id_order}" class="btn btn-sm btn-outline-dark">{'View'|adminT}</a></td>
						</tr>
						{/foreach}
					</tbody>
				</table>
			</div>
			{else}
			<p class="text-muted mb-0">{'No orders yet.'|adminT}</p>
			{/if}
		</div>
	</div>
</div>

<div class="modal fade" id="customerContactModal" tabindex="-1" aria-labelledby="customerContactModalLabel" aria-hidden="true">
	<div class="modal-dialog modal-dialog-centered">
		<div class="modal-content">
			<form method="post">
				<input type="hidden" name="sendCustomerContact" value="1">
				<input type="hidden" name="token" value="{$adminToken}">
				<div class="modal-header">
					<h5 class="modal-title" id="customerContactModalLabel">{'Contact customer'|adminT}</h5>
					<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="{'Close'|adminT}"></button>
				</div>
				<div class="modal-body">
					<p class="small text-muted mb-3">
						<strong>{$customer.user_full_name|escape}</strong>
						{if $customerHasPhone}<br>{'Phone'|adminT}: {$customer.phone|escape}{/if}
						{if $customerHasEmail}<br>{'Email'|adminT}: {$customer.email|escape}{/if}
					</p>

					<div class="mb-3">
						<label class="form-label" for="customerContactMessage">{'Message'|adminT}</label>
						<textarea id="customerContactMessage" name="contact_message" class="form-control" rows="5" required placeholder="{'Write your message to the customer...'|adminT}"></textarea>
					</div>

					<div class="mb-0">
						<label class="form-label d-block">{'Send via'|adminT}</label>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="contact_channel" id="contactChannelWhatsapp" value="whatsapp"{if $customerHasPhone} checked{else} disabled{/if}>
							<label class="form-check-label" for="contactChannelWhatsapp">
								WhatsApp
								{if $customerContactWapioReady}
								<span class="text-muted small">({'via Wapio API'|adminT})</span>
								{else}
								<span class="text-muted small">({'opens wa.me link'|adminT})</span>
								{/if}
							</label>
						</div>
						<div class="form-check">
							<input class="form-check-input" type="radio" name="contact_channel" id="contactChannelEmail" value="email"{if !$customerHasPhone && $customerHasEmail} checked{elseif !$customerHasPhone} disabled{/if}>
							<label class="form-check-label" for="contactChannelEmail">{'Email'|adminT}</label>
						</div>
						{if !$customerHasPhone && !$customerHasEmail}
						<p class="small text-danger mb-0 mt-2">{'Customer has no phone or email on file.'|adminT}</p>
						{/if}
					</div>
				</div>
				<div class="modal-footer">
					<button type="button" class="btn btn-outline-secondary btn-sm" data-bs-dismiss="modal">{'Cancel'|adminT}</button>
					<button type="submit" class="btn btn-dark btn-sm"{if !$customerHasPhone && !$customerHasEmail} disabled{/if}>{'Send message'|adminT}</button>
				</div>
			</form>
		</div>
	</div>
</div>

{if $contactRedirectUrl}
<script>
(function () {
	var url = {$contactRedirectUrl|@json_encode nofilter};
	if (url) {
		window.open(url, '_blank', 'noopener');
	}
})();
</script>
{/if}
