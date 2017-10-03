<template>
	<li>
		<img v-if="size.fileexists" src="images/yes.png" width="16" height="16" />
		<img v-else src="images/no.png" width="16" height="16" />
		{{ size.label}} ({{ size.width }}&#215;{{ size.height }} pixels)
	</li>
</template>

<script>
	export default {
		props: [
			'size',
		],
	}
</script>