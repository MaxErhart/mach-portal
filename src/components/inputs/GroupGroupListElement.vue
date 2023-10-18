<template>
  <div class="group-group-list-elements">
    <div class="row-wrapper" v-for="(entry,idx) in entries" :key="entry">
      <SelectElement :optional="false" :label="`Permission for ${entry.sourceType}`" :cast="getCast(entry.sourceType)" :data="getData(entry.sourceType)" :search="true" :presetValue="entry.source"  @selectedEntry="source($event, idx)"/>
      <SelectElement :optional="false" :label="`Permission on ${entry.targetType}`" :cast="getCast(entry.targetType)" :data="getData(entry.targetType)" :search="true" :presetValue="entry.target" @selectedEntry="target($event, idx)"/>
      <SelectElement :optional="false" label="Permission type" :data="permissionTypes" :search="false" :presetValue="entry.type" @selectedEntry="type($event, idx)"/>
      <button id="delete-pseudo-element" @click.prevent="deleteEntry(idx)">
        <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
      </button>
    </div>
    <input type="hidden" :name="name" :value="JSON.stringify(entries)">  
    Add Submission Permission
    <div class="button-collection">
      <button class="add-row" @click.prevent="addEntry(0)">Group on Group</button>
      <button class="add-row" @click.prevent="addEntry(1)">Group on User</button>
      <button class="add-row" @click.prevent="addEntry(2)">User on Group</button>
      <button class="add-row" @click.prevent="addEntry(3)">User on User</button>
    </div>


  </div>
</template>

<script>
import SelectElement from '@/components/inputs/SelectElement.vue'
export default {
  name: 'GroupGroupListElement',
  components: {
    SelectElement
  },
  props: {
    name: String,
    presetValue: Object,
    groups: Array,
    users: Array,
  },
  data() {
    return {
      entries: [],
      permissionTypes: [
        {id: 1, name: 'Read'},
        {id: 2, name: 'Edit'},
        {id: 3, name: 'Delete'},
      ],
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
  },
  mounted() {
    this.matchPresets(this.presetValue)
  },
  watch: {
    presetValue(to) {
      this.matchPresets(to)
    }
  },   
  methods: {
    getCast(type) {
      if(type==='Group') {
        return undefined
      }
      return this.castUser
    },
    getData(type) {
      if(type==='Group') {
        return this.groups
      }
      return this.users
    },
    castUser(user) {
      return {id: user.id, name: `(${user.id}) ${user.firstname} ${user.lastname}`}
    },
    matchPresets(values) {
      console.log(values)
      if(!values) {
        return
      }
      this.entries = []
      values.forEach(val=>{
        this.entries.push({
          id:val.id,
          sourceType: val.agent_type==="App\\Models\\Group"?'Group':'User',
          source: parseInt(val.agent_id),
          targetType: 'group_id' in val?'Group':'User',
          target:  parseInt('group_id' in val?val.group_id:val.user_id),
          type: val.permission
        })
      })
    },
    source(event, idx) {
      this.entries[idx].source = event.id
    },
    target(event, idx) {
      this.entries[idx].target = event.id

    },
    type(event, idx) {
      this.entries[idx].type = event.id
    },        
    addEntry(typesId) {
      switch(typesId) {
        case 0:
          this.entries.push({sourceType: 'Group', source: null, targetType: 'Group', target:null, type:null,id:null})
          break
        case 1:
          this.entries.push({sourceType: 'Group', source: null, targetType: 'User', target:null, type:null,id:null})
          break
        case 2:
          this.entries.push({sourceType: 'User', source: null, targetType: 'Group', target:null, type:null,id:null})
          break
        case 3:
          this.entries.push({sourceType: 'User', source: null, targetType: 'User', target:null, type:null,id:null})
          break
      }
    },
    deleteEntry(idx) {
      this.entries.splice(idx, 1)
    },   
  }
}
</script>


<style scoped lang="scss">
.group-group-list-elements {
  padding: .5rem 0;
}
.row-wrapper {
  position: relative;
  // outline: 1px solid green;
  display: grid;
  grid-template-columns: 1fr 1fr 175px;
  grid-gap: 5px;
  padding: 5px 25px 5px 5px;
}
.button-collection {
  width: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;
  justify-content: space-between;
  gap: 1rem;
}
.add-row {
  width: 100%;
  height: 32px;
  margin: 5px 0;
  border: 2px solid #dddddd;
  cursor: pointer;
  &:hover {
    background: #dddddd;
  }
}
#delete-pseudo-element {
  position: absolute;
  visibility: visible;
  background: none;
  border: none;
  right: 0px;
  height: 100%;
  z-index: 10;
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
</style>
