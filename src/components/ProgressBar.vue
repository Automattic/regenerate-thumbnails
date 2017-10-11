<template>
	<div id="progressbar" class="ui-progressbar ui-widget ui-widget-content ui-corner-all" role="progressbar" aria-valuemin="0" aria-valuemax="100" :aria-valuenow="progress + '%'">
		<div class="ui-progressbar-value ui-widget-header ui-corner-left" :style="{ width: progress + '%' }"></div>
	</div>
</template>

<script>
	export default {
		props: [
			'progress',
		],
	}
</script>
