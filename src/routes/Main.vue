<template>
	<div>
		<p v-html="regenerateThumbnails.i18n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.i18n.Home.intro2"></p>

		<h2 class="title">{{ regenerateThumbnails.i18n.Home.thumbnailSizes }}</h2>
		<p>{{ regenerateThumbnails.i18n.Home.thumbnailSizesDescription }}</p>
		<ul>
			<li
				is="thumbnail-size"
				v-for="(size, label) in regenerateThumbnails.data.thumbnailSizes"
				:key="label"
				:size="size"
				:text="regenerateThumbnails.i18n.common.thumbnailSizeItemWithCropMethod"
				:textCropped="regenerateThumbnails.i18n.common.thumbnailSizeItemIsCropped"
				:textProportional="regenerateThumbnails.i18n.common.thumbnailSizeItemIsProportional"
			></li>
		</ul>

		<h2 class="title">{{ regenerateThumbnails.i18n.Home.commandLineInterface }}</h2>
		<p v-html="regenerateThumbnails.i18n.Home.commandLineInterfaceText"></p>

		<p><em>DEBUG: WordPress REST API nonce is <code>{{ regenerateThumbnails.wpApiSettings.nonce }}</code>.</em></p>
	</div>
</template>

<script>
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ThumbnailSize from '../components/ThumbnailSize.vue'

	export default {
		data      : () => ({
			regenerateThumbnails: regenerateThumbnails,
			posts               : [],
		}),
		components: {
			ThumbnailSize,
		},
	}
</script>