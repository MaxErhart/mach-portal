<template>
  <div id="nav-bar">
    <div id="tabs" ref="tabs">
      <div id="tab-indicator" :style="tabIndicatorPosition"></div>
      <div class="tab" v-for="(tab, index) in tabs" :key="index" :style="tabStyle" :class="{active: activeTab?.id==tab.id}" @click="changeTab(tab)">{{tab.text}}</div>
      <button id="scroll-right" :style="{width: tabWidth/3}" @click="scroll(tabWidth/2)" v-if="hasOverflowLeft">
        <svg width="17.5" height="32" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1">
          <path transform="rotate(-90 8.75 16)" stroke="null" d="m24.26548,22.22072l-14.49123,-14.49084c-0.27165,-0.27175 -0.6403,-0.42439 -1.0246,-0.42439c-0.38439,0 -0.75294,0.15273 -1.0247,0.42448l-14.49045,14.49084c-0.56592,0.56592 -0.56592,1.48338 0,2.04929c0.56582,0.56582 1.48338,0.56592 2.04929,0l13.46585,-13.46614l13.46653,13.46614c0.28296,0.28296 0.65383,0.42439 1.0247,0.42439c0.37087,0 0.74174,-0.14143 1.0247,-0.42448c0.56582,-0.56592 0.56582,-1.48338 -0.0001,-2.04929z" id="XMLID_224_"/>
        </svg>     
      </button>
      <button id="scroll-left" :style="{width: tabWidth/3}" @click="scroll(-tabWidth/2)" v-if="hasOverflowRight">
        <svg width="17.5" height="32" xmlns="http://www.w3.org/2000/svg" xml:space="preserve" version="1.1">
          <path transform="rotate(90 8.75 16)" stroke="null" d="m24.26548,22.22072l-14.49123,-14.49084c-0.27165,-0.27175 -0.6403,-0.42439 -1.0246,-0.42439c-0.38439,0 -0.75294,0.15273 -1.0247,0.42448l-14.49045,14.49084c-0.56592,0.56592 -0.56592,1.48338 0,2.04929c0.56582,0.56582 1.48338,0.56592 2.04929,0l13.46585,-13.46614l13.46653,13.46614c0.28296,0.28296 0.65383,0.42439 1.0247,0.42439c0.37087,0 0.74174,-0.14143 1.0247,-0.42448c0.56582,-0.56592 0.56582,-1.48338 -0.0001,-2.04929z" id="XMLID_224_"/>
        </svg>     
      </button>      
    </div>
    <section class="history-nav" v-if="paginate">
      <button @click="goBack()" :class="{active: history_pointer>0}">
        <ion-icon name="arrow-back-outline"></ion-icon>
      </button>
      <button @click="goForward()" :class="{active: history_step<0}">
        <ion-icon name="arrow-forward-outline"></ion-icon>
      </button>
    </section>
  </div>
</template>

