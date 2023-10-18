<template>
  <div class="data-placeholder" :style="scale">

    <template v-if="animation==='spinner'">
      <svg class="spinner" width="65px" height="65px" viewBox="0 0 66 66" xmlns="http://www.w3.org/2000/svg">
        <circle class="path" fill="none" stroke-width="6" stroke-linecap="round" cx="33" cy="33" r="30"></circle>
      </svg>
    </template>

    <div class="table" v-if="animation==='table'" :style="{'--x': x+'%'}">
    <!-- <div class="table" v-if="animation==='table'" :style="{'--x': x+'%', '--offset': offset+'%'}"> -->

      <div class="header"></div>
      <div class="row" v-for="index in nRows" :key="index" :class="{index}" :style="{'--offset': calcOffset(index)+'%'}"></div>

    </div>

  </div>
</template>

<script>
export default {
  name: 'DataPlaceholder',
  props: {
    side_length: {
      default: 65,
      type: Number,
    },
    animation: {
      type: String,
      default: 'spinner',
    },
  },
  data() {
    return {
      nRows: 8,
      x: -10,
      offset: 0.5,
    }
  },
  mounted() {
    setInterval(()=>{
      if(this.x>210) {
        this.x = -110
      }
      this.x+=2.0
    }, 12)
  },
  computed: {
    scale() {
      const scale_factor = this.side_length/65
      return {'transform': `scale(${scale_factor})`}
    }
  },
  methods: {
    calcOffset(index) {
      return this.offset*index
    }
  }
}
</script>


<style scoped lang="scss">
.table {
  width: 100%;
  display: flex;
  flex-direction: column;
  :nth-child(2n):not(.header) {
    // background-color: #ffffff;
    background: linear-gradient(105deg,
      #eeeeee calc(var(--x) - 10% - var(--offset)),
      #F3F3F3 calc(var(--x) - 3% - var(--offset)),
      #FCFCFC calc(var(--x) - var(--offset)),
      #F3F3F3 calc(var(--x) + 3% - var(--offset)),
      #eeeeee calc(var(--x) + 10% - var(--offset))
    );
  }
  :nth-child(2n+1):not(.header) {
    background: linear-gradient(105deg,
      #dddddd calc(var(--x) - 10% - var(--offset)),
      #E3E3E3 calc(var(--x) - 3% - var(--offset)),
      #ECECEC calc(var(--x) - var(--offset)),
      #E3E3E3 calc(var(--x) + 3% - var(--offset)),
      #dddddd calc(var(--x) + 10% - var(--offset))
    );
  }
}
.header {
  // background: #00876c;
  width: 100%;
  height: 40px;
  background-color: #00876c;
  background: linear-gradient(95deg,
    rgba(0,135,108,1) calc(var(--x) - 10%),
    #179279 calc(var(--x) - 3%),
    #46A894 var(--x),
    #179279 calc(var(--x) + 3%),
    rgba(0,135,108,1) calc(var(--x) + 10%)
  );
}
.row {
  width: 100%;
  height: 40px;
}

$offset: 187;
$duration: 1.6s;
.data-placeholder {
   display: flex;
   align-items: center;
   justify-content: center;
}
circle {
  stroke: black;
  stroke-dasharray: $offset;
  stroke-dashoffset: 0;
  transform-origin: center;
  animation: dash $duration ease-in-out infinite;
}
svg {
  animation: rotator $duration linear infinite;
}
@keyframes rotator {
  0% { transform: rotate(0deg); }
  100% { transform: rotate(270deg); }
}
@keyframes dash {
 0% { stroke-dashoffset: $offset; }
 50% {
   stroke-dashoffset: $offset/4;
   transform:rotate(135deg);
 }
 100% {
   stroke-dashoffset: $offset;
   transform:rotate(450deg);
 }
}

</style>
