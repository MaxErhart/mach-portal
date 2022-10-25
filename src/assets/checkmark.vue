<template>
  <svg :width="`${width}px`" :height="`${height}px`">
    <path d="m21.78078,3.4371c-0.36695,-0.3683 -0.96863,-0.3683 -1.33558,0l-11.10962,11.10692l-5.72142,-5.76054c-0.36695,-0.36965 -0.96459,-0.36965 -1.33423,0l-2.00472,2.00337c-0.36695,0.3656 -0.36695,0.96729 0,1.33558l8.38719,8.4425c0.36695,0.3656 0.96459,0.3656 1.33558,0l13.78618,-13.78753c0.37234,-0.3683 0.37234,-0.97133 0,-1.34098l-2.00337,-1.99933z"/>
  </svg>


</template>

<script>
export default {
  name: 'CogWheel',
  props: {
    width: {
      default: 24,
      type: Number
    },
    height: {
      default: 24,
      type: Number
    },
    stroke: {
      default: '#2c3e50',
      type: String
    },
    strokeWidth: {
      default: 1,
      type: Number
    }
  }

}
</script>


<style scoped lang="scss">
svg {
  fill: #2c3e50;
  stroke: #2c3e50;
  stroke-width: 1;
}
</style>
