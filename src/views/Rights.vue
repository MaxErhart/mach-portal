<template>
  <div id="rights">
    <h1>Edit User and Group Rights</h1>
    <div id="rights-content">

      <div id="rights-content-header">
        <div id="tabs">
          <div id="tab-indicator" :style="tabIndicatorPosition"></div>
          <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">User Rights</div>
          <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">Group Rights</div>
        </div>         
      </div>

      <div id="rights-content-body" v-if="users != null && groups != null">
        <div id="side-panel" >
          <div class="side-panel-object" :class="{active: sidePanelItem.id == activeObjectId}" v-for="sidePanelItem in sidePanelItems" :key="sidePanelItem" @click="setActiveObject(sidePanelItem.id)">{{removeDefaultGroupName(sidePanelItem.name)}}</div>
        </div>
        <div id="active-object-rights" v-if="activeObjectId != null && objectRights.length > 0">
          <div class="column-names">
            <div id="buffer">
              <select></select>
            </div>
            <div class="col-name">
              <div class="col-type">Write</div>
              <div class="col-name-objects">
                <div >Users</div>
                <div>Groups</div>
              </div>
            </div>
            <div class="col-name">
              <div class="col-type">Read</div>
              <div class="col-name-objects">
                <div>Users</div>
                <div>Groups</div>
              </div>
            </div>

         
          </div>
          <div id="user-rights">
            
            <div class="user-right"  v-for="right in objectRights" :key="right.featureId">
              <div class="feature-selection">                
                <select name="feature" class="feature" v-model="right.featureId" @change="updateFeature(right)">
                  <option :value="right.featureId">{{features[right.featureId]}}</option>
                  <option :value="featureId" v-for="(feature, featureId) in remainingFeatures(objectRights)" :key="featureId">{{feature}}</option>
                </select>
              </div>

              <div class="right-option" v-if="right.w && right.loading!='w'"> 
                <div class="right-option-body" v-if="!right.w.loading">
                  <div class="right-option-element">
                    <div class="right-option-element-body">
                      <SelectOnceFromList :listOfItems="users" rightType="w" objectType="users" :rightObject="right" @selection-changed="updateRight($event, right)"/>
                    </div>
                  </div>
                  <div class="right-option-element">
                    <div class="right-option-element-body">
                      <SelectOnceFromList :listOfItems="groups" rightType="w" objectType="groups" :rightObject="right" @selection-changed="updateRight($event, right)"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="no-right-option" v-else @click="createRight(right, 'w')">
                <template v-if="right.loading =='w'"> Loading...</template>
                <template v-else>create Write options</template>
              </div>
              <div class="right-option" v-if="right.r && right.loading!='r'"> 
                <div class="right-option-body">
                  <div class="right-option-element">
                    <div class="right-option-element-body">
                      <SelectOnceFromList :listOfItems="users" rightType="r" objectType="users" :rightObject="right" @selection-changed="updateRight($event, right)"/>
                    </div>
                  </div>
                  <div class="right-option-element">
                    <div class="right-option-element-body">
                      <SelectOnceFromList :listOfItems="groups" rightType="r" objectType="groups" :rightObject="right" @selection-changed="updateRight($event, right)"/>
                    </div>
                  </div>
                </div>
              </div>
              <div class="no-right-option" v-else @click="createRight(right, 'r')">
                <template v-if="right.loading == 'r'"> Loading...</template>
                <template v-else>create Read options</template>
                
              </div>                

            </div>
          </div>
        </div>
        <div id="no-active-object-rights" v-else>
          No active Object Rights
        </div>
        <div id="new-feature-rights" v-if="activeObjectId != null" >
          New Right
          <select name="feature" class="feature"  @change="createNewFeature(selectedNewFeature)" v-model="selectedNewFeature">
            <option :value="featureId" v-for="(feature, featureId) in remainingFeatures(objectRights)" :key="featureId">{{feature}}</option>
          </select>
        </div>
      </div>


    </div>
  </div>
</template>

<script>
import axios from "axios";
import SelectOnceFromList from '@/components/SelectOnceFromList.vue'

var _ = require('lodash');

