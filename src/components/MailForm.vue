<template>
  <form class="mail-form" ref="mail_form" @submit.prevent="send()">
    <InputElement label="Set from email address" type="email" tooltip="By default mails will be send from: portal@mach.kit.edu with alias MACH-Portal" name="mail_from"/>
    <InputElement label="Set from email alias" type="text" name="mail_from_alias"/>
    <!-- <MultiInputElement label="Add cc" name="cc"/> -->
    <InputElement label="Email Subject" :required="true" name="mail_subject"/>
    <Textarea label="Email Body" name="mail_body"/>
    <FileUpload @fileChange="attachments=$event"/>
    <Button text="Send Mail" ref="submit" :loading="submit_loading"/>
  </form>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
// import MultiInputElement from '@/components/inputs/MultiInputElement.vue'
import Textarea from '@/components/inputs/Textarea.vue'
import FileUpload from '@/components/inputs_23/FileUpload.vue'
import Button from '@/components/Button.vue'
import axios from "axios";


export default {
  name: 'MailForm',
  components: {
    InputElement,
    // MultiInputElement,
    FileUpload,
    Textarea,
    Button,
  },
  props: {
    users: Array,
  },
  data() {
    return {
      attachments: null,
      submit_loading: false,
    }
  },
  methods: {
    send() {
      this.submit_loading = true
      const url = `${this.$store.getters.getApiUrl}/email`
      var formData = new FormData(this.$refs.mail_form);  
      formData.append('users', JSON.stringify(this.users))
      for(var i=0; i<this.attachments?.length; i++) {
        formData.append('attachments[]', this.attachments[i])
      }
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        this.submit_loading = false
        this.$refs.submit.success = true
        console.log(response.data)
      }).catch(error=>{
        this.submit_loading = false
        this.$refs.submit.error = true
        console.log(error.response)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
.mail-form {
  width: 100%;
}
</style>