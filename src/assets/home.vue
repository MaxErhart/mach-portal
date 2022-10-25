<template>
  <svg :width="`${width}px`" :height="`${height}px`">
    <path d="m12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z"/>
  </svg>
</template>

<script>
export default {
  name: 'Home',
  props: {
    width: {
      default: 24,
      type: Number
    },
    height: {
      default: 24,
      type: Number
    },   
  },
}
</script>


<style scoped lang="scss">
svg {
  fill: #2c3e50;
  stroke-width: 0;
}
</style>
