<template>
  <div id="nav-bar">
    <div id="tabs" ref="tabs">
      <div id="tab-indicator" :style="tabIndicatorPosition"></div>
      <div class="tab" v-for="(tab, index) in tabs" :key="index" :style="tabStyle" :class="{active: activeTab==index}" @click="changeTab(index, tab.route)">{{tab.text}}</div>
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
  </div>
</template>

<script>
export default {
  name: 'NavBar',
  props: {
    tabs: Object,
    tabWidth: {
      default: 120,
      type: Number,
    },
    singleRoute: {
      default: false,
      type: Boolean,
    }
  },
  data() {
    return {
      activeTab: 0,
      scrollDistance: 0,
      tabsWidth: null,
    }
  },
  watch: {
    $route(to) {
      this.synchronizeTabsWithRoute(to.name);
    },
  },
  mounted() {
    this.synchronizeTabsWithRoute(this.$route.name);
    this.tabsWidth = this.$refs.tabs.getBoundingClientRect().width;
    window.addEventListener('resize', ()=>{
        this.tabsWidth = this.$refs.tabs.getBoundingClientRect().width;
    })    
  },
  computed: {
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
      const xPos = this.tabWidth*this.activeTab;
      return {
        transform: `translate(${xPos+this.scrollDistance}px, 34px)`,
        width: `${this.tabWidth}px`
      }
    },
    tabStyle: function() {
      return {
        width: `${this.tabWidth}px`,
        // width: `100%`,
        'min-width': `${this.tabWidth}px`,
        transform: `translateX(${this.scrollDistance}px)`
      }
    },
  },
  methods: {
    scroll(distance) {
      this.scrollDistance+=distance;
    },
    synchronizeTabsWithRoute(routeName) {
      if(this.singleRoute) {
        return
      }
      this.tabs.forEach((tab, index)=>{
        if(!tab.route) {
          return
        }
        if(routeName==tab.route.name) {
          this.activeTab=index
        }
      })
    },
    changeTab(index, route) {
      if(!route) {
        this.activeTab=index
        this.$emit('change', index)
        return        
      }
      if(!this.singleRoute) {
        this.$router.push(route)
      }
      if(index==this.activeTab) {
        this.$emit('reload', route)
      } else {
        this.activeTab=index
        this.$emit('change', index)
      }

    }
  }
}
</script>


<style scoped lang="scss">
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
