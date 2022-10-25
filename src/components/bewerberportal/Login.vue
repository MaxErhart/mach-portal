<template>
  <div class="login">
    <h2>Login</h2>
    <form ref="form_login" @submit.prevent="login()">
      <!-- <InputElement :data="inputElementData_firstName" name="first_name"/> -->
      <!-- <InputElement :data="inputElementData_lastName" name="last_name"/> -->
      <InputElement :data="inputElementData_email" name="email"/>
      <InputElement :data="inputElementData_bewerbungsNummer" name="bewerbungs_nummer"/>
      <Button :loading="buttonElementData_login.loading" :disabled="buttonElementData_login.disabled" :text="buttonElementData_login.text" />
    </form>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import Button from '@/components/Button.vue'
import axios from "axios";

export default {
  name: 'Login',
  components: {
    InputElement,
    Button,
  },
  data() {
    return {
      inputElementData_bewerbungsNummer: {label: "Application Number", required: true, tooltip: null, type: "number"},
      inputElementData_firstName: {label: "First Name", required: true, tooltip: null, type: "text"},
      inputElementData_lastName: {label: "Last Name", required: true, tooltip: null, type: "text"},
      inputElementData_email: {label: "Email", required: true, tooltip: null, type: "email", autocomplete: "email"},
      buttonElementData_login: {loading: false, disabled: false, text: "Login"},
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },     
  },
  methods: {
    login() {
      const url = `${this.apiUrl}/bewerberportal/login`
      var formData = new FormData(this.$refs.form_login);   


      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        var bewerber = response.data
        bewerber["loggedin"] = true

        this.$emit('loggedin', bewerber)
        console.log(bewerber)
      }).catch(error=>{
        // console.log(error.response.status==400)

        // this.emitter.emit('showResponseMessage', {error: {status: 400, data: {message: "Application not found. The list of applications is updated every few days. Please try again later or contact the support."}}})    
        this.emitter.emit('showResponseMessage', {error: error.response})    

        console.log(error.response)
      })
    }
  }
}
</script>


<style scoped lang="scss">
.login {
  width: 100%;
  min-width: 280px;
  max-width: 210mm;  
}
</style>
