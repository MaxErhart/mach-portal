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
          <div class="side-panel-object" :class="{active: sidePanelItem.id == activeObjectId}" v-for="sidePanelItem in sidePanelItems" :key="sidePanelItem" @click="activeObjectId=sidePanelItem.id">{{sidePanelItem.name}}</div>
        </div>
        <div id="active-object-rights" v-if="activeObjectId != null && activeUserRights != null && activeUserRights.length > 0">
          <div id="user-rights" v-if="activeTab==0">
            <div class="user-right" v-for="right in activeUserRights" :key="right">
              <div class="feature">
                {{features[right.featureId]}}
              </div>
              <div class="right">
                <template v-if="right.rights=='r'">
                  Read
                </template>
                <template v-else>
                  Write
                </template>                
              </div>


              <div class="right-target-users">
                <template v-for="user in right.rightsTarget.users" :key="user">
                  {{user}}
                </template>

              </div>
              <div class="right-target-groups">
                <template v-for="group in right.rightsTarget.groups" :key="group">
                  {{group}}
                </template>                
              </div>


            </div>
          </div>
        </div>
        <div id="no-active-object-rights" v-else>
          No active Object Rights
        </div>
      </div>


    </div>
  </div>
</template>

<script>
import axios from "axios";
var _ = require('lodash');

export default {
  name: 'Rights',
  components: {
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
    }
  },
  mounted() {
    // this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[0])   
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/editRights.php',
    }).then(response => {
      this.users = response.data.users
      this.groups = response.data.groups
      this.features = response.data.features.reduce(function(obj,item) {
        obj[item.featureId] = item.featureName; 
        return obj;
      }, {});
      this.userRights = response.data.userRights
      this.groupRights = response.data.groupRights
      console.log(response.data)
    })
  },
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 175*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },
    sidePanelItems: function() {
      if(this.activeTab == 0) {
        var users = _.orderBy(this.users, 'lastname')
        return users.map((el) => {
          return {name: el.lastname, id: el.userId} 
        }) 
      } else {
        var groups = _.orderBy(this.groups, 'groupName')
        return groups.map((el) => {
          return {name: el.groupName, id: el.groupId}
        })
      }
    },
    activeUserRights: function() {
      return this.userRights.filter(el => el.userId == this.activeObjectId)
    }
  },
  methods: {
    changeTab(tabId) {
      this.activeTab = tabId
      this.activeObjectId = null
    }
  }
}
</script>

<style lang="scss" scoped>
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
      width: 100%;
      display: flex;
      flex-direction: row;
      > #side-panel {
        width: 150px;
        > .side-panel-object {
          &.active {
            background-color: #aaa;
          }
        }

      }
    }
  }

</style>