<template>
  <div class="create-form">
    <div class="create-form-body">
      <div class="create-form-selection">
        <div class="form-item-selection" v-on:click="addSelection('header')"></div>
      </div>
      <div id="create-form-content" ref="createFormContent">
        <div class="form-item-wrapper" v-for="(el,index) in selections" :key="el">
          <div  @mousedown.self.prevent="startDrag($event, index)" :ref="el.id" :id="el.id" class="form-item"  v-html="el.element"></div>
        </div>
      </div>
    </div>

  </div>
</template>

<script>
export default {
  name: 'FormCreator',
  data() {
    return {
      // options: {'header': },
      selections: [],
      itemHeights: [],
      items: {},
      dragingTarget: null,
      top: null,
      left: null,
      height: null,
    }
  },
  mounted(){
    document.addEventListener("mousemove", ($event) => this.draging($event));
    document.addEventListener("mouseup", ($event) => this.dragEnd($event));
  },
  methods: {
    async addSelection(item){
      if(item == 'header'){
        var elTextContent = 'Header'
        var id = `${Math.floor(Math.random()*100000000)}`
        var el = `<h1>${elTextContent}</h1>`
        var selection = {element: el, id: id, top: 0, bottom: 0};
        this.selections.push(selection)
        await this.$nextTick()
        var top = this.$refs[id].getBoundingClientRect().top;
        var bottom = this.$refs[id].getBoundingClientRect().bottom;
        this.selections[this.selections.length - 1].top = top;
        this.selections[this.selections.length - 1].bottom = bottom;
      }
    },
    startDrag(event, index){
      this.top = this.$refs.createFormContent.getBoundingClientRect().top;
      this.left = this.$refs.createFormContent.getBoundingClientRect().left;
      this.dragingTarget = {el: event.target, index: index};
      event.target.parentElement.style.zIndex = 100;
      if(index>0){
        this.height = this.$refs[this.selections[index-1].id].getBoundingClientRect().bottom;
      } else {
        this.height = this.top;
      }
      
    },
    draging(event){
      if(this.dragingTarget){
        this.dragingTarget.el.style.zindex = 10;
        var item = this.selections[this.dragingTarget.index];
        if(this.dragingTarget.index > 0 && this.dragingTarget.index < this.selections.length-1) {
          // this.dragingTarget.el.style.left = `${event.pageX - this.left}px`;
          this.dragingTarget.el.style.top = `${event.pageY - this.selections[this.dragingTarget.index-1].bottom}px`;
          if(event.pageY < this.selections[this.dragingTarget.index-1].top + (this.selections[this.dragingTarget.index-1].bottom - this.selections[this.dragingTarget.index-1].top)/2) {
            this.selections.splice(this.dragingTarget.index, 1);
            this.selections.splice(this.dragingTarget.index-1,0,item);
            this.dragingTarget.index -= 1;
          } else if(event.pageY > this.selections[this.dragingTarget.index+1].top + (this.selections[this.dragingTarget.index+1].bottom - this.selections[this.dragingTarget.index+1].top)/2) {
            this.selections.splice(this.dragingTarget.index, 1);
            this.selections.splice(this.dragingTarget.index+1,0,item);
            this.dragingTarget.index += 1;
          }      
        } else if(this.dragingTarget.index >= this.selections.length-1) { 
          this.dragingTarget.el.style.top = `${event.pageY - this.selections[this.dragingTarget.index-1].bottom}px`;
          if(event.pageY < this.selections[this.dragingTarget.index-1].top + (this.selections[this.dragingTarget.index-1].bottom - this.selections[this.dragingTarget.index-1].top)/2) {
            this.selections.splice(this.dragingTarget.index, 1);
            this.selections.splice(this.dragingTarget.index-1,0,item);
            this.dragingTarget.index -= 1;
          }
        } else {
          // this.dragingTarget.el.style.left = `${event.pageX - this.left}px`;
          this.dragingTarget.el.style.top = `${event.pageY - this.top}px`;
          if(event.pageY > this.selections[this.dragingTarget.index+1].top + (this.selections[this.dragingTarget.index+1].bottom - this.selections[this.dragingTarget.index+1].top)/2) {
            this.selections.splice(this.dragingTarget.index, 1);
            this.selections.splice(this.dragingTarget.index+1,0,item);
            this.dragingTarget.index += 1;
          }
        }
      }
    },
    dragEnd(){
      if(this.dragingTarget){
        this.dragingTarget.el.parentElement.style.zIndex = 1;
        console.log(this.dragingTarget.el.parentElement.getBoundingClientRect().top - this.top)
        this.dragingTarget.el.style.top = 0;
        this.dragingTarget = null;
      }
    }
  }

}
</script>

<style lang="scss" scoped>
  .create-form {
    text-align: center;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  .create-form-body {
    height: 100%;
    border: 1px solid black;
    display: grid;
    grid-template-columns: 250px auto;
  }
  .create-form-selection{
    height: 100%;
    border: 1px solid blue;
  }
  #create-form-content{
    position: relative;
    height: 100%;
    border: 1px solid red;
    display: flex;
    flex-direction: column;
  }
  .form-item-selection {
    height: 50px;
    width: 100%;
    border: 1px solid green;
  }
  .form-item {
    background-color: rgb(243, 243, 243);
    border: 1px solid purple;
    // height: 100px;
    position: relative;
    width: 100%;
  }
  .form-item-wrapper{
    border: 1px solid gray;
    position: relative;
  }
</style>