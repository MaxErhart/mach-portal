<template>
  <template v-if="data">
    <div class="section-element">
      <div class="item">
        <div class="item-content" :style="sectionStyle">
          {{data.content}}
        </div>      
      </div>
    </div>    
  </template>
</template>

<script>
export default {
  name: 'SecitonElement',
  props: {
    id: Number,
    data: Object,
  },
  data() {
    return {
    }
  },
  computed: {
    sectionStyle() {
      return {
        'text-decoration': this.data.underline ? 'underline' : 'none',
        'font-weight': this.data.bold ? 'bold' : 'normal',
        'color': this.data.color,
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
    margin: 25px 0 0 0;
  }
</style>