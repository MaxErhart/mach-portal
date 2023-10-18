<template>
  <div id="group-app-settings">
    <h1>Group App Settings</h1>
    <NavBar :tabs="navTabs" @reload="reload($event)" :tabWidth="200"/>
    <div id="group-app-settings-main-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-else>
        <IndexGroupAppSettings :data="data" v-if="method=='show' || method=='index'" @deleteElement="deleteElement($event)" :key="indexDataReload"/>
        <StoreGroupAppSettings :entry="entry" v-if="method=='store' || (method=='update' && entry)" @updateentry="updateEntry($event)" @newentry="data.push($event)" :key="storeEntryReload"/>

      </template>

    </div>
  </div>
</template>

<script>
import IndexGroupAppSettings from '@/components/group_app_settings/IndexGroupAppSettings.vue'
import StoreGroupAppSettings from '@/components/group_app_settings/StoreGroupAppSettings.vue'
import NavBar from '@/components/NavBar.vue'
import axios from "axios";

export default {
  name: 'GroupAppSettings',
  props: {
  },
  components: {
    IndexGroupAppSettings,
    NavBar,
    StoreGroupAppSettings
  },
  data() {
    return {
      data: [],
      awaitData: false,
      navTabs: [
        {text: 'List Group App Settings', route: {name: 'GroupAppSettings'}},
        {text: 'New Group App Settings', route: {name: 'NewGroupAppSettings'}},
      ],
      fetchedAllData: false,
      indexDataReload: 0,
      storeEntryReload: 0,
    }
  },
  watch: {
    $route() {
      if((this.method=='index' || this.method=='update') && !this.data.filter(el=>{el.id==this.$route.params.id})) {
        this.getEntry(this.$route.params.id);
      } else if(!this.fetchedAllData && this.method=='index') {
        this.getData();
      }
    }
  },
  computed: {
    entry() {
      if(this.$route.params.id && this.data.length>0) {
        return this.data.filter(event=>event.id==this.$route.params.id)[0]
      } else {
        return null;
      }
    },
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
    apiAuthUrl() {
        return this.$store.getters.getApiAuthUrl;
    },
    method() {
      if(this.$route.meta.new && this.$route.params.id) {
        return 'update';
      }
      if(this.$route.meta.new) {
        return 'store';
      }      
      if(this.$route.params.id) {
        return 'show';
      }
      return 'index';
    }
  },
  mounted() {
    if(this.method=='index') {
      this.getData();
    } else if(this.method=='show' || this.method=='update') {
      this.getEntry(this.$route.params.id);
    }
  },
  methods: {
    updateEntry(event) {
      console.log(event)
      const index = this.data.indexOf(this.data.filter(e=>e.id==event.id)[0])
      this.data.splice(index, 1, event)
    },
    reload(route) {
      if(route.name=='NewGroupAppSettings') {
        this.storeEntryReload++;
      } else if(route.name=='GroupAppSettings') {
        this.indexDataReload++;
      }
    },
    getEntry(id) {
      this.awaitData = true;
      const url = `${this.apiUrl}/groupappsettings/${id}`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.data = this.data.concat(response.data);
        this.awaitData = false;
      })      
    },
    getData() {
      this.data=[];
      this.fetchedAllData = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/groupappsettings`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.data = this.data.concat(response.data);
        this.awaitData = false;
      })
    },
    deleteElement(event) {
      const url = `${this.apiUrl}/groupappsettings/${event.id}`;
      this.data.splice(event.index, 1)
      axios({
        method: 'delete',
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })      
    }
  } 
}
</script>

<style lang="scss" scoped>
#group-app-settings {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
#group-app-settings-main-content {
  background-color: #fff;
  width: 100%;
  box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>