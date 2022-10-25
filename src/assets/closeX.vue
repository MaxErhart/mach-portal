<template>
  <svg :viewBox="`0 0 ${100} ${100}`">
    <polygon points="96,14 82,0 48,34 14,0 0,14 34,48 0,82 14,96 48,62 82,96 96,82 62,48 "/>
  </svg>
</template>

<script>
export default {
  name: 'CloseX',
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
  // position: relative;
  // top: -0.5px;
  // right: -0.5px;
  transform: translate(0.25px, -0.25px);
  stroke: #2c3e50;
  stroke-width: 1;
}
</style>
