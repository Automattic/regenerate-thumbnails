<template>
	<li :class="[size.fileexists ? 'exists' : 'notexists']" v-html="thumbnailText"></li>
</template>

<script>
	require('../helpers/formatUnicorn');

	export default {
		props   : [
			'size',
			'l10n',
		],
		computed: {
			thumbnailText: function () {
				// F{lename is false if the thumbnail is larger than the original
				if (this.size.filename) {
					// Crop type is undefined for unregistered sizes
					if (typeof this.size.crop !== 'undefined') {
						this.size.cropMethod = (this.size.crop) ? this.l10n.common.thumbnailSizeItemIsCropped : this.l10n.common.thumbnailSizeItemIsProportional;

						return this.l10n.common.thumbnailSizeItemWithCropMethod.formatUnicorn(this.size);
					} else {
						return this.l10n.common.thumbnailSizeItemWithoutCropMethod.formatUnicorn(this.size);
					}

				} else {
					return this.l10n.common.thumbnailSizeBiggerThanOriginal.formatUnicorn(this.size);
				}
			}
		},
	}
</script>
