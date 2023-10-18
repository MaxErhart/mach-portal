<template>
  <div id="apps">
    <h1>Apps</h1>
    <NavBar :tabs="navTabs" @reload="reload($event)"/>
    <div id="apps-main-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-else>
        <IndexApps :apps="apps" :method="method" v-if="method=='show' || method=='index'" @deleteElement="deleteElement($event)" :key="indexAppsReload"/>
        <StoreApp :app="app" v-if="method=='store' || method=='update' && app" @editApp="editApp($event)" @newApp="apps.push($event)" :key="storeAppReload"/>
      </template>

    </div>
  </div>
</template>

<script>
import IndexApps from '@/components/apps/IndexApps.vue'
import StoreApp from '@/components/apps/StoreApp.vue'
import NavBar from '@/components/NavBar.vue'
import axios from "axios";

export default {
  name: 'Apps',
  props: {
  },
  components: {
    IndexApps,
    StoreApp,
    NavBar
  },
  data() {
    return {
      apps: [],
      awaitData: false,
      navTabs: [
        {text: 'List Apps', route: {name: 'Apps'}},
        {text: 'New App', route: {name: 'NewApp'}},
      ],
      fetchedAllApps: false,
      indexAppsReload: 0,
      storeAppReload: 0,
    }
  },
  watch: {
    $route() {
      if((this.method=='index' || this.method=='update') && !this.apps.filter(el=>{el.id==this.$route.params.id})) {
        this.getApp(this.$route.params.id);
      } else if(!this.fetchedAllApps && this.method=='index') {
        this.getApps();
      }
    }
  },
  computed: {
    app() {
      if(this.$route.params.id && this.apps.length>0) {
        return this.apps.filter(app=>app.id==this.$route.params.id)[0]
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
      this.getApps();
    } else if(this.method=='show' || this.method=='update') {
      this.getApp(this.$route.params.id);
    }
  },
  methods: {
    editApp(event) {
      const index = this.apps.indexOf(this.apps.filter(a=>a.id==event.id)[0])
      this.apps[index]=event.data
    },
    reload(route) {
      if(route.name=='NewApp') {
        this.storeAppReload++;
      } else if(route.name=='Apps') {
        this.indexAppsReload++;
      }
    },
    getApp(id) {
      this.awaitData = true;
      const url = `${this.apiUrl}/apps/${id}`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.apps = this.apps.concat(response.data);
        this.awaitData = false;
      })      
    },
    getApps() {
      this.apps=[];
      this.fetchedAllApps = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/apps`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.apps = this.apps.concat(response.data);
        this.awaitData = false;
      })
    },
    deleteElement(event) {
      const url = `${this.apiUrl}/apps/${event.id}`;
      this.apps.splice(event.index, 1)
      axios({
        method: 'delete',
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(e=>{
        console.log(e)
      })    
    },
  } 
}
</script>

<style lang="scss" scoped>
#apps {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
#apps-main-content {
  background-color: #fff;
  width: 100%;
  box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>