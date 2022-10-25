<template>
  <div class="lehrveranstaltungen">
    <h1>Lehrveranstaltungen </h1>
    <NavBar :tabs="navTabs"/>

    <LvLehrveranstaltungen v-if="$route.name=='LvLehrveranstaltungen'"/>
    <LvUsers v-if="$route.name=='LvUsers'"/>
    <LvExams v-if="$route.name=='LvExams'"/>

  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import LvUsers from '@/components/lehrveranstaltungen/Users.vue'
import LvLehrveranstaltungen from '@/components/lehrveranstaltungen/Lehrveranstaltungen.vue'
import LvExams from '@/components/lehrveranstaltungen/Exams.vue'
// import axios from "axios";
export default {
  name: 'Lehrveranstaltungen',
  components: {
    NavBar,
    LvUsers,
    LvLehrveranstaltungen,
    LvExams,    
  },
  data() {
    return {
      navTabs: [
        {text: 'Lehrveranstaltungen', route: {name: 'LvLehrveranstaltungen'}},
        {text: 'User', route: {name: 'LvUsers'}},
        {text: 'Exams', route: {name: 'LvExams'}},
      ],      
    }
  }
}
</script>

<style lang="scss" scoped>
.lehrveranstaltungen {
  // border: 1px solid red;
  width: 100%;
  color: #2c3e50;
  text-align: center;
}
</style>