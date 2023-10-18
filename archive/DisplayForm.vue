<template>
  <div class="form-overview">
    <div class="form-overview-header">
      <div id="tabs">
        <div id="tab-indicator" :style="tabIndicatorPosition"></div>
        <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">Submit Form</div>
        <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">Form Submissions</div>
        <div class="tab" :class="{active: activeTab==2}" @click="changeTab(2)">Replies</div>
        <div class="tab" :class="{active: activeTab==3}" @click="changeTab(3)">Flags</div>
      </div>
    </div>
    <div class="selected-tab-content">
      <SubmitForm :anon="false" :replies="replies" :submissionId="submissionId" :preset="preset" :form="form" v-if="activeTab==0" ></SubmitForm>
      <FormSubmissions :replies="replies" :selectedSubmissionIds="selectedSubmissionIds" :formName="form.metadata.formName" :elements="form.elements" :submissions="submissions" v-if="activeTab==1" @selected-submission-change="selectedSubmissionChange($event)" @delete-submission="deleteSubmission($event)" @edit-submission="passSubmissionData($event)"></FormSubmissions>
      <Reply :formId="form.metadata.formId" :selectedSubmissionIds="selectedSubmissionIds"  v-if="activeTab==2" />
      <FlagSubmission :selectedSubmissionIds="selectedSubmissionIds"  v-if="activeTab==3" />
    </div>
  </div>
</template>

<script>
import axios from "axios";
import FormSubmissions from '../components/FormSubmissions.vue'
import SubmitForm from '../components/SubmitForm.vue'
import Reply from '../components/Reply.vue'
import FlagSubmission from '../components/FlagSubmission.vue'
export default {
  name: 'DisplayForm',
  components: {
    FormSubmissions,
    SubmitForm,
    Reply,
    FlagSubmission,
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
    this.getSubmissions(this.formId);
  },
  methods: {
    selectedSubmissionChange(event) {
      if(event.type=='add') {
        this.selectedSubmissionIds.push(event.id)
      } else {
        var index = this.selectedSubmissionIds.indexOf(event.id);
        this.selectedSubmissionIds.splice(index, 1);
      }
      console.log(this.selectedSubmissionIds)
    },
    changeTab(tab) {
      this.activeTab = tab      
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
    getSubmissions(id) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getFormSubmissions.php',
        data: {formId: id, mode: 'select'}
      }).then(response => {
        console.log(response.data)
        if(response.data.error == null) {
          this.replies = response.data.formSubmissions.replies
          if(response.data.formSubmissions != null) {
            this.submissions = response.data.formSubmissions.submissions;
          } else {
            this.error=404
          }
        } else {
          this.$router.push({name: 'Home'})
        }
        
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
  background-color: #f2f2f2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
</style>