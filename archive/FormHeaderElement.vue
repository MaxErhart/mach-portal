<template>
  <div class="header-element">
    <div class="item">
      <component :is="tag" class="item-content">
        {{content}}
      </component>      
    </div>
  </div>

</template>

<script>
export default {
  name: 'HeaderElement',
  props: {
    id: String,
    tag: String,
    content: String,
    type: String,
  },
  data() {
    return {
    }
  },
}
</script>


<style scoped lang="scss">
  .item {
    > * {
      padding: 0px 0;
      margin: 0px 0;    
    } 
  }
  .edit-element-window {
    padding: 0px 0;
    pointer-events: auto;
    > section {
      margin: 5px 0;
    }
  }
</style>