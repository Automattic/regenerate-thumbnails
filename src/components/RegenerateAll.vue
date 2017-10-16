<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>

		<ol v-if="results" v-bind:start="listStart">
			<li v-for="result in results" :key="result.id">
				Regenerated {{ result.name }}
			</li>
		</ol>
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
				let maxListItems = 10;

				do {
					wp.apiRequest({
						namespace: 'wp/v2',
						endpoint : 'media?_fields=id,title',
						data     : {
							media_type: 'image',
							orderby   : 'id',
							order     : 'asc',
							page      : page,
							per_page  : 10,
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

										if (this.results.length > maxListItems) {
											this.results = this.results.slice(maxListItems * -1);
											this.listStart = processed - maxListItems + 1;
										}

										console.log(attachment);
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
		components: {
			ProgressBar,
		},
	}
</script>

<style lang="scss" scoped>
	.ui-progressbar {
		margin-top: 20px;
	}
</style>
