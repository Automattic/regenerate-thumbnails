<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>

		<p>
			<button v-if="!finishedMessage && progress < 100" class="button button-secondary button-large" v-on:click="togglePause">
				{{ isPaused ? this.regenerateThumbnails.l10n.RegenerateMultiple.resume : this.regenerateThumbnails.l10n.RegenerateMultiple.pause }}
			</button>
		</p>

		<div v-if="finishedMessage">
			<p><strong>{{ finishedMessage }}</strong></p>

			<div v-if="errorItems.length" id="regenerate-thumbnails-error-log">
				<h2 class="title">{{ regenerateThumbnails.l10n.RegenerateMultiple.errorsEncountered }}</h2>

				<ol>
					<li v-for="errorItem in errorItems" :key="errorItem.id" v-html="errorItem.message"></li>
				</ol>
			</div>
		</div>

		<h2 class="title">{{ regenerateThumbnails.l10n.RegenerateMultiple.regenerationLog }}</h2>
		<div id="regenerate-thumbnails-log">
			<ol v-if="logItems" :start="listStart">
				<li v-for="logItem in logItems" :key="logItem.id" v-html="logItem.message"></li>
			</ol>
		</div>
	</div>
</template>

<script>
	require('../../helpers/formatUnicorn.js');
	import ProgressBar from '../../components/ProgressBar.vue'

	export default {
		props     : [
			'settings',
		],
		data() {
			return {
				regenerateThumbnails: regenerateThumbnails,
				listStart           : 1,
				progress            : 0,
				logItems            : [],
				errorItems          : [],
				isPaused            : false,
				finishedMessage     : false,
			}
		},
		mounted   : function () {
			let vue = this;
			let processed = 0;
			let totalItems = 0;
			let page = 1;
			let totalPages = 0;
			let maxLogItems = 500; // To keep the DOM size from going insane
			let startTime = Date.now();
			let chunkErrorRetry = 3;

			let titleElement = document.getElementsByTagName('title')[0];
			let title = titleElement.innerHTML;
			titleElement.innerHTML = vue.progress + '% | ' + title;

			// Prompt the user to confirm if they try to leave the page
			window.onbeforeunload = function () {
				return true;
			};

			if (Array.isArray(vue.settings.regenerateWhat)) {
				// A little transformation of the data
				let attachments = [];
				for (let id of vue.settings.regenerateWhat) {
					attachments.push({id: id});
				}

				totalItems = vue.settings.regenerateWhat.length;

				processAttachment(attachments);
			} else {
				processChunkOfAttachments();
			}

			function processChunkOfAttachments() {
				let namespace = 'wp/v2';
				let endpoint = 'media';
				let data = {
					page    : page,
					per_page: 25,
				};

				switch (vue.settings.regenerateWhat) {
					case 'featured-images':
						namespace = 'regenerate-thumbnails/v1';
						endpoint = 'featuredimages';
						break;
					case 'all':
					default:
						data._fields = 'id';
						data.is_regeneratable = 1;
						data.exclude_site_icons = 1;
						data.orderby = 'id';
						data.order = 'asc';
						break;
				}

				wp.apiRequest({
					namespace: namespace,
					endpoint : endpoint,
					data     : data,
					type     : 'GET',
					dataType : 'json',
					context  : vue,
				})
					.done((attachments, textStatus, jqXHR) => {
						chunkErrorRetry = 3;

						totalItems = jqXHR.getResponseHeader('x-wp-total');
						totalPages = jqXHR.getResponseHeader('x-wp-totalpages');

						processAttachment(attachments);
					})
					.fail((jqXHR, textStatus, errorThrown) => {
						console.log('Regenerate Thumbnails: Error getting a chunk of thumbnail IDs to process.', jqXHR, textStatus, errorThrown);
						if (chunkErrorRetry > 1) {
							chunkErrorRetry--;
							processChunkOfAttachments();
						} else {
							vue.finishedMessage = vue.regenerateThumbnails.l10n.RegenerateMultiple.error;
						}
					});
			}

			function processAttachment(attachments) {
				// Is there a better way to do this?
				if (vue.isPaused) {
					setTimeout(function () {
						startTime = startTime + 1000;
						processAttachment(attachments);
					}, 1000);

					return;
				}

				let attachment = attachments.shift();

				wp.apiRequest({
					namespace: 'regenerate-thumbnails/v1',
					endpoint : 'regenerate/' + attachment.id,
					data     : {
						only_regenerate_missing_thumbnails : vue.settings.onlyMissing,
						delete_unregistered_thumbnail_files: vue.settings.deleteOld,
						update_usages_in_posts             : vue.settings.updatePosts,
					},
					type     : 'GET',
					dataType : 'json',
					context  : vue,
				})
					.done((result, textStatus, jqXHR) => {
						if ( null === result ) {
							logError( vue, attachment.id, jqXHR, textStatus, result );
							return;
						}

						// Make the attachment name clickable
						if ( result.edit_url !== null ) {
							let a = document.createElement('a');
							a.href = result.edit_url;
							a.textContent = result.name;
							result.name = a.outerHTML;
						}

						vue.logItems.push({
							id     : result.id,
							message: vue.regenerateThumbnails.l10n.RegenerateMultiple.logRegeneratedItem.formatUnicorn(result),
						});
					})
					.fail((jqXHR, textStatus, errorThrown) => {
						logError( vue, attachment.id, jqXHR, textStatus, errorThrown );
					})
					.always(() => {
						processed++;
						vue.progress = Math.round((processed / totalItems) * 100);

						titleElement.innerHTML = vue.progress + '% | ' + title;

						// Keep the log size under control
						if (vue.logItems.length > maxLogItems) {
							vue.logItems = vue.logItems.slice(maxLogItems * -1);
							vue.listStart = processed - maxLogItems + 1;
						}

						// If done, show how long it took
						if (processed == totalItems) {
							let secondsTaken = (Date.now() - startTime) / 1000;
							let durationString = '';

							if (secondsTaken > 3600) {
								let hoursTaken = secondsTaken / 3600;
								durationString = vue.regenerateThumbnails.l10n.RegenerateMultiple.hours.formatUnicorn({
									count: hoursTaken.toFixed(1),
								});
							} else if (secondsTaken > 60) {
								let minutesTaken = secondsTaken / 3600;
								durationString = vue.regenerateThumbnails.l10n.RegenerateMultiple.minutes.formatUnicorn({
									count: minutesTaken.toFixed(1),
								});
							} else {
								durationString = vue.regenerateThumbnails.l10n.RegenerateMultiple.seconds.formatUnicorn({
									count: secondsTaken.toFixed(),
								});
							}

							vue.finishedMessage = vue.regenerateThumbnails.l10n.RegenerateMultiple.duration.formatUnicorn({
								duration: durationString,
							});
						}

						// Process the next attachment, or next page if we've run out
						if (attachments.length > 0) {
							processAttachment(attachments);
						} else if (page < totalPages) {
							page++;
							processChunkOfAttachments();
						}
					});
			}

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

			window.onbeforeunload = undefined;
		},
		updated   : function () {
			this.$nextTick(function () {
				let logBox = document.getElementById('regenerate-thumbnails-log');

				if (logBox.scrollHeight - logBox.scrollTop <= logBox.clientHeight + 25) {
					logBox.scrollTop = logBox.scrollHeight - logBox.clientHeight;
				}
			});
		},
		methods   : {
			togglePause() {
				this.isPaused = !this.isPaused;
			},
		},
		components: {
			ProgressBar,
		},
	}
</script>

<style lang="scss" scoped>
	.ui-progressbar {
		margin: 20px auto;
	}

	#regenerate-thumbnails-log {
		height: 495px;
		overflow: auto;
	}

	#regenerate-thumbnails-error-log {
		max-height: 250px;
		overflow: auto;
	}

	li {
		margin-left: 25px;
	}
</style>
