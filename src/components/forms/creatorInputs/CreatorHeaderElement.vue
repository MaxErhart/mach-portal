<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>
    <component :is="type.name" class="item-content" :style="style">
      {{content}}
    </component>
    <div class="form-item-buttons no-drag">
      <div class="form-item-option edit-form-item no-drag" @click="toggleEdit()">
        <img class="no-drag" :src="require(`@/assets/edit.svg`)">
      </div>
      <div class="form-item-option delete-form-item no-drag" @click="deleteItem(el)">
        <img class="no-drag" :src="require(`@/assets/delete.svg`)">
      </div>
    </div>

    <div class="edit-element" :class="{active: edit}">
      <section class="label-section">
        <InputElement label="Edit Header" type="text" :required="true" @valueChange="content=$event" :presetValue="content"/>
      </section> 
      <section class="type-section">
        <SelectElement label="Header type" :required="true" :data="typeSelect" @selectedEntry="type=$event" :presetValue="type.id"/>
      </section>
      <section class="label-section">
        <Checkbox label="Underline Text" @inputChange="underline=$event" :presetValue="underline=='true'?true:false"/>
      </section>
      <section class="color-section">
        <label for="Text Color"></label>
        <input type="color" v-model="color">
      </section>      
      <section class="hidden-section">
        <input type="hidden" :name="name" :value="JSON.stringify(elementData)">
      </section>      
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorHeaderElement',
  components: {
    InputElement,
    SelectElement,
    Checkbox
  },
  props: {
    id: Number,
    name: String,
    position: Number,
    presetData: Object,
    formCratorIdentifier: Number,
  },
  data() {
    return {
      content: 'Header',
      type: {id: 0, name: 'h1'},
      typeSelect: [
        {id: 0, name: 'h1'},
        {id: 1, name: 'h2'},
        {id: 2, name: 'h3'},
        {id: 3, name: 'h4'},
        {id: 4, name: 'h5'},
        {id: 5, name: 'h6'},
      ],
      edit: false,
      underline: false,
      color: '#2c3e50',      
    }
  },
  mounted() {
    if(this.presetData) {
      this.matchPresets(this.presetData)
    }
  },
  watch: {
    presetData(to) {
      this.matchPresets(to)
    }
  },  
  computed: {
    elementData() {
      return {
        component: 'HeaderElement',
        position: this.position,
        id: this.id,
        show: true,
        input: false,
        data: {
          content: this.content,
          type: this.type.name,
          underline: this.underline,
          color: this.color
        }
      }
    },
    selectData() {
      var data = {label: 'Input Type', required: true}
      this.typeSelect.forEach((v, i)=>{
        data[String(i)]=v.name
      })
      return data
    },
    style() {
      var style = {'color': `${this.color}`}
      if(this.underline) {
        style['text-decoration'] = 'underline'
      }
      return style
    },
  },  
  methods: {
    matchPresets(value) {
      this.type=this.typeSelect.find(el=>el.name==value.data.type)
      this.content=value.data.content
      this.color=value.data.color
      this.underline=value.data.underline
    },
    test(e){
      console.log(e)
    },
    deleteItem() {
      this.$emit("deleteItem", this.formCratorIdentifier)
    },    
    toggleEdit() {
      if(this.edit) {
        this.edit = false
        this.$emit('editDeactivated')
      } else {
        this.edit = true
        this.$emit('editActivated')
      }
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
h1 {
  margin:0;
  padding: 8px 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h2 {
  margin: 0;
  padding: 8px 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h3 {
  margin: 0;
  padding: 4px 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h4 {
  padding: 0;
  margin: 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h5 {
  padding: 0;
  margin: 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h6 {
  padding: 0;
  margin: 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
.creator-input-element {
  padding: 0 10px;
  position: relative;
  &:hover {
    .form-item-buttons {
      visibility: visible;
    }
  }
  &.edit {
    outline: 2px solid rgba(0, 0, 0, .45);
  }
}
.edit-element {
  margin-left: 8px;
  display: none;
  &.active {
    display: block;
  }
}

.clickable-overlay {
  position: absolute;
  width: calc(100% - 20px);
  height: 100%;
  z-index: 1;
  &.edit {
    display: none;
  }
}
.form-item-buttons {
  visibility: hidden;
  user-select: none;
  position: absolute;
  z-index: 2;
  top: 0px;
  right: 0px;
  display: flex;
  .form-item-option {
    border: 2px solid $text_dark;
    width: 48px;
    cursor: pointer !important;
    &:hover {
      box-shadow: 0 0 4px 4px inset rgba(0, 0, 0, 0.2);
    }
  }
  .edit-form-item {
    border-bottom-left-radius: 4px;
    border-right: 1px solid $text_dark;
  }
  .delete-form-item {
    border-left: 1px solid $text_dark;
  }
}
.color-section {
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: flex-start;
}
</style>
