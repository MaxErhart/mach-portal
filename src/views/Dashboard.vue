<template>
  <div class="dashboard">
    <UserActivity/>
  </div>
</template>

<script>
import UserActivity from '@/components/dashboard/UserActivity.vue'
export default {
  name: 'Dashboard',
  components: {
    UserActivity,
  },
  data() {
    return {
    }
  },
  computed: {

  },
  mounted() {

  },
  methods: {
    test() {
    }
  }
}
</script>

<style scoped lang="scss">

  
</style>