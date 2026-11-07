{* Antepli tema stil sırası: renkler → theme4 altyapı CSS (sepet/ödeme/hesap sayfaları için) → sayfa CSS → antepli → kullanıcı CSS *}
{assign var=_t4css value=$domain|cat:'templates/theme4/css/'}
<link rel="stylesheet" href="{($css_dir|cat:'colors.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'style.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'pages.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'account-orders.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'account-hub.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'auth.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'notifications.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'cart-modal.css')|asset_url}" />
<link rel="stylesheet" href="{($_t4css|cat:'shopmore.css')|asset_url}" />
{if $css}
<link rel="stylesheet" href="{($_t4css|cat:$css)|asset_url}" />
{/if}
<link rel="stylesheet" href="{($css_dir|cat:'antepli-skin.css')|asset_url}" />
<link rel="stylesheet" href="{($css_dir|cat:'antepli.css')|asset_url}" />
<link rel="stylesheet" href="{($css_dir|cat:'custom.css')|asset_url}" />
