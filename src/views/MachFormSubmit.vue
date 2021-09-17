<template>
  <div class="form-overview">
    <div class="form-overview-header">
      <div id="tabs">
        <div id="tab-indicator" :style="tabIndicatorPosition"></div>
        <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">Login</div>
        <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">
          <template v-if="!preset">
            Registration
          </template>
          <template v-else>
            Update Registration
          </template>          
        </div>
      </div>      
    </div>
    <div class="selected-tab-content">
      
      <div class="submission-unique-key" v-if="activeTab==0">
        <h2>Anonymous Login Mechanical Engineering</h2>
    
        <div class="anon-login-description">
          To update your previous registration data and to access registration replies please enter your unique key.
          The unique key was send via email when you first registered.
        </div>

        <div class="anon-login-description">
          Submit form <span class="redirect-span" @click="changeTab(1)">here</span>.
        </div>

        <section class="input-section">
          <label for="unique-key-input">Enter submission key</label>
          <input id="unique-key-input" type="text" v-model="anonSubmissionKey" @keyup.enter="getSubmission()">
          <button class="kit-button" @click="getSubmission()">Submit</button>
          <div class="not-found-error" v-if="submissionNotFound">registration not found</div>
        </section>

      </div>      
      
      <SubmitForm @form-submitted="activeTab=0" v-if="activeTab==1" replyFrom="Department of Mechanical Enigneering" :key="preset" :replies="replies" :submissionId="submissionId" :preset="preset" :form="form"></SubmitForm>
    </div>
    <div class="anon-form-submit-footer">
      If you have any questions please contact the support under <a href="mailto: zk-aprf@mach.kit.edu">zk-aprf@mach.kit.edu</a> 
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
      formId: null,
      anonSubmissionKey: null,
      submissionNotFound: false,
    }
  },
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 175*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },    
  },
  mounted() {
    if(this.$route.meta.id) {
      this.formId = this.$route.meta.id
    } else {
      this.formId = this.$route.params.id
    }
    this.getFormData(this.formId);
  },
  methods: {
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
            this.replies = response.data.formSubmissions.replies
            if(response.data.formSubmissions != null) {
              this.submissions = response.data.formSubmissions.submissions[0];
            } else {
              this.error=404
            }
            this.$store.commit('setFormSubmissionData', this.submissions)
            this.preset = true
            this.submissionId = this.submissions.formSubmissionId
            this.activeTab = 1
          } else {
            this.submissionNotFound = true
          }
        } else {
          this.$router.push({name: 'Home'})
        }
        console.log(this.$store.getters.getFormSubmissionData)
      })
    },
    getFormData(id) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getForm.php',
        data: {mode: 'select', id: id}
      }).then((response) => {
        console.log(response.data)
        if(response.data.error == null) {
          this.form = {metadata: null, elements: []}
          this.form.metadata = response.data.metadata
          this.form.elements = response.data.elements
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
    width: 175px;
    justify-content: center;
    align-items: center;
    &.active {
      color: #00876c;
    }
  }
  > #tab-indicator {
    position: absolute;
    height: 3px;
    width: 175px;
    background-color: #00876c;
    transition: 300ms transform ease-in-out;
  }
}
.selected-tab-content {
  width: 100%;
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
  // border: 1px solid black;
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
</style>