<template>
  <div class="nav-item-main" :class="{active: active}">
    <router-link :to="{name: route, params}">
      <div class="icon-wrapper">
        <component v-if="!ion" class="nav-item-link-icon" :is="src" :width="width" :height="height" />
          <ion-icon v-else class="nav-item-link-icon" :name="icon"></ion-icon>

      </div>
      <div class="nav-text">{{text}}</div>
    </router-link>
  </div>
</template>

<script>
import {defineAsyncComponent} from "vue";
export default {
  name: 'MainNavItem',
  props: {
    route: String,
    active: Boolean,
    text: String,
    icon: String,
    width: Number,
    height: Number,
    ion: Boolean,
    params: Object,
  },
  data() {
    return {
      availableIcons: ['cogwheel', 'trash', 'threeHorizontalBars', 'home', 'forms', 'createforms'],
    }
  },
  computed: {
    src() {
      if(this.availableIcons.includes(this.icon)) {
        return defineAsyncComponent(() => import(`@/assets/${this.icon}.vue`))
      }
      return null
    }
  },
  methods: {
  }
}
</script>


<style scoped lang="scss">
svg {
  fill: #2c3e50;
  stroke: #2c3e50;
  width: 24px;
  height: 24px;
}
.nav-text {
  position: absolute;
  left: 51px;
  width: 129px;
  font-size: 16px;
  // z-index: -1;
}
.icon-wrapper {
  background: #fff;
}
.nav-item-main {
  position: relative;
  overflow: hidden;
  height: 38px;
  &.active * {
    color: #00876c;
    fill: #00876c;
    stroke: #00876c;
    transition: all 200ms ease;
    background-color: rgb(243, 243, 243);

    &:hover {
    background-color: #bfc8cc;
      * {
        background-color: #bfc8cc;
      }
    }
  }
  &:hover {
    background-color: #bfc8cc;
    * {
      background-color: #bfc8cc;
    }
  }
}
a {
  text-decoration: none;
  color: #2c3e50;
  width: 100%;
  height: 100%;
  display: flex;
  flex-direction: row;
  align-items: center;

  .nav-item-link-icon {
    font-size: 24px;
    position: relative;
    background: #fff;
    left: 0;
    margin: 0 14px 0 14px;
  }
}
</style>