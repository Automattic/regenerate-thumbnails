<template>
	<p class="red">{{ msg }}</p>
</template>

<script>
export default {
  name: 'app',
  data () {
	return {
	  msg: 'Hello world!'
	}
  }
}
</script>

<style lang="scss">
.red {
  color: red;
}
</style>
