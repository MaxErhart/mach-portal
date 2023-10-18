<template>
  <div class="permissions">
    <form class="permissions-form" ref="form" @submit.prevent="submit()">
      <SelectElement @selectedEntry="selectGroup($event)" :data="groups" :search="true" name="group" :label="group_selectElement.label" :required="group_selectElement.required"/>
      <SelectElement @selectedEntry="selectApp($event)" :search="true" :data="filteredApps" :label="app_selectElement.label" :required="app_selectElement.required"/>
      <div id="display-selected-apps">
        <div class="selected-app" v-for="(app, index) in selectedApps" :key="app">
          <span>{{app.name}}</span>
          <button @click="removeApp(index)">
            <svg width="16" height="16" viewBox="0 0 16 16"><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
          </button>
        </div>
      </div>
      <Button text="Submit"/>
    </form>
  </div>
</template>

<script>
import SelectElement from '../components/inputs/SelectElement.vue'
import Button from '../components/Button.vue'
import axios from "axios";
export default {
  name: 'Home',
  components: {
    SelectElement,
    Button,
  },
  data() {
    return {
      groupsFetched: false,
      groups: null,
      group_selectElement: {label: 'Select Group', required: true},
      app_selectElement: {label: 'Select App', required: false},

      appsFetched: false,
      apps: null,
      selectedApps: [],
    }
  },
  mounted() {
    this.getGroups()
    this.getApps()
  },
  computed: {
    filteredApps() {
      if(!this.apps) {
        return []
      }
      return this.apps.filter(app=>!this.selectedApps.map(app=>app.id).includes(app.id))
    }
  },
  methods: {
    selectGroup(group) {
      this.selectedApps=group.app_permissions
    },
    removeApp(index) {
      this.selectedApps.splice(index, 1)
    },
    selectApp(app) {
      this.selectedApps.push(app)
    },
    submit() {
      const url = `${this.$store.getters.getApiUrl}/permissions`
      var formData = new FormData(this.$refs.form);
      formData.append('apps', JSON.stringify(this.selectedApps.map(app=>app.id)))
      for (const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        console.log(response.data)
      }).catch(e=>{
        console.log(e.response)
      })
    },
    getApps() {
      const url = `${this.$store.getters.getApiUrl}/apps`

      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        console.log(response.data)
        this.apps = response.data
        this.appsFetched = true
      }).catch(error=>{
        console.log(error.response)
      })
    },
    async getGroups() {
      const {groups} = await this.$store.dispatch('groups')
      this.groupsFetched = true
      this.groups = groups
    },
  },
}
</script>

<style lang="scss" scoped>

#display-selected-apps {
  width: 100%;
  border: 1px solid black;
  // border-top: none;
  border-radius: 2px;
  height: 240px;
  overflow-y: scroll;
  margin: -15px 0 4px 0;
  > .selected-app {
    background-color: #f0f0f0;
    height: 30px;
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
    color: #2c3e50;
    padding: 0 12px;
    button {
      border: none;
      background: none;
      margin-left: auto;
      &:hover {
        cursor: pointer;
        stroke: red;
        fill: red;
      }
    }
    &:hover {
      background-color: #2c3e50;
      color: white;
      fill: white;
      stroke: white;
    }
  }
}
</style>