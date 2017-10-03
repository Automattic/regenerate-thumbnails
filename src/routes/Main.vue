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
				:text="regenerateThumbnails.i18n.Home.thumbnailSizeItem"
				:textCropped="regenerateThumbnails.i18n.Home.thumbnailSizeItemCropped"
				:textProportional="regenerateThumbnails.i18n.Home.thumbnailSizeItemProportional"
			></li>
		</ul>

		<h2 class="title">{{ regenerateThumbnails.i18n.Home.commandLineInterface }}</h2>
		<p v-html="regenerateThumbnails.i18n.Home.commandLineInterfaceText"></p>

		<h2 class="title">AJAX Test</h2>
		<ul v-if="posts && posts.length">
			<li v-for="post in posts" :key="post.id">
				{{ post.title.rendered }}
			</li>
		</ul>

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
		created() {
			WPRESTAPI.get('wp/v2/posts')
				.then(response => {
					this.posts = response.data
				})
				.catch(error => {
					console.log(error)
				});
		},
		components: {
			ThumbnailSize,
		},
	}
</script>