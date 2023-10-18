<template>
  <DataPlaceholder v-if="loading" animation="spinner"/>
  
  <div id="store-form" v-else>
    <div id="store-form-header">
      <template v-if="form">
        <h4 >Updating Form: <router-link class="link-style" :to="`/submit/${form.id}`">{{form.name}}</router-link></h4>
      </template>
      <h4 v-else>Create Form:</h4>
    </div>    
    <form id="store-form-body" @submit.prevent="form ? updateForm(form.id) : storeForm()" ref="form">
      <div id="form-settings">
        <section>
          <Checkbox label="Display Form" tooltip="controls if form is visible to other users or only to oneself" :presetValue="form ? form.display : true" name="display" ref="display"/>
        </section>
        <section>
          <InputElement label="Name" type="text" :required="true"  name="name" ref="name" :presetValue="form?.name"/>
        </section>
        <section>
          <InputElement label="Deadline" type="date" :required="false" name="deadline" ref="deadline" :presetValue="form?.deadline" />
        </section>
        <section id="multiplesubmissions-checkbos">
          <Checkbox label="Multiple Submissions" name="multiple_submissions" ref="multipleSubmissions" :presetValue="form?.multiple_submissions"/>
        </section>
        <section>
          <MultiSelectElementBox ref="tags" :emptyEmptySearchOption="emptyOption" @emptySearchOption="createTag($event)" :search="true" :inputTypeable="true" label="Tags" :required="false" name="tags" :data="tags" :presetValue="form ? form.tags : []"/>
        </section>                   
      </div>
      <div id="elements-settings">
        <FormCreator name="formcreator" :form_id="form?.id" :presetValue="form?.form_elements"/>
      </div>
      <div>
        <div style="margin: 5px 0 2px 0;text-decoration: underline">
          Form settings:
        </div>
        <div class="form-visability">
          <Checkbox style="margin: 8px;" label="Public Form" :presetValue="publicForm" name="public" ref="public" @inputChange="publicForm=$event"/>
          <div style="margin: 5px 0 0 0;text-decoration: underline" v-if="!publicForm">
            Select users and groups to see and submit your form:
          </div>
          <div class="user-group-select-container" v-if="!publicForm">
            <MultiSelectElementBox :search="true" label="Groups" :data="groups" name="group_observers_submit" :presetValue="groupObserversSubmit"/>
            <MultiSelectElementBox :cast="castUser" :search="true" label="Users" :data="users" name="user_observers_submit" :presetValue="userObserversSubmit"/>
          </div>
          <div style="margin: 5px 0 0 0;text-decoration: underline" v-if="!publicForm">
            Select users and groups that can see the form but can not create new submissions:
          </div>
          <div class="user-group-select-container" v-if="!publicForm">
            <MultiSelectElementBox :search="true" label="Groups" :data="groups" name="group_observers_view" :presetValue="groupObserversView"/>
            <MultiSelectElementBox :cast="castUser" :search="true" label="Users" :data="users" name="user_observers_view" :presetValue="userObserversView"/>
          </div>
        </div>    
      </div>
      <div>
        <div style="margin: 5px 0 2px 0;text-decoration: underline">
          Submission settings:
        </div>
        <div class="own-submissions">
          <SelectElement :required="true" :presetValue="own_sub" label="Own Submission Setting" name="own_sub" :search="false" :data="[{id:0,name:'hide'},{id:1,name:'view'},{id:2,name:'edit'},{id:3,name:'delete'}]"/>
        </div>
        Wildcard:
        <InputElement label="Source" name="wildcard_source" @valueChange="wildcard_source=$event"/>
        <InputElement label="Target" name="wildcard_target" @valueChange="wildcard_target=$event"/>
        <SelectElement @select="wildcard_permission=$event" :search="false" label="Permission" :data="[{id:1,name:'view'},{id:2,name:'edit'},{id:3,name:'delete'}]" name="wildcard_permission"/>
        <Button text="Make submission settings" @click.prevent="getWildcardSubmissionSettings()"/>
        
        <!-- <RadioButton name="submissions" :data="{label: '', '0': 'Standard', '1': 'Custom'}" :presetValue="form ? parseInt(form.submissions) : null" @inputChange="submissions=$event"/> -->
        <section>
          <GroupGroupListElement :users="users" :groups="groups" name="submissionPermissions" :presetValue="form?.permissions" ref="submissionPermissions"/>
        </section>
      </div>
      <div>
        <div style="margin: 5px 0 2px 0;text-decoration: underline">
          Form co-authors:
        </div>
        <div class="form-visability">
          <div style="margin: 5px 0 0 0">
            Select users and groups to see and edit your form:
          </div>
          <div class="user-group-select-container">
            <MultiSelectElementBox :search="true" label="Groups" :data="groups" name="group_form_permission_edit" :presetValue="group_form_permission_edit"/>
            <MultiSelectElementBox :cast="castUser" :search="true" label="Users" :data="users" name="user_form_permission_edit" :presetValue="user_form_permission_edit"/>
          </div>

        </div>    
      </div>
      <div class="submit-form">
        <Button ref="submit" :loading="submitLoading" :disabled="submitDisabled" :text="form ? 'Update' : 'Submit'"/>
      </div>
    </form>
  </div>
</template>

