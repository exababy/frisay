<div class="tab-pane fade" id="product-file-info-{$id_product}" role="tabpanel">
	<ul class="list-unstyled mb-0">
	{foreach $documents as $document}
		<li class="mb-2"><a class="btn btn-outline-primary" href="{$document.url|escape}" download="{$document.file_name|escape}">{$document.title|escape} — İndir</a></li>
	{/foreach}
	</ul>
</div>
