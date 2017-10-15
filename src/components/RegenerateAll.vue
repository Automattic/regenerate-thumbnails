<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>
	</div>
</template>

<script>
	import ProgressBar from '../components/ProgressBar.vue'

	export default {
		data() {
			return {
				regenerateThumbnails: regenerateThumbnails,
				progress            : 0,
			}
		},
		mounted   : function () {
			this.$nextTick(function () {
				let processed = 0;
				let page = 1;
				let total = 0;
				let totalPages = 0;

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
									endpoint : 'attachmentinfo/' + attachment.id,
									type     : 'GET',
									dataType : 'json',
									async    : false,
									context  : this
								})
									.done((attachment, textStatus, jqXHR) => {
										processed++;
										this.progress = Math.round((processed / total) * 100);

										console.log(attachment);
									})
									.fail(function (error) {
										processed++;
										console.log('ERROR!', error);
									});
							});
						})
						.fail(function (error) {
							console.log(error);
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
