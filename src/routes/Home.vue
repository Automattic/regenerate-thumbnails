<template>
	<div>
		<p><em>DEBUG: WordPress REST API nonce is <code>{{ restAPINonce }}</code>.</em></p>
	</div>
</template>

<script>
	export default {
		data() {
			return {
				restAPINonce        : wpApiSettings.nonce,
				regenerateThumbnails: regenerateThumbnails,
			}
		}
	}
</script>