{* ============================================================
   HOME_STORY.TPL — Front Story Display & Modal Viewer
   ============================================================ *}
{if $stories|@count > 0}
<section class="bk-story-section" aria-label="Hikayeler">
	<div class="bk-story-inner" role="list">
		{foreach $stories as $s name=storyLoop}
		{* cover_url yoksa media_url'i kullan *}
		{assign var="displayCover" value=$s.cover_url}
		{if !$displayCover && $s.media_url}
			{assign var="displayCover" value=$s.media_url}
		{/if}
		{* Media var mı? (cover veya media) *}
		{assign var="hasMedia" value=false}
		{if $s.cover_file || $s.media_file}
			{assign var="hasMedia" value=true}
		{/if}

		<div class="bk-story-item{if $hasMedia} has-story{/if}"
			 role="listitem"
			 data-story-id="{$s.id_story}"
			 data-story-title="{$s.title|escape}"
			 data-media-type="{$s.media_type|escape}"
			 data-media-url="{$s.media_url|escape}"
			 data-link-url="{$s.link_url|escape}"
			 data-cover-url="{$displayCover|escape}">

			<div class="bk-story-ring{if $hasMedia} has-story{/if}">
				<div class="bk-story-avatar">
					{if $displayCover}
					<img src="{$displayCover|escape}"
						 alt="{$s.title|escape}"
						 loading="lazy" />
					{else}
					<div class="bk-story-avatar-placeholder">
						<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
							<circle cx="12" cy="12" r="10"/><path d="M12 8v4m0 4h.01"/>
						</svg>
					</div>
					{/if}

					{if $s.media_type == 'video' && $s.media_file}
					<span class="bk-story-video-badge" title="Video">
						<svg width="10" height="10" viewBox="0 0 24 24" fill="currentColor">
							<polygon points="5 3 19 12 5 21 5 3"/>
						</svg>
					</span>
					{/if}
				</div>
			</div>

			<span class="bk-story-name">{$s.title|escape}</span>
		</div>
		{/foreach}
	</div>
</section>

{* ---- Fullscreen Story Modal Viewer ---- *}
<div class="bk-story-modal-overlay" id="bkStoryModalOverlay" aria-hidden="true" tabindex="-1">
	<div class="bk-story-modal-container">

		{* Top Progress Bar & Header *}
		<div class="bk-story-modal-top">
			<div class="bk-story-progress-bar">
				<div class="bk-story-progress-fill" id="bkStoryProgressFill"></div>
			</div>
			<div class="bk-story-modal-header">
				<div class="bk-story-author">
					<img src="" id="bkStoryModalAvatar" alt="" class="bk-story-author-img">
					<span id="bkStoryModalTitle" class="bk-story-author-title"></span>
				</div>
				<button type="button" class="bk-story-modal-close" id="bkStoryModalClose" aria-label="Kapat">
					<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
						<line x1="18" y1="6" x2="6" y2="18"/>
						<line x1="6" y1="6" x2="18" y2="18"/>
					</svg>
				</button>
			</div>
		</div>

		{* Media Body *}
		<div class="bk-story-modal-body">
			<img id="bkStoryModalImg" src="" alt="" class="bk-story-media-img" style="display:none;">
			<video id="bkStoryModalVideo" src="" class="bk-story-media-video" playsinline preload="auto" style="display:none;"></video>

			{* Touch / Click zones for Prev & Next *}
			<div class="bk-story-touch-zone bk-story-touch-left" id="bkStoryTouchPrev" aria-label="Önceki"></div>
			<div class="bk-story-touch-zone bk-story-touch-right" id="bkStoryTouchNext" aria-label="Sonraki"></div>
		</div>

		{* Footer CTA Button *}
		<div class="bk-story-modal-footer" id="bkStoryModalFooter" style="display:none;">
			<a href="#" id="bkStoryModalCtaBtn" class="bk-story-cta-btn" target="_self">
				<span>İncele / Detaya Git</span>
				<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
					<path d="M5 12h14M12 5l7 7-7 7"/>
				</svg>
			</a>
		</div>

		{* Navigation Arrows (Desktop) *}
		<button class="bk-story-nav-btn bk-story-nav-prev" id="bkStoryNavPrev" aria-label="Önceki Hikaye">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
				<path d="m15 18-6-6 6-6"/>
			</svg>
		</button>
		<button class="bk-story-nav-btn bk-story-nav-next" id="bkStoryNavNext" aria-label="Sonraki Hikaye">
			<svg width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5">
				<path d="m9 18 6-6-6-6"/>
			</svg>
		</button>

	</div>
</div>
{/if}
