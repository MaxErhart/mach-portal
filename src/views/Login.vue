<template>
  <div class="login">
    {{returnRoute}}
  </div>
</template>

<script>
// import axios from "axios";
export default {
  name: 'Login',
  components: {
  },
  computed: {
    returnRoute() {
      return this.$route.params.returnRoute
    }
  }
}
</script>

<style lang="scss" scoped>

</style>