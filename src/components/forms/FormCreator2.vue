<template>
  <div class="form-creator">
    <ul class="list-of-elements">
      <li class="element" @click="addElement('HeaderElement')">Header</li>
    </ul>
    <ul class="form-preview">
      <li class="element-preview" :style="element.id===activeElement?.id?active:null" :class="{active: element.id===activeElement?.id}" @mousedown="startDrag($event, element, index)" v-for="(element,index) in prevElements" :key="element">
        {{element.component}}
      </li>
      <li class="element-preview placeholder" v-if="activeElement"></li>
      <li class="element-preview" :style="element.id===activeElement?.id?active:null" :class="{active: element.id===activeElement?.id}" @mousedown="startDrag($event, element, index+prevElements.length)" v-for="(element,index) in postElements" :key="element">
        {{element.component}}
      </li>
    </ul>
  </div>
</template>

<script>
export default {
  name: 'FormCreator',
  data() {
    return {
      elements: [],
      activeElement: null,
      id: 0,
    }
  },
  computed: {
    active() {
      return {
        top: this.activeElement?.top+'px',
      }
    },
    postElements() {
      if(this.activeElement===null) {
        return []
      }
      const post = []
      for(let i=this.activeElement.index;i<this.elements.length;i++) {
        post.push(this.elements[i])
      }
      return post
    },
    prevElements() {
      if(this.activeElement===null) {
        return this.elements
      }
      const prev = []
      for(let i=0;i<this.activeElement.index; i++) {
          prev.push(this.elements[i])
      }
      return prev
    },
  },
  methods: {
    addElement(component) {
      this.elements.push({component, id: this.id})
      this.id++
    },
    startDrag(event, element,index) {
      const {top, height} = event.target.getBoundingClientRect()
      console.log(top,height,element,index,event.target.getBoundingClientRect())
      this.activeElement = {...element, index, top:top-92, height, offset: event.pageY-top, start: top-92, id:element.id}
      document.addEventListener("mousemove", this.draging)
      document.addEventListener("mouseup", this.endDrag)
    },
    draging(event) {
      this.activeElement.top = event.pageY-this.activeElement.offset-92
      if(this.activeElement.top>this.activeElement.start+this.activeElement.height) {
        const element = JSON.parse(JSON.stringify(this.elements[this.activeElement.index]))
        this.elements.splice(this.activeElement.index, 1)
        this.elements.splice(this.activeElement.index+1, 0, element)
        this.activeElement.index+=1
        this.activeElement.start=
        // this.endDrag()
        console.log(this.elements)
      }
    },
    endDrag() {
      document.removeEventListener("mousemove", this.draging)
      document.removeEventListener("mouseup", this.endDrag)
    }
  },
}
</script>


<style scoped lang="scss">
.form-creator {
  position: relative;
  width: 100%;
  min-height: 540px;
  border: 1px solid black;
  display: grid;
  grid-template-columns: 120px auto;
}
ul {
  list-style-type: none;
  margin: 0;
  padding: 0;
}
.form-preview {
  position: relative;
  border: 1px solid black;
  top: 0;
}
.element-preview {
  height: 60px;

  &.placeholder {
    border: 1px solid green;
  }
  &.active {
    position: absolute;
    width: 100%;
    border: 1px solid red;
  }
}
</style>
