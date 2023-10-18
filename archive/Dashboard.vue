<template>
  <div class="dashboard">
    <h1>Dashboard</h1>
    <div class="dashboard-content">  
      <DashboardCard route="CreateForm" icon="createForm.svg" text="Create Form"/>
      <DashboardCard route="AllForms" icon="list.svg" text="View Forms"/>
    </div>
  </div>
</template>

<script>
import DashboardCard from '@/components/DashboardCard.vue'
export default {
  name: 'Dashboard',
  components: {
    DashboardCard,
  },
  data() {
    return {

    }
  },
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[2])
  },
  methods: {

  }
}
</script>

<style lang="scss" scoped>
  .dashboard {
    text-align: center;
    height: 100%;
    display: flex;
    flex-direction: column;
    width: 100%;
  }
  .dashboard-content {
    position: relative;
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    grid-auto-rows: 60px;
    grid-gap: 10px;
    height: 100%;
  }
</style>