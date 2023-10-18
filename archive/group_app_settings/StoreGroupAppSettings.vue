<template>
  <div id="store-entry">
    <div id="store-entry-header">
      <template v-if="entry">
        <h4 >Updating Group App Settings: <router-link class="link-style" :to="`/group_app_settings/${entry.id}`">{{entry.id}}</router-link></h4>
      </template>
      <h4 v-else>Group App Settings Registration</h4>
    </div>
    <div id="store-entry-body">
      <form @submit.prevent="entry ? updateForm(entry.id) : submitForm()" action="" id="entry-form" ref="form">
        <section id="select-group" class="form-element">
          <SelectElement :dynamic="false" :data="groupsSelectData" name="group" ref="selectGroup" :presetValue="entry?entry.groups:null" :nameAsValue="false"/>
        </section>
        <section id="select-app" class="form-element">
          <SelectElement :dynamic="false" :data="appsSelectData" name="app" ref="selectApp" :presetValue="entry?entry.apps:null" :nameAsValue="false"/>
        </section>        
        <section class="select-type form-element" v-for="index in numTypes" :key="index">
          <AgentSettingSelect :presetType="entry?selectedTypes[index-1]:null" :presetValues="entry?entry.settings[index-1]:null" @typeSelected="typeSelected($event, index)" @typeInputChanged="typeInputChanged($event, index)" :types="remainingTypes(index)" :users="users" :groups="groups" :name="'selectAgentSettings_'+index" ref="selectAgentSettings"/>
        </section> 
        <section id="submit-form" class="form-element">
          <Button :loading="submitLoading" :disabled="submitDisabled" :text="entry ? 'Update' : 'Submit'" />
        </section>
      </form>
    </div>
  </div>
</template>

<script>
// import InputElement from '@/components/inputs/InputElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import AgentSettingSelect from '@/components/group_app_settings/AgentSettingSelect.vue'
import Button from '@/components/Button.vue'
import axios from "axios";


