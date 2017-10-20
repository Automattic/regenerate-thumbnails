<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>

		<div id="regenerate-thumbnails-log">
			<ol v-if="results" :start="listStart">
				<li v-for="result in results" :key="result.id">
					Regenerated {{ result.name }}
				</li>
			</ol>
		</div>
	</div>
</template>

<script>
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
				results             : [],
			}
		},
		mounted   : function () {
			let vue = this;
			let processed = 0;
			let totalItems = 0;
			let page = 1;
			let totalPages = 0;
			let maxLogItems = 500; // To keep the DOM size from going insane

			let titleElement = document.getElementsByTagName('title')[0];
			let title = titleElement.innerHTML;
			titleElement.innerHTML = vue.progress + '% | ' + title;

			// Prompt the user to confirm if they try to leave the page
			window.onbeforeunload = function () {
				return true;
			};

			processPageOfAttachments();

			function processPageOfAttachments() {
				let namespace = 'wp/v2';
				let endpoint = 'media';
				let data = {
					page    : page,
					per_page: 5,
				};

				switch (vue.settings.regenerateWhat) {
					case 'featured-images':
						namespace = 'regenerate-thumbnails/v1';
						endpoint = 'featuredimages';
						break;
					case 'all':
					default:
						data._fields = 'id';
						data.media_type = 'image';
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
						totalItems = jqXHR.getResponseHeader('x-wp-total');
						totalPages = jqXHR.getResponseHeader('x-wp-totalpages');

						processAttachment(attachments);
					})
					.fail((jqXHR, textStatus, errorThrown) => {
						console.log('ERROR!', jqXHR, textStatus, errorThrown);
					});
			}

			function processAttachment(attachments) {
				let attachment = attachments.shift();

				wp.apiRequest({
					namespace: 'regenerate-thumbnails/v1',
					endpoint : 'regenerate/' + attachment.id,
					data     : {
						only_regenerate_missing_thumbnails : vue.settings.onlyMissing,
						delete_unregistered_thumbnail_files: vue.settings.deleteOld,
						update_usages_in_posts             : vue.settings.updatePosts,
					},
					type     : 'POST',
					dataType : 'json',
					context  : vue,
				})
					.done((result, textStatus, jqXHR) => {
						vue.results.push(result);
					})
					.fail((jqXHR, textStatus, errorThrown) => {
						console.log('ERROR!', jqXHR, textStatus, errorThrown);
					})
					.always(() => {
						processed++;
						vue.progress = Math.round((processed / totalItems) * 100);

						titleElement.innerHTML = vue.progress + '% | ' + title;

						// Keep the log size under control
						if (vue.results.length > maxLogItems) {
							vue.results = vue.results.slice(maxLogItems * -1);
							vue.listStart = processed - maxLogItems + 1;
						}

						// Process the next attachment, or next page if we've run out
						if (attachments.length > 0) {
							processAttachment(attachments);
						} else if (page < totalPages) {
							page++;
							processPageOfAttachments();
						}
					});
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
		height: 500px;
		overflow: auto;
	}
</style>
