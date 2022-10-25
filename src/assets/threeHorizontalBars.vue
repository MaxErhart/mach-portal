<template>
  <svg :width="`${width}px`" :height="`${height}px`">
    <rect :y="height/7" :width="width" :height="height/7" :rx="width/15" :ry="height/15"></rect>
    <rect :y="height*3/7" :width="width" :height="height/7" :rx="width/15" :ry="height/15"></rect>
    <rect :y="height*5/7" :width="width" :height="height/7" :rx="width/15" :ry="height/15"></rect>
  </svg>
</template>

<script>
export default {
  name: 'ThreeHorizontalBars',
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
}
</style>
