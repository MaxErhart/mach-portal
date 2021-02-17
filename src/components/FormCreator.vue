<template>
  <div class="create-form">
    <div class="crate-form-metadata">
      <section class="form-name">
        <label for="form-name">Form Name:</label>
        <input type="text" class="form-name-input" v-model="formName">
      </section>
    </div>
    <div class="create-form-body">
      <div id="create-form-content" ref="createFormContent">
        <div class="form-item-wrapper" v-for="(el,index) in selections" :key="el">
          <div @mousedown.self.prevent="startDrag($event, index)" :ref="el.id" :id="el.id" class="form-item">
            <component class="component" :is="el.component" v-bind="el.props"></component>
          </div>
          <div class="form-item-buttons">
            <div class="form-item-option edit-form-item" @click="el.props.editable ? el.props.editable = false : el.props.editable=true">
              <img :src="require(`@/assets/edit.svg`)">
            </div>
            <div class="form-item-option delete-form-item" @click="deleteItem(el)">
              <img :src="require(`@/assets/delete.svg`)">
            </div>
          </div>          
        </div>
      </div>
      <div class="create-form-selection">
        <div class="form-item-selection" v-on:click="addSelection('header')">Header</div>
        <div class="form-item-selection" v-on:click="addSelection('section')">Section</div>
        <div class="form-item-selection" v-on:click="addSelection('input')">Input</div>
      </div>      
    </div>
    <button class="kit-button" id="save-form-button" v-on:click="saveForm()">Safe Form</button>
  </div>
</template>

<script>
import axios from "axios";
import HeaderElement from './HeaderElement.vue'
import SectionElement from './SectionElement.vue'
import InputElement from './InputElement.vue'
export default {
  name: 'FormCreator',
  components: {
    HeaderElement,
    SectionElement,
    InputElement
  },
  data() {
    return {
      selections: [],
      dragingTarget: null,
      top: null,
      left: null,
      height: null,
      formName: null,
    }
  },
  mounted(){
    document.addEventListener("mousemove", ($event) => this.draging($event));
    document.addEventListener("mouseup", ($event) => this.dragEnd($event));
  },
  methods: {
    async addSelection(item){
      var id = `${Math.floor(Math.random()*100000000)}`
      var selection = {component: "none", props: {editable: false}, id: id, top: 0, bottom: 0};
      if(item == 'header'){
        selection = {component: "HeaderElement", props: {editable: false, id: id}, id: id, top: 0, bottom: 0};
      } else if(item == 'section') {
        selection = {component: "SectionElement", props: {editable: false, id: id}, id: id, top: 0, bottom: 0};
      } else if(item == 'input') {
        selection = {component: "InputElement", props: {editable: false, id: id}, id: id, top: 0, bottom: 0};
      }
      this.selections.push(selection)
      await this.$nextTick()
      var top = this.$refs[id].getBoundingClientRect().top;
      var bottom = this.$refs[id].getBoundingClientRect().bottom;
      this.selections[this.selections.length - 1].top = top;
      this.selections[this.selections.length - 1].bottom = bottom;     
    },
    deleteItem(element) {
      const index = this.selections.indexOf(element)
      this.selections.splice(index, 1)
    },
    generateElHtml(element) {
      if(element.type == 'header') {
        return `<${element.tag} class="item-content">${element.content}</${element.tag}>`;
      }
    },
    startDrag(event, index){
      this.top = this.$refs.createFormContent.getBoundingClientRect().top;
      this.left = this.$refs.createFormContent.getBoundingClientRect().left;
      this.dragingTarget = {el: event.target, index: index};
      event.target.parentElement.style.zIndex = 100;
      event.target.style.zIndex = 100;
      event.target.style.boxShadow = "inset 0 1px 1px rgba(0,0,0,0.1), 0 0 8px rgba(102,175,233,0.6)";
      if(index>0){
        this.height = this.$refs[this.selections[index-1].id].getBoundingClientRect().bottom;
      } else {
        this.height = this.top;
      }
      
    },
    async draging(event){
      if(this.dragingTarget){
        var item = this.selections[this.dragingTarget.index];
        if(this.dragingTarget.index == 0) {
          this.dragingTarget.el.style.top = `${event.pageY - this.top - 4}px`;
          if(this.selections.length > 1) {
            if(event.pageY > this.selections[this.dragingTarget.index+1].top) {
              this.selections.splice(this.dragingTarget.index, 1);
              this.selections.splice(this.dragingTarget.index+1,0,item);
              this.dragingTarget.index += 1;
              await this.$nextTick()
              this.updateYPosList(this.selections[this.dragingTarget.index-1])
            }            
          }
        } else if(this.dragingTarget.index == this.selections.length - 1) {
          this.dragingTarget.el.style.top = `${event.pageY - this.selections[this.dragingTarget.index-1].bottom-4}px`;
            if(event.pageY < this.selections[this.dragingTarget.index-1].bottom) {
              this.selections.splice(this.dragingTarget.index, 1);
              this.selections.splice(this.dragingTarget.index-1,0,item);
              this.dragingTarget.index -= 1;
              await this.$nextTick()
              this.updateYPosList(this.selections[this.dragingTarget.index+1])              
            }
        } else {
          this.dragingTarget.el.style.top = `${event.pageY - this.selections[this.dragingTarget.index-1].bottom-4}px`;
            if(event.pageY < this.selections[this.dragingTarget.index-1].bottom) {
              this.selections.splice(this.dragingTarget.index, 1);
              this.selections.splice(this.dragingTarget.index-1,0,item);
              this.dragingTarget.index -= 1;
              await this.$nextTick()
              this.updateYPosList(this.selections[this.dragingTarget.index+1])                
            } else if(event.pageY > this.selections[this.dragingTarget.index+1].top) {
              this.selections.splice(this.dragingTarget.index, 1);
              this.selections.splice(this.dragingTarget.index+1,0,item);
              this.dragingTarget.index += 1;
              await this.$nextTick()
              this.updateYPosList(this.selections[this.dragingTarget.index-1])                
            }     
        }
      }
    },
    dragEnd(){
      if(this.dragingTarget){
        this.dragingTarget.el.parentElement.style.zIndex = 1;
        this.dragingTarget.el.style.zIndex = 1;
        this.dragingTarget.el.style.boxShadow = "none";
        this.dragingTarget.el.style.top = 0;
        for(var i=0; i<this.selections.length; i++) {
          this.updateYPosList(this.selections[i])
        }
        this.$store.commit('updateSelectionsOrder', this.selections);
        this.dragingTarget = null;
      }
    },
    updateYPosList(selection){
      var rect = this.$refs[selection.id].getBoundingClientRect()
      selection.top = rect.top
      selection.bottom = rect.bottom
    },
    saveForm() {
      axios({
				method: 'post',
				url: 'https://www-3.mach.kit.edu/api/saveForm.php',
				data: {formName: this.formName, elements: this.$store.getters.getSelectionsData},
			}).then((response) => {
        console.log(response.data)
      })   
    },
  }

}
</script>

