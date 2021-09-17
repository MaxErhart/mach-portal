<template>
  <div class="section-element">
    <div class="item">
      <component :is="tag" class="item-content" :style="{'text-decoration': underline ? 'underline' : 'none', 'font-weight': bold ? 'bold' : 'normal'}">
        {{content}}
      </component>      
    </div>
  </div>
</template>

<script>
export default {
  name: 'SecitonElement',
  props: {
    editable: Boolean,
    id: String,
    type: String,
    content: String,
    tag: String,
    underline: Boolean,
    bold: Boolean, 
  },
  data() {
    return {
    }
  }
}
</script>


<style scoped lang="scss">
  .item-content {
    width: 100%;
    display: block;
    min-height: 1em;
  }
  .section-element {
    width: 100%;
    margin: 25px 0 0 0;
  }
</style>