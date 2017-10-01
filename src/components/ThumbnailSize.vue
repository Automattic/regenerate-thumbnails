<template>
	<li v-html="thumbnail"></li>
</template>

<script>
	require('../helpers/formatUnicorn');

	export default {
		props   : [
			'size',
			'text',
			'textCropped',
			'textProportional',
		],
		computed: {
			thumbnail: function () {
				this.size.cropMethod = (this.size.crop) ? this.textCropped : this.textProportional;

				return this.text.formatUnicorn(this.size);
			}
		},
	}
</script>