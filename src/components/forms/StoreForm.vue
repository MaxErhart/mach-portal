<template>
  <div id="store-form">
    <div id="store-form-header">
      <template v-if="form">
        <h4 >Updating Form: <router-link class="link-style" :to="`/createforms/${form.id}`">{{form.name}}</router-link></h4>
      </template>
      <h4 v-else>Create Form:</h4>
    </div>    
    <form id="store-form-body" @submit.prevent="form ? updateForm(form.id) : storeForm()" ref="form">
      <div id="form-settings">
        <section>
          <Checkbox :data="{label: 'Display Form', tooltip: 'controls if form is visible to other users or only to oneself'}" :presetValue="form ? form.display : true" name="display" ref="display"/>
        </section>
        <section>
          <InputElement :data="{label: 'Name', type: 'text', required: true}"  name="name" ref="name" :presetValue="form ? form.name : null"/>
        </section>
        <section>
          <InputElement :data="{label: 'Deadline', type: 'date', required: false,  placeholder: 'Date'}" name="deadline" ref="deadline" :presetValue="form ? form.deadline : null" />
        </section>
        <section id="multiplesubmissions-checkbos">
          <Checkbox :data="{label: 'Multiple Submissions'}" name="multiple_submissions" ref="multipleSubmissions" :presetValue="form ? Boolean(form.multiple_submissions) : null"/>
        </section>
        <section>
          <MultiSelectElement label="Tags" :required="false" name="tag" :data="formatTags(tags)" :preset="form ? form.tags : []"/>
        </section>                   
      </div>
      <div id="elements-settings">
        <FormCreator name="formcreator" :presetValue="form ? form.form_elements : null"/>
      </div>
      <div>
        <div style="margin: 5px 0 2px 0;text-decoration: underline">
          Form settings:
        </div>        
        <Checkbox style="margin: 8px;" :data="{label: 'Public Form'}" :presetValue="publicForm" name="public" ref="public" @inputChange="publicForm=$event"/>
        <template v-if="!publicForm">
          <div style="margin: 5px 0 0 0;text-decoration: underline">
            Allow users and groups to see and submit your form:
          </div>
          <SelectAgents name="formPermissions" :presetValues="form==null ? null : {users: form.user_observers, groups: form.group_observers}"/>
        </template>
      </div>
      <div>
        <div style="margin: 5px 0 2px 0;text-decoration: underline">
          Submission settings:
        </div>
        <RadioButton name="submissions" :data="{label: '', '0': 'Standard', '1': 'Custom'}" :presetValue="form ? form.submissions : null" @inputChange="submissions=$event"/>
        <template v-if="submissions==1">
          <section>
            <GroupGroupListElement name="submissionPermissions" :presetValue="form ? form.permissions : null"/>
          </section>

        </template>
      </div>
      <div class="submit-form">
        <Button :loading="submitLoading" :disabled="submitDisabled" :text="form ? 'Update' : 'Submit'"/>
      </div>
    </form>
  </div>
</template>

