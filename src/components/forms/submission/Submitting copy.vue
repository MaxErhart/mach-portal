<template>
  <div class="submitting" v-if="form">
    <div class="submitting-body">
      <h3 class="section-header">{{form.name}}</h3>
      <form class="form" @submit.prevent="update ? postUpdate($event) : submit($event)" ref="form">
        <section class="form-element-section" >
          <component :form="form" v-for="el in form_elements" :key="el.id" :id="el.id" :readonly="submitReadonly" v-on="el.component=='FileUploadElement'?{fileChange: ($event)=>setFormFile($event, el.id)}:{}" :ref="`form_element_${el.id}`" :is="el.component" :presetValue="presetValue(el)" :submissions="submissions" v-bind="el.data" :name="String(el.id)"/>
        </section>
        <section id="submit-form">
          <Button :stretch="false" ref="submit" :loading="submitLoading" :disabled="submitDisabled || submitReadonly" :text="update  ? 'Update' : copy ? 'Copy' : 'Submit'" />
        </section>
        <section class="owner-section" v-if="!is_public">
          <div class="select-wrapper"  v-if="show_owner_select">
            <SelectElement :valueCast="ownerValueCast" name="submission_owner" :presetValue="current_owner_option_id" label="Select owner of the submission" :data="owners" :search="true"/>
          </div>
          <button class="open-owner-select" @click.prevent="toggleOwnerSelect()" :class="{active:show_owner_select}">
            <ion-icon name="chevron-down-outline"></ion-icon>
          </button>
        </section>
      </form>

      <template v-if="submission && update && !copy">

        <h3 ref="submission_replies" class="section-header" v-if="submission.replies && submission.replies.length>0">List of Replies</h3>
        <div class="submission-replies" v-if="submission.replies && submission.replies.length>0">
          <div :ref="`replyid${reply.id}`" v-for="(reply,index) in submission.replies.slice().reverse()" :key="reply">
            <Reply :id="`replyid${reply.id}`" :index="submission.replies.length-index" :reply="reply" />
          </div>
        </div>

        <h3 class="section-header">Reply to Submission</h3>
        <form @submit.prevent="submitReply()" ref="replyForm" class="reply-form">
          <InputElement label="Subject" name="reply_subject" :required="true" type="text" ref="reply_subject"/>
          <label for="reply_content" class="reply-content-label">Content</label>
          <textarea id="reply_content" name="reply_content" rows="10" class="reply-content" ref="reply_body"/>
          <FileUpload label="Attachments" @fileChange="replyAttachments=$event"  ref="reply_attachments" name="reply_attachments"/>
          <Checkbox :label="`Also send email with contents of the reply to the submission owner / members of: ${submission.owner.name}`" :presetValue="false" name="send_mail"/>
          <Button ref="reply_submit_button" :loading="replySubmitLoading" :disabled="replySubmitLoading" text="Reply to Submission" />
        </form>
      </template>
    </div>
    <div class="submitting-footer"></div>
  </div>
</template>


