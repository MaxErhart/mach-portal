<template>
  <div class="section-element">
    <div class="item">
      <div class="item-content" :style="sectionStyle">
        {{content}}
      </div>      
    </div>
  </div>    
</template>

<script>
export default {
  name: 'SecitonElement',
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