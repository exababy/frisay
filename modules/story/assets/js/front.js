/* ============================================================
   STORY MODULE — FRONT JS
   ============================================================ */
document.addEventListener('DOMContentLoaded', function () {
	var storyItems = Array.from(document.querySelectorAll('.bk-story-item'));
	var overlay = document.getElementById('bkStoryModalOverlay');
	if (!storyItems.length || !overlay) return;

	var modalImg = document.getElementById('bkStoryModalImg');
	var modalVideo = document.getElementById('bkStoryModalVideo');
	var modalTitle = document.getElementById('bkStoryModalTitle');
	var modalAvatar = document.getElementById('bkStoryModalAvatar');
	var progressFill = document.getElementById('bkStoryProgressFill');
	var modalFooter = document.getElementById('bkStoryModalFooter');
	var ctaBtn = document.getElementById('bkStoryModalCtaBtn');
	var closeBtn = document.getElementById('bkStoryModalClose');
	var prevBtn = document.getElementById('bkStoryNavPrev');
	var nextBtn = document.getElementById('bkStoryNavNext');
	var touchPrev = document.getElementById('bkStoryTouchPrev');
	var touchNext = document.getElementById('bkStoryTouchNext');

	var currentIndex = -1;
	var timer = null;
	var startTime = 0;
	var duration = 5000; // 5 saniye varsayılan resim süresi

	storyItems.forEach(function (item, index) {
		item.addEventListener('click', function () {
			openStory(index);
		});
	});

	function openStory(index) {
		if (index < 0 || index >= storyItems.length) {
			closeStory();
			return;
		}

		var item = storyItems[index];
		var mediaUrl = item.getAttribute('data-media-url');
		var linkUrl = item.getAttribute('data-link-url');
		var title = item.getAttribute('data-story-title');
		var coverUrl = item.getAttribute('data-cover-url');
		var mediaType = item.getAttribute('data-media-type');

		// Eğer medya yoksa ama link varsa doğrudan o linke git
		if (!mediaUrl && linkUrl) {
			window.location.href = linkUrl;
			return;
		}

		// Medya da yoksa işlem yapma
		if (!mediaUrl) {
			return;
		}

		currentIndex = index;

		// Modal bilgilerini doldur
		modalTitle.textContent = title || '';
		modalAvatar.src = coverUrl || mediaUrl;

		// Footer CTA
		if (linkUrl) {
			ctaBtn.href = linkUrl;
			modalFooter.style.display = 'flex';
		} else {
			modalFooter.style.display = 'none';
		}

		// Reset progress
		resetTimer();

		// Medya tipine göre ayarla
		if (mediaType === 'video') {
			modalImg.style.display = 'none';
			modalVideo.style.display = 'block';
			modalVideo.src = mediaUrl;
			modalVideo.currentTime = 0;
			modalVideo.play().then(function () {
				duration = (modalVideo.duration && !isNaN(modalVideo.duration)) ? modalVideo.duration * 1000 : 5000;
				startProgress(duration);
			}).catch(function () {
				duration = 5000;
				startProgress(duration);
			});

			modalVideo.onended = function () {
				nextStory();
			};
		} else {
			modalVideo.style.display = 'none';
			modalVideo.pause();
			modalImg.style.display = 'block';
			modalImg.src = mediaUrl;
			duration = 5000;
			startProgress(duration);
		}

		overlay.classList.add('active');
		document.body.style.overflow = 'hidden';
	}

	function startProgress(msDuration) {
		resetTimer();
		var start = Date.now();
		timer = setInterval(function () {
			var elapsed = Date.now() - start;
			var pct = Math.min((elapsed / msDuration) * 100, 100);
			progressFill.style.width = pct + '%';

			if (elapsed >= msDuration) {
				resetTimer();
				nextStory();
			}
		}, 30);
	}

	function resetTimer() {
		if (timer) clearInterval(timer);
		timer = null;
		progressFill.style.width = '0%';
	}

	function nextStory() {
		if (currentIndex + 1 < storyItems.length) {
			openStory(currentIndex + 1);
		} else {
			closeStory();
		}
	}

	function prevStory() {
		if (currentIndex - 1 >= 0) {
			openStory(currentIndex - 1);
		} else {
			closeStory();
		}
	}

	function closeStory() {
		resetTimer();
		if (modalVideo) {
			modalVideo.pause();
			modalVideo.src = '';
		}
		overlay.classList.remove('active');
		document.body.style.overflow = '';
		currentIndex = -1;
	}

	// Kontroller
	if (closeBtn) closeBtn.addEventListener('click', closeStory);
	if (prevBtn) prevBtn.addEventListener('click', prevStory);
	if (nextBtn) nextBtn.addEventListener('click', nextStory);
	if (touchPrev) touchPrev.addEventListener('click', prevStory);
	if (touchNext) touchNext.addEventListener('click', nextStory);

	// Klavye kontrolleri
	document.addEventListener('keydown', function (e) {
		if (!overlay.classList.contains('active')) return;
		if (e.key === 'Escape') closeStory();
		if (e.key === 'ArrowRight') nextStory();
		if (e.key === 'ArrowLeft') prevStory();
	});
});
