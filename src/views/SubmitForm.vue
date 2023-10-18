<template>
  

  <div class="submit-form" v-if="form && groups">
    <NavBar @readHistory="readHistory($event)" :tabs="navTabs" ref="nav_tabs" :paginate="true"/>
    <div class="submit-form-alert-deadline" v-if="deadlinePassed(form?.deadline)">The deadline for this form has passed.</div>
    <div class="submit-form-alert-close-to-deadline" v-if="timeToDeadline(form?.deadline).show">Deadline is in {{timeToDeadline(form?.deadline).hours}} hours and {{timeToDeadline(form?.deadline).minutes}} minutes.</div>
    <div class="submit-form-main-content" :style="{display: route=='submissions' ? 'block' : 'none'}">
      <ViewSubmissions ref="view_submissions" :loading="awaitFormData||awaitSubmissionData" :optionLoading="optionLoading" :form="form" :form_and_reference_submissions="submissions" @copySubmission="copySubmission($event)" @archive="archive($event)" @dearchive="dearchive($event)" @editSubmission="editSubmission($event)" @deleteSubmission="deleteSubmission($event)"/>
    </div>
    <div class="submit-form-main-content" v-if="!(awaitFormData||awaitSubmissionData)">
      <Submitting @seen="seen($event)" :selected_organization="selected_organization" v-if="route=='submit'" :form="form" :submissions="submissions" @newReply="newReply($event)" @updateSubmission="updateSubmission($event)" @submitted="addSubmission($event)"/>
      <FormBescheide v-if="route=='bescheide'"/>
      <FormInfo v-if="route=='info'" :form="form"/>
      <UploadSubmissions :form="form" v-if="route=='upload_submissions'"/>
    </div>
    <div v-else>
      <DataPlaceholder/>
    </div>
  </div>

</template>

<script>

