<template>
  <div class="bewerberportal">
    <div class="portal-header">
      <h1>Application portal</h1>
    </div>
    <div class="portal-body">
      <!-- <Registration/> -->
      <Login v-if="!storage_bewerberportal || !storage_bewerberportal.loggedin" @loggedin="login($event)"/>
      <div class="dashboard" v-else>

        <DataProtection v-if="!storage_bewerberportal.data_protection" :bewerber="storage_bewerberportal" @submitDataProtection="handleDataProtection($event)"/>
        <template v-else>
          <h4>Degree course: {{storage_bewerberportal.Studiengang}}</h4>
          <h4>Current status of application: {{storage_bewerberportal.status}}</h4>
          <h4 v-if="!(storage_bewerberportal.reason_of_rejection === null || storage_bewerberportal.reason_of_rejection.match(/^ *$/) !== null)">Reason of rejection: {{storage_bewerberportal.reason_of_rejection}}</h4>
          <RegisterEntranceExam :bewerber="storage_bewerberportal" @register="register($event)"/>
          
          <div class="bescheide" v-if="storage_bewerberportal.loggedin && storage_bewerberportal.bescheid.length>0">
            <h2>Bescheide</h2>
            <Bescheid :name="bescheid.name" :file_pdf="bescheid.file_pdf" v-for="bescheid in storage_bewerberportal.bescheid" :key="bescheid"/>
          </div>    
        </template>
        <Button :loading="false" :disabled="false" text="Logout" @click="logout()"/>

      </div>
    </div>
    <div class="portal-footer">
      Support <a href= "mailto:zk-aprf@mach.kit.edu">zk-aprf@mach.kit.edu</a>
    </div>
  </div>
</template>

<script>
// import Registration from '@/components/bewerberportal/RegistrationUTEcopy.vue'
import Login from '@/components/bewerberportal/Login.vue'
import Bescheid from '@/components/bewerberportal/Bescheid.vue'
import RegisterEntranceExam from '@/components/bewerberportal/RegisterEntranceExam.vue'
import DataProtection from '@/components/bewerberportal/DataProtection.vue'
// import axios from "axios";
import Button from '@/components/Button.vue'
import axios from "axios";
export default {
  name: 'Bewerberportal',
  components: {
    // Registration,
    Login,
    RegisterEntranceExam,
    Button,
    Bescheid,
    DataProtection,
  },
  data() {
    return {
      // bewerber: null,
      storage_bewerberportal: {loggedin: false},
    }
  },
  mounted() {
    if(localStorage.bewerberportal) {
      this.storage_bewerberportal = JSON.parse(localStorage.bewerberportal)
      this.updateInfo()
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },     
    info_entranceExam() {
      var registered = false
      if(this.storage_bewerberportal!==null && this.storage_bewerberportal.entrance_exam_registered) {
        registered = true
      }
      return {term: "winter", year: "22/23", entrance_exam_registered: registered}
    }
  },
  methods: {
    updateInfo() {
      const url = `${this.apiUrl}/bewerberportal/login`
      var formData = new FormData();
      formData.append('email', this.storage_bewerberportal['KIT-E-Mail'])   
      formData.append('bewerbungs_nummer', this.storage_bewerberportal['Bewerbungs-nummer'])   

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

        this.storage_bewerberportal = bewerber
        console.log(bewerber)
      }).catch(error=>{

        console.log(error.response)
      })
    },
    handleDataProtection(bewerber) {
      if(!bewerber.data_protection) {
        this.logout()
      } else {
        this.storage_bewerberportal.data_protection = bewerber.data_protection
        localStorage.bewerberportal = JSON.stringify(this.storage_bewerberportal)
        console.log(this.storage_bewerberportal)
      }
    },
    login(bewerber) {
      this.storage_bewerberportal = bewerber
      localStorage.bewerberportal = JSON.stringify(bewerber)
      console.log(this.storage_bewerberportal)
    },
    register(register) {
      this.storage_bewerberportal.entrance_exam_registered = register
      localStorage.bewerberportal = JSON.stringify(this.storage_bewerberportal)
    },
    logout() {
      localStorage.removeItem('bewerberportal')
      this.storage_bewerberportal = null
    }
  },
}
</script>

<style lang="scss" scoped>
.portal-footer {
  width: 100%;
  min-width: 280px;
  max-width: 210mm;
  margin: 40px 0;
}
.bewerberportal {
  display: flex;
  flex-direction: column;
  min-height: 100vh;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;
}
.portal-body {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: center;
  background-color: #fff;
  width: 100%;
}
.dashboard {
  width: 100%;
  min-width: 280px;
  max-width: 210mm;
}
</style>