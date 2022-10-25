<template>
  <div class="register-entrance-exam" :style="style_registrationStatus">
    <h4 class="title" :style="style_title_registrationStatus">
      Registration for the entrance examination of {{bewerber.entrance_exam.term}} term {{bewerber.entrance_exam.year}}
    </h4>
    <h4>Deadline: {{bewerber.entrance_exam.deadline}}</h4>
    <div></div>
    <div class="body">
      <template v-if="bewerber.status==='Rejected for the time being'">
        <div v-if="!registered">You are not registered for the entrance examination.</div>
        <div v-if="!registered">By registrating for the entrance examination you apply for admission to the entrance examination.</div>
        <div v-if="registered">You are registered for the entrance examination.</div>      
        <div v-if="registered">The eligibility of your registration will be checked.</div>
        <Button v-if="!registered" @click="register(true)" :loading="buttonElementData_register.loading" :disabled="buttonElementData_register.disabled" :text="buttonElementData_register.text" />
        <Button v-if="registered" @click="register(false)" :loading="buttonElementData_unregister.loading" :disabled="buttonElementData_unregister.disabled" :text="buttonElementData_unregister.text" />        
      </template>
      <template v-else>Note: Only applicants with the application status "Rejected for the time being" can register for the entrance examination.</template>


    </div>
  </div>
</template>

<script>
import Button from '@/components/Button.vue'
import axios from "axios";
export default {
  name: 'RegisterEntranceExam',
  props: {
    bewerber: Object,
  },
  components: {
    Button
  },
  data() {
    return {
    }
  },
  computed: {
    registered() {
      return this.bewerber.entrance_exam_registered
    },
    buttonElementData_register() {
      return {loading: false, disabled: false, text: "Register"}
    },
    buttonElementData_unregister() {
      return {loading: false, disabled: false, text: "Cancel Registration"}
    },    
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },     
    style_registrationStatus() {
      var color = 'gray'
      if(this.registered) {
        color = 'green'
      }
      return {
        'border': `2px solid ${color}`
      }
    },
    style_title_registrationStatus() {
      var color = 'gray'
      if(this.registered) {
        color = 'green'
      }
      return {
        'text-decoration': `underline`,
        'text-decoration-color': color,
      }
    },    
  },
  methods: {
    register(register) {

      const url = `${this.apiUrl}/bewerberportal/register`
      var formData = new FormData();
      var bewerber = JSON.parse(localStorage.bewerberportal)
      formData.append('first_name', bewerber.Vorname)
      formData.append('last_name', bewerber.Name)
      formData.append('bewerbungs_nummer', bewerber['Bewerbungs-nummer'])
      formData.append('register', register ? "1" : "0")

      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then((response)=>{
        this.$emit('register', response.data.entrance_exam_registered)
      }).catch(error=>{
        console.log(error.response)
      })
    }
  },

}
</script>


<style scoped lang="scss">
.register-entrance-exam {
  border-radius: 6px;
  margin: 5px;
  display: flex;
  flex-direction: column;
  align-items: center;
  // > .title {
  //   padding: 5px;
  //   text-align: center;
  //   width: 100%;
  //   font-size: 1.2em;
  // }
  > .body {
    padding: 5px;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
}
</style>
