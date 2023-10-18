<template>
  <div id="form">
    <h1>Form</h1>
    <NavBar :tabs="navTabs" @reload="reload($event)"/>
    <div id="form-main-content">
      <IndexForms @copyForm="copyForm($event)" :copyLoading="copyLoading" :deleteLoading="deleteLoading" :loading="awaitData" v-if="route=='CreateForms'" :data="forms" @uploadSubmissions="uploadSubmissions($event)" @editForm="editForm($event)" @deleteForm="deleteForm($event)" :key="indexFormReload"/>
      <StoreForm :loading="awaitData" @createTag="createTag($event)" :key="storeFormReload" @storeForm="getForms()" @updateForm="updateForm($event)" v-if="route=='NewForm'" :tags="tags" :form="form" ref="store_form"/>
      <UploadSubmissions v-if="route=='UploadSubmissions'" :form="form"/>
    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import IndexForms from '@/components/forms/IndexForms.vue'
import StoreForm from '@/components/forms/StoreForm.vue'
import UploadSubmissions from '@/components/forms/UploadSubmissions.vue'
import axios from "axios";
export default {
  name: 'CreateForms',
  components: {
    NavBar,
    IndexForms,
    StoreForm,
    UploadSubmissions
  },
  data() {
    return {
      storeFormReload: 0,
      indexFormReload: 0,
      awaitData: false,
      fetchedAllData: false,
      forms: [],
      tags: [],
      deleteLoading: false,
      copyLoading: false,
    }
  },
  mounted() {
    this.getForms();
    this.getTags();
  },
  watch: {
  },  
  computed: {
    route() {
      return this.$route.name
    },
    navTabs() {
      var tabs = [
        {id: 0, text: 'List Forms', route: {name: 'CreateForms'}},
        {id: 1, text: 'New Form', route: {name: 'NewForm'}},
      ]
      if(this.route=='UploadSubmissions') {
        tabs.push({id: 2, text: 'Upload', route: {name: 'UploadSubmissions'}})
      }
      return tabs
    },
    form() {
      if(this.$route.params.id && this.forms.length>0) {
        console.log(this.forms.filter(form=>form.id==this.$route.params.id)[0])
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
  },
  methods: {
    createTag(tag) {
      const url = `${this.apiUrl}/tags`;
      const formData = new FormData()
      formData.append('name', tag.value)
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.tags.push(response.data);
        console.log(this.tags,response.data)
        this.$refs.store_form.$refs.tags.$refs.select.awaiting = false
      }).catch(error=>{
        console.log(error)
        this.$refs.store_form.$refs.tags.$refs.select.awaiting = false
      })
    },
    updateForm(form) {
      this.forms = this.forms.map(f=>{
        if(f.id==form.id) {
          return form
        }
        return f
      })
    },
    async copyForm(id) {
      this.copyLoading = true
      const {error} = await this.$store.dispatch('_forms', {method: 'copy', form_id:id})
      console.log(error?.response)
      this.copyLoading = false
      if(!error) {
        this.refreshForms()
      }
    },
    async refreshForms() {
      this.awaitData = true
      const {forms} = await this.$store.dispatch('_forms', {method: 'get'})
      this.forms = forms
      console.log(forms)
      this.awaitData = false
    },
    reload(route) {
      if(route.name=='NewForm') {
        this.storeFormReload++;
      } else if(route.name=='CreateForms') {
        this.indexFormReload++;
      }
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
    async getForms() {
      this.$router.push({name:'CreateForms',params:{}})

      this.awaitData = true
      const {forms} = await this.$store.dispatch('_forms', {method: 'get'})
      this.forms = forms
      this.awaitData = false
    },  
    uploadSubmissions(id) {
      this.$router.push({name: 'upload_submissions', params: {id: id}})
      // this.$router.push({name: 'UploadSubmissions', params: {id: id}})
    },
    editForm(event) {
      this.$router.push({name: 'NewForm', params: {id: event}})
    },
    async deleteForm(id) {
      this.deleteLoading = true
      const {forms,error} = await this.$store.dispatch('_forms', {method:'delete', form_id:id})
      console.log(error?.response)
      this.forms = forms
      this.deleteLoading = false
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
  position: relative;
  background-color: #fff;
  width: 100%;
  box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>