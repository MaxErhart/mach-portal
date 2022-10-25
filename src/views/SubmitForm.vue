<template>
  <div class="submit-form">
    <NavBar :tabs="navTabs" @change="updateTab($event)" ref="navBar"/>
    <div class="submit-form-main-content">
      <template v-if="awaitData">
        Awaiting Data (Try logging in or refresh the page.)
      </template>
      <template v-else>
        <Submitting v-if="tabIndex==0" :form="form" :submission="edit!=null ? submissions[edit.index] : null" @updated="updateSubmission($event)" @submitted="addSubmission($event)"/>
        <ViewSubmissions :form="form" :submissions="submissions" v-if="tabIndex==1" @editSubmission="editSubmission($event)" @deleteSubmission="deleteSubmission($event)"/>
        <FormInfo v-if="tabIndex==2"/>
      </template>

    </div>
  </div>
</template>

<script>
import NavBar from '@/components/NavBar.vue'
import Submitting from '@/components/forms/submission/Submitting.vue'
import ViewSubmissions from '@/components/forms/submission/ViewSubmissions.vue'
import FormInfo from '@/components/forms/submission/FormInfo.vue'
import axios from "axios";
export default {
  name: 'SubmitForm',
  props: {
  },
  components: {
    NavBar,
    Submitting,
    ViewSubmissions,
    FormInfo,
  },
  data() {
    return {
      form: null,
      awaitData: true,
      tabIndex: 0,
      navTabs: [
        {text: 'Submit Form', route: null},
        {text: 'Submissions', route: null},
        {text: 'Info', route: null},
      ],
      submissions: null,
      edit: null,
    }
  },
  computed: {
    apiUrl() {
      return this.$store.getters.getApiUrl;
    },
  },
  mounted() {
    if(!this.$route.params.id) {
      this.$router.push({name: 'Forms'})
    }
    if(localStorage.form) {
      const form = JSON.parse(localStorage.form)
      localStorage.removeItem('form')
      if(form.id==this.$route.params.id) {
        this.form==form
        this.awaitData = false
      }
    }
    if(this.form==null) {
      this.getForm(this.$route.params.id)
    }
    if(this.submissions==null) {
      this.getSubmissions(this.$route.params.id)
    }    
  },
  watch: {
    '$store.state.user': function() {
      if(this.form==null) {
        this.getForm(this.$route.params.id)
      }
      if(this.submissions==null) {
        this.getSubmissions(this.$route.params.id)
      }      
    }
  },
  methods: {
    encodeUrl(url){
      url = url.replaceAll(":", "%3A")
      url = url.replaceAll("/", "%2F")
      url = url.replaceAll("#", "%23")
      return url
    },
    updateSubmission(event) {
      console.log(this.submissions)
      console.log(event)
      const index = this.submissions.map(s=>s.id).indexOf(event.id)
      this.submissions[index] = event
      this.$refs.navBar.activeTab = 1
      this.tabIndex = 1
    },
    addSubmission(event) {
      if(this.submissions==null){
        this.submissions=[event]
        
      } else {
        this.submissions.push(event)
      }
      this.tabIndex = 1
    },
    editSubmission(event) {
      this.edit = {id: event.id, index: event.index}
      this.tabIndex = 0
      this.$refs.navBar.activeTab = 0
    },
    deleteSubmission(event) {
      const url = `${this.apiUrl}/submissions/${event.id}`
      
      axios({
        method: 'delete',
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        this.submissions.splice(event.index, 1)
        console.log(response.data)
      }).catch(e=>{
        console.log(e.response)
      })
    },
    updateTab(index) {
      this.tabIndex = index
    },
    getForm(id) {
      this.form = null;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms/${id}`
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.form = response.data
        this.awaitData = false;
      }).catch(error=>{
        if(error.response.status==401) {
          this.handle401()        
        }
        console.log(error.response)
      })
    },
    handle401(){
      var url = `https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=${this.encodeUrl(window.location.href)}`
      window.location.href = url;
    },
    getSubmissions(formId) {
      this.submissions = null;
      this.awaitData = true;
      const url = `${this.apiUrl}/submissions?form_id=${formId}`
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.submissions = response.data
        this.awaitData = false;
      }).catch(error=>{
        console.log(error.response)
      })   
    }
  }
}
</script>

<style lang="scss" scoped>
.submit-form {
  position: relative;
  width: 100%;
  text-align: center;
  height: 100%;
  display: flex;
  flex-direction: column;
}
.submit-form-main-content {
  position: relative;
  background-color: #fff;
  width: 100%;
  height: 100%;
  padding: 10px 0;
  // box-shadow: rgba(0, 0, 0, 0.12) 0px 1px 3px, rgba(0, 0, 0, 0.24) 0px 1px 2px;
}
</style>