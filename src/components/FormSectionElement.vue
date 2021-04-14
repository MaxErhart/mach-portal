<template>
  <div class="section-element">
    <div class="item">
      <component :is="tag" class="item-content">
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
    text-align: start;
    white-space: pre-line;
    word-break: break-all;
  }
  .section-element {
    position: relative;
    width: 100%;
    float: left;
  }
</style>