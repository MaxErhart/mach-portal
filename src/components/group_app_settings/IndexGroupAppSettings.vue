<template>
  <div id="index-data" v-if="data">
    <div id="index-data-body">
      <div id="index-data-body-data-grid" :style="{gridTemplateColumns: `repeat(${displayProperties.length}, minmax(auto, 1fr))`}">
        <div class="column-name" v-for="prop in displayProperties" :key="prop">{{prop.displayName}}</div>
        <button id="row-pseudo-element" :style="mousedownStyle" :class="{active: mousedown.id}"></button>
        <div class="row" v-for="(entry, entryIndex) in data" :key="entry.id" @mousedown="handleMousedown($event, entry.id)" @click="selectEntry($event, entry.id)" :class="{mousedown: entry.id==mousedown.id}">
          <div class="data-field" v-for="(value, name, index) in cleanDataProperties(entry)" :key="name">
            <button id="delete-pseudo-element" v-if="index==displayProperties.length-1" @click.stop="deleteElement(entry.id, entryIndex)">
              <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
            </button>
            <span class="agent-settings" v-if="name.startsWith('settings_') && typeof(value)=='object'">
              <div class="agent" v-for="agent in value" :key="agent"><span>-</span><span>{{agent}}</span></div>
            </span>
            <span class="value-span" v-else>{{value}}</span>
          </div>
        </div>
      </div>
    </div>
  </div>



  
</template>

<script>
import moment from 'moment'
export default {
  name: 'IndexGroupAppSettings',
  props: {
    data: Object,
  },
  data() {
    return {
      displayProperties: [
        {prop: ['apps', 'name'], displayName: 'App Name', format: null},
        {prop: ['groups', 'name'], displayName: 'Group Name', format: null},
        {prop: ['settings'], displayName: 'Index', format: 'index', type: 'controllerMethodSetting'},
        {prop: ['settings'], displayName: 'Store', format: 'store', type: 'controllerMethodSetting'},
        {prop: ['settings'], displayName: 'Update', format: 'update', type: 'controllerMethodSetting'},
        {prop: ['settings'], displayName: 'Destroy', format: 'destroy', type: 'controllerMethodSetting'},
        {prop: ['created_at'], displayName: 'Created at', format: 'DD.MM.YYYY', type: 'date'},
        {prop: ['updated_at'], displayName: 'Updated at', format: 'DD.MM.YYYY', type: 'date'},
      ],
      mousedown: {
        id: null,
        style: {top: 0, right: 0, bottom: 0, left:0},
      }
    }
  },
  computed: {
    mousedownStyle() {
      return {
        top: `${this.mousedown.style.top}px`,
        height: `${this.mousedown.style.bottom-this.mousedown.style.top}px`,
        width: `${this.mousedown.style.right-this.mousedown.style.left}px`,
      }
    }
  },
  methods: {
    cleanDataProperties(entry) {
      const newEl = {};
      this.displayProperties.forEach(prop=>{
        var tmp = entry
        var keys = []  
        prop.prop.forEach(layer=>{
          tmp = tmp[layer];
          keys.push(layer); 
        })  
        if(prop.format){
          if(prop.type=='date') {
            newEl[keys.join('_')] = moment(tmp).format(prop.format);
          } else if(prop.type=='controllerMethodSetting') {
            const setting = tmp.filter(e=>e.type==prop.format)[0]
            if(!setting) {
              newEl[`settings_${prop.format}`] = 'restricted';
            } else {
              var agents = setting.users.map(u=>`${u.lastname} (${u.id})`)
              agents = agents.concat(setting.groups.map(g=>`${g.name}`))
              if(agents.length>3) {
                agents = agents.slice(0,3)
                agents.push('...')
              }
              newEl[`settings_${prop.format}`] = setting.unrestricted ? 'unrestricted': agents;
            }

          }
        } else {
          newEl[keys.join('_')] = tmp;

        }


      })
      return newEl;
    },
    handleMousedown(event, id) {
      document.addEventListener('mouseup',this.handleMouseup);
      const body = event.target.parentElement.parentElement.getBoundingClientRect();
      const element = event.target.getBoundingClientRect();
      this.mousedown.style.top = element.top-body.top;
      this.mousedown.style.right = body.right;
      this.mousedown.style.bottom = element.bottom-body.top;
      this.mousedown.style.left = body.left;
      this.mousedown.id = id;     
    },
    selectEntry(event, id) {
      if(event.target.id!='remove-icon' && event.target.id!='delete-pseudo-element') {
        this.$router.push({name: 'NewGroupAppSettings', params: {id}})
      }
    },
    handleMouseup() {
      this.mousedown.id=null;
      document.removeEventListener('mouseup',this.handleMouseup);
    },
    deleteElement(entryId, entryIndex) {
      this.$emit('deleteElement', {id: entryId, index: entryIndex})
    }
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#index-data-body-data-grid {
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
.agent-settings {
  display: flex;
  flex-direction: column;
  // justify-content: flex-start;
  > *:nth-child(2n) {
    // border-left: 1px solid black;
  }
}
.agent {
  display: grid;
  grid-template-columns: 8px auto;
  :first-child {
    font-weight: bold;
    padding: 0 0 0 2px;
    // margin-right: auto;
  }
}
</style>