export default {
  name: 'Rights',
  components: {
    SelectOnceFromList
  },
  data() {
    return {
      activeTab: 0,
      activeObjectId: null,
      users: null,
      groups: null,
      features: null,
      userRights: null,
      groupRights: null,
      objectRights: null,
      selectedNewFeature: null
    }
  },
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[4])   
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/editRights.php',
    }).then(response => {
      console.log(response.data)
      if(response.data.error == null) {
        this.users = _.orderBy(response.data.users, 'lastname')
        this.groups = _.orderBy(response.data.groups, 'groupName')
        this.features = response.data.features.reduce(function(obj,item) {
          obj[item.featureId] = item.featureName; 
          return obj;
        }, {});
        this.userRights = response.data.userRights
        this.groupRights = response.data.groupRights
        console.log(this.userRights)
      } else {
        console.log(response.data)
        this.$router.push('/')
      }

    })
  },
  beforeUnmount() {
    axios({
      method: 'post',
      url: 'https://www-3.mach.kit.edu/api/editRights.php',
      data: {user_rights: 0, mode: 'cleanup'}
    })
    axios({
      method: 'post',
      url: 'https://www-3.mach.kit.edu/api/editRights.php',
      data: {user_rights: 1, mode: 'cleanup'}
    })          
  },
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 175*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },
    sidePanelItems: function() {
      if(this.activeTab == 0) {
        return this.users.map((el) => {
          return {name: el.lastname, id: el.userId} 
        }) 
      } else {
        return this.groups.map((el) => {
          return {name: el.groupName, id: el.groupId}
        })
      }
    },
  },
  methods: {
    removeDefaultGroupName: function(name) {
      if(name.includes("MACH-Portal", 0)) {
        return name.substring(12)
      } else {
        return name
      }
    },    
    createNewFeature(feature) {
      this.objectRights.push({
        featureId: feature,
        w: null,
        r: null
      })
    },
    createRight(right, rightType) {
      if(!right['loading']) {
        right[rightType] = {}
        right[rightType]['users'] = []
        right[rightType]['groups'] = []
        right['loading'] = rightType
        axios({
          method: 'post',
          url: 'https://www-3.mach.kit.edu/api/editRights.php',
          data: {user_rights: this.activeTab==0, mode: 'insert', featureId: right.featureId, objectId: this.activeObjectId, rights: rightType, rightsTarget: right[rightType]}
        }).then(response => {
          right['loading'] = null
          right[rightType]['id'] = response.data.id
          console.log(right)
        })
      }


    },
    updateRight(event, right) {
      console.log(event)
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/editRights.php',
        data: {user_rights: this.activeTab==0, mode: 'update', id: event.id, rightsTarget: event.rightsTarget, featureId: right.featureId}
      }).then(response => {
        console.log(response.data)
      })
    },
    updateFeature(right) {
      if(right.w) {
        axios({
          method: 'post',
          url: 'https://www-3.mach.kit.edu/api/editRights.php',
          data: {user_rights: this.activeTab==0, mode: 'update', id: right.w.id, rightsTarget: {users: right.w.users, groups: right.w.groups}, featureId: right.featureId}
        })
      }
      if(right.r) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/editRights.php',
        data: {user_rights: this.activeTab==0, mode: 'update', id: right.r.id, rightsTarget: {users: right.r.users, groups: right.r.groups}, featureId: right.featureId}
      })        
      }
    },
    deleteRight(right) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/editRights.php',
        data: {user_rights: this.activeTab==0, moder: 'delete', right: right}
      }).then(response => {
        console.log(response)
      })
    },   
    changeTab(tabId) {
      this.activeTab = tabId
      this.activeObjectId = null
    },

    remainingFeatures(userRights) {
      var remainingFeatures = JSON.parse(JSON.stringify(this.features))
      userRights.forEach(el => {
        delete remainingFeatures[el.featureId]
      })
      return remainingFeatures
    },
    setActiveObject(sidePanelItemId) {
      this.activeObjectId = sidePanelItemId
      this.objectRights = this.objectFeatureRights()
    },
    objectId(item) {
      if(item.userRightId) {
        return item.userRightId
      } else if(item.groupRightId) {
        return item.groupRightId
      } else {
        return null
      }
    },    
    objectFeatureRights() {
      var activeObjectRights = []
      if(this.activeTab==0) {
        activeObjectRights = this.userRights.filter(el => el.userId == this.activeObjectId)
      } else {
        activeObjectRights = this.groupRights.filter(el => el.groupId == this.activeObjectId)
      }
      
      var temp = []
      var features = {}
      activeObjectRights.forEach(el => {  
        
        if(!(el.featureId in features)) {
          features[el.featureId] = true
          var row = {featureId: el.featureId}
          if(el.rights == 'w') {
            row['w'] = {users: el.rightsTarget.users, groups: el.rightsTarget.groups, id: this.objectId(el)}
            row['r'] = null
          } else {
            row['r'] = {users: el.rightsTarget.users, groups: el.rightsTarget.groups, id: this.objectId(el)}
            row['w'] = null
          }
          temp.push(row)
        } else {
          if(el.rights == 'w') {
            temp.filter(x => x.featureId == el.featureId)[0]['w'] = {users: el.rightsTarget.users, groups: el.rightsTarget.groups, id: this.objectId(el)}
          } else {
            temp.filter(x => x.featureId == el.featureId)[0]['r'] = {users: el.rightsTarget.users, groups: el.rightsTarget.groups, id: this.objectId(el)}
          }          
        }
      })
      // console.log()
      console.log(temp)
      return temp
    }    
  }
}
</script>

