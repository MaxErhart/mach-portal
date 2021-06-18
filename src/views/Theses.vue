<template>
  <div id="theses">
    <h1>Theses</h1>
    <div id="theses-main">
      <div id="theses-header">
        <div id="tabs">
          <div id="tab-indicator" :style="tabIndicatorPosition"></div>
          <div class="tab" :class="{active: activeTab==0}" @click="changeTab(0)">Bachelor Theses</div>
          <div class="tab" :class="{active: activeTab==1}" @click="changeTab(1)">Master Theses</div>
        </div>        
      </div>
      <div id="new-thesis" v-if="addThesisRights">
        <div id="open-form" @click="newThesis(activeTab)">
          <span id="open-form-indicator" :class="{open: open}">^</span>New Thesis
        </div>
        <div class="expand-bottom-window" :class="{open: open}" >
          <router-view></router-view>
        </div>
      </div>

      <div id="loading" v-if="tabLoading">Loading...</div>
      <div id="theses-body" v-else>
        <div class="col-names" v-for="item in colNames" :key="item">{{item}}</div>
        <div class="row" :class="{odd: index%2 != 0}" v-for="(row, index) in data" :key="row">
          <div class="row-item" v-for="(item, name) in row.displayData" :key="name">
            <a v-if="name=='VAThema'" :href="`https://www-3.mach.kit.edu/dfiles/${item.file}`">{{item.title}}</a>
            <a v-else-if="name=='Ansp_Email'" :href="`mailto:${item}`">{{item}}</a>
            <template v-else>
              {{item}}
              <div class="row-options" v-if="name=='DatumX' && row.notDisplayData.write">
                <!-- <div class="option">
                  <img :src="require(`@/assets/edit.svg`)">
                </div> -->
                <div class="option" @click="deleteThesis(row.notDisplayData, index)">
                  <img :src="require(`@/assets/delete.svg`)">
                </div>
              </div>
            </template>
          </div>
        </div>
      </div>
      <div id="loading" v-if="infiniteScrollLoading">Loading...</div>
      <div id="theses-footer">

      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";

export default {
  name: 'About',
  components: {
  },
  data() {
    return {
      activeTab: null,
      tabLoading: false,
      data: null,
      colNames: ['Email', 'Ansprechpartner', 'Thema', 'Institut', 'Datum'],
      open: false,
      infiniteScrollCounter: 0,
      infiniteScrollLoading: false,
      oldNewDataOffset: 0,
      getOldData: false,
      user: null,
    }
  },  
  mounted() {
    this.$store.commit('setCurrentRoute', this.$store.getters.getRoutes[1])
    this.changeTab(0);
    this.user = JSON.parse(localStorage.user)
    document.addEventListener("scroll", ($event) => this.infiniteScroll($event));
  },
  computed: {
    tabIndicatorPosition: function() {
      const xPos = 175*this.activeTab;
      return {transform: `translate(${xPos}px, 34px)`}
    },
    addThesisRights: function() {
      if(this.user){
        if(this.user.rights.theses) {
          if(this.user.rights.theses.write.groups.length > 0 || this.user.rights.theses.write.users.length > 0) {
            return true
          } else {
            return false
          }
        } else {
          return false
        }
      } else {
        return false
      }
    },
    rowModRights: function() {
      if(localStorage.isLoggedIn){
        const attributes = JSON.parse(localStorage.userInformation)
        if(attributes.memberOf) {
          if(attributes.memberOf.includes('MACH-Portal-Admin')) {
            return true
          }
        }
        return false
      } else {
        return false
      }
      
    }

  },
  methods: {
    changeTab(tab) {
      this.$router.push({path: '/theses'})
      this.open = false;
      this.activeTab = tab;
      this.tabLoading = true;
      this.infiniteScrollCounter = 0;
      this.getOldData = false;
      this.oldNewDataOffset = 0;    
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/theses.php',
        data: {thesis: this.activeTab == 0 ? 'bc' : 'ma', limit: [0, 50], offset: this.oldNewDataOffset, getOldData: this.getOldData}
      }).then(response => {
        this.data = response.data.data;
        this.tabLoading = false;
        this.getOldData = response.data.oldData
        this.oldNewDataOffset = response.data.offset
        console.log(response.data)  
      })
    },
    infiniteScroll() {
      const st = window.scrollY;
      const sh = document.body.scrollHeight;
      const ih = window.innerHeight;
      
      if((st+ih)/sh>0.9 && !this.infiniteScrollLoading) {
        this.infiniteScrollLoading = true
        this.infiniteScrollCounter = this.infiniteScrollCounter + 1
        const lowerLimit = 50*this.infiniteScrollCounter
        axios({
          method: 'post',
          url: 'https://www-3.mach.kit.edu/api/theses.php',
          data: {thesis: this.activeTab == 0 ? 'bc' : 'ma', limit: [lowerLimit, 50], offset: this.oldNewDataOffset, getOldData: this.getOldData}
        }).then(response => {
          this.data = this.data.concat(response.data.data);
          this.infiniteScrollLoading = false
          this.getOldData = response.data.oldData
          this.oldNewDataOffset = response.data.offset
        })      
      }

    },
    newThesis(tab) {
      if(this.open){
        this.$router.push({path: '/theses'})
      } else {
        if(tab==0) {
          this.$router.push({path: '/theses/newthesis/65'})
        } else if(tab==1) {
          this.$router.push({path: '/theses/newthesis/66'})
        }
      }
      this.open = !this.open
    },
    deleteThesis(thesisData, index) {
      this.data.splice(index, 1)
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/editTheses.php',
        data: {thesis: this.activeTab == 0 ? 'bc' : 'ma', mode: 'delete', metaData: thesisData}
      }).then(response => {
        console.log(response)
      })
    }
  }
}
</script>

