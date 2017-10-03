<template>
	<li>
		<img v-if="size.fileexists" src="images/yes.png" width="16" height="16" />
		<img v-else src="images/no.png" width="16" height="16" />
		<span v-html="thumbnailText"></span>
	</li>
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
				this.size.cropMethod = (this.size.crop) ? this.i18n.common.thumbnailSizeItemCropped : this.i18n.common.thumbnailSizeItemProportional;

				return this.i18n.common.thumbnailSizeItem.formatUnicorn(this.size);
			}
		},
	}
</script>