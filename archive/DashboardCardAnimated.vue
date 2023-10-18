<template>
    <div class="dashboard-card" @click="openCard($event)" :class="{active: active}" ref="dashboardCard">
      <div class="dashboard-card-icon" v-if="!active" ref="dashboardCardIcon">
        <img :src="require(`@/assets/${icon}`)">
      </div>
      <div class="dashboard-card-text" v-if="!active" ref="dashboardCardText">
        {{text}}
      </div>
      <transition v-on:enter="enter">
        <div class="dashboard-card-active-content" v-if="active">
          <HelloWorld v-if="com=='HelloWorld'"/>
          content wee
        </div>
      </transition>
    </div>
</template>

<script>
import Velocity from 'velocity-animate'
import HelloWorld from '../components/HelloWorld.vue'
export default {
  name: 'DashboardCard',
  components: {
    HelloWorld,
  },
  props: {
    icon: String,
    text: String,
    com: String,
  },
  data() {
    return {
      active: false,
    }
  },
  mounted() {
    document.body.addEventListener('click', this.openCard)
  },
  methods: {
    enter(el, done){
      Velocity(el, { scale: [1, 0.5] }, { duration: 200, delay: 100, complete: done })
    },
    openCard(event){
      event.stopPropagation()
      if((event.target ==  this.$refs.dashboardCard || event.target.parentElement == this.$refs.dashboardCard || event.target.parentElement.parentElement == this.$refs.dashboardCard) && !this.active){
        this.active = true;
      } else if (!(event.target ==  this.$refs.dashboardCard || event.target.parentElement ==  this.$refs.dashboardCard) && this.active) {
        this.active = false;
      }
    }
  },
}
</script>


<style scoped lang="scss">
  .dashboard-card {
    position: relative;
    cursor: pointer;
    display: flex;
    flex-direction: row;
    align-items: center;    
    font-size: 18px;
    min-width: 250px;
    height: 60px;
    border-radius: 10px;
    box-shadow: 0 2px 3px 0 rgba(0,0,0,.3);
    background-color: rgb(243, 243, 243);
    transition: all 200ms ease;
    backface-visibility: hidden;
    &:not(.active):hover {

      height: 60px;
      transform: translateY(-2px);
      box-shadow: 0 2px 5px 0 rgba(0,0,0,.3);
      transition: all 200ms ease;
    }
    &:not(.active):active {

      box-shadow: inset 0 0 4px rgba(0,0,0,.3);
    }    
    .dashboard-card-icon {
      margin: 0 20px;
    }

    &.active{
      position: absolute;
      height: 100%;
      width: 100%;
      top:0;
      // border-radius: 0;
      transition: all 200ms ease;
      z-index: 1;
    }
  }

  .dashboard-card-active-content {
    transform: scale(0.0);
    width: 100%;
    height: 100%;
    text-align: start;
    padding: 10px;
  }

</style>