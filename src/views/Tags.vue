<template>
  <div id="tags">
    <h1>Tags</h1>
    <NavBar :tabs="navTabs" @reload="reload($event)"/>
    <div id="tags-main-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-else>
        <IndexTags v-if="method=='index'" :data="tags" :method="method" @deleteEntry="deleteEntry($event)" :key="indexReload"/>
        <StoreTag v-if="method=='store' || method=='update'" :tag="tag" :key="storeReload"/>
      </template>

    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import IndexTags from '@/components/tags/IndexTags.vue'
import StoreTag from '@/components/tags/StoreTag.vue'
import axios from "axios";
export default {
  name: 'CreateForms',
  components: {
    NavBar,
    IndexTags,
    StoreTag
  },
  data() {
    return {
      storeReload: 0,
      indexReload: 0,
      awaitData: false,
      fetchedAllData: false,
      tags: [],
      navTabs: [
        {text: 'List Tags', route: {name: 'Tags'}},
        {text: 'New Tag', route: {name: 'NewTag'}},
      ],     
    }
  },
  mounted() {
    if(this.method=='index') {
      this.getTags()
    } else if(this.method=='show' || this.method=='update') {
      this.getTag(this.$route.params.id);
    }    
  },
  watch: {
    $route() {
      if((this.method=='index' || this.method=='update') && !this.tags.filter(el=>{el.id==this.$route.params.id})) {
        this.getTag(this.$route.params.id);
      } else if(!this.fetchedAllForms && this.method=='index') {
        this.getTags();
      }
    }
  },  
  computed: {
    tag() {
      if(this.$route.params.id && this.tags.length>0) {
        return this.tags.filter(tag=>tag.id==this.$route.params.id)[0]
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
      return 'index';
    }    
  },
  methods: {
    reload(route) {
      if(route.name=='NewForm') {
        this.storeFormReload++;
      } else if(route.name=='CreateForms') {
        this.indexFormReload++;
      }
    },    
    getTag(id) {
      this.awaitData = true;
      const url = `${this.apiUrl}/tags/${id}`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.tags = this.tags.concat(response.data);
        this.awaitData = false;
      })      
    },
    getTags() {
      this.tags = []
      const url = `${this.apiUrl}/tags`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.tags = this.tags.concat(response.data);
        console.log(this.tags)
      })
    },    
    deleteEntry(event) {
      const url = `${this.apiUrl}/tags/${event.id}`;
      this.tags.splice(event.index, 1)
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
#tags {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
#tags-main-content {
  background-color: #fff;
  width: 100%;
  box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>