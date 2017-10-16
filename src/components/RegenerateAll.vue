<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>

		<div id="regenerate-thumbnails-log">
			<ol v-if="results" v-bind:start="listStart">
				<li v-for="result in results" :key="result.id">
					Regenerated {{ result.name }}
				</li>
			</ol>
		</div>
	</div>
</template>

<script>
	import ProgressBar from '../components/ProgressBar.vue'

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
			this.$nextTick(function () {
				let processed = 0;
				let page = 1;
				let total = 0;
				let totalPages = 0;
				let MaxLogItems = 500; // To keep the DOM size from going insane

				do {
					wp.apiRequest({
						namespace: 'wp/v2',
						endpoint : 'media',
						data     : {
							_fields           : ['id', 'title'],
							media_type        : 'image',
							exclude_site_icons: 1,
							orderby           : 'id',
							order             : 'asc',
							page              : page,
							per_page          : 50,
						},
						type     : 'GET',
						dataType : 'json',
						async    : false,
						context  : this
					})
						.done((attachments, textStatus, jqXHR) => {
							total = jqXHR.getResponseHeader('x-wp-total');
							totalPages = jqXHR.getResponseHeader('x-wp-totalpages');

							attachments.forEach(attachment => {
								wp.apiRequest({
									namespace: 'regenerate-thumbnails/v1',
									endpoint : 'regenerate/' + attachment.id,
									data     : {
										only_regenerate_missing_thumbnails : this.settings.onlyMissing,
										delete_unregistered_thumbnail_files: this.settings.deleteOld,
										update_usages_in_posts             : this.settings.updatePosts,
									},
									type     : 'POST',
									dataType : 'json',
									async    : false,
									context  : this
								})
									.done((attachment, textStatus, jqXHR) => {
										processed++;
										this.progress = Math.round((processed / total) * 100);

										this.results.push(attachment);

										if (this.results.length > MaxLogItems) {
											this.results = this.results.slice(MaxLogItems * -1);
											this.listStart = processed - MaxLogItems + 1;
										}
									})
									.fail(error => {
										processed++;
										this.progress = Math.round((processed / total) * 100);
										console.log('ERROR!', error);
									});
							});
						})
						.fail(error => {
							console.log('ERROR!', error);
						});

					page++;
				}
				while (page <= totalPages);
			});
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
		height: 250px;
		overflow: auto;
	}
</style>
