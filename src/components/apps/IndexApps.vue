<template>
  <div id="index-apps" v-if="apps">
    <div id="index-apps-body">
      <div id="index-apps-body-data-grid" :style="{gridTemplateColumns: `repeat(${displayProperties.length}, minmax(auto, 1fr))`}">
        <div class="column-name" v-for="prop in displayProperties" :key="prop">{{prop.displayName}}</div>
        <div class="row" v-for="(app, appIndex) in filter(apps)" :key="app.id" @click="selectApp($event, app.id)">
          <div class="data-field" v-for="(value, name, index) in cleanAppProperties(app)" :key="name">
            <button id="delete-pseudo-element" v-if="index==displayProperties.length-1" @click="deleteElement(app.id, appIndex)">
              <svg id="remove-icon" xmlns="http://www.w3.org/2000/svg" width="16" height="16" class="bi bi-backspace" viewBox="0 0 16 16" data-v-4d7acd4a=""><path d="M5.83 5.146a.5.5 0 0 0 0 .708L7.975 8l-2.147 2.146a.5.5 0 0 0 .707.708l2.147-2.147 2.146 2.147a.5.5 0 0 0 .707-.708L9.39 8l2.146-2.146a.5.5 0 0 0-.707-.708L8.683 7.293 6.536 5.146a.5.5 0 0 0-.707 0z" data-v-4d7acd4a=""></path><path d="M13.683 1a2 2 0 0 1 2 2v10a2 2 0 0 1-2 2h-7.08a2 2 0 0 1-1.519-.698L.241 8.65a1 1 0 0 1 0-1.302L5.084 1.7A2 2 0 0 1 6.603 1h7.08zm-7.08 1a1 1 0 0 0-.76.35L1 8l4.844 5.65a1 1 0 0 0 .759.35h7.08a1 1 0 0 0 1-1V3a1 1 0 0 0-1-1h-7.08z" data-v-4d7acd4a=""></path></svg>
            </button>
            <span class="value-span">{{value}}</span>
          </div>
        </div>
      </div>
    </div>
  </div>
</template>

<script>
import moment from 'moment'
export default {
  name: 'IndexApps',
  props: {
    apps: Object,
    method: String,
  },
  data() {
    return {
      displayProperties: [
        {prop: 'name', displayName: 'Name', format: null},
        {prop: 'created_at', displayName: 'Created at', format: 'DD.MM.YYYY'},
        {prop: 'updated_at', displayName: 'Updated at', format: 'DD.MM.YYYY'},
      ],
      mousedown: {
        id: null,
        style: {top: 0, right: 0, bottom: 0, left:0},
      }
    }
  },
  methods: {
    filter(apps) {
      if(this.method=='show') {
        return apps.filter(app=>app.id==this.$route.params.id);
      } else {
        return apps;
      }
    },
    cleanAppProperties(app) {
      const newEl = {};
      this.displayProperties.forEach(prop=>{
        if(prop.format) {
          newEl[prop.prop] = moment(app[prop.prop]).format(prop.format);
        } else {
          newEl[prop.prop] = app[prop.prop];
        }
      })
      return newEl;
    },
    selectApp(event, id) {
      if(event.target.id!='remove-icon' && event.target.id!='delete-pseudo-element') {
        this.$router.push({name: 'NewApp', params: {id}})
      }
    },
    deleteElement(appId, appIndex) {
      this.$emit('deleteElement', {id: appId, index: appIndex})
    }
  },
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#index-apps-body-data-grid {
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
      background-color: $table_dark;
    }
  }
  &:nth-child(2n+1){
    > * {
      background-color: $table_light;
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