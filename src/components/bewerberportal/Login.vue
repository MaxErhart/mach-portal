<template>
  <div class="login">
    <h2>Login</h2>
    <form ref="form_login" @submit.prevent="login()">
      <InputElement label="Email" :required="true" type="email" autocomplete="email" name="Email" tooltip="KIT application email"/>
      <InputElement label="Application Number" :required="true" name="Number" tooltip="7 digit number starting with 7"/>
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
      for (const pair of formData.entries()) {
        console.log(`${pair[0]}, ${pair[1]}`);
      }
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
      }).catch(error=>{
        this.emitter.emit('showErrorMessage', {error: error,action:'Login'})    
        console.log(error,error.response)
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