<script>
import Button from '@/components/Button.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import FormCreator from '@/components/forms/FormCreator.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
// import RadioButton from '@/components/inputs/RadioButton.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import GroupGroupListElement from '@/components/inputs/GroupGroupListElement.vue'
import MultiSelectElementBox from '@/components/inputs/MultiSelectElementBox.vue'
// import MultiSelectElement from '@/components/inputs/MultiSelectElement.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import axios from 'axios';
export default {
  name: 'StoreForm',
  components: {
    InputElement,
    FormCreator,
    Button,
    Checkbox,
    // MultiSelectElement,
    // RadioButton,
    SelectElement,
    GroupGroupListElement,
    MultiSelectElementBox,
    DataPlaceholder,
  },
  props: {
    form: Object,
    tags: Array,
    loading: {
      type: Boolean,
      default: false,
    },
  },
  data() {
    return {
      emptyOption: {name: 'Use entered search term to create a new tag and add it to the form'},
      name: '',
      multipleSubmissions: false,
      deadline: null,

      submitLoading: false,
      submitDisabled: false,

      publicForm: true,

      submissions: 0,
      users: null,
      groups: null,
      groupObserversSubmit: null,
      userObserversSubmit: null,
      groupObserversView: null,
      userObserversView: null,
      group_form_permission_edit: null,
      user_form_permission_edit: null,
      wildcard_source:null,
      wildcard_target:null,
      wildcard_permission: null,
      own_sub: 3,
    }
  },
  mounted() {
    this.matchPresets(this.form)
    this.getUsers()
    this.getGroups()
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    }, 
  },
  watch: {
    form(to) {
      this.matchPresets(to)
    }
  },
  methods: {
    async getWildcardSubmissionSettings() {
      const url = `${this.$store.getters.getApiUrl}/_forms/wildcard` 
      var formData = new FormData()
      formData.append('wildcard_source',this.wildcard_source)
      formData.append('wildcard_target',this.wildcard_target)
      formData.append('wildcard_permission',this.wildcard_permission?.id)
      formData.append('groups',JSON.stringify(this.groups))
      const {data, error} = await axios({
          method:'post',
          url,
          data: formData,
          headers: {
              'Content-Type': 'multipart/form-data'
          }
      }).catch(error=>{
          return {data:null,error}
      })
      this.$refs.submissionPermissions.entries = data.concat(this.$refs.submissionPermissions.entries)
      console.log(data,error?.response)

    },
    createTag(option) {
      this.$emit('createTag', option)
    },
    async getGroups() {
      const {groups} = await this.$store.dispatch('groups')
      this.groups = groups
    },
    async getUsers() {
      const {users} = await this.$store.dispatch('users')
      this.users = users
    },
    matchPresets(form) {
      if(!form) {
        return
      }
      this.own_sub = form.submissions
      this.publicForm = form.public
      this.submissions = form.submissions
      this.groupObserversSubmit = form.group_observers?.filter(g=>g.pivot.submit_permission==2 || g.pivot.permission==2)
      this.userObserversSubmit = form.user_observers?.filter(u=>u.pivot.submit_permission==2 || u.pivot.permission==2)
      this.groupObserversView = form.group_observers?.filter(g=>g.pivot.submit_permission==1 || g.pivot.permission==1)
      this.userObserversView = form.user_observers?.filter(u=>u.pivot.submit_permission==1 || u.pivot.permission==1)
      this.group_form_permission_edit = form.group_observers?.filter(g=>g.pivot.form_permission==2 || g.pivot.permission==3)
      this.user_form_permission_edit = form.user_observers?.filter(u=>u.pivot.form_permission==2 || u.pivot.permission==3)
    },
    castUser(user) {
      return {id: user.id, name: `(${user.id}) ${user.firstname} ${user.lastname}`}
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
    getElements(formData) {
      var elements = []
      var del = []
      for (const pair of formData.entries()) {
        if(pair[0].endsWith('_formcreator')) {
          elements.push(JSON.parse(pair[1]))
          del.push(pair[0])
        }
      }
      del.forEach(entry=>{
        formData.delete(entry)
      })
      return elements
    },
    async updateForm(id) {
      var formData = new FormData(this.$refs.form);
      const elements = this.getElements(formData)
      formData.append('elements', JSON.stringify(elements))
      this.submitLoading = true;
      this.submitDisabled = true;
      // for (const pair of formData.entries()) {
      //   console.log(`${pair[0]}, ${pair[1]}`);
      // }
      const {error} = await this.$store.dispatch('_forms', {method:'update', form_id: id, formData})
      this.submitLoading = false;
      this.submitDisabled = false;
      this.handleSubmitError(error, 'Submit Form')
    },
    handleSubmitError(error, action=null) {
      if(error) {
        this.emitter.emit('showErrorMessage', {error: error,action})    
        this.$refs.submit.error = true
        return
      }
      this.handleSubmitSuccess()
    },
    handleSubmitSuccess() {
      this.$emit('storeForm')
      this.$refs.submit.success = true
    },
    async storeForm() {
      this.submitDisabled = true;
      this.submitLoading = true;
      var formData = new FormData(this.$refs.form);
      const elements = this.getElements(formData)
      formData.append('elements', JSON.stringify(elements))
      // for (const pair of formData.entries()) {
      //   console.log(`${pair[0]}, ${pair[1]}`);
      // }
      const {error} = await this.$store.dispatch('_forms', {method: 'post', formData})
      this.submitLoading = false;
      this.submitDisabled = false;
      this.handleSubmitError(error, 'Submit Form')
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.user-group-select-container {
  display: grid;
  grid-template-columns: 1fr 1fr;
  grid-gap: 12px;
}
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
