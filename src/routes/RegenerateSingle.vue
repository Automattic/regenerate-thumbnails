<template>
	<div v-if="dataLoaded">
		<div v-if="attachmentInfo.error">
			<p v-html="errorText"></p>
		</div>
		<div v-else>
			<h2 class="title">{{ attachmentInfo.name }}</h2>

			<p><code>{{ attachmentInfo.relative_path }}</code></p>

			<img
				v-if="attachmentInfo.fullsizeurl"
				:src="attachmentInfo.fullsizeurl"
				class="image-preview"
				:alt="regenerateThumbnails.i18n.RegenerateSingle.preview"
			/>

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
		}),
		created() {
			WPRESTAPI.get('regenerate-thumbnails/v1/attachmentinfo/' + this.$route.params.id)
				.then(response => {
					this.attachmentInfo = response.data;
					this.dataLoaded = true;
				})
				.catch(error => {
					this.restAPIError = this.regenerateThumbnails.i18n.RegenerateSingle.restAPIError.formatUnicorn(error.response.data);
					console.log(error);
				});
		},
		computed  : {
			errorText: function () {
				return this.regenerateThumbnails.i18n.RegenerateSingle.error.formatUnicorn(this.attachmentInfo);
			}
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