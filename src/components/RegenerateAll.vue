<template>
	<div>
		<progress-bar :progress="progress"></progress-bar>
	</div>
</template>

<script>
	import {WPRESTAPI} from '../helpers/wprestapi';
	import ProgressBar from '../components/ProgressBar.vue'

	export default {
		data() {
			return {
				regenerateThumbnails: regenerateThumbnails,
				progress            : Math.floor(Math.random() * 100),
			}
		},
		created() {
			window.setInterval(myCallback, 1000, this);

			function myCallback(vue) {
				vue.progress = Math.floor(Math.random() * 100);
			}
		},
		components: {
			ProgressBar,
		},
	}
</script>

<style lang="scss" scoped>
	.ui-progressbar {
		margin-top: 20px;
	}
</style>
