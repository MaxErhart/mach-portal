<template>
  <div id="form">
    <h1>Form</h1>
    <NavBar :tabs="navTabs" @reload="reload($event)"/>
    <div id="form-main-content">
      <template v-if="awaitData">
        Awaiting Data
      </template>
      <template v-else>
        <IndexForms v-if="method=='index'" :data="forms" :method="method" @deleteEntry="deleteEntry($event)" :key="indexFormReload"/>
        <StoreForm v-if="method=='store' || method=='update'" :tags="tags" :form="form" :key="storeFormReload"/>
      </template>

    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import IndexForms from '@/components/forms/IndexForms.vue'
import StoreForm from '@/components/forms/StoreForm.vue'
import axios from "axios";
export default {
  name: 'CreateForms',
  components: {
    NavBar,
    IndexForms,
    StoreForm
  },
  data() {
    return {
      storeFormReload: 0,
      indexFormReload: 0,
      awaitData: false,
      fetchedAllData: false,
      forms: [],
      tags: [],
      navTabs: [
        {text: 'List Forms', route: {name: 'CreateForms'}},
        {text: 'New Form', route: {name: 'NewForm'}},
      ],     
    }
  },
  mounted() {
    this.getTags()
    if(this.method=='index') {
      this.getForms();
    } else if(this.method=='show' || this.method=='update') {
      this.getForm(this.$route.params.id);
    }    
  },
  watch: {
    $route() {
      if((this.method=='index' || this.method=='update') && !this.forms.filter(el=>{el.id==this.$route.params.id})) {
        this.getForm(this.$route.params.id);
      } else if(!this.fetchedAllForms && this.method=='index') {
        this.getForms();
      }
    }
  },  
  computed: {
    form() {
      if(this.$route.params.id && this.forms.length>0) {
        return this.forms.filter(form=>form.id==this.$route.params.id)[0]
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
    getForm(id) {
      this.awaitData = true;
      const url = `${this.apiUrl}/forms/${id}`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        console.log(response.data)
        this.forms = this.forms.concat(response.data);
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
      })
    },   
    getForms() {
      this.forms=[];
      this.fetchedAllData = true;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        console.log(response.data)
        this.forms = this.forms.concat(response.data);
        this.awaitData = false;
      }).catch(error=>{
        console.log(error.response)
      })
    },
    deleteEntry(event) {
      const url = `${this.apiUrl}/forms/${event.id}`;
      this.forms.splice(event.index, 1)
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
#form {
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
#form-main-content {
  background-color: #fff;
  width: 100%;
  box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>