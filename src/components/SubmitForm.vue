<template>
  <div class="replies" v-if="preset">
    <div class="replies-body" v-for="reply in replies[submissionId]" :key="reply">
      <DisplaySubmissionReply :reply="reply" :from="replyFrom"/>
    </div>
  </div>
  <div class="form-overview">
    <form id="form" v-if="form.metadata != null">
      <div id="form-header" >
        {{form.metadata.formName}}
      </div>
      <div id="form-body">
        <section class="form-element" v-for="el in form.elements" :key="el">
          <component class="form-component" :uploadPercentage="uploadPercentage" :elementId="el.elementId" :preset="preset" :is="componentDic[el.component]" v-bind="el.data"></component>
        </section>
      </div>
      <div id="form-footer">
        <div class="buttons" v-if="!submissionsLoading">
          <button class="kit-button" v-if="!preset" @click.prevent="submitForm()">Submit</button>
          <button class="kit-button" v-if="preset" @click.prevent="updateFormSubmission()">Update</button>
        </div>
        <div v-else>Loading...</div>  
      </div>
    </form>
    <div v-else>Loading...</div>
  </div>
</template>

<script>
import axios from "axios";
import FormInputElement from '../components/FormInputElement.vue'
import FormHeaderElement from '../components/FormHeaderElement.vue'
import FormSectionElement from '../components/FormSectionElement.vue'
import FormFileUploadElement from '../components/FormFileUploadElement.vue'
import FormSelectionElement from '../components/FormSelectionElement.vue'
import DisplaySubmissionReply from '../components/DisplaySubmissionReply.vue'
export default {
  name: 'DisplayForm',
  props: {
    form: Object,
    replies: Object,
    preset: Boolean,
    submissionId: Number,
    replyFrom: String,
    anon: Boolean,
    targetEmail: String,
    anonFormId: String,
  },
  components: {
    FormInputElement,
    FormHeaderElement,
    FormSectionElement,
    FormFileUploadElement,
    FormSelectionElement,
    DisplaySubmissionReply,
  },
  data() {
    return {
      componentDic: {InputElement: 'FormInputElement', HeaderElement: 'FormHeaderElement', SectionElement: 'FormSectionElement', FileUploadElement: 'FormFileUploadElement', SelectionElement: 'FormSelectionElement'},
      hasActiveInput: null,
      id: null,
      submissionsLoading: false,
    }
  },
  methods: {
    updateFormSubmission() {
      this.submissionsLoading=true
      var formData = new FormData(document.getElementById("form"))
      formData.append('formSubmissionId', this.submissionId)
      formData.append('mode', 'update')
      axios.post( 'https://www-3.mach.kit.edu/api/submitForm.php',
        formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: function( progressEvent ) {
            this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ))
          }.bind(this)          
        }
      ).then((response) => {
        this.submissionsLoading=false
        console.log(response.data)
      })
    },
    submitForm() {
      this.submissionsLoading = true
      var formData = new FormData(document.getElementById("form"))
      formData.append('formId', this.form.metadata.formId)
      formData.append('mode', 'submit')
      formData.append('anon', this.anon)
      formData.append('targetEmail', this.targetEmail)
      formData.append('formName', this.form.metadata.formName)
      formData.append('anonFormId', this.anonFormId)
      axios.post( 'https://www-3.mach.kit.edu/api/submitForm.php',
        formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: function( progressEvent ) {
            this.uploadPercentage = parseInt( Math.round( ( progressEvent.loaded / progressEvent.total ) * 100 ))
          }.bind(this)           
        }
      ).then((response) => {
        this.submissionsLoading = false
        this.$emit('form-submitted')
          console.log(response.data)
        }
      )   
    }
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

#form {
  background-color: #f2f2f2;
  width: 100%;
  max-width: 740px;
  padding: 20px 10px;
  display: flex;
  flex-direction: column;
}
#form-header {
  color: #2c3e50;
  font-size: 18px;
  border-bottom: 2px solid #2c3e50;
  padding: 5px 0;
}
#form-body {
  height: 100%;
  margin: 20px 0;
  padding: 5px;
}
.form-element {
  margin: 6px 0;
}
#form-footer {
  padding: 5px;    
}
#loading {
  background-color: rgb(238, 238, 238);
  width: 100%;
  max-width: 860px;
  padding: 20px 10px;
}
.replies {
  width: 100%;
  max-width: 740px;
  margin: 10px 0;
}
.replies-body {
  widows: 100%;
}
</style>