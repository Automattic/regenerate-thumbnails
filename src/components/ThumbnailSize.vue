<template>
	<li v-html="magicText"></li>
</template>

<script>
	require('../formatUnicorn.js');

	export default {
		props   : [
			'size',
			'text',
			'textCropped',
			'textProportional',
		],
		computed: {
			magicText: function () {
				this.size.cropMethod = (this.size.crop) ? this.textCropped : this.textProportional;

				return this.text.formatUnicorn(this.size);
			}
		}
	}
</script>