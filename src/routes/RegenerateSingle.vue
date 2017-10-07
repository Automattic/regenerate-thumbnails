<template>
	<div v-if="!dataLoaded">
		<p>{{ regenerateThumbnails.i18n.common.loading }}</p>
	</div>

	<div v-else-if="dataLoaded">
		<div v-if="attachmentInfo.error">
			<p v-html="errorText"></p>
		</div>
		<div v-else>
			<h2 class="title">{{ attachmentInfo.name }}</h2>

			<p v-html="filenameAndDimensions"></p>

			<img
				v-if="attachmentInfo.fullsizeurl"
				:src="attachmentInfo.fullsizeurl"
				class="image-preview"
				:alt="regenerateThumbnails.i18n.RegenerateSingle.preview"
			/>

			<p>
				<label>
					<input
						type="checkbox"
						id="regenthumbs-regenopt-onlymissing"
						:checked="regenerateThumbnails.options.onlyMissingThumbnails"
					/>
					{{ regenerateThumbnails.i18n.RegenerateSingle.onlyRegenerateMissingThumbnails }}
				</label>
			</p>
			<p>
				<label>
					<input
						type="checkbox"
						id="regenthumbs-regenopt-updateposts"
						:checked="regenerateThumbnails.options.updatePostContents"
					/>
					{{ regenerateThumbnails.i18n.RegenerateSingle.updatePostContents }}
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
					{{ regenerateThumbnails.i18n.RegenerateSingle.deleteOldThumbnails }}
				</label>
			</p>

			<p v-if="regenerationError"><strong v-html="regenerationError"></strong></p>

			<p class="submit">
				<button class="button button-primary button-hero" v-on:click="regenerate">
					{{ regenerateThumbnails.i18n.common.regenerateThumbnails }}
				</button>
			</p>

			<p>{{ regenerateThumbnails.i18n.RegenerateSingle.registeredSizes }}</p>
			<ul>
				<li
					is="thumbnail-status"
					v-for="size in attachmentInfo.registered_sizes"
					v-bind:key="size.label"
					v-bind:size="size"
					v-bind:i18n="regenerateThumbnails.i18n"
				></li>
			</ul>

			<div v-if="attachmentInfo.unregistered_sizes.length">
				<p>{{ regenerateThumbnails.i18n.RegenerateSingle.unregisteredSizes }}</p>
				<ul>
					<li
						is="thumbnail-status"
						v-for="size in attachmentInfo.unregistered_sizes"
						v-bind:key="size.label"
						v-bind:size="size"
						v-bind:i18n="regenerateThumbnails.i18n"
					></li>
				</ul>
			</div>
		</div>
	</div>

	<div v-else-if="restAPIError">
		<p v-html="restAPIError"></p>
	</div>
</template>

<script>
	require('../helpers/formatUnicorn');
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ThumbnailStatus from "../components/ThumbnailStatus.vue";

	export default {
		data      : () => ({
			regenerateThumbnails: regenerateThumbnails,
			dataLoaded          : false,
			attachmentInfo      : {},
			restAPIError        : false,
			regenerationComplete: false,
			regenerationError   : false,
		}),
		created() {
			WPRESTAPI.get('regenerate-thumbnails/v1/attachmentinfo/' + this.$route.params.id)
				.then(response => {
					this.attachmentInfo = response.data;
					document.getElementsByTagName('title')[0].innerHTML = this.regenerateThumbnails.i18n.RegenerateSingle.title.formatUnicorn(this.attachmentInfo);
					this.dataLoaded = true;
				})
				.catch(error => {
					this.restAPIError = this.regenerateThumbnails.i18n.RegenerateSingle.errorWithMessage.formatUnicorn({
						'error': error.response.data.message,
					});
					console.log(error);
				});
		},
		computed  : {
			errorText            : function () {
				return this.regenerateThumbnails.i18n.RegenerateSingle.errorWithMessage.formatUnicorn(this.attachmentInfo);
			},
			filenameAndDimensions: function () {
				return this.regenerateThumbnails.i18n.RegenerateSingle.filenameAndDimensions.formatUnicorn({
					filename: this.attachmentInfo.relative_path,
					width   : this.attachmentInfo.width,
					height  : this.attachmentInfo.height,
				});
			},
		},
		methods   : {
			regenerate      : function (event) {
				// On second button click
				if (this.regenerationComplete) {
					history.back();
					return;
				}

				event.target.disabled = true;
				event.target.innerText = regenerateThumbnails.i18n.RegenerateSingle.regenerating;

				// If this isn't done, the checkboxes revert to defaults
				this.regenerateThumbnails.options.onlyMissingThumbnails = document.getElementById('regenthumbs-regenopt-onlymissing').checked;
				this.regenerateThumbnails.options.deleteOldThumbnails = document.getElementById('regenthumbs-regenopt-deleteoldthumbnails').checked;
				this.regenerateThumbnails.options.updatePostContents = document.getElementById('regenthumbs-regenopt-updateposts').checked;

				WPRESTAPI.post('regenerate-thumbnails/v1/regenerate/' + this.$route.params.id, {
					regeneration_args     : {
						only_regenerate_missing_thumbnails : this.regenerateThumbnails.options.onlyMissingThumbnails,
						delete_unregistered_thumbnail_files: this.regenerateThumbnails.options.deleteOldThumbnails,
					},
					update_usages_in_posts: this.regenerateThumbnails.options.updatePostContents,
				})
					.then(response => {
						this.regenerationComplete = true;
						this.attachmentInfo = response.data;

						event.target.innerText = regenerateThumbnails.i18n.RegenerateSingle.done;
						event.target.disabled = false;
					})
					.catch(error => {
						event.target.innerText = regenerateThumbnails.i18n.RegenerateSingle.errorRegenerating;
						this.regenerationError = this.regenerateThumbnails.i18n.RegenerateSingle.errorRegeneratingMessage.formatUnicorn(error.response.data);
						console.log(error);
					});
			},
			checkUpdatePosts: function (event) {
				if (event.target.checked) {
					document.getElementById('regenthumbs-regenopt-updateposts').checked = true;
				}
			},
		},
		components: {
			ThumbnailStatus
		},
	}
</script>

<style lang="scss" scoped>
	.image-preview {
		max-width: 500px;
		max-height: 200px;
	}

	li {
		margin-left: 25px;
	}

	li.exists {
		list-style: url('images/yes.png');
	}

	li.notexists {
		list-style: url('images/no.png');
	}
</style>