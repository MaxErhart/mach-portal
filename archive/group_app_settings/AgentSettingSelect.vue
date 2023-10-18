<template >
  <div id="element-body">
    <section id="select-type" class="form-element">
      <SelectElement :dynamic="false" :data="selectData" :presetValue="selectedType ? selectedType.name : null" @inputChanged="typeInputChanged($event)" @selectedEntry="typeSelected($event)" :height="selectedType ? 0 : 24" :emit="true" :name="'type_'+name" :nameAsValue="false"/>
    </section>
    <section id="unrestricted-checkbos" v-if="selectedType">
      <label :for="'restricted_'+name">Unrestricted:</label>
      <input type="checkbox" :name="'unrestricted_'+name" :checked="unrestricted" :true-value="true" :false-value="false" @change="unrestricted=!unrestricted">
    </section>
    <section id="select-agents" class="form-element-wrapper" v-if="selectedType && !unrestricted" >
      <div id="select-groups" class="select-agent">
        <SelectElement :dynamic="false" :data="groupSelectData" :height="24" :clear="true" :emit="true" @selectedEntry="addGroup($event)" :nameAsValue="false"/>
        <div class="display-selected-agents">
          <div class="agent" v-for="(group,index) in selectedGroups" :key="group.id">
            <span>
              {{groupname(group)}}
            </span>
            <span>
              <button id="delete-pseudo-element" @click="removeGroup(index)">
                <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
              </button>              
            </span> 
          </div>
        </div>
      </div>
      <div id="select-users" class="select-agent">
        <SelectElement :dynamic="false" :data="userSelectData" :height="24" :clear="true" :emit="true" @selectedEntry="addUser($event)" :nameCast="username" :nameAsValue="false"/>
        <div class="display-selected-agents">
          <div class="agent" v-for="(user,index) in selectedUsers" :key="user.id">
            <span>{{username(user)}}</span>
            <span>
              <button id="delete-pseudo-element" @click="removeUser(index)">
                <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
              </button>              
            </span>
          </div>
        </div>
      </div>
    </section>  
  </div>
  <input type="hidden" :name="name" :value="JSON.stringify({groups: selectedGroups, users: selectedUsers})">
</template>

<script>
import * as validationSettings from '@/validationSettings.json'
import SelectElement from '@/components/inputs/SelectElement.vue'
// import InputElement from '@/components/inputs/InputElement.vue'
export default {
  name: 'SelectOpenListElement',
  components: {
    // InputElement,
    SelectElement,
  },
  emits: ['typeSelected', 'typeInputChanged'],
  props: {
    types: Object,
    users: Object,
    groups: Object,
    name: String,
    required: Boolean,
    presetValues: Object,
    presetType: Object,
  },
  data() {
    return {
      validationSettings,
      value: null,
      selectActive: false,

      isFocused: false,
      deFocusedOnce: false,
      selectedGroups: [],
      selectedUsers: [],
      selectedType: null,
      unrestricted: true,
    }
  },
  mounted() {
    if(this.presetValues) {
      this.selectedType=this.presetValues.type
      this.unrestricted=this.presetValues.unrestricted
      this.selectedUsers=this.presetValues.users
      this.selectedGroups=this.presetValues.groups
    }
    if(this.presetType) {
      this.selectedType=this.presetType
    }
  },
  watch: {
    presetValues(to) {
      this.unrestricted=to.unrestricted
      this.selectedUsers=to.users
      this.selectedGroups=to.groups    
    },
    presetType(to) {
      this.selectedType = to;
    }
  },
  computed: {
    selectData() {
      var data = {label: 'Select Type', required: false, placeholder: null, tooltip: null}
      this.types.forEach((v, i)=>{
        data[String(i)]=v
      })
      return data
    },
    groupSelectData() {
      var data = {label: 'Select Group', required: false, placeholder: 'type to search for group', tooltip: null}
      this.filteredGroups.forEach((v, i)=>{
        data[String(i)]=v
      })
      return data
    }, 
    userSelectData() {
      var data = {label: 'Select User', required: false, placeholder: 'type to search for user', tooltip: null}
      this.filteredUsers.forEach((v, i)=>{
        data[String(i)]=v
        // data[String(i)]=`${v.firstname} ${v.lastname} (${v.id})`
      })
      console.log(this.filteredUsers)
      return data
    },           
    filteredGroups() {
      return this.groups.filter(g=>!this.selectedGroups.map(e=>e.id).includes(g.id))
    },
    filteredUsers() {
      console.log(this.users)
      return this.users.filter(u=>!this.selectedUsers.map(e=>e.id).includes(u.id))
    },
    hasError() {
      if(this.required && !this.selectedType) {
        return true;
      } else if(this.selectedType && this.selectedUsers==[] && this.selectedGroups==[]) {
        return true;
      }
      return false;
    },
  },
  methods: {
    typeSelected(event) {
      this.selectedType = event;
      this.$emit('typeSelected', event);
    },
    typeInputChanged(event) {
      var match = null
      this.types.forEach(e=>{
        if(e.name==event) {
          match = e
        }
      })
      this.selectedType = match
      this.$emit('typeInputChanged', event);
    },
    removeUser(index) {
      this.selectedUsers.splice(index, 1)
    },
    removeGroup(index) {
      this.selectedGroups.splice(index, 1)
    },        
    addGroup(group) {
      this.selectedGroups.push(group);
    },
    addUser(user) {
      this.selectedUsers.push(user);
    },
    groupname(group) {
      return group.name;
    },
    username(user) {
      return `${user.lastname} (${user.id})`;
    },

  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';

#delete-pseudo-element {
  position: absolute;
  background: none;
  border: none;
  right: 0px;
  height: 100%;
  top: 0;
  display: flex;
  flex-direction: column;
  justify-content: center;
  cursor: pointer;
  &:hover {
    > * {
      fill: red;
    }
  }
}
#select-agents {
  display: grid;
  grid-gap: 8px;
  grid-template-columns: 1fr 1fr;
}
.display-selected-agents{
  margin-top: -12px;
  margin-bottom: 24px;
  padding: 4px 4px;
  height: 108px;
  max-height: 108px;
  box-shadow: 0 0 4px 2px inset rgba(0, 0, 0, 0.2);
  overflow-y: scroll;
  &:hover {
    outline: 2px solid black;
    border-radius: 2px;
  }
}

.agent{
  position: relative;
  padding: 4px 0;
  &:hover {
    background-color: $background_hover_dark;
    color: $text_light;
    fill: $text_light;
  }
}
#unrestricted-checkbos {
  margin: 24px 0 0 0;
}
</style>