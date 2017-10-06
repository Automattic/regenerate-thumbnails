<template>
	<li v-bind:class="[size.fileexists ? 'exists' : 'notexists']" v-html="thumbnailText"></li>
</template>

<script>
	require('../helpers/formatUnicorn');

	export default {
		props   : [
			'size',
			'i18n',
		],
		computed: {
			thumbnailText: function () {
				if (typeof this.size.crop !== 'undefined') {
					this.size.cropMethod = (this.size.crop) ? this.i18n.common.thumbnailSizeItemIsCropped : this.i18n.common.thumbnailSizeItemIsProportional;

					return this.i18n.common.thumbnailSizeItemWithCropMethod.formatUnicorn(this.size);
				} else {
					return this.i18n.common.thumbnailSizeItemWithoutCropMethod.formatUnicorn(this.size);
				}
			}
		},
	}
</script>