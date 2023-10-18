<template>
  <div class="index-forms" ref="body">
    <TagsBar :tags="tags" @tagChange="tagChange($event)"/>
    <NavBar :tabs="navTabs" :tabWidth="tabWidth" @change="tabChange($event)"/>
    <div id="index-forms-body">
      <div id="index-forms-body-data-grid" :style="{gridTemplateColumns: `repeat(${displayProperties.length}, minmax(auto, 1fr))`}">
        <div class="column-name" v-for="prop in displayProperties" :key="prop">{{prop.displayName}}</div>
        <div class="row" v-for="(form) in filter(forms)" :key="form.id" @click="selectElement($event, form.id)">
          <div class="data-field" v-for="(value, name) in cleanProps(form)" :key="name">
            <span class="value-span">{{value}}</span>
          </div>
        </div>
      </div>
    </div>    
  </div>
</template>

<script>
import TagsBar from '@/components/TagsBar.vue'
import NavBar from '@/components/NavBar.vue'
import moment from 'moment'
export default {
  name: 'IndexFormsSubmission',
  components: {
    TagsBar,
    NavBar,
  },
  props: {
    forms: Object,
  },
  data() {
    return {
      activeTag: null,
      recent: true,
      tabWidth: 175,
      displayProperties: [
        {prop: 'name', displayName: 'Name', format: null},
        {prop: 'deadline', displayName: 'Deadline', type: 'date', format: 'DD.MM.YYYY'},
      ], 
      navTabs: [
        {text: 'Recent', route: {name: null}},
        {text: 'Expired', route: {name: null}},
      ],           
    }
  },
  mounted() {
    this.$nextTick(()=>{
      if(this.$refs.body) {
        this.tabWidth = this.$refs.body.getBoundingClientRect().width/2
      }
    })
  },
  computed: {

    tags() {
      var tags = [{id: -1, name: 'Alle'}]
      this.forms.forEach(e=>{
        e.tags.forEach(t=>{
          if(!tags.includes(t)){
            tags.push(t)
          }
        })
      })
      return tags
    },
  },
  methods: {
    tabChange(e) {
      if(e==0){
        this.recent=true
      } else if(e==1) {
        this.recent=false
      }
    },
    cleanProps(entry) {
      const newEl = {};
      this.displayProperties.forEach(prop=>{
        if(prop.type=='date') {
          if(entry[prop.prop]) {
            newEl[prop.prop] = moment(entry[prop.prop]).format(prop.format);
          } else {
            newEl[prop.prop] = ""
          }
        } else if(prop.type=='boolean') {
          newEl[prop.prop] = entry[prop.prop] ? 'true' : 'false';
        } else {
          newEl[prop.prop] = entry[prop.prop];
        }
      })
      return newEl;
    },    
    filter(forms) {
      if(!forms) {
        return null
      }
      var filtered_forms = forms
      if(this.activeTag==null || this.activeTag.id==-1) {
        filtered_forms = forms;
      } else {
        filtered_forms = forms.filter(f=>f.tags.includes(this.activeTag))
      }
      if(this.recent) {

        filtered_forms = filtered_forms.filter((f)=>{
          if(!f.deadline){
            return true
          } else {
            return moment(f.deadline).format("yyyy-MM-DD")>=moment().format("yyyy-MM-DD")
          }
        })
      } else {
        filtered_forms = filtered_forms.filter((f)=>{
          if(!f.deadline){
            return false
          } else {
            return moment(f.deadline).format("yyyy-MM-DD")<moment().format("yyyy-MM-DD")
          }
        })
      }
      return filtered_forms
    },
    tagChange(event) {
      this.activeTag = event
    },
    selectElement(event, id) {
      localStorage.form = JSON.stringify(this.forms.filter(f=>f.id==id)[0])
      this.$router.push({name: 'submissions', params: {id: id}})
    },  
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#index-forms-body-data-grid {
  position: relative;
  display: grid;
  justify-content: center;
  margin: 0 auto;
  list-style: none;
}
.column-name {
  position: relative;
  width: 100%;
  text-align: center;
  background-color: $kit_green;
  color: $text_light;
  padding: 8px 0;
}
.row {
  display: contents;
  outline: black;
  
  &:nth-child(2n){
    > * {
      background-color: #eee;
    }
  }
  &:nth-child(2n+1){
    > * {
      background-color: #fff;
    }
  }
  &:hover {
    > * {
      cursor: pointer;
      background-color: $text_dark;
      color: $text_light;
    }
    #delete-pseudo-element {
      visibility: visible;
      fill: $text_light;
    }
  }
  
}
.data-field {
  position: relative;
  text-align: center;
  padding: 8px 0;
}
#row-pseudo-element {
  position: absolute;
  pointer-events: none;
  background: none;
  margin: none;
  padding: none;
  border: none;
  border-radius: none;
  transition: box-shadow 0.2s ease;
  box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.0);
  &.active {
    transition: box-shadow 0.4s ease;
    box-shadow: 0 0 2px 1px rgba(0, 0, 0, 0.65);
    z-index: 11;
    outline: 2px solid black;
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
