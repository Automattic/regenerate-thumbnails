function logError( vue, attachmentID, jqXHR, textStatus, errorThrown ) {
	console.log('Regenerate Thumbnails: Error while trying to regenerate attachment ID ' + attachmentID, jqXHR, textStatus, errorThrown);

	let item = {};

	// Make the attachment ID clickable
	let a = document.createElement('a');
	a.href = vue.regenerateThumbnails.data.genericEditURL + attachmentID;
	a.textContent = attachmentID;
	let clickableID = a.outerHTML;

	if (
		jqXHR !== null &&
		jqXHR.hasOwnProperty('responseJSON') &&
		jqXHR.responseJSON !== null
	) {
		if (
			jqXHR.responseJSON.hasOwnProperty('data') &&
			jqXHR.responseJSON.data !== null &&
			jqXHR.responseJSON.data.hasOwnProperty('attachment') &&
			jqXHR.responseJSON.data.attachment !== null
		) {
			item = {
				id     : jqXHR.responseJSON.data.attachment.ID,
				message: vue.regenerateThumbnails.l10n.RegenerateMultiple.logSkippedItem.formatUnicorn({
					id    : clickableID,
					name  : jqXHR.responseJSON.data.attachment.post_title,
					reason: jqXHR.responseJSON.message,
				}),
			};
		} else {
			item = {
				id     : attachmentID,
				message: vue.regenerateThumbnails.l10n.RegenerateMultiple.logSkippedItemNoName.formatUnicorn({
					id    : clickableID,
					reason: jqXHR.responseJSON.message,
				}),
			};
		}
	} else {
		item = {
			id     : attachmentID,
			message: vue.regenerateThumbnails.l10n.RegenerateMultiple.logSkippedItemNoName.formatUnicorn({
				id    : clickableID,
				reason: errorThrown,
			}),
		};
	}

	vue.logItems.push(item);
	vue.errorItems.push(item);
}