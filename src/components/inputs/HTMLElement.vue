<template>
  <div class="section-element">
    <div class="item">
      <div class="item-content" v-html="content" :style="sectionStyle">
      </div>      
    </div>
  </div>    
</template>

<script>
export default {
  name: 'HTMLElement',
  props: {
    underline: Boolean,
    bold: Boolean,
    color: String,
    content: String,
  },
  data() {
    return {
    }
  },
  computed: {
    sectionStyle() {
      return {
        'text-decoration': this.underline ? 'underline' : 'none',
        'font-weight': this.bold ? 'bold' : 'normal',
        'color': this.color,
      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
::v-deep a{
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
.item-content {
  width: 100%;
  display: block;
  min-height: 1em;
  text-align: left;
}
.section-element {
  width: 100%;
  margin: 16px 0;
}
</style>