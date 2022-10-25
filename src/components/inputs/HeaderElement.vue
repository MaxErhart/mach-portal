<template>
  <template v-if="data">
    <div class="header-element">
      <div class="item">
        <component :is="data.type" class="item-content" :style="headerStyle">
          {{data.content}}
        </component>      
      </div>
    </div>    
  </template>
</template>

<script>
export default {
  name: 'HeaderElement',
  props: {
    id: Number,
    data: Object,
  },
  data() {
    return {
    }
  },
  computed: {
    headerStyle() {
      return {
        'color': this.data.color,
        'text-decoration': this.data.underline ? 'underline' : 'none',
      }
    }
  }
}
</script>


<style scoped lang="scss">
h1 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
h2 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
h3 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
h4 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
h5 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
h6 {
  color: none;
  border: none;
  padding: none;
  margin: 0px;
}
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