<template>
	<div v-if="!dataLoaded">
		<p>{{ regenerateThumbnails.l10n.common.loading }}</p>
	</div>

	<div v-else-if="dataLoaded">
		<div v-if="errorText">
			<p v-html="errorText"></p>
		</div>
		<div v-else>
			<h2 class="title">{{ attachmentInfo.name }}</h2>

			<p v-html="filenameAndDimensions"></p>

			<img
				v-if="attachmentInfo.fullsizeurl"
				:src="attachmentInfo.fullsizeurl"
				class="image-preview"
				:alt="regenerateThumbnails.l10n.RegenerateSingle.preview"
			/>

			<p>
				<label>
					<input
						type="checkbox"
						id="regenthumbs-regenopt-onlymissing"
						:checked="regenerateThumbnails.options.onlyMissingThumbnails"
					/>
					{{ regenerateThumbnails.l10n.RegenerateSingle.onlyRegenerateMissingThumbnails }}
				</label>
			</p>
			<p>
				<label>
					<input
						type="checkbox"
						id="regenthumbs-regenopt-updateposts"
						:checked="regenerateThumbnails.options.updatePostContents"
					/>
					{{ regenerateThumbnails.l10n.RegenerateSingle.updatePostContents }}
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
					{{ regenerateThumbnails.l10n.RegenerateSingle.deleteOldThumbnails }}
				</label>
			</p>

			<p v-if="regenerationError"><strong v-html="regenerationError"></strong></p>

			<p class="submit">
				<button class="button button-primary button-hero" v-on:click="regenerate">
					{{ regenerateThumbnails.l10n.common.regenerateThumbnails }}
				</button>
			</p>

			<p>{{ regenerateThumbnails.l10n.RegenerateSingle.registeredSizes }}</p>
			<ul>
				<li
					is="thumbnail-status"
					v-for="size in attachmentInfo.registered_sizes"
					v-bind:key="size.label"
					v-bind:size="size"
					v-bind:l10n="regenerateThumbnails.l10n"
				></li>
			</ul>

			<div v-if="attachmentInfo.unregistered_sizes.length">
				<p>{{ regenerateThumbnails.l10n.RegenerateSingle.unregisteredSizes }}</p>
				<ul>
					<li
						is="thumbnail-status"
						v-for="size in attachmentInfo.unregistered_sizes"
						v-bind:key="size.label"
						v-bind:size="size"
						v-bind:l10n="regenerateThumbnails.l10n"
					></li>
				</ul>
			</div>
		</div>
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
			errorText           : false,
			attachmentInfo      : {},
			regenerationComplete: false,
			regenerationError   : false,
		}),
		created() {
			WPRESTAPI.get('regenerate-thumbnails/v1/attachmentinfo/' + this.$route.params.id)
				.then(response => {
					this.attachmentInfo = response.data;

					if (typeof this.attachmentInfo.error !== 'undefined') {
						this.errorText = this.regenerateThumbnails.l10n.RegenerateSingle.errorWithMessage.formatUnicorn(this.attachmentInfo);
					} else {
						document.getElementsByTagName('title')[0].innerHTML = this.regenerateThumbnails.l10n.RegenerateSingle.title.formatUnicorn(this.attachmentInfo);
					}

					this.dataLoaded = true;
				})
				.catch(error => {
					this.errorText = this.regenerateThumbnails.l10n.RegenerateSingle.errorWithMessage.formatUnicorn({
						'error': error.response.data.message,
					});

					this.dataLoaded = true;

					console.log(error);
				});
		},
		computed  : {
			filenameAndDimensions: function () {
				return this.regenerateThumbnails.l10n.RegenerateSingle.filenameAndDimensions.formatUnicorn({
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
				event.target.innerText = regenerateThumbnails.l10n.RegenerateSingle.regenerating;

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

						event.target.innerText = regenerateThumbnails.l10n.RegenerateSingle.done;
						event.target.disabled = false;
					})
					.catch(error => {
						event.target.innerText = regenerateThumbnails.l10n.RegenerateSingle.errorRegenerating;
						this.regenerationError = this.regenerateThumbnails.l10n.RegenerateSingle.errorRegeneratingMessage.formatUnicorn(error.response.data);
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
