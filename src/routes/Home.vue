<template>
	<component :is="currentView" v-on:regenerate="regenerate" :settings="settings"></component>
</template>

<script>
	import HomeIntro from './subpages/HomeIntro.vue'
	import HomeRegenerateMultiple from './subpages/HomeRegenerateMultiple.vue'

	export default {
		data() {
			return {
				currentView: HomeIntro,
				settings   : {},
			}
		},
		methods   : {
			regenerate(what) {
				if (HomeIntro === this.currentView) {
					this.settings = {
						onlyMissing: document.getElementById('regenthumbs-regenopt-onlymissing').checked,
						updatePosts: document.getElementById('regenthumbs-regenopt-updateposts').checked,
						deleteOld  : document.getElementById('regenthumbs-regenopt-deleteoldthumbnails').checked,
					};
				}

				this.settings.regenerateWhat = what;
				this.currentView = HomeRegenerateMultiple;
			},
		},
		components: {
			HomeIntro,
		},
	}
</script>