import DataPlaceholder from '@/components/DataPlaceholder.vue'
import NavBar from '@/components/NavBar.vue'
import Submitting from '@/components/forms/submission/Submitting.vue'
import ViewSubmissions from '@/components/forms/submission/ViewSubmissions.vue'
import FormInfo from '@/components/forms/submission/FormInfo.vue'
import FormBescheide from '@/components/forms/submission/FormBescheide.vue'
import UploadSubmissions from '@/components/forms/UploadSubmissions2.vue'
export default {
  name: 'SubmitForm',
  props: {
  },
  components: {
    DataPlaceholder,
    NavBar,
    Submitting,
    ViewSubmissions,
    FormInfo,
    FormBescheide,
    UploadSubmissions,
  },
  data() {
    return {
      form: null,
      awaitSubmissionData: false,
      awaitFormData: false,
      submissionRefresh: null,

      submissions: null,
      optionLoading: null,
      submission_highlight: null,
      pagination_history: [],
      groups: null,
      users:null,
      route_submission_id: null,

      selected_organization: null,

    }
  },
  computed: {
    route() {
      return this.$route.name
    },
    navTabs() {
      var navTabs = [
        {id: 0,text: 'Submissions', route: {name: 'submissions',params: {id:this.$route.params?.id}}},
        {id: 1, text: 'Submit Form', route: {name: 'submit',params: {id:this.$route.params?.id}}},
      ]
      if(this.form?.form_bescheid_settings.length>0) {
        navTabs = [...navTabs, {id: 2, text: 'Bescheide', route: {name: 'bescheide',params: {id:this.$route.params?.id}}}, {id: 3, text: 'Info', route: {name: 'info',params: {id:this.$route.params?.id}}}]
      } else {
      navTabs = [...navTabs, {id: 3, text: 'Info', route: {name: 'info'}}]
      }
      return navTabs
    },
  },
  async mounted() {
    this.route_submission_id = this.$route.params?.submission_id
    this.getGroups()
    this.getUsers()
    if(this.form==null) {
      this.getForm(this.$route.params.id)
    }
  },
  methods: {
  
    filterOrganization(submissions) {
      console.log(this.submissions,this.form,this.selected_organization)
      if(!this.form || !this.form.organization_proxy || this.selected_organization===null) {
        return submissions
      }
      const on_group_permissions = this.form.permissions.group.filter(permission=>permission.agent_type==="App\\Models\\Group" && permission.agent_id===this.selected_organization.id)
      const on_user_permissions = this.form.permissions.user.filter(permission=>permission.agent_type==="App\\Models\\Group" && permission.agent_id===this.selected_organization.id)
      const filtered = submissions[this.form.id].map(submission=>{
        if(submission.owner_type==="App\\Models\\User") {
          const permission = on_user_permissions.find(u_permission=>u_permission.user_id==submission.owner_id)
          var max_permission = 0
          if(permission) {
            max_permission = permission.permission
            // return submission
          }
          submission.owner.groups.forEach(group=>{
            const member_permission = on_group_permissions.find(g_permission=>g_permission.group_id==group.id)
            // console.log(member_permission, submission.owner.groups,on_group_permissions)

            if(member_permission && member_permission.permission>max_permission) {
              max_permission = member_permission.permission
            }
          })
          const member = this.selected_organization.users.find(u=>u.id===submission.owner_id)
          if(member && this.form.submissions>max_permission) {
            max_permission = this.form.submissions
          }
          if(max_permission>0) {
            submission.permission = max_permission
            return submission
          }
        } else if(submission.owner_type==="App\\Models\\Group") {
          const permission = on_group_permissions.find(g_permission=>g_permission.group_id==submission.owner_id)
          var max_permission_g = 0
          if(permission && permission>max_permission) {
            max_permission_g = permission.permission
          }
          if(this.selected_organization.id===submission.owner_id && this.form.submissions>max_permission_g) {
            max_permission_g = this.form.submissions
          }
          if(max_permission_g>0) {
            submission.permission = max_permission_g
            return submission
          }
        }
        return null
      }).filter(submission=>submission!==null)
      const ret = JSON.parse(JSON.stringify(submissions))
      ret[this.form.id] = filtered
      console.log(ret)

      return ret
    },
    async getUsers() {
      if(this.$route.meta.login!=undefined && !this.$route.meta.login) {
        return
      }
      const {users,error} = await this.$store.dispatch('users')
      console.log(error?.response)
      this.users = users
    },
    async getGroups() {
      if(this.$route.meta.login!=undefined && !this.$route.meta.login) {
        return
      }
      const {groups,error} = await this.$store.dispatch('groups')
      console.log(error?.response)
      this.groups = groups
    },
    archive({submissions,archive_groups}) {
      this.submissions = submissions
      this.form.archive_groups = archive_groups
    },
    dearchive({submissions,archive_groups}) {
      this.submissions = submissions
      this.form.archive_groups = archive_groups
    },
    timeToDeadline(date) {
      const deadline = new Date(date)
      deadline.setDate(deadline.getDate()+1)
      deadline.setHours(0)
      deadline.setMinutes(0)
      const today = new Date()
      const hours = `${Math.floor((deadline-today) /36e5)}`.padStart(2,'0')
      const minutes = `${60-today.getMinutes()}`.padStart(2,'0')
      var show = false
      if(hours>0 && hours <24) {
        show = true
      }
      return {hours,minutes,show}
    },
    deadlinePassed(date) {
      const deadline = new Date(date)
      const today = new Date()
      if((deadline.getFullYear()<today.getFullYear() || deadline.getMonth()<today.getMonth() || deadline.getDate()<today.getDate()) && date!==null) {
        return true
      }
      return false
    },
    async newReply(submissions) {
      this.submissions = submissions
      return 
    },
    async readHistory({pointer}) {
      this.submission_highlight = this.pagination_history[pointer]?.highlight
      console.log(this.pagination_history,this.$refs.nav_tabs.history)
      if(this.submission_highlight) {
        var count = 0
        while(this.$refs.view_submissions==null && count<4000) {
          count += 1
          await this.$nextTick()
        }
        this.$refs.view_submissions.highlight(this.submission_highlight)
      }
    },
    seen(event) {
      this.submissions[this.form.id] = this.submissions[this.form.id]?.map(sub=>{
        if(sub.id==event.submission.id) {
          sub.replies=event.replies
        }
        return sub
      })
    },
    encodeUrl(url){
      url = url.replaceAll(":", "%3A")
      url = url.replaceAll("/", "%2F")
      url = url.replaceAll("#", "%23")
      return url
    },
    updateSubmission(event) {
      this.submissions[this.form.id] = this.submissions[this.form.id].map(s=>{
        if(s?.id==event?.id) {
          return event
        }
        return s
      })
      this.handleSubmit(event)
    },
    addSubmission(event) {
      this.submissions[this.form.id].push(event)
      this.handleSubmit(event)
    },
    async handleSubmit(submission) {
      this.submission_highlight = submission
      this.$refs.nav_tabs.history[this.$refs.nav_tabs.history_pointer].route.params = {id:this.$route.params?.id,submission_id: `${submission.id}`}
      this.$router.push({name:'submissions',params:{id:this.$route.params?.id}})
      var count = 0
      while(this.$refs.view_submissions==null && count<2000) {
        count += 1
        await this.$nextTick()
      }
      this.$refs.view_submissions?.highlight(submission)
      this.pagination_history[this.$refs.nav_tabs.history_pointer+1] = {highlight:this.submission_highlight}
    },
    editSubmission(id) {
      this.$router.push({name: 'submit', params: {submission_id: id, copy: 0}})
    },
    copySubmission(id) {
      this.$router.push({name: 'submit', params: {submission_id: id, copy: 1}})
    },
    async deleteSubmission(id) {
      this.optionLoading = 1
      const {submissions,error} = await this.$store.dispatch('_submissions',{method:'delete',submission_id:id,form_id:this.form.id})
      console.log(error?.response)
      console.log(this.submissions, submissions)
      this.submissions[this.form.id]=submissions
      this.optionLoading = null
    },
    async getForm(id) {
      this.awaitFormData = true
      const {form,submissions,error} = await this.$store.dispatch('_submissions', {form_id:id})
      // console.log(form,submissions)
      this.form = form
      this.submissions=submissions
      const action = `Get form with id=${id}`
      this.handleError(error,action)
      this.awaitFormData = false
    },
    handleError(error, action) {
      if(!error?.response) {
        return
      }
      if(error?.response.status===401) {
        this.emitter.emit('showErrorMessage', {error: error.response, action: action, redirect: this.redirectHome()})
      } else if(error?.response.status===403) {
        this.emitter.emit('showErrorMessage', {error: error.response, action: action, redirect: this.redirectLogin()})
      } else {
        this.emitter.emit('showErrorMessage', {error: error.response, action: action, redirect: null})
      }
    },
    redirectLogin(){
      var url = `https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=${this.encodeUrl(window.location.href)}`
      return url
    },
    redirectHome() {
      return {name:'Home'}
    },
  }
}
</script>

<style lang="scss" scoped>
.submit-form-alert-deadline {
  text-align: left;
  color: #dc3545;
}
.submit-form-alert-close-to-deadline{
  text-align: left;
  color: #ffc107;
}
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
  // background-color: #fff;
  width: 100%;
  height: 100%;
}
</style>