<template>
	<div>
		<p><em>DEBUG: WordPress REST API nonce is <code>{{ restAPINonce }}</code>.</em></p>

		<p v-html="regenerateThumbnails.i18n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.i18n.Home.intro2"></p>
		<p>{{ regenerateThumbnails.i18n.Home.intro3 }}</p>

		<h2>{{ regenerateThumbnails.i18n.Home.thumbnailSizes }}</h2>
		<ul>
			<li
				is="thumbnail-size"
				v-for="(size, label) in regenerateThumbnails.data.thumbnailSizes"
				v-bind:key="label"
				v-bind:size="size"
				v-bind:text="regenerateThumbnails.i18n.Home.thumbnailSizeItem"
				v-bind:textCropped="regenerateThumbnails.i18n.Home.thumbnailSizeItemCropped"
				v-bind:textProportional="regenerateThumbnails.i18n.Home.thumbnailSizeItemProportional"
			></li>
		</ul>

		<h2>{{ regenerateThumbnails.i18n.Home.commandLineInterface }}</h2>
		<p v-html="regenerateThumbnails.i18n.Home.commandLineInterfaceText"></p>
	</div>
</template>

<script>
	import ThumbnailSize from '../components/ThumbnailSize.vue'

	export default {
		data() {
			return {
				restAPINonce        : wpApiSettings.nonce,
				regenerateThumbnails: regenerateThumbnails,
			}
		},
		components: {
			ThumbnailSize
		}
	}
</script>

<style lang="scss">
	h2 {
		margin-top: 50px;
	}
</style>