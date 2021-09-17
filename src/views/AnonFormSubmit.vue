<template>
  <div class="form-overview">
    <div class="form-overview-header">
      <div id="tabs">
        <div id="tab-indicator" :style="tabIndicatorPosition"></div>
        <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">
          <template v-if="!preset">
            Registrierung
          </template>
          <template v-else>
            Registrierung ändern
          </template>          
        </div>
        <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">Registrierungscode eingeben</div>        
      </div>      
    </div>
    <div class="selected-tab-content">
      <div class="successful-submission-banner" v-if="successfulSubmission">
        Das Formular wurde erfolgreich versendet!
      </div>
      <div class="submission-unique-key" v-if="activeTab==1">
        <h2>{{form.metadata.title}}</h2>
    
        <!-- <div class="anon-login-description">
          {{form.metadata.body}}
        </div> -->

        <div class="anon-login-description">
          Falls Sie sich noch nicht registriert haben, können Sie das <span class="redirect-span" @click="changeTab(0)">hier</span> tun.
        </div>

        <section class="input-section">
          <label for="unique-key-input">Registrierungscode eingeben</label>
          <input id="unique-key-input" type="text" v-model="anonSubmissionKey" @keyup.enter="getSubmission()">
          <button class="kit-button" @click="getSubmission()">Absenden</button>
          <div class="not-found-error" v-if="submissionNotFound">Registrierung nicht gefunden</div>
        </section>
      </div>      
      
      <div class="anon-submission">       
        <div class="anon-submission-email-step" v-if="emailStep && !preset && activeTab==0">
          <h2 class="form-title">{{form.metadata.title}}</h2>
      
          <div class="anon-login-description">
            {{form.metadata.body}}
          </div>
          <div class="anon-login-description">
            Bitte geben Sie Ihre Emailadresse ein, um sich zu registrieren.
            An diese Emailadresse wird ihr persönlicher Registrierungscode gesendet.
            Falls Sie sich bereit registriert haben, können Sie Ihre Daten <span class="redirect-span" @click="changeTab(1)">hier</span> abrufen.
          </div>                    
          <section class="input-section">
            <label for="email-key-input">Emailadresse</label>
            <input id="email-key-input" type="email" v-model="targetEmail" @keyup.enter="emailStep = false">
            <button class="kit-button" @click="emailStep = false">Absenden</button>
          </section>          
        </div>
        <SubmitForm @form-submitted="reset()" v-if="activeTab==0 && !emailStep" replyFrom="Department of Mechanical Enigneering" :anonFormId="anonFormId" :anon="true" :targetEmail="targetEmail" :key="preset" :replies="replies" :submissionId="submissionId" :preset="preset" :form="form"></SubmitForm>

      </div>
    </div>
    <div class="anon-form-submit-footer">
      Bei Fragen wenden Sie sich an <a :href="'mailto: '+ form.metadata.supportEmail">{{form.metadata.supportEmail}}</a> 
    </div>
  </div>

</template>

