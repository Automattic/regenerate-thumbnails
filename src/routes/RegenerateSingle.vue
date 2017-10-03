<template>
	<div v-if="dataLoaded">
		<div v-if="attachmentInfo.error">
			<p><strong>ERROR:</strong> <span v-html="attachmentInfo.error"></span></p>
		</div>
		<div v-else>
			<h2 class="title">{{ attachmentInfo.name }}</h2>

			<img
				v-if="attachmentInfo.fullsizeurl"
				:src="attachmentInfo.fullsizeurl"
				class="image-preview"
				alt="Preview"
			/>

			<p>Thumbnail status for currently registered sizes:</p>
			<ul>
				<li
					is="thumbnail-status"
					v-for="size in attachmentInfo.registered_sizes"
					v-bind:key="size.label"
					v-bind:size="size"
				></li>
			</ul>

			<div v-if="attachmentInfo.unregistered_sizes.length">
				<p>The attachment says it also has these thumbnail sizes but they are no longer in use by WordPress. They can probably be safely deleted.</p>
				<ul>
					<li
						is="thumbnail-status"
						v-for="size in attachmentInfo.unregistered_sizes"
						v-bind:key="size.label"
						v-bind:size="size"
					></li>
				</ul>
			</div>
		</div>
	</div>

	<div v-else-if="errorMessage">
		<p>There was an error fetching about this attachment via the WordPress REST API. The error was: <em>{{ errorMessage }}</em></p>
	</div>
</template>

<script>
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ThumbnailStatus from "../components/ThumbnailStatus.vue";

	export default {
		components: {ThumbnailStatus},
		data      : () => ({
			regenerateThumbnails: regenerateThumbnails,
			dataLoaded          : false,
			attachmentInfo      : {},
			errorMessage        : false,
		}),
		created() {
			WPRESTAPI.get('regenerate-thumbnails/v1/attachmentinfo/' + this.$route.params.id)
				.then(response => {
					this.attachmentInfo = response.data;
					this.dataLoaded = true;
				})
				.catch(error => {
					this.errorMessage = error.response.data.message;
					console.log(error);
				});
		},
	}
</script>

<style lang="scss" scoped>
	.image-preview {
		max-width: 500px;
		max-height: 200px;
	}
</style>