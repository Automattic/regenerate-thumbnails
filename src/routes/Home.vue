<template>
	<div>
		<p v-html="regenerateThumbnails.l10n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.l10n.Home.intro2"></p>

		<h2 class="title">{{ regenerateThumbnails.l10n.Home.regenerateAllImages }}</h2>

		<p v-if="attachmentCount">{{ attachmentCount }}</p>

		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-onlymissing"
					:checked="regenerateThumbnails.options.onlyMissingThumbnails"
				/>
				{{ regenerateThumbnails.l10n.common.onlyRegenerateMissingThumbnails }}
			</label>
		</p>
		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-updateposts"
					:checked="regenerateThumbnails.options.updatePostContents"
				/>
				{{ regenerateThumbnails.l10n.Home.updatePostContents }}
			</label>
		</p>
		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-deleteoldthumbnails"
					:checked="regenerateThumbnails.options.deleteOldThumbnails"
					v-on:change="checkUpdatePosts"
				/>
				{{ regenerateThumbnails.l10n.common.deleteOldThumbnails }}
			</label>
		</p>

		<p class="submit">
			<button class="button button-primary button-hero" v-on:click="regenerate">
				{{ regenerateThumbnails.l10n.common.regenerateThumbnails }}
			</button>
		</p>

		<h2 class="title">{{ regenerateThumbnails.l10n.Home.thumbnailSizes }}</h2>
		<p>{{ regenerateThumbnails.l10n.Home.thumbnailSizesDescription }}</p>
		<ul>
			<li
				is="thumbnail-size"
				v-for="(size, label) in regenerateThumbnails.data.thumbnailSizes"
				:key="label"
				:size="size"
				:text="regenerateThumbnails.l10n.common.thumbnailSizeItemWithCropMethodNoFilename"
				:textCropped="regenerateThumbnails.l10n.common.thumbnailSizeItemIsCropped"
				:textProportional="regenerateThumbnails.l10n.common.thumbnailSizeItemIsProportional"
			></li>
		</ul>
	</div>
</template>

<script>
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ThumbnailSize from '../components/ThumbnailSize.vue'

	export default {
		data() {
			return {
				regenerateThumbnails: regenerateThumbnails,
				attachmentCount     : 0,
			}
		},
		created() {
			WPRESTAPI.get('wp/v2/media', {
					params: {
						media_type: 'image',
						per_page  : 1,
					}
				})
				.then(response => {
					this.attachmentCount = this.regenerateThumbnails.l10n.Home.attachmentCount.formatUnicorn({
						'attachmentCount': response.headers['x-wp-total'],
					});
				})
				.catch(error => {
					console.log(error);
				});
		},
		methods   : {
			regenerate(event) {
				alert( 'Not implemented yet.' );
			},
			checkUpdatePosts(event) {
				if (event.target.checked) {
					document.getElementById('regenthumbs-regenopt-updateposts').checked = true;
				}
			},
		},
		components: {
			ThumbnailSize,
		},
	}
</script>
