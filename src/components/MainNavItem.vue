<template>
  <div class="nav-item-main" :class="{active: isActive}" :style="{'height': height+'px'}">
    <router-link :to="{name: route[0]}">
      <div class="nav-item-link-icon" v-if="path!=''">
        <svg width="24" height="24">
          <path :d="path"/>
        </svg>
      </div>
      <div class="nav-item-link-text" v-if="showText">{{text}}</div>
    </router-link>
  </div>
</template>

<script>
export default {
  name: 'MainNavItem',
  props: {
    route: Object,
    path: String,
    isActive: Boolean,
    text: String,
    showText: Boolean,
  },
  computed: {
    height(){
      return this.$store.getters.getNavItemMainHeight;
    }
  },
  methods: {
  }
}
</script>


<style scoped lang="scss">
  svg {
    fill: #2c3e50;
    width: 24px;
    height: 24px;
  }
  .nav-item-main {
    display: flex;
    a {
      text-decoration: none;
      color: #2c3e50;
      width: 100%;
      height: 100%;
      display: flex;
      flex-direction: row;
      justify-content: flex-start;
      align-items: center;
      padding-left: 16px;

      .nav-item-link-icon {
        margin-right: 8px;
        width: 24px;
        height: 24px;
      }
    }

    &.active * {
      color: #00876c;
      fill: #00876c;
      stroke: #00876c;
      background-color: rgb(243, 243, 243);
      transition: all 200ms ease;
    }
    &:hover {
      background-color: #bfc8cc;
    }
  }
</style>