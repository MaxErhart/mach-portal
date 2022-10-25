<template>
  <div class="create-form">
    <div class="elements-label">Form:</div>
    <div class="create-form-body" >
      <div class="create-form-selection">
        <div class="form-item-selection" v-on:click="addElement('HeaderElement')">Header</div>
        <div class="form-item-selection" v-on:click="addElement('SectionElement')">Section</div>
        <div class="form-item-selection" v-on:click="addElement('InputElement')">Input</div>
        <div class="form-item-selection" v-on:click="addElement('FileUploadElement')">File Input</div>
        <div class="form-item-selection" v-on:click="addElement('SelectElement')">Select</div>
        <div class="form-item-selection" v-on:click="addElement('Checkbox')">Checkbox</div>
        <div class="form-item-selection" v-on:click="addElement('SelectReferenceElement')">Select Reference</div>
      </div>

      <div id="create-form-content" ref="contentContainer">
        <div class="form-item-wrapper" :class="{active: index==dragIndex}" :ref="el.id" v-for="(el, index) in elements" :key="el.id" :style="index==dragIndex ? dynamicStyleWrapper : null">
          <div class="element" :class="{active: index==dragIndex, dragable: dragable}" @mousedown="startDrag($event, index)" :style="index==dragIndex ? dynamicStyleElement : null" >
            <component :position="index" :id="Number(el.el_id)" :formCratorIdentifier="Number(el.id)" :presetData="el.presetData" class="component" :is="componentDict[el.type]" :name="`${el.id}_${name}`" @deleteItem="deleteItem($event)" @editActivated="editActivated()" @editDeactivated="editDeactivated()"></component>
          </div>
        </div>
      </div>      
    </div>
  </div>
</template>

