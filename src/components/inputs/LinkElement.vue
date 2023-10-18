<template>
  <div class="section-element">
    <div class="item">
      <div class="item-content" :style="style">
        <a :href="url" v-if="alias">{{alias}}</a>
        <a :href="url" v-else>{{url}}</a>
      </div>      
    </div>
  </div>    
</template>

<script>
export default {
  name: 'LinkElement',
  props: {
    url: String,
    alias: String,
  },
  data() {
    return {
    }
  },
  computed: {
    style() {
      return {

      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
  
.item-content {
  width: 100%;
  display: block;
  min-height: 1em;
  text-align: left;

}
a {
  position: relative;
  color: $kit_green;
  text-decoration: none;
  &:visited {
    color: $kit_green;
  }
  &::before {
    content: "";
    position: absolute;
    width: min(25%, 25px);
    border: 1px solid $kit_green;
    bottom: 0;
    transition: width 0.2s ease;
  }
  &:hover {
    &::before {
      width: 100%;
      // transition: width 0.2s ease;
    }
  }
}
.section-element {
  width: 100%;
  margin: 16px 0;
}
</style>