export default {
  name: 'StoreGroupAppSettings',
  components: {
    // InputElement,
    AgentSettingSelect,
    Button,
    SelectElement
  },
  props: {
    entry: Object,
  },
  data() {
    return {
      submitLoading: false,
      submitDisabled: false,
      users: [],
      groups: [],
      apps: [],
      fetchedAllUsers: false,
      fetchedAllGroups: false,
      fetchedAllApps: false,
      awaitUserData: false,
      awaitGroupData: false,
      awaitAppData: false,
      types: [
        {name: 'index', id: 0},
        {name: 'store', id: 1},
        {name: 'update', id: 2},
        {name: 'destroy', id: 3},
      ],
      selectedTypes: [],
    }
  },
  mounted() {
    if(this.entry) {
      this.entry.settings.forEach(s=>{
        this.selectedTypes.push(this.types.filter(e=>e.name==s.type)[0])
      })
    }
    this.getApps();
    this.getGroups();
    this.getUsers();
  },
  computed: {
    groupsSelectData() {
      var data = {label: 'Select Group', required: false, placeholder: null, tooltip: null}
      this.groups.forEach((v, i)=>{
        data[String(i)]=v
      })
      console.log(data)
      return data
    },
    appsSelectData() {
      var data = {label: 'Select App', required: false, placeholder: null, tooltip: null}
      this.apps.forEach((v, i)=>{
        data[String(i)]=v
      })
      console.log(this.users)
      return data
    },    
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
    numTypes() {
      if(this.selectedTypes.length<4) {
        return this.selectedTypes.length+1
      }
      return 4
    },      
  },
  methods: {
    typeSelected(event, index) {
      this.selectedTypes[index-1] = event
    },
    remainingTypes(index) {
      // console.log(this.selectedTypes)
      var tmp = this.selectedTypes.length>=index?[this.selectedTypes[index-1]]:[]
      tmp = tmp.concat(this.types.filter(e => !this.selectedTypes.map(t=>t.id).includes(e.id)))
      return tmp
    },
    typeInputChanged(event, index) {
      var match = false
      this.types.forEach(e=>{
        if(e.name==event) {
          this.selectedTypes[index-1] = e
          match = true
        }
      })
      if(!match) {
        this.selectedTypes.splice(index-1, 1)
      }
    },
    getGroups() {
      this.groups=[];
      this.awaitGroupData = true;
      const url = `${this.apiUrl}/groups`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.fetchedAllGroups = true;
        this.groups = this.groups.concat(response.data);
        console.log(this.groups)
        this.awaitGroupData = false;
      })
    },    
    getUsers() {
      this.users=[];
      this.awaitUserData = true;
      const url = `${this.apiUrl}/users`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.fetchedAllUsers = true;
        this.users = this.users.concat(response.data);
        this.awaitUserData = false;
      })
    },
    getApps() {
      this.apps=[];
      this.awaitAppData = true;
      const url = `${this.apiUrl}/apps`;
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.fetchedAllApps = true;
        this.apps = this.apps.concat(response.data);
        this.awaitAooData = false;
      })
    },      
    validateInputs() {
      const inputs = [this.$refs.selectGroup, this.$refs.selectApp, this.$refs.selectAgentSettings]
      inputs.forEach(el=>{
        if(el.hasError) {
          el.deFocusedOnce = true;
          return false;
        }
      })          
      if(this.selectedTypes.length==0){
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
    updateForm(id) {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();
      var gatheredData = new FormData(this.$refs.form);     
      var formData = {};
      var settings = {};
      for(var pair of gatheredData.entries()) {
        if(pair[0].startsWith('type_selectAgentSettings') && (pair[1]=='0' || pair[1]=='1' || pair[1]=='2' || pair[1]=='3')) {
          if(gatheredData.get('unrestricted_'+pair[0].slice(5))) {
            settings[this.types.filter(e=>e.id==pair[1])[0].name]={unrestricted: true}
          } else {
            settings[this.types.filter(e=>e.id==pair[1])[0].name]=JSON.parse(gatheredData.get(pair[0].slice(5)))
          }
        }
      }
      formData['groupId'] = gatheredData.get('group')
      formData['appId']= gatheredData.get('app')
      formData['settings']= JSON.stringify(settings);
      axios({
        method: 'put',
        url: `${this.apiUrl}/groupappsettings/${id}`,
        data: formData,
        headers: {
          'Content-Type': 'application/json' // Put method does not support multipart/form-data as Content-Type
        }
      }).then(response=>{
        console.log(response.data)
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('updateentry', response.data);
        this.$router.push({name: 'GroupAppSettings'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
      })
    },
    submitForm() {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();
      var gatheredData = new FormData(this.$refs.form);     
      var formData = new FormData();
      var settings = {};
      for(var pair of gatheredData.entries()) {
        if(pair[0].startsWith('type_selectAgentSettings') && (pair[1]=='0' || pair[1]=='1' || pair[1]=='2' || pair[1]=='3')) {
          if(gatheredData.get('unrestricted_'+pair[0].slice(5))) {
            settings[this.types.filter(e=>e.id==pair[1])[0].name]={unrestricted: true}
          } else {
            settings[this.types.filter(e=>e.id==pair[1])[0].name]=JSON.parse(gatheredData.get(pair[0].slice(5)))
          }
        }
      }
      formData.append('groupId', gatheredData.get('group'))
      formData.append('appId', gatheredData.get('app'))
      formData.append('settings', JSON.stringify(settings));
    
      axios({
        method: 'post',
        url: `${this.apiUrl}/groupappsettings`,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newentry', response.data);
        this.$router.push({name: 'GroupAppSettings'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
      })

    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#store-entry {
  position: relative;
  display: flex;
  flex-direction: column;
  width: 100%;
  align-items: center;
  padding: 10px;
}
#store-entry-header {
  border-bottom: 2px solid $text_dark;
  width: 100%;
  max-width: 640px;
}
#store-entry-body {
  position: relative;
  width: 100%;
  max-width: 640px;
}
h4 {
  margin-top: 12px;
  margin-bottom: 12px;
  margin-left: 0;
  margin-right: 0;
  font-weight: bold;
}
.form-element {
  position: relative;
}
#entry-form {
  position: relative;
}
</style>
