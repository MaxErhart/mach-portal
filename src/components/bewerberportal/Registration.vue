<template>
  <div class="registration">
    <div class="reg-header">
    </div>
    <div class="reg-body">
      <form @submit.prevent="submit()" class="form" ref="form">
        <h2 class="form-title">Registration for the entrance examination of summer term 2022 is closed</h2>


       

       


        

        

               

        

                                            
      </form>
    </div>
    <div class="reg-footer"></div>
  </div>
</template>

<script>
import Button from '@/components/Button.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import * as bewerberportal_settings from '@/bewerberportal_settings.json'
import * as countries from '@/components/inputs/countries.json'
import axios from "axios";
export default {
  name: 'Registration',
  components: {
    InputElement,
    SelectElement,
    Checkbox,
    Button,
  },
  data() {
    return {
      bewerberportal_settings,
      countries,
      sex: {"0": "male", "1": "female", "2": "diverse", label: 'Gender', numoptions: 3, placeholder: "", required: true},
      fnameinput: {label: 'First name', type: 'text', required: true, autocomplete: 'first-name'},
      lnameinput: {label: 'Family name', type: 'text', required: true, autocomplete: 'family-name'},
      emailinput: {label: 'Email', type: 'email', required: true, autocomplete: 'email'},
      application_number: {label: 'Application number', type: 'number', required: true},
      applicant_number: {label: 'Applicant number', type: 'number', required: true},
      degree: {label: 'Degree', type: 'text', required: true, '0': 'Master of Science Mechanical Engineering', '1': 'Master of Science Materials Science and Technology'},
      birthinput: {label: 'Date of Birth', type: 'date', placeholder: ' ', required: true, autocomplete: 'bday'},
      street: {label: 'Street, street number', type: 'text', placeholder: '', required: true, autocomplete: 'street-address'},
      // streetNo: {label: 'Street number', type: 'text', placeholder: '', required: true},
      zipcode: {label: 'Zipcode', type: 'text', placeholder: '', required: true, autocomplete: 'postal-code'},
      city: {label: 'City', type: 'text', placeholder: '', required: true, autocomplete: 'address-level2'},
      consentExam: {label: 'I hereby apply for admission to the entrance examination in the above selected course of study on the published date.', required: true},
      consentDataStorage: {label: 'I voluntarily consent to the storage and processing of my personal data for the purpose of the application procedure at the KIT Department of Mechanical Engineering and to the forwarding of this data to the admissions committee until revocation.', required: true},
      consentPrevExam: {label: 'I further declare that I have not yet participated or have participated only once in an entrance examination at KIT in the above-mentioned course of study. Note that the entrance examinations for summer term 2021 and winter term 2021/22 will not be considered.', required: true},
      
      formElements: ['consentExam', 'consentDataStorage', 'consentPrevExam', 'sex', 'fnameinput', 'lnameinput', 'emailinput', 'application_number', 'applicant_number', 'degree', 'birthinput', 'street', 'zipcode', 'city', 'country'],
      // formElements: ['consentExam', 'consentDataStorage', 'consentPrevExam', 'sex', 'fnameinput', 'lnameinput', 'emailinput', 'application_number', 'applicant_number', 'degree', 'birthinput', 'street', 'streetNo', 'zipcode', 'city', 'country'],
      submitLoading: false,
      submitDisabled: false,
    }
  },
  computed: {
    country() {
      var base = {label: 'Country', placeholder: "type to search and select", required: true, autocomplete: 'nope'}
      base["numoptions"] = this.countries.length
      this.countries.default.forEach((c, index)=>{
        base[`${index}`] = `${c.name} (${c.code})`
      })
      return base
    },
    customValidApplication() {
      return this.bewerberportal_settings.default.valid_application_numbers.filter(e=>e.year==new Date().getFullYear())[0]
    },
    customValidApplicant() {
      return this.bewerberportal_settings.default.valid_applicant_numbers.filter(e=>e.year==new Date().getFullYear())[0]
    },    
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },    
  },

  methods: {
    clearFormInputs() {
      for(let i=0; i<this.formElements.length; i++) {
        if (this.$refs[this.formElements[i]].value !== undefined) {
          this.$refs[this.formElements[i]].value=null
          this.$refs[this.formElements[i]].deFocusedOnce=false
        }
        if (this.$refs[this.formElements[i]].selected !== undefined) {
          this.$refs[this.formElements[i]].selected=null
          this.$refs[this.formElements[i]].deFocusedOnce=false
        }
        if (this.$refs[this.formElements[i]].checked !== undefined) {
          this.$refs[this.formElements[i]].checked=false
          this.$refs[this.formElements[i]].deFocusedOnce=false
        }               
      } 
    },
    clearButtonError() {
      this.$refs.button.error = false
    },
    submit() {
      this.submitLoading = false
      this.submitDisabled = false
      var formData = new FormData(this.$refs.form);

      for(let i=0; i<this.formElements.length; i++) {
        this.$refs[this.formElements[i]].deFocusedOnce = true
      }
      for(let i=0; i<this.formElements.length; i++) {
        if (this.$refs[this.formElements[i]].hasError) {
          this.$refs.button.error = true
          window.setTimeout(this.clearButtonError, 800)
          return null
        }  
      }      
      const url = `${this.apiUrl}/applicants`
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/applicant_email.php',
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      })       
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.submitLoading = false
        this.submitDisabled = false
        window.scrollTo(0,0)
        this.clearFormInputs()
        this.emitter.emit('showResponseMessage', {error: false, data: response.response})    
        console.log(response.data)
      }).catch(response=>{
        this.submitLoading = false
        this.submitDisabled = false
        this.emitter.emit('showResponseMessage', {error: true, data: response.response})    
        console.log(response.data)    
      })
     
    },
  }

}
</script>


<style scoped lang="scss">
.reg-body {
  display: grid;
  grid-template-areas: 
    " . header . "
    " . form . ";
  grid-template-columns: 1fr minmax(auto, 210mm) 1fr;
  grid-template-rows: auto auto;
  align-items: center;
  padding: 10px;
 
}
.form-title {
  grid-area: header;
  // text-decoration: underline;
  font-weight: bold;
}
.form {
  border: 1px solid rgba(0, 0, 0, 0.6);
  border-radius: 4px;   
  grid-area: form;
  padding: 20px;
  width: 100%;
  min-width: 480px;
  max-width: 210mm;  
}
.section-group {
  margin-top: 30px;
  > span {
    font-weight: bold;
    // text-decoration: underline;
  }
}
</style>
