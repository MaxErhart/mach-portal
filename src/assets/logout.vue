<template>
  <svg :width="`${width}px`" :height="`${height}px`">
    <path d="M20,3v18H8v-1h11V4H8V3H20z M11.1,15.1l0.7,0.7l4.4-4.4l-4.4-4.4l-0.7,0.7l3.1,3.1H3v1h11.3L11.1,15.1z"></path>
  </svg>

</template>
<script>
export default {
  name: 'Logout',
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
  stroke-width: 1;
  stroke: #2c3e50;
}
</style>
