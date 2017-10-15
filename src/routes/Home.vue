<template>
	<component :is="currentView" v-on:regenerate="regenerate"></component>
</template>

<script>
	import HomeIntro from '../components/HomeIntro.vue'
	import RegenerateAll from '../components/RegenerateAll.vue'

	export default {
		data() {
			return {
				currentView: HomeIntro,
			}
		},
		methods   : {
			regenerate() {
				this.currentView = RegenerateAll;
			},
		},
		components: {
			HomeIntro,
		},
	}
</script>
