<template>
	<div>
		<p v-html="regenerateThumbnails.l10n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.l10n.Home.intro2"></p>

		<h2 class="title">{{ regenerateThumbnails.l10n.Home.regenerateAllImages }}</h2>

		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-onlymissing"
					:checked="checkboxOnlyMissing"
					v-on:change="checkboxChange('checkboxOnlyMissing', $event)"
				/>
				{{ regenerateThumbnails.l10n.common.onlyRegenerateMissingThumbnails }}
			</label>
		</p>

		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-updateposts"
					:checked="checkboxUpdatePosts"
					v-on:change="checkboxChange('checkboxUpdatePosts', $event)"
				/>
				{{ regenerateThumbnails.l10n.Home.updatePostContents }}
			</label>
		</p>

		<p>
			<label>
				<input
					type="checkbox"
					id="regenthumbs-regenopt-deleteoldthumbnails"
					:checked="checkboxDeleteOld"
					v-on:change="checkboxChange('checkboxDeleteOld', $event)"
				/>
				{{ regenerateThumbnails.l10n.common.deleteOldThumbnails }}
			</label>
		</p>

		<p class="submit">
			<button class="button button-primary button-hero" v-on:click="regenerate">
				{{ ButtonText }}
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
				ButtonText          : regenerateThumbnails.l10n.Home.RegenerateThumbnailsForAllAttachments,
				checkboxOnlyMissing : regenerateThumbnails.options.onlyMissingThumbnails,
				checkboxUpdatePosts : regenerateThumbnails.options.updatePostContents,
				checkboxDeleteOld   : regenerateThumbnails.options.deleteOldThumbnails,
			}
		},
		created() {
			// The _fields parameter requires WordPress 4.9+ and allows for faster response downloads
			WPRESTAPI.get('wp/v2/media?_fields=id', {
				params: {
					media_type: 'image',
					per_page  : 1,
				}
			})
				.then(response => {
					this.ButtonText = this.regenerateThumbnails.l10n.Home.RegenerateThumbnailsForXAttachments.formatUnicorn({
						'attachmentCount': response.headers['x-wp-total'],
					});
				})
				.catch(error => {
					console.log(error);
				});
		},
		methods   : {
			regenerate() {
				this.$emit('regenerate');
			},
			checkboxChange(prop, event) {
				this[prop] = event.target.checked;

				// Check the update posts checkbox when the delete checkbox is checked
				if ( 'checkboxDeleteOld' === prop && this[prop] ) {
					this.checkboxUpdatePosts = true;
				}
			},
		},
		components: {
			ThumbnailSize,
		},
	}
</script>
