/* Story Module Admin JS */
document.addEventListener('DOMContentLoaded', function () {
	var select = document.getElementById('storyMediaTypeSelect');
	var fileInput = document.getElementById('storyMediaFileInput');
	var helpText = document.getElementById('storyMediaHelpText');

	if (select && fileInput && helpText) {
		select.addEventListener('change', function () {
			if (this.value === 'video') {
				fileInput.setAttribute('accept', 'video/mp4,video/webm');
				helpText.textContent = 'Lütfen MP4 veya WebM formatında video yükleyin.';
			} else {
				fileInput.setAttribute('accept', 'image/*');
				helpText.textContent = 'Görsel (JPG, PNG, WebP, GIF) yükleyin.';
			}
		});
	}
});
