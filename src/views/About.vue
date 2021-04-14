<template>
  <div class="about">
    <h1>About</h1>
  </div>
</template>

<script>
export default {
  name: 'Theses',
  components: {
  },
  data() {
    return {

    }
  },  
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[3])
  },  
}
</script>

<style lang="scss" scoped>
  .about {
    text-align: center;
  }
</style>