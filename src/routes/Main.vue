<template>
	<div>
		<p v-html="regenerateThumbnails.l10n.Home.intro1"></p>
		<p v-html="regenerateThumbnails.l10n.Home.intro2"></p>

		<progress-bar :progress="progress"></progress-bar>

		<h2 class="title">{{ regenerateThumbnails.l10n.Home.thumbnailSizes }}</h2>
		<p>{{ regenerateThumbnails.l10n.Home.thumbnailSizesDescription }}</p>
		<ul>
			<li
				is="thumbnail-size"
				v-for="(size, label) in regenerateThumbnails.data.thumbnailSizes"
				:key="label"
				:size="size"
				:text="regenerateThumbnails.l10n.common.thumbnailSizeItemWithCropMethod"
				:textCropped="regenerateThumbnails.l10n.common.thumbnailSizeItemIsCropped"
				:textProportional="regenerateThumbnails.l10n.common.thumbnailSizeItemIsProportional"
			></li>
		</ul>

		<h2 class="title">{{ regenerateThumbnails.l10n.Home.commandLineInterface }}</h2>
		<p v-html="regenerateThumbnails.l10n.Home.commandLineInterfaceText"></p>
	</div>
</template>

<script>
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ProgressBar from '../components/ProgressBar.vue'
	import ThumbnailSize from '../components/ThumbnailSize.vue'

	export default {
		data      : () => ({
			regenerateThumbnails: regenerateThumbnails,
			progress            : 37,
		}),
		components: {
			ProgressBar,
			ThumbnailSize,
		},
	}
</script>
