<template>
	<div>
		<p><em>DEBUG: WordPress REST API nonce is <code>{{ restAPINonce }}</code>.</em></p>

		<p v-if="!regenerateThumbnails.data.resizingSupported">{{ regenerateThumbnails.i18n.resizingNotSupported }}</p>
	</div>
</template>

<script>
	export default {
		name: 'app',
		data() {
			return {
				restAPINonce        : wpApiSettings.nonce,
				regenerateThumbnails: regenerateThumbnails,
			}
		}
	}
</script>

<style lang="scss">

</style>