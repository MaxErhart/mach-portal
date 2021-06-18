<template>
  <div class="create-form">
    <h1>Create Form</h1>
    <div class="create-form-header">
      <div id="tabs">
        <div id="tab-indicator" :style="tabIndicatorPosition"></div>
        <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">Form Settings</div>
        <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">Target Users</div>
        <div class="tab" :class="{active: activeTab==2}" @click="changeTab(2)">Form Body</div>
        <div class="tab" :class="{active: activeTab==3}" @click="changeTab(3)">Email Notifications</div>
      </div>
    </div>    
    
    <div class="create-form-body">
      <CreateFormSettings :formName="formName" :deadline="deadline" :multipleSubmissions="multipleSubmissions" @settings-change="updateSettings($event)"  v-if="activeTab==0"/>
      <UserAndGroupSelect :users="users" :groups="groups" :userSelection="userSelection" :groupSelection="groupSelection" @remove-user="removeUser($event)" @remove-group="removeGroup($event)" @add-user="userSelection.push($event.user)" @add-group="groupSelection.push($event.group)" v-if="activeTab==1"/>
      <FormCreator :presetSelection="presetSelection" v-if="activeTab==2"/>
      <Email :emails="emails" :formId="$route.params.id" :userSelection="userSelection" :users="users" :groupSelection="groupSelection" :groups="groups" v-if="activeTab==3"/>
    </div>
    <div class="create-form-footer" v-if="activeTab != 3">
      <button class="kit-button" @click="update()" v-if="$route.params.id">Update</button>
      <button class="kit-button" @click="submit()" v-else>Submit</button>
    </div>
  </div>
</template>

<script>
import axios from "axios";
import FormCreator from '@/components/FormCreator.vue'
import CreateFormSettings from '@/components/CreateFormSettings.vue'
import UserAndGroupSelect from '@/components/UserAndGroupSelect.vue'
import Email from '@/components/Email.vue'
export default {
  name: 'CreateForm',
  components: {
    FormCreator,
    CreateFormSettings,
    UserAndGroupSelect,
    Email,
  },
  data() {
    return {
      activeTab: 0,

      formName: null,
      deadline: null,
      multipleSubmissions: null,

      users: null,
      groups: null,
      userSelection: [],
      groupSelection: [],

      presetSelection: null,

      emails: null,

      componentDic: {
        input: 'InputElement',
        header: "HeaderElement",
        section: 'SectionElement',
        file: 'FileUploadElement',
        selection: 'SelectionElement'
      }      

    }
  },
  mounted() {
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/getUsersAndGroups.php',
    }).then(response => {
      if(response.data.error == null) {
        this.users=response.data.users
        this.groups=response.data.groups
      }
    })   
    if(this.$route.params.id) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/email.php',
        data: {mode: 'get', formId: this.$route.params.id}
      }).then(response => {
        if(response.data.error == null) {
          this.emails=response.data.emails
        }
      })

      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getForm.php',
        data: {id: this.$route.params.id}
      }).then((response) => {
        if(response.data.error == null) {
          console.log(response.data)
          this.formName = response.data.metadata.formName
          this.deadline = response.data.metadata.deadline
          this.multipleSubmissions = response.data.metadata.multipleSubmissions
          this.userSelection = response.data.metadata.targetUsers.users
          this.groupSelection = response.data.metadata.targetUsers.groups
          var selections = []
          response.data.elements.forEach(el => {
            var selection = {
              component: el.component,
              props: {editable: false, id: el.elementId, preset: true},
              elementId: el.elementId,
              data: el.data,
            }
            selections.push(selection)      
          })
          console.log(selections)
          this.$store.commit('setSelections', selections)                  
        } else {
          this.$router.push({name: 'Home'})
        }
      })      
      
    }
  },
  beforeUnmount() {
    this.$store.commit('deleteSelections');
  },   
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 175*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },    
  },
  methods: {
    changeTab(tab) {
      this.activeTab = tab      
    },
    updateSettings(event) {
      console.log(event)  
      this.$data[event.name] = event.value
      console.log(this.formName)
    },
    removeUser(event) {
      const index = this.userSelection.indexOf(event.user)
      this.userSelection.splice(index, 1)
    },
    removeGroup(event) {
      const index = this.groupSelection.indexOf(event.group)
      this.groupSelection.splice(index, 1)
    } ,  
    submit() {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/saveForm.php',
        data: {mode: 'insert', formName: this.formName, deadline: this.deadline, multipleSubmissions: this.multipleSubmissions, targetUsers: this.userSelection, targetGroups: this.groupSelection, elements: this.$store.getters.getSelectionsData}
      }).then(response => {
        console.log(response.data)
      })
    },
    update() {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/saveForm.php',
        data: {mode: 'update',formId: this.$route.params.id,formName: this.formName, deadline: this.deadline, multipleSubmissions: this.multipleSubmissions, targetUsers: this.userSelection, targetGroups: this.groupSelection, elements: this.$store.getters.getSelectionsData}
      }).then(response => {
        console.log(response.data)
      })
    }
  } 
}
</script>

<style lang="scss" scoped>
.create-form {
  text-align: center;
  display: flex;
  flex-direction: column;
  width: 100%;
  
}
.create-form-header {
  width: 100%; 
  > #tabs {
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
    }
    > #tab-indicator {
      position: absolute;
      height: 3px;
      width: 175px;
      background-color: #00876c;
      transition: 300ms transform ease-in-out;
    }
  }
}
.create-form-body {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  box-shadow: 0 2px 3px 0 rgba(0,0,0,.2);
  width: 100%;
  min-height: 200px;
  background-color: rgb(243, 243, 243);
}
.create-form-footer {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  background-color: rgb(243, 243, 243);
  padding: 5px 0;
  > button {
    width: 435.2px;
    font-size: 16px;
  }
}
</style>