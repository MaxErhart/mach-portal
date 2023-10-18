<template>
  <div class="scroll-x" @wheel.prevent="handleScroll($event)" ref="scrollbar" :class="{active: active}">
    <div class="bar" :style="barStyle" @mousedown="moveScroll($event)"></div>
  </div>
</template>

<script>
export default {
  name: 'ScrollX',
  data() {
    return {
      position: 0,
      barWidth: 100,
      active: false,
      mouseStartX: 0,
    }
  },
  mounted() {
    window.addEventListener('mousemove', (event)=>{
      if(this.active) {
        const newPosition = event.clientX-this.mouseStartX + this.barStartX
        if(newPosition<0) {
          this.position = 0
          return
        } else if(newPosition>this.$refs.scrollbar.getBoundingClientRect().width-this.barWidth) {
          this.position = this.$refs.scrollbar.getBoundingClientRect().width-this.barWidth
          return
        }        
        this.position = newPosition
      }
    });
    window.addEventListener('mouseup', ()=>{
      this.active = false
    });    
  },
  computed: {
    barStyle() {
      const transition = 'transform 0.2s ease'
      return {'transform': `translate3d(${this.position}px, 0, 0)`, 'width': `${this.barWidth}px`, transition: this.active ? 'none' : transition}
    }
  },
  methods: {
    moveScroll(event) {
      this.mouseStartX = event.clientX
      this.barStartX = this.position
      this.active=true
    },
    handleScroll(event) {
      const newPosition = this.position + event.deltaY
      if(newPosition<0) {
        this.position = 0
        return
      } else if(newPosition>this.$refs.scrollbar.getBoundingClientRect().width-this.barWidth) {
        this.position = this.$refs.scrollbar.getBoundingClientRect().width-this.barWidth
        return
      }
      this.position += event.deltaY

    }
  }
}
</script>


<style scoped lang="scss">
.scroll-x {
  display: block;
  width: 100%;
  border-radius: 5px;
  height: 10px;
  transition: background-color 0.2s ease-out;
  &:hover {
    background-color: rgba(0,0,0,0.1);
    transition: background-color 0.2s ease-in;
  }
  &.active{
    background-color: rgba(0,0,0,0.1);
  }

}
.bar {
  cursor: pointer;
  position: absolute;
  border-radius: 5px;
  background: rgba(0,0,0,0.5);
  height: 10px;
  margin-left: 2px;
}
</style>