<script>
export default {
  name: 'NavBar',
  props: {
    tabs: Object,
    paginate: {
      default: false,
      type: Boolean,
    },
    tabWidth: {
      default: 120,
      type: Number,
    },
  },
  data() {
    return {
      scrollDistance: 0,
      tabsWidth: null,
      history: [],
      history_step: 0,

      paginating: false,
    }
  },
  watch: {
    tabs() {
      this.synchronizeTabsWithRoute(this.$route);
    },
    $route(to) {
      if(this.paginating) {
        this.paginating = false
        return
      }
      this.synchronizeTabsWithRoute(to);
    },
  },
  mounted() {
    this.synchronizeTabsWithRoute(this.$route);
    this.tabsWidth = this.$refs.tabs?.getBoundingClientRect().width;
    window.addEventListener('resize', ()=>{
        this.tabsWidth = this.$refs.tabs?.getBoundingClientRect().width;
    })    
  },
  computed: {
    activeTab() {
      if(this.history.length<=0) {
        return null
      }
      return this.history[this.history_pointer]
    },
    history_pointer() {
      return this.history.length-1+this.history_step
    },
    hasOverflowRight: function() {
      if(this.tabWidth*this.tabs.length + this.scrollDistance>this.tabsWidth) {
        return true;
      }
      return false;
    },
    hasOverflowLeft: function() {
      if(this.scrollDistance<0) {
        return true;
      }
      return false;
    },    
    tabIndicatorPosition: function() {
      var xPos = this.tabWidth*this.tabs.indexOf(this.tabs.find(tab=>tab.id===this.activeTab?.id))
      if(xPos<0) {
        xPos = 0
      }
      return {
        transform: `translate(${xPos+this.scrollDistance}px, 34px)`,
        width: `${this.tabWidth}px`
      }
    },
    tabStyle: function() {
      return {
        width: `${this.tabWidth}px`,
        'min-width': `${this.tabWidth}px`,
        transform: `translateX(${this.scrollDistance}px)`
      }
    },
  },
  methods: {
    goForward() {
      this.paginating = true
      if(this.history_step<0) {
        this.history_step++
      }
      if(this.activeTab.route) {
        this.$router.push(this.activeTab.route)
      }
      this.$emit('readHistory', {history: this.history, pointer: this.history_pointer})
    },
    goBack() {
      this.paginating = true
      if(this.history_step>1-this.history.length) {
        this.history_step--
      }
      if(this.activeTab.route) {
        this.$router.push(this.activeTab.route)
      }
      this.$emit('readHistory', {history: this.history, pointer: this.history_pointer})
    },
    updateHistory(tab) {
      if(this.history.length>0) {
        if(this.history[this.history_pointer].id===tab.id) {
          return
        }
        this.history = this.history.slice(0,this.history_pointer+1)
      }
      this.history.push(tab)
      this.history_step = 0
      this.$emit('updateHistory', {history: this.history, pointer: this.history_pointer})
    },
    scroll(distance) {
      this.scrollDistance+=distance;
    },
    synchronizeTabsWithRoute(route) {
      if(route.name===this.history[this.history_pointer]?.route?.name) {
        this.history[this.history_pointer].route.params = this.$route.params
        return
      }
      this.tabs.forEach((tab)=>{
        if(!tab.route) {
          return
        }

        if(route.name==tab.route.name) {
          this.updateHistory({id:tab.id, text:tab.text, route:{name:this.$route.name,params:this.$route.params}})
        }
      })
    },
    changeTab(tab) {
      if(tab.id==this.activeTab?.id) {
        this.$emit('reload', tab)
      } else {
        this.updateHistory(tab)
        if(this.activeTab.route) {
          this.$router.push(this.activeTab.route)
        }
        this.$emit('change', tab)
      }

    }
  }
}
</script>


<style scoped lang="scss">
.history-nav {
  display: flex;
  padding: 4px 0;
  flex-direction: row;
  align-items: flex-start;
  gap: 8px;
  button {
    position: relative;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 24px;
    padding: 2px;
    border-radius: 50%;
    cursor: pointer;
    background-color: #f9f9f9;
    color: hsl(210, 29%, 76%);
    &:active {
      background-color: #cccccc;
    }
    &.active {
      color: hsl(210, 29%, 24%);
    }
  }
}
#nav-bar {
  width: 100%;
}
#tabs {
  position: relative;
  overflow: hidden;
  background-color: #e3e3e3;
  height: 37px;
  display: flex;
  flex-direction: row;
  box-shadow: 0px 1px 2px 1px rgba(0, 0, 0, 0.1);
  > .tab {
    cursor: pointer;
    display: flex;
    justify-content: center;
    align-items: center;
    transition: transform 0.3s ease-in-out;
    &.active {
      color: #00876c;
    }
  }
  > #tab-indicator {
    position: absolute;
    height: 3px;
    background-color: #00876c;
    transition: 300ms transform ease-in-out;
  }
}
.selected-tab-content {
  width: 100%;
  background-color: #f2f2f2;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}
#scroll-left {
  position: absolute;
  display: flex;
  flex-direction: column;
  right: 0;
  width: 80px;
  padding: 0;
  z-index: 0;
  border: none;
  cursor: pointer;
  height: 100%;
  background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.15));
  &:hover {
    background: linear-gradient(to right, rgba(0,0,0,0), rgba(0,0,0,0.25));
    box-shadow: 3px 0 3px 0px rgba(0, 0,0,0.25);
    > svg {
      fill: rgba(0,0,0,1);
    }
  }
  > svg {
    transform: scale(0.8);
    margin: auto 10px auto auto;
    fill: rgba(0,0,0,0.4);

  }
}
#scroll-right {
  position: absolute;
  display: flex;
  flex-direction: column;
  left: 0;
  width: 80px;
  padding: 0;
  z-index: 0;
  border: none;
  height: 100%;
  cursor: pointer;
  background: linear-gradient(to left, rgba(0,0,0,0), rgba(0,0,0,0.15));
  &:hover {
    background: linear-gradient(to left, rgba(0,0,0,0), rgba(0,0,0,0.25));
    box-shadow: -3px 0 3px 0px rgba(0, 0,0,0.25);
    > svg {
      fill: rgba(0,0,0,1);
    }
  }
  > svg {
    transform: scale(0.8);
    margin: auto auto auto 10px;
    fill: rgba(0,0,0,0.4);

  }
}
</style>
