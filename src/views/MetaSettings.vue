<template>
  <div class="meta-settings">
    <div class="header">
      <NavBar :tabs="navTabs"/>
    </div>
    <div class="body">
      <Permissions v-if="route==='Permissions'"/>
      <Settings v-if="route==='MetaSettings'"/>
    </div>

  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import Settings from '@/components/metasettings/Settings.vue'
import Permissions from '@/components/metasettings/Permissions.vue'
export default {
  name: 'MetaSettings',
  components: {
    NavBar,
    Settings,
    Permissions,
  },
  data() {
    return {
      navTabs: [
        {id: 0, text: 'Settings', route: {name: 'MetaSettings'}},
        {id: 1, text: 'Permissions', route: {name: 'Permissions'}},
      ],
    }
  },
  computed: {
    route() {
      return this.$route.name
    }
  },
}
</script>

<style lang="scss" scoped>
.meta-settings {
  color: #2c3e50;
  display: flex;
  flex-direction: column;
  gap: 20px;
  align-items: stretch;
  .body {
    display: flex;
    flex-direction: column;
    align-items: center;
  }
}
</style>