<style lang="scss" scoped>
  .delete-right {
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    width: 120px;
    padding: 2px;
    background-color: #efefef;
    border: 1px solid #2c3e50;             
    &:hover {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);

    }
    &:active {
      box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
    }      
  }
  select {
    display: block;
    width: 120px;
    height: 28px;
    font-size: 14px;
    border: 1px solid #ccc;
  }
  #rights {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
  }
  #rights-content {
    background-color: #eee;
    width: 100%;

    > #rights-content-header {
      width: 100%;
      height: 37px;
      > #tabs {
        background-color: #e3e3e3;
        height: 100%;
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
    
    > #rights-content-body {
      min-height: 50px;
      width: 100%;
      display: grid;
      grid-template-columns: 150px auto;
      > #side-panel {
        grid-row: 1/ span 2;
        width: 150px;
        border: 1px solid #2c3e50;
        > .side-panel-object {
          &.active {
            background-color: #aaa;
          }
        }
      }
      > #new-feature-rights {
        grid-column: 2/ span 1;
      }
      > #no-active-object-rights {
        width: 100%;
        display:flex;
        align-items: center;
        justify-content: center;

      }      
      > #active-object-rights {
        display: grid;
        > .column-names {
          display: flex;
          flex-direction: row;
          width: 100%;
          > #buffer {
            > * {
              visibility: hidden;
            }
          }
          > .col-name {
            width: 100%;
            > .col-type {

              text-align: center;
            }
            > .col-name-objects {
              display: flex;
              flex-direction: row;
              > * {
                width: 100%;
                text-align: center;
              }
            }
          }
        }
        > #user-rights {
          > .user-right {
            // width: 100%;
            display: grid;
            grid-template-columns: auto 1fr 1fr;
            align-items: center;
            min-height: 60px;
            > .feature-selection {
              margin: 0 2px 0 0;
            }
            margin: 4px 2px;

            > .no-right-option {
              cursor: pointer;
              display: flex;
              justify-content: center;
              align-items: center;
              width: 100%;
              height: 100%;
              background-color: #efefef;
              border: 1px solid #2c3e50;
              border-radius: 2px;              
              &:hover {
                box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);

              }
              &:active {
                box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
              }  
            }
            .right-option {
              width: 100%;
              justify-content: center;


              > .right-option-title {
                text-align: center;
              }
              >.right-option-body {
                display: flex;
                flex-direction: row;
                >.right-option-element {
                  width: 100%;
                  background-color: #fff;
                  margin: 0 1px;
                  >.right-option-element-title {
                    text-align: center;
                  }                 
                }
              }
            }
            

          }
        }
      }      
    }

  }

</style>