<style scoped lang="scss" >
  label {
    display: block;
    font-size: 16px;
    width: 100%;
    font-weight: 500;
    margin: 5px 10px;
    text-align: left;
  }
  input {
    user-select: auto !important;
    display: block;
    height: 40px;
    width: calc(100% - 20px);
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px;
    margin: 5px 10px;
  }
  .create-form {
    text-align: center;
    display: flex;
    flex-direction: column;
    height: 100%;
  }
  .create-form-body {
    height: 100%;
    display: grid;
    grid-template-columns: auto 105px;
    padding: 4px;
    grid-gap: 4px;
  }
  .create-form-selection{
    height: 100%;
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
  #save-form-button{
    margin: 4px 4px;
    font-size: 16px;
    font-weight: 500;    
  }
  .form-item {
    cursor: move;
    position: relative;
    height: 100%;
    width: 100%;
  }
  .component {
    pointer-events: none;
  }
  .form-item-buttons {
    position: absolute;
    z-index: 2;
    top:0;
    right:0;
    display: flex;
    flex-direction: row;
    margin: 0px 0;
    visibility: hidden;
  }
  
  .form-item-option {
    width: 20px;
    height: 20px;
    border: 1px solid rgba(0,0,0,0.2);
    cursor: pointer;
    background-color: #ddd;
  }
  .edit-form-item {
    border-radius: 0 0 0 5px;
    border-right: none;
  }
  .item-type {
    > * {
      margin:0;
      pointer-events: none;
      user-select: none;
    }
  }
  .form-item-wrapper{
    position: relative;
    margin: 4px 4px;
    &:hover {
      box-shadow: inset 0 1px 1px rgba(0,0,0,0.1), 0 0 8px rgba(102,175,233,0.6);
      >.form-item-buttons {
        visibility: visible;
      }    
    }      
  }
  .edit-element-container{
    margin: 5px;
    padding: 5px;
    position: relative;
    top: 0;
  }
</style>