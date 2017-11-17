<template>
	<div>
		<p v-html="regenerateThumbnails.l10n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.l10n.Home.intro2"></p>

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

		<div v-if="regenerateThumbnails.data.thumbnailIDs">
			<p>
				<button class="button button-primary button-hero" v-on:click="regenerate(regenerateThumbnails.data.thumbnailIDs)">
					{{ regenerateThumbnails.l10n.Home.RegenerateThumbnailsForXAttachments }}
				</button>
			</p>
		</div>
		<div v-else>
			<p>
				<button class="button button-primary button-hero" v-on:click="regenerate('all')">
					{{ ButtonAllText }}
				</button>
			</p>

			<p v-if="usingFeaturedImages">
				<button class="button button-primary button-hero" v-on:click="regenerate('featured-images')">
					{{ ButtonFeaturedImagesText }}
				</button>
			</p>
		</div>

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

		<h2>{{ regenerateThumbnails.l10n.Home.alternatives }}</h2>
		<p v-html="alternativesText1"></p>
		<p v-html="alternativesText2"></p>
	</div>
</template>

<script>
	require('../../helpers/formatUnicorn.js');
	import ThumbnailSize from '../../components/ThumbnailSize.vue'

	export default {
		data() {
			return {
				regenerateThumbnails    : regenerateThumbnails,
				usingFeaturedImages     : false,
				ButtonAllText           : regenerateThumbnails.l10n.Home.RegenerateThumbnailsForAllAttachments,
				ButtonFeaturedImagesText: regenerateThumbnails.l10n.Home.RegenerateThumbnailsForFeaturedImagesOnly,
				checkboxOnlyMissing     : regenerateThumbnails.options.onlyMissingThumbnails,
				checkboxUpdatePosts     : regenerateThumbnails.options.updatePostContents,
				checkboxDeleteOld       : regenerateThumbnails.options.deleteOldThumbnails,
			}
		},
		computed  : {
			alternativesText1() {
				return this.regenerateThumbnails.l10n.Home.alternativesText1.formatUnicorn({
					'url-cli'             : 'https://en.wikipedia.org/wiki/Command-line_interface',
					'url-wpcli'           : 'https://wp-cli.org/',
					'url-wpcli-regenerate': 'https://developer.wordpress.org/cli/commands/media/regenerate/',
				});
			},
			alternativesText2() {
				return this.regenerateThumbnails.l10n.Home.alternativesText2.formatUnicorn({
					'url-photon' : 'https://jetpack.com/support/photon/',
					'url-jetpack': 'https://jetpack.com/',
				});
			}
		},
		created() {
			if (this.regenerateThumbnails.data.thumbnailIDs) {
				return;
			}

			// TODO: Probably better to preload this rather than fetch via AJAX

			// Update button with total attachment count
			wp.apiRequest({
				namespace: 'wp/v2',
				endpoint : 'media',
				data     : {
					_fields           : 'id',
					media_type        : 'image',
					exclude_site_icons: 1,
					per_page          : 1,
				},
				type     : 'GET',
				dataType : 'json',
				context  : this
			})
				.done((data, textStatus, jqXHR) => {
					this.ButtonAllText = this.regenerateThumbnails.l10n.Home.RegenerateThumbnailsForAllXAttachments.formatUnicorn({
						'attachmentCount': jqXHR.getResponseHeader('x-wp-total').toLocaleString(),
					});
				})
				.fail((jqXHR, textStatus, errorThrown) => {
					console.log('Regenerate Thumbnails: Error getting the total attachment count.', jqXHR, textStatus, errorThrown);
				});

			// Update button with total featured images count
			wp.apiRequest({
				namespace: 'regenerate-thumbnails/v1',
				endpoint : 'featuredimages',
				data     : {
					per_page: 1,
				},
				type     : 'GET',
				dataType : 'json',
				context  : this
			})
				.done((data, textStatus, jqXHR) => {
					this.ButtonAllText = this.regenerateThumbnails.l10n.Home.RegenerateThumbnailsForAllXAttachments.formatUnicorn({
						'attachmentCount': jqXHR.getResponseHeader('x-wp-total').toLocaleString(),
					});

					if (jqXHR.getResponseHeader('x-wp-total') < 1) {
						this.usingFeaturedImages = false;
					} else {
						this.ButtonFeaturedImagesText = this.regenerateThumbnails.l10n.Home.RegenerateThumbnailsForXFeaturedImagesOnly.formatUnicorn({
							'attachmentCount': jqXHR.getResponseHeader('x-wp-total').toLocaleString(),
						});
						this.usingFeaturedImages = true;
					}
				})
				.fail((jqXHR, textStatus, errorThrown) => {
					console.log('Regenerate Thumbnails: Error getting the total featured images count.', jqXHR, textStatus, errorThrown);
				});
		},
		methods   : {
			regenerate(what) {
				this.$emit('regenerate', what);
			},
			checkboxChange(prop, event) {
				this[prop] = event.target.checked;

				// Check the update posts checkbox when the delete checkbox is checked
				if ('checkboxDeleteOld' === prop && this[prop]) {
					this.checkboxUpdatePosts = true;
				}
			},
		},
		components: {
			ThumbnailSize,
		},
	}
</script>
