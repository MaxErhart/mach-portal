<template>
  <div class="home">
    <h1>Home of </h1>
  </div>
</template>

<script>
// import axios from "axios";
export default {
  name: 'Home',
  components: {
  },
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[0])   
  },  
}
</script>

<style lang="scss" scoped>
  .home {
    color: #2c3e50;
    text-align: center;
  }
</style>