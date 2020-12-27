<template>
  <div class="create-form">
    <div class="create-form-body">
      <div class="create-form-selection">
        <div class="form-item-selection" v-on:click="addSelection('header')"></div>
      </div>
      <div id="create-form-content" ref="createFormContent">
        <div class="form-item-wrapper" v-for="(el,index) in selections" :key="el">
          <div  @mousedown.self="startDrag($event, index)" :ref="el.id" :id="el.id" class="form-item"  v-html="el.element"></div>
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
    addSelection(item){
      if(item == 'header'){
        var elTextContent = 'Header'
        var id = `${Math.floor(Math.random()*100000000)}`
        var el = `<h1>${elTextContent}</h1>`
        this.selections.push({element: el, id: id})
      }
    },
    startDrag(event, index){
      console.log(this.$refs)
      console.log(event.target.id, index, this.$refs[this.selections[index].id].getBoundingClientRect())
      this.top = this.$refs.createFormContent.getBoundingClientRect().top;
      this.left = this.$refs.createFormContent.getBoundingClientRect().left;
      this.dragingTarget = event.target
      if(index>0){
        this.height = this.$refs[this.selections[index-1].id].getBoundingClientRect().bottom;
      } else {
        this.height = 0;
      }
      
    },
    draging(event){
      if(this.dragingTarget){
        this.dragingTarget.style.left = `${event.pageX - this.left}px`;
        this.dragingTarget.style.top = `${event.pageY - this.height}px`;
      }
    },
    dragEnd(){
      // add snap back
      if(this.dragingTarget){
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
  }
</style>