<script>
import axios from "axios";
import SubmitForm from '../components/SubmitForm.vue'
export default {
  name: 'MachFormSubmit',
  components: {
    SubmitForm,
  },
  data() {
    return {
      replies: null,
      activeTab: 0,
      uploadPercentage: 0,
      form: {metadata: null, elements: []},
      preset: false,
      submissions: null,
      submissionId: null,
      selectedSubmissionIds: [],
      anonFormId: null,
      anonSubmissionKey: null,
      submissionNotFound: false,
      emailStep: true,
      targetEmail: null,
      successfulSubmission: false,
    }
  },
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 215*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },    
  },
  mounted() {
    if(this.$route.params.id) {
      this.anonFormId = this.$route.params.id
    }
    this.getFormData(this.anonFormId);
    if(this.$route.query.tab) {
      this.activeTab = this.$route.query.tab
    }
  },
  methods: {
    reset() {
      this.activeTab = 0
      this.emailStep = true
      this.successfulSubmission = true
    },
    changeTab(tab) {
      this.activeTab = tab      
    },    
    selectedSubmissionChange(event) {
      if(event.type=='add') {
        this.selectedSubmissionIds.push(event.id)
      } else {
        var index = this.selectedSubmissionIds.indexOf(event.id);
        this.selectedSubmissionIds.splice(index, 1);
      }
      console.log(this.selectedSubmissionIds)
    },
    passSubmissionData(event) {
      this.preset=true
      this.activeTab = 0
      this.submissionId = event.data.displayData.formSubmissionId
      console.log(event)
    },
    deleteSubmission(event) {
      this.submissions.splice(this.submissions.indexOf(event.data), 1)
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
        data: {formId: this.formId, mode: 'delete', formSubmissionId: event.data.displayData.formSubmissionId, submissionOwnerId: event.data.displayData.userId}
      }).then(response => {  
        console.log(response.data)          
      })      
    },
    getSubmission() {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
        data: {formId: this.formId, mode: 'anon', anonSubmissionKey: this.anonSubmissionKey}
      }).then(response => {
        console.log(response.data)
        if(response.data.error == null) {
          if(response.data.formSubmissions != null) {
            this.submissionNotFound = false
            this.emailStep = false
            this.replies = response.data.formSubmissions.replies
            if(response.data.formSubmissions != null) {
              this.submissions = response.data.formSubmissions.submissions[0];
            } else {
              this.error=404
            }
            this.$store.commit('setFormSubmissionData', this.submissions)
            this.preset = true
            this.submissionId = this.submissions.formSubmissionId
            this.activeTab = 0
          } else {
            this.submissionNotFound = true
          }
        } else {
          this.$router.push({name: 'Home'})
        }
        
      })
    },
    getFormData(id) {
      console.log(id)
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getForm.php',
        data: {mode: 'anon', id: id}
      }).then((response) => {
        console.log(response.data)
        if(response.data.error == null) {
          this.form = {metadata: null, elements: []}
          this.form.metadata = response.data.metadata
          this.form.elements = response.data.elements
          this.formId = response.data.metadata.formId
          console.log(this.form)
        } else {
          this.$router.push({name: 'Home'})
        }
      })
    },    
  }

}
</script>


<style scoped lang="scss">
.form-overview {
  background-color: #f2f2f2;
  padding: 0 0 10px 0;
  width: 100%;
  display: flex;
  flex-direction: column;
  align-items: center;  
}
.form-overview-header {
  width: 100%; 
}
#tabs {
  background-color: #e3e3e3;
  height: 37px;
  display: flex;
  flex-direction: row;
  box-shadow: 0px 1px 4px 0px rgba(0, 0, 0, 0.2);
  > .tab {
    cursor: pointer;
    display: flex;
    width: 215px;
    justify-content: center;
    align-items: center;
    &.active {
      color: #00876c;
    }
  }
  > #tab-indicator {
    position: absolute;
    height: 3px;
    width: 215px;
    background-color: #00876c;
    transition: 300ms transform ease-in-out;
  }
}
.selected-tab-content {
  width: 100%;
  padding: 5px 0 0 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 100%;
  // border: 1px solid red;
}
.submission-unique-key{
  position: relative;
  width: 100%;
  max-width: 740px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin: 20px 0;
  > h2 {
    margin: 10px auto;
  }
  > .anon-login-description {
    align-self: start;
    font-weight: bold;
    margin: 10px 0 10px 0;
  }
}
.input-section {
  position: relative;
  width: 100%;
  max-width: 700px;
  > input {
    user-select: auto !important;
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px !important;
  }
  > label {
    display: flex;
    align-items: center;
    text-align: start;
    width: 100%;
  }  
}
.redirect-span {
  color: #00876c;
  &:hover {
    cursor: pointer;
    color: #007755;
  }
  text-decoration: underline;
}
.not-found-error {
  top: 28px;
  transform: translateX(-105%);
  position: absolute;
  color: red;
  // border: 1px solid green;
}
.anon-form-submit-footer {
  width: 100%;
  max-width: 740px;
  font-size: 0.8em;
}
.anon-submission-email-step {
  position: relative;
  width: 100%;
  max-width: 740px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  margin: 20px 0;
  > .form-title {
    margin: 10px auto;
  }
  > .anon-login-description {
    align-self: start;
    font-weight: bold;
    margin: 10px 0 10px 0;
  }
}
.successful-submission-banner {
  width: 100%;
  color: green;
  padding: 10px 0 10px 0;
  margin: 5px;
  text-align: center;
  border: 1px solid green;
  background-color: white;
}
</style>