<script>
import Button from '@/components/Button.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import MultiSelectElement from '@/components/inputs/MultiSelectElement.vue'
import FormCreator from '@/components/forms/FormCreator.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import RadioButton from '@/components/inputs/RadioButton.vue'
import SelectAgents from '@/components/inputs/SelectAgents.vue'
import GroupGroupListElement from '@/components/inputs/GroupGroupListElement.vue'
import axios from "axios";
export default {
  name: 'StoreForm',
  components: {
    InputElement,
    FormCreator,
    Button,
    Checkbox,
    MultiSelectElement,
    SelectAgents,
    RadioButton,
    GroupGroupListElement
  },
  props: {
    form: Object,
    tags: Object,
  },
  data() {
    return {
      name: '',
      multipleSubmissions: false,
      deadline: null,

      submitLoading: false,
      submitDisabled: false,

      publicForm: true,

      submissions: 0,
    }
  },
  mounted() {
    if(this.form!==null) {
      this.publicForm = this.form.public
      this.submissions = this.form.submissions
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    }, 
  },
  methods: {
    formatTags(tags) {
      return tags.map(t=>t.name)
    },
    validateInputs() {
      const el =  this.$refs.name;
      if(el.hasError) {
        el.deFocusedOnce = true;
        return false;
      }
      return true;
    },    
    enableButton(time) {
      return new Promise(()=>{
        setTimeout(()=>{
          if(!this.submitLoading) {
            this.disableButton = false
          }
        }, time)
      })
    },
    formatFormData(formData) {
      var formData_formatted = new FormData();
      var elements = {}
      var tags = []
      var permissions = []
      var multiselect = {}
      const regex = new RegExp(/^\d/)
      const intRegex = new RegExp(/^[0-9]\d*$/)
      for(var pair of formData.entries()) {
        if(pair[0]=='name' || pair[0]=='deadline') {
          formData_formatted.append(pair[0], pair[1]);
        } else if(pair[0]=='multiple_submissions') {
          formData_formatted.append(pair[0], pair[1]=='true'?1:0);
        } else if(pair[0]=='display') {
          formData_formatted.append(pair[0], pair[1]);
        } else if(pair[0].startsWith('tag')) {
          tags.push(pair[1])
        } else if(regex.test(pair[0])) {
          const fieldname = pair[0].split('_')
          // const id = fieldname[0]
          const id = fieldname[0]
          const name = fieldname[2]
          if(!(id in elements)) {
            elements[id] = {}
          }
          if(fieldname[fieldname.length-1]=='data') {
            if(!('data' in elements[id])) {
              elements[id]['data'] = {}
            }
            if(pair[1]==='true') {
              pair[1]=true
            } else if(pair[1]==='false') {
              pair[1]=false
            } else if(intRegex.test(pair[1])) {
              pair[1]=Number(pair[1])
            }
            elements[id]['data'][name] = pair[1]
          } else if(fieldname[fieldname.length-2]=='data') {
            if(!(id in multiselect)) {
              multiselect[id] = {vals: [], name: name}
            }
            if(pair[1]==='true') {
              pair[1]=true
            } else if(pair[1]==='false') {
              pair[1]=false
            } else if(intRegex.test(pair[1])) {
              pair[1]=Number(pair[1])
            }            
            multiselect[id]['vals'].push(pair[1])
          }else {
            if(pair[1]==='true') {
              pair[1]=true
            } else if(pair[1]==='false') {
              pair[1]=false
            } else if(intRegex.test(pair[1])) {
              pair[1]=Number(pair[1])
            }      
            elements[id][name] = pair[1]
          }
        } else if(pair[0]=='formPermissions') {
          formData_formatted.append('form_permissions', pair[1])
        } else if(pair[0]=='public') {
          formData_formatted.append('public', pair[1]=='true'?1:0)
        } else if(pair[0]=='submissions') {
          formData_formatted.append('submissions', pair[1])
        } else if(pair[0]=='viewSubmissionsSource') {
          formData_formatted.append('view_submissions_source', pair[1])
        } else if(pair[0]=='viewSubmissionsTarget') {
          formData_formatted.append('view_submissions_target', pair[1])
        } else if(pair[0]=='editSubmissions') {
          formData_formatted.append('edit_submissions', pair[1])
        } else if(pair[0]=='editSubmissionsSource') {
          formData_formatted.append('edit_submissions_source', pair[1])
        } else if(pair[0]=='editSubmissionsTarget') {
          formData_formatted.append('edit_submissions_target', pair[1])
        } else if(pair[0].startsWith('submissionPermissions')) {
          permissions.push(JSON.parse(pair[1]))
        }
      }
      for (const [key, value] of Object.entries(multiselect)) {
        elements[key]['data'][value['name']]=value['vals'];
      }

      formData_formatted.append('elements', JSON.stringify(elements))
      formData_formatted.append('tags', JSON.stringify(tags))
      formData_formatted.append('permissions', JSON.stringify(permissions))
      return formData_formatted
    },
    updateForm(id) {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();      
      var formData = new FormData(this.$refs.form);
      for(var entry3 of formData.entries()) {
       console.log(entry3)
      }      
      formData = this.formatFormData(formData)
      for(var entry2 of formData.entries()) {
       console.log(entry2)
      }        
      const data = {}
      for(var entry of formData.entries()) {
        data[entry[0]] = entry[1];
      }

      axios({
        method: 'put',
        url: `${this.apiUrl}/forms/${id}`,
        data: data,
        headers: {
          'Content-Type': 'application/json'
        }
      }).then((response)=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newEntry', response.data);
        this.$router.push({name: 'CreateForms'});        
      }).catch(error=>{
        console.log(error.response)
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
      })
    },
    storeForm() {
      
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();      
      var formData = new FormData(this.$refs.form);
      
      formData = this.formatFormData(formData)
    
      axios({
        method: 'post',
        url: `${this.apiUrl}/forms`,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then((response)=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newEntry', response.data);
        this.$router.push({name: 'CreateForms'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        console.log(error.response)
        this.emitter.emit('showResponseMessage', {error: error.response})    
      })
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#store-form{
  display: flex;
  flex-direction: column;
  padding: 10px;
  align-items: center;
}
#store-form-header {
  border-bottom: 2px solid $text_dark;
  width: 100%;
}
#store-form-body {
  padding: 10px;
  width: 100%;
}
#multiplesubmissions-checkbos {
  margin-top: 32px;
  margin-bottom: 12px;
  display: flex;
  flex-direction: column;
  > input {
    zoom: 2;
  }
}
</style>