<script>
import Button from '@/components/Button.vue'
import Reply from '@/components/forms/submission/Reply.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import MultiSelectElement from '@/components/inputs/MultiSelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import HeaderElement from '@/components/inputs/HeaderElement.vue'
import SectionElement from '@/components/inputs/SectionElement.vue'
import FileUpload from '@/components/inputs_23/FileUpload.vue'
import SelectReferenceElement from '@/components/inputs/SelectReferenceElement.vue'
import DoubleInputElement from '@/components/inputs/DoubleInputElement.vue'
import LinkElement from '@/components/inputs/LinkElement.vue'
import HTMLElement from '@/components/inputs/HTMLElement.vue'
import axios from "axios";
export default {
  name: 'Submitting',
  components: {
    Reply,
    DoubleInputElement,
    Button,
    InputElement,
    MultiSelectElement,
    Checkbox,
    SelectElement,
    HeaderElement,
    SectionElement,
    FileUpload,
    SelectReferenceElement,
    LinkElement,
    HTMLElement,
  },
  props: {
    form: Object,
    submissions: [Array,Object],
  },
  data() {
    return {
      formFiles: null,
      replyAttachments: null,
      submitLoading: false,
      submitDisabled: false,
      replySubmitLoading: false,
      count: 0,
      users:null,
      groups:null,
      selected_owner:null,
      show_owner_select:false,
    }
  },
  mounted() {
    this.getUsers()
    this.getGroups()
    if(this.submission && this.update) {
      this.postSeen()
    }
    if(this.$route.hash) {
      this.scrollToElement(this.$route.hash.substring(1))
    }
  },
  watch: {
  },
  computed: {
    is_public() {
      const fragments = window.location.href.split("/")
      if(fragments.indexOf('#')+1==fragments.indexOf('public')) {
        return true
      }
      return false
    },
    form_elements() {
      const elements = []
      if(this.form?.form_elements?.[this.form.id]) {
        Object.keys(this.form.form_elements[this.form.id]).forEach(el_id=>{
          const form_element = this.form.form_elements[this.form.id][el_id]
          if(form_element.form_id!=this.form.id) {
            return
          }
          elements.push(form_element)
        })
      }
      elements.sort((a,b)=> {
        return a.position-b.position
      })
      return elements
    },
    submission() {
      return this.submissions?.[this.form.id]?.find(submission=>submission.id==this.$route.params?.submission_id)
    },
    current_owner_option_id() {
      if(this.selected_owner===null) {
        if(this.submission?.owner_type==='App\\Models\\User' && (this.update || this.copy)) {
          return `user.${this.submission.owner_id}`
        } else if(this.submission?.owner_type==='App\\Models\\Group'  && (this.update || this.copy)) {
          return `group.${this.submission.owner_id}`
        } else {
          return `user.${this.user.id}`
        }
      }
      return this.selected_owner.id
    },
    user() {
      return this.$store.getters.getProfile
    },
    owners() {
      if(this.users===null||this.groups===null) {
        return []
      }
      var owners = {}
      Object.keys(this.users).forEach(key=>{
        owners[`user.${this.users[key].id}`] = this.users[key].name
      })
      Object.keys(this.groups).forEach(key=>{
         owners[`group.${this.groups[key].id}`] = this.groups[key].name
      })
      return owners
    },
    submitReadonly() {
      if(this.form===null) {
        return true
      }
      if(this.update && this.submission?.permission>=2) {
        return false
      }
      if(this.form.submit_permission<2) {
        return true
      }
      return false
    },
    copy() {
      if(!+this.$route.params?.copy) {
        return false
      }
      return true
    },
    update() {
      if(this.$route.params?.submission_id && !this.copy) {
        return true
      } else {
        return false
      }
    },    
  },  
  methods: {
    ownerValueCast(option) {
      const type = option.id.split('.')[0]
      const id = option.id.split('.')[1]
      if(type=='user') {
        return JSON.stringify({type:'App\\Models\\User',id:id})
      } else {
        return JSON.stringify({type:'App\\Models\\Group',id:id})
      }
    },
    toggleOwnerSelect() {
      this.show_owner_select = !this.show_owner_select
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
    scrollToElement(id) {
      if(this.$refs[id]) {
        const element = this.$refs[id]
        element.scrollIntoView({ behavior: "smooth", block: "start" });
        window.scrollTo({ top: window.scrollY - 45, behavior: 'smooth' });
      } else if(this.count>100) {
        return
      } else {
        this.count += 1
        setTimeout(()=>{
          this.$nextTick(()=>{
              this.scrollToElement(id)
          })
        },100)
      }
    },
    setFormFile(files, element_id) {
      if(!this.formFiles) {
        this.formFiles = {}
      }
      this.formFiles[element_id] = files
    },
    postSeen() {
      const url = `${this.$store.getters.getApiUrl}/seen/${this.submission.id}`
      axios({
        method: 'post',
        url: url,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        this.$emit('seen', {submission: this.submission, replies: response.data})
      }).catch(e=>{
        console.log(e.response)
      })
    },
    async submitReply() {
      this.replySubmitLoading = true
      var formData = new FormData(this.$refs.replyForm);   
      formData.append("submission_id", this.submission.id)
      for(var i=0; i<this.replyAttachments?.length; i++) {
        formData.append('reply_attachments[]', this.replyAttachments[i])
      }
      const {submissions, error} = await this.$store.dispatch('_replies', {method: 'post',form_id:this.submission.form_id, submission_id: this.submission.id, formData})
      console.log(submissions,error?.response)
      this.$emit('newReply', submissions)
      this.$refs.reply_body.value = ''
      this.$refs.reply_subject.clear()
      this.$refs.reply_attachments.clear()
      this.$refs.submission_replies?.scrollIntoView({behavior:"smooth"})
      this.replySubmitLoading = false
    },
    handleError(error, action) {
      this.$refs.submit.error = true
      if(error?.response?.status==400) {
        if(error.response.data.type==0) {
          const lowest = {pos:null,el_id:null}
          error.response.data.elements.forEach(el=>{
            if(!el) {
              return
            }
            this.$refs[`form_element_${el.id}`].customError = {message: el.message}
            this.$refs[`form_element_${el.id}`].deFocusedOnce = true
            this.$refs.submit.errorMessage = error.response.data.message
            const ref_el = Object.keys(this.form.form_elements[this.form.id]).find(el_id=>el_id==el.id)
            if(lowest.pos===null) {
              lowest.pos = ref_el.position
              lowest.el_id=el.id
            } else if(lowest.pos>ref_el.position) {
              lowest.pos = ref_el.position
              lowest.el_id=el.id
            }
          })
          const el_one_up = Object.keys(this.form.form_elements[this.form.id]).find(el_id=>this.form.form_elements[this.form.id][el_id].position+1===lowest.pos)
          if(el_one_up) {
            var ret = this.getInputOfRef(this.$refs[`form_element_${el_one_up.id}`].$refs)
            ret.scrollIntoView({behavior:"smooth"})
          } else {
            this.$refs.form.scrollIntoView({behavior:"smooth"})
          }
        } else if(error.response.data.type==1) {
          this.$refs.submit.errorMessage = error.data.message
        }
      } else {
        this.emitter.emit('showErrorMessage', {error: error, action: action, redirect: null})
      }
    },
    parseBoolean(string) {
      if(typeof string!='string') {
        return string
      }
      switch(string?.toLowerCase()?.trim()){
          case "true": 
          case "yes": 
          case "1": 
            return true;

          case "false": 
          case "no": 
          case "0": 
          case null: 
          case undefined:
            return false;

          default: 
            return JSON.parse(string);
      }
    },
    presetValue(el) {
      if(!this.update && !this.copy) {
        return null
      }
      if(this.submission==null){
        return null
      }
      return this.submission._data[el.id]
    },
    async postUpdate() {
      var formData = new FormData(this.$refs.form);   
      formData.append("form_id", this.$route.params.id)
      for (const value of formData.values()) {
        console.log(value);
      }
      this.submitLoading = true
      const {submission, error} = await this.$store.dispatch('_submissions', {method: 'update', submission_id: this.submission.id,formData, form_id:this.$route.params.id})
      this.submitLoading = false
      console.log(submission,error?.response)
      if(error) {
        this.handleError(error, `Submit form with id=${this.form.id}`)
        return
      }
      this.handleSuccess(submission, "updateSubmission")
    
    },
    handleSuccess(data, emit) {
      this.$refs.submit.success = true
      this.$emit(emit, data)
      this.form_elements.forEach(el=>{
        if(!el.input) {
          return
        }
        console.log(el)
        this.$refs[`form_element_${el.id}`].clear()
      })
    },
    isDOMElement(input) {
      return input instanceof Element
    },
    getInputOfRef(ref) {
      var input = ref
      while(!(input instanceof Element)) {
        input = ref.input
      }
      return input
    },
    async submit() {
      this.submitLoading = true
      var formData = new FormData(this.$refs.form);
      const form_id = this.$route.params.id
      this.form_elements.forEach(el=>{
        if(el.component=="FileUploadElement" && this.formFiles && el.id in this.formFiles) {
          for(var i=0; i<this.formFiles[el.id].length; i++) {
            formData.append(`${el.id}[]`, this.formFiles[el.id][i]['file'])
          }
        }
        this.$refs[`form_element_${el.id}`].deFocusedOnce=true
      })
      const {submission, error} = await this.$store.dispatch('_submissions', {method:'post',form_id,formData,anon:this.is_public})
      console.log(submission)
      this.submitLoading = false
      if(error) {
        this.handleError(error, `Submit form with id=${this.form.id}`)
        return
      }
      this.handleSuccess(submission, 'submitted')
    },
  },
}
</script>


<style scoped lang="scss">
.owner-section {
  .select-wrapper {
    margin: 16px 0;
  } 
}
.open-owner-select{
  position: absolute;
  bottom: 0;
  left: 0;
  width:100%;
  max-width: 210mm;
  display: flex;
  justify-content: center;
  align-items: center;
  cursor: pointer;
  padding: 4px 0;
  background: linear-gradient(0deg, rgba(218,218,218,1) 0%, rgba(255,255,255,0) 100%);
  &:hover {
    background: linear-gradient(0deg, rgba(156,156,156,1) 0%, rgba(255,255,255,0) 100%);
  }
  &.active {
    > * {
      transform: rotate(180deg);
    }
  }
}
.submission-replies {
  > * {
    max-width: 210mm;
    width: 100%;
  }
  width: 100%;
  border-radius: 4px;
  display: flex;
  flex-direction: column;
  align-items: center;
  justify-content: center;
  gap: 20px 20px;
}
.reply-content-label {
  display: flex;
  width: 100%;
}
.reply-form {
  width: 100%;
  max-width: 210mm;
  border: 1px solid rgba(0, 0, 0, 0.6);
  padding: 20px;
  border-radius: 4px;
}
.reply-content {
  width: 100%;
}
h1 {
  border: none;
  padding: none;
  margin: 0px;
}
.submitting {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}
.section-header {
  max-width: 210mm;
  margin: 40px 0 6px 0;
  width: 100%;
}
.submitting-body {
  margin: 0 0 210px 0;
  display: flex;
  flex-direction: column;
  align-items: center;
  // padding: 10px 10px 60px 10px;
}
.form {
  position: relative;
  border: 1px solid rgba(0, 0, 0, 0.6);
  border-radius: 4px;
  padding: 20px 20px 20px 20px;
  width: 100%;
  max-width: 210mm;
  display: flex;
  flex-direction: column;
  gap:16px;
}
</style>