<script>
import CreatorInputElement from '@/components/forms/creatorInputs/CreatorInputElement.vue'
import CreatorHeaderElement from '@/components/forms/creatorInputs/CreatorHeaderElement.vue'
import CreatorSectionElement from '@/components/forms/creatorInputs/CreatorSectionElement.vue'
import CreatorFileUploadElement from '@/components/forms/creatorInputs/CreatorFileUploadElement.vue'
import CreatorSelectElement from '@/components/forms/creatorInputs/CreatorSelectElement.vue'
import CreatorCheckboxElement from '@/components/forms/creatorInputs/CreatorCheckboxElement.vue'
import CreatorSelectReferenceElement from '@/components/forms/creatorInputs/CreatorSelectReferenceElement.vue'
export default {
  name: 'FormCreator',
  props: {
    presetValue: Object,
    name: String,
  },
  components: {
    CreatorInputElement,
    CreatorHeaderElement,
    CreatorSectionElement,
    CreatorFileUploadElement,
    CreatorSelectElement,
    CreatorCheckboxElement,
    CreatorSelectReferenceElement,
  },
  data() {
    return {
      componentDict: {
        InputElement: 'CreatorInputElement',
        SectionElement: 'CreatorSectionElement',
        HeaderElement: 'CreatorHeaderElement',
        FileUploadElement: 'CreatorFileUploadElement',
        SelectElement: 'CreatorSelectElement',
        Checkbox: 'CreatorCheckboxElement',
        SelectReferenceElement: 'CreatorSelectReferenceElement',
      },
      elements: [],
      dragIndex: null,
      offsetY: 0,
      id: 0,
      jumpCorrection: 0,
      dragWrapperTop: 0,
      MARGIN: 8,
      wrapperHeight: 0,
      dragable: true,
    }
  },
  mounted(){
    if(this.presetValue) {
      this.presetValue.forEach(e=>{
        this.addElement(e.component)
        this.elements[this.elements.length-1]['presetData']=e.data
        this.elements[this.elements.length-1]['position']=e.position
        this.elements[this.elements.length-1]['el_id']=e.id
      })
      this.elements.sort((a,b)=>a.position>b.position ? 1: -1)
    }
  },
  watch: {
    presetValue(to) {
      if(to==null) {
        this.elements=[]
      } else {
        to.forEach(e=>{
          this.addElement(e.component)
          this.elements[this.elements.length-1]['presetData']=e.data
        })
        this.elements.sort((a,b)=>a.position>b.position ? 1: -1)
      }
    }
  },
  computed: {
    dynamicStyleElement() {
      return {
        'position': 'absolute',
        'top': `${this.dragTop}px`,
        'width': '100%'       
      }
    },
    dynamicStyleWrapper() {
      return {
        'height': `${this.wrapperHeight}px` 
      }
    },
    dragTop() {
      return this.jumpCorrection-this.offsetY
    },
    dragToContainerTop() {
      return this.dragTop + this.dragWrapperTop
    },
  },
  methods: {
    editActivated() {
      this.dragable = false
    },
    editDeactivated() {
      this.dragable = true
    },

    initElementPositions() {
      const topParent = this.$refs.contentContainer.getBoundingClientRect().top
      for(var i=0; i<this.elements.length; i++) {
        this.elements[i].top = this.$refs[this.elements[i].id].getBoundingClientRect().top - topParent
        this.elements[i].height = this.$refs[this.elements[i].id].getBoundingClientRect().height
      }
    },


    updateElementPositions(change) {
      const top = this.elements[this.dragIndex].top
      this.elements[this.dragIndex].top = this.elements[this.dragIndex+change].top
      this.elements[this.dragIndex+change].top = top
    },


    addElement(type) {
      var newElement = {type: type, id: `${this.id}`, top: 0, height: 0};
      this.id = this.id+1;
      this.elements.push(newElement)
    },


    startDrag(event, index) {
      if(!this.dragable){
        return
      }
      if(Array.from(event.target.classList).includes('no-drag')) {
        return
      }
      const topContainer = this.$refs.contentContainer.getBoundingClientRect().top
      const topWrapper = event.target.parentNode.getBoundingClientRect().top
      
      this.initElementPositions();


      this.wrapperHeight = event.target.parentNode.getBoundingClientRect().height
      this.dragWrapperTop = topWrapper - topContainer
      this.startMouseY = event.clientY
      this.dragIndex = index
      document.addEventListener("mousemove", this.draging)
      document.addEventListener("mouseup", this.endDrag)

    },


    draging(event) {
      // change position of element being dragged
      const offsetY = this.startMouseY - event.clientY
      this.offsetY = offsetY

      if(this.elements.length>1) {
        if(this.dragIndex==0) {

          const cond1 = this.elements[this.dragIndex+1].top<this.dragToContainerTop
          // console.log(cond1)
          if(cond1) {
            this.dragWrapperTop = this.elements[this.dragIndex+1].top
            this.jumpCorrection = this.jumpCorrection - this.elements[this.dragIndex+1].height - this.MARGIN
            this.updateElementPositions(+1);
            const element = this.elements[this.dragIndex]
            this.elements.splice(this.dragIndex, 1);
            this.elements.splice(this.dragIndex+1,0,element);
            this.dragIndex++;
          }
        } else if(this.dragIndex==this.elements.length-1) {
          const cond2 = this.elements[this.dragIndex-1].top + this.elements[this.dragIndex-1].height>this.dragToContainerTop
          // console.log(cond2)
          if(cond2) {
            this.dragWrapperTop = this.elements[this.dragIndex-1].top
            this.jumpCorrection = this.jumpCorrection + this.elements[this.dragIndex-1].height + this.MARGIN
            this.updateElementPositions(-1);
            const element = this.elements[this.dragIndex]
            this.elements.splice(this.dragIndex, 1);
            this.elements.splice(this.dragIndex-1,0,element);
            this.dragIndex--;
          }

        } else {
          const cond1 = this.elements[this.dragIndex+1].top<this.dragToContainerTop
          const cond2 = this.elements[this.dragIndex-1].top + this.elements[this.dragIndex-1].height>this.dragToContainerTop
          // console.log(cond1, cond2)
          if(cond1) {
            this.dragWrapperTop = this.elements[this.dragIndex+1].top
            this.jumpCorrection = this.jumpCorrection - this.elements[this.dragIndex+1].height - this.MARGIN
            this.updateElementPositions(+1);
            const element = this.elements[this.dragIndex]
            this.elements.splice(this.dragIndex, 1);
            this.elements.splice(this.dragIndex+1,0,element);
            this.dragIndex++;
          } else if(cond2) {
            this.dragWrapperTop = this.elements[this.dragIndex-1].top
            this.jumpCorrection = this.jumpCorrection + this.elements[this.dragIndex-1].height + this.MARGIN
            this.updateElementPositions(-1);
            const element = this.elements[this.dragIndex]
            this.elements.splice(this.dragIndex, 1);
            this.elements.splice(this.dragIndex-1,0,element);
            this.dragIndex--;
          }          
        }
      }
    },


    endDrag() {
      document.removeEventListener("mousemove", this.draging)
      document.removeEventListener("mouseup", this.endDrag)    
      this.jumpCorrection = 0
      this.dragWrapperTop = 0
      this.offsetY = 0
      this.dragIndex = null
      console.log(this.elements)
    },
    deleteItem(e) {
      var a = this.elements.map(e=>Number(e.id)).indexOf(e)
      this.elements.splice(a, 1)
    },

  }

}
</script>

<style scoped lang="scss" >
.create-form {
  text-align: center;
  display: flex;
  flex-direction: column;
  height: 100%;
}
.create-form-body {
  min-height: 400px;
  display: grid;
  grid-template-columns: 105px auto;
  grid-gap: 4px;
}
#create-form-content{
  position: relative;
  height: 100%;
  display: flex;
  flex-direction: column;
  background-color: #fff;
  border: 1px solid rgba(0,0,0,0.2);
  border-radius: 2px;
}
.form-item-selection {
  padding: 5px;
  cursor: pointer;
  border-radius: 2px;
  background-color: rgb(233, 233, 233);
  margin: 2px 0;
  box-shadow: 0 0 2px rgba(0, 0, 0, 0.3);
  &:hover{
    background-color: rgb(223, 223, 223); 
  }
  &:active{
    box-shadow: inset 0 0 4px rgba(0, 0, 0, 0.3);
  }
}


.form-item-wrapper {
  position: relative;
  margin: 4px 0;
  &:hover:not(.active) {
    outline: 2px solid rgba(0,0,0,0.45);
  }  
  &:first-of-type {
    margin: 0 0 4px 0;
  }
  &.active {
    box-shadow: 0 0 2px 2px inset rgba(0,0,0,0.1), 0 0 4px 4px rgba(102,175,233,0.6);
  }

}
.element {
  position: relative;
  user-select: none;

  &.active {
    outline: 2px solid rgba(0,0,0,0.45);
    background-color: #fff;
    z-index: 1;
  }
  &.dragable {
    cursor: grab;
  }
}
</style>