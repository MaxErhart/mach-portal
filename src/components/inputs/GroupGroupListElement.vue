<template>
  <div class="group-group-list-elements">
    <div class="row-wrapper" v-for="(entry,idx) in entries" :key="entry">
      <SelectElement :data="groupSelectDataSource" :presetValue="presetValue ? String(entries[idx].source) : null" :height="24" :clear="false" @selectedEntry="source($event, idx)" :emit="true" :nameAsValue="false" :dynamic="false"/>
      <SelectElement :data="groupSelectDataTarget" :presetValue="presetValue ? String(entries[idx].target) : null" :height="24" :clear="false" @selectedEntry="target($event, idx)" :emit="true" :nameAsValue="false" :dynamic="false"/>
      <SelectElement :data="permissionTypeData" :presetValue="presetValue ? String(entries[idx].type) : null" :height="24" :clear="false" @selectedEntry="type($event, idx)" :emit="true" :nameAsValue="true" :dynamic="true"/>
      <button id="delete-pseudo-element" @click.prevent="deleteEntry(idx)">
        <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
      </button>
      <input type="hidden" :name="`${name}_${idx}`" :value="JSON.stringify({source: entries[idx].source, target: entries[idx].target, type: entries[idx].type})">  
    </div>
    <button class="add-row" @click.prevent="addEntry()">Add Permission</button>

  </div>
</template>

<script>
import SelectElement from '@/components/inputs/SelectElement.vue'
import axios from "axios";
export default {
  name: 'GroupGroupListElement',
  components: {
    SelectElement
  },
  props: {
    name: String,
    presetValue: Object,
  },
  data() {
    return {
      entries: [],
      groups: [],
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },    
    groupSelectDataSource() {
      var data = {label: 'Permissions for Group', required: true, tooltip: null}
      data["numoptions"] = this.groups.length
      this.groups.forEach((v, i)=>{
        data[`${i}`]=v
      })
      return data
    },
    groupSelectDataTarget() {
      var data = {label: 'Permissions on Group', required: true, tooltip: null}
      data["numoptions"] = this.groups.length
      this.groups.forEach((v, i)=>{
        data[`${i}`]=v
      })
      return data
    },    
    permissionTypeData() {
      var data = {label: 'Permissions type', required: true, tooltip: null, "numoptions":2, "0": "read", "1": "edit"}
      return data
    },    
    filteredGroups() {
      return this.groups.filter(g=>!this.selectedGroups.map(e=>e.id).includes(g.id))
    },          
  },
  mounted() {
    this.getGroups()
    if(this.presetValue) {
      for(var idx=0; idx<this.presetValue.length;idx++) {
        this.entries.push({source: this.presetValue[idx].agent_id, target: this.presetValue[idx].group_id, type: this.presetValue[idx].permission})
      }
    }
  },
  watch: {
    presetValue(to) {
      for(var idx=0; idx<to.length;idx++) {
        this.entries.push({source: to[idx].agent_id, target: to[idx].group_id, type: to[idx].permission})
      }
    }
  },   
  methods: {
    source(event, idx) {
      this.entries[idx].source = event.id
    },
    target(event, idx) {
      this.entries[idx].target = event.id

    },
    type(event, idx) {
      this.entries[idx].type = event
    },        
    addEntry() {
      this.entries.push({source: null, target:null, type:null})
    },
    deleteEntry(idx) {
      this.entries.splice(idx-1, 1)
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
        this.groups = this.groups.concat(response.data);
        this.awaitGroupData = false;
      })
    },    
  }
}
</script>


<style scoped lang="scss">
.row-wrapper {
  position: relative;
  // outline: 1px solid green;
  display: grid;
  grid-template-columns: 1fr 1fr 175px;
  grid-gap: 5px;
  padding: 5px 25px 5px 5px;
}
.add-row {
  width: 100%;
  height: 32px;
  margin: 5px 0;
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
