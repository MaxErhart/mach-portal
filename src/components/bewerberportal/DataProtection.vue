<template>
  <div class="data-protection">
    <h4>Consent before registration</h4>
    <form ref="form_data_protection" @submit.prevent="submit()">
      <!-- <Checkbox :presetValue="checkedExam" :data="checkboxElementData_Exam" @inputChange="checkedExam=$event"/> -->
      <Checkbox :presetValue="checkedDataStorage" :data="checkboxElementData_DataStorage" @inputChange="checkedDataStorage=$event"/>
      <Checkbox :presetValue="checkedPrevExam" :data="checkboxElementData_PrevExam" @inputChange="checkedPrevExam=$event"/>
      <Button :loading="buttonElementData_login.loading" :disabled="buttonElementData_login.disabled" :text="buttonElementData_login.text" />
    </form>
  </div>
</template>

<script>
import Checkbox from '@/components/inputs/Checkbox.vue'
import Button from '@/components/Button.vue'
import axios from "axios";
export default {
  name: 'DataProtection',
  components: {
    Checkbox,
    Button,
  },
  props: {
    bewerber: Object,
  },
  data() {
    return {
      // checkboxElementData_Exam: {required: true, label: "I hereby apply for admission to the entrance examination in the above selected course of study on the published date.", disabled: false},
      checkboxElementData_DataStorage: {required: true, label: "I voluntarily consent to the storage and processing of my personal data for the purpose of the application procedure at the KIT Department of Mechanical Engineering and to the forwarding of this data to the admissions committee until revocation.", disabled: false},
      checkboxElementData_PrevExam: {required: true, label: "I further declare that I have not yet participated or have participated only once in an entrance examination at KIT in the above-mentioned course of study. Note that the entrance examinations for summer term 2021, winter term 2021/22 and summer term 2022 will not be considered.", disabled: false},
      // buttonElementData_login: {loading: false, disabled: false, text: "Submit"},
      checkedExam: true,
      checkedDataStorage: false,
      checkedPrevExam: false,
    }
  },
  computed: {
    buttonElementData_login() {
      var disabled = !(this.checkedDataStorage && this.checkedPrevExam && this.checkedExam)
      return {loading: false, disabled: disabled, text: "Submit"}
    },
    checked() {
      if(this.checkedExam && this.checkedDataStorage &&this.checkedPrevExam) {
        return true
      }
      return false
    },
    apiUrl() {
      return this.$store.getters.getApiUrl;
    },     
  },
  methods: {
    submit() {
      const url = `${this.apiUrl}/bewerberportal/data_protection`
      var formData = new FormData();
      console.log(this.bewerber)
      formData.append("first_name", this.bewerber.Vorname)
      formData.append("last_name", this.bewerber.Name)
      formData.append("bewerbungs_nummer", this.bewerber["Bewerbungs-nummer"])
      formData.append("data_protection", this.checked ? "1" : "0")      
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.$emit('submitDataProtection', response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    }
  }  
}
</script>


<style scoped lang="scss">
.data-protection{
  margin: 100px 0;
}
</style>