<style lang="scss" scoped>
  .row-options {
    position: absolute;
    transform: translateX(100%);
    right: -5px;
    height: 100%;
    width: 60px;
    display: flex;
    flex-direction: row;
    align-items: center;

    > .option {
      box-sizing: border-box;
      border: 1px solid #2c3e50;
      border-radius: 2px;
      background-color: #e0e0e0;
      width: 100%;      
      display: flex;
      margin: 0.5px;
      padding: 1.5px;
      &:hover {
        box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8);
        cursor: pointer;
      }
      &:active {
        box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.8), 0 0 0 1px black;
      }
      > img {
        height: 24px;
        margin: auto;
      }

    }
  }
  a {
    color: #007755;
    text-decoration: none;
    &:hover {
      text-decoration: underline;
    }
  }
  #loading {
    padding: 25px;
  }
  #theses {
    width: 100%;
    display: flex;
    flex-direction: column;
    align-items: center;
    padding: 0px 42px 0 0; 
  }
  #theses-main {
    width: 100%;
    background: #eee;
    display: flex;
    flex-direction: column;
    align-items: stretch;
  }
  #theses-header {
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
  #theses-body {
    display: grid;
    grid-template-columns: repeat(5, auto);
    grid-auto-rows: auto;
    padding: 5px;
    width: 100%;
    > .col-names {
      display: flex;
      background-color: #e6e6e6;
      justify-content: center;
      align-content: center;
      padding: 5px 5px;
      margin: 5px 0;
    }
    > .row {
      display: contents;
      &.odd {
        > .row-item {
          background-color: #e0e0e0;
        }
      }
      > .row-item {
        position: relative;
        padding: 5px 2px;
        min-height: 40px;
        display: flex;
        align-items: center;
      }
    }
  }
  #open-form {
    
    border-bottom: none;
    box-shadow: none;
    cursor: pointer;
    padding: 10px;
    &:hover {
      background-color: rgb(208, 208, 208);
    }
    > #open-form-indicator {
      margin-right: 10px;
      display: inline-block;
      transform: rotate(90deg);
      &.open {
        transform: rotate(180deg) translateY(25%);
      }
    }
  }
  .expand-bottom-window {
    border-top: none;
    overflow: hidden;
    height: 0;
    &.open {
      height: 470px;
      transition: all 300ms ease-in-out;
    }
  }
  #new-thesis {
    box-shadow: 0px 0px 2px 1px rgba(0, 0, 0, 0.2);
    margin: 5px;
  }
</style>