<template>
  <div class="tags-bar-body">
    <div class="title">Tags</div>
    <div class="tags-bar" ref="bar" @wheel="handlescroll($event)">
      <div class="tags-wrapper" ref="wrapper"  :style="wrapperStyle">
        <div class="row">
          <div class="tag" v-for="tag in tags" :key="tag.id" @click="setActive(tag)" :class="{'active': tag.id==activeId}">
            <div class="tag-content">
              {{tag.name}}
            </div>
          </div>
        </div>
      </div>
    </div>
    <div class="scroll-bar" :style="scrollbarStyle"></div>
  </div>


</template>

<script>
export default {
  name: 'TagsBar',
  props: {
    tags: Object,
  },
  data() {
    return {
      barWidth: 0,
      wrapperWidth: 0,
      wrapperOffsetX: 0,
      activeId: null,
    }
  },
  mounted() {
    this.barWidth = this.$refs.bar.getBoundingClientRect().width;
    this.wrapperWidth = this.$refs.wrapper.getBoundingClientRect().width;
    window.addEventListener('resize', ()=>{
      this.barWidth = this.$refs.bar.getBoundingClientRect().width;
      this.wrapperWidth = this.$refs.wrapper.getBoundingClientRect().width;
    })
  },
  computed: {
    scrollbarStyle() {
      const min_offset = this.barWidth-this.wrapperWidth
      const max_offset = 0
      const width = this.barWidth*(1-(this.wrapperWidth-this.barWidth)/this.wrapperWidth)
      const left = (max_offset-this.wrapperOffsetX)/(max_offset-min_offset)*this.barWidth*(this.wrapperWidth-this.barWidth)/this.wrapperWidth   
      return {
        visibility: this.barWidth>=this.wrapperWidth ? 'hidden' : 'visible',
        width: `${width}px`,
        left: `${left}px`
      }
    },
    wrapperStyle() {
      return {
        left: `${this.wrapperOffsetX}px`
      }
    }
  },
  methods: {
    setActive(tag) {
      this.activeId = tag.id
      this.$emit('tagChange', tag)
    },
    handlescroll(event) {
      const min_offset = this.barWidth-this.wrapperWidth
      const max_offset = 0
      const new_offset = this.wrapperOffsetX + event.wheelDelta
      if(new_offset<min_offset) {
        this.wrapperOffsetX = min_offset
      } else if(new_offset>max_offset){
        this.wrapperOffsetX = 0
      } else {
        this.wrapperOffsetX = new_offset
      }
    }
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.title {
  margin: 4px 0;
}
.tags-bar-body {
  position: relative;
}
.tags-bar {
  position: relative;
  border-top: 1px solid rgba(0, 0, 0, 0.2);
  border-bottom: 1px solid rgba(0, 0, 0, 0.2);
  width: 100%;
  height: 40px;
  overflow: hidden;
}
.tags-wrapper {
  position: absolute;
  height: 100%;
  display: inline-block;

}
.row {
  height: 100%;
  padding: 0 5px;
  display: flex;
  flex-direction: row;
  align-items: center;  
}
.tag {
  user-select: none;
  background-color: #dfdfdf;
  border-radius: 16px;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  height: 30px;
  padding: 0 12px;
  margin: 5px;
  cursor: pointer;
  transition: box-shadow 0.1s ease;
  &:hover {
    transition: box-shadow 0.1s ease;
    box-shadow: 0 0 12px 12px inset #cacaca;
  }
  &.active {
    &:hover {
      box-shadow: none;
    }
    color: #fff;
    background-color: $kit_green;
  }
}
.scroll-bar {
  position: absolute;
  bottom: -1px;
  border: 1px solid $kit_green;
}
</style>
