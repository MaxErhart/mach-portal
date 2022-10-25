<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>

    <div class="item">
      <label for="input-field">{{label}}</label>
      <input id="file-input" type="file">
      <label id="file-upload-text">
          <span><img :src="require(`@/assets/upload.svg`)"></span>
          <span>Upload File</span>
      </label>
      <div class="file-tooltip">{{tooltip}}</div>
    </div>

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
        <InputElement :data="{label: 'Edit Label', type: 'text', required: true}" :name="`${name}_label_data`" @valueChange="label=$event" :presetValue="label"/>
      </section>
      <section class="tooltip-section">
        <InputElement :data="{label: 'Edit tooltip', type: 'text', required: false}" :name="`${name}_tooltip_data`" @valueChange="tooltip=$event" :presetValue="tooltip"/>
      </section>      
      <section class="path-section">
        <InputElement :data="{label: 'Edit Path', type: 'text', required: true}" :name="`${name}_path_data`" @valueChange="path=$event" :presetValue="path"/>
      </section>      
      <section class="required-section">
        <Checkbox :data="{label: 'Required', required: false}" :name="`${name}_required_data`" @inputChange="required=$event" :presetValue="required"/>
      </section>
    
      <section class="hidden-section">
        <input type="hidden" :name="`${name}_component`" value="FileUploadElement">
        <input type="hidden" :name="`${name}_position`" :value="position">
        <input type="hidden" :name="`${name}_id`" :value="id ? id : null">
      </section>      
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
// import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorHeaderElement',
  components: {
    InputElement,
    // SelectElement,
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
      label: 'File',
      required: true,
      tooltip: '',
      path: 'D:\\inetpub\\MPortal\\dfiles\\forms',

      edit: false,
    }
  },
  mounted() {
    if(this.presetData) {
      this.label=this.presetData.label
      this.tooltip=this.presetData.tooltip
      this.path=this.presetData.path
      this.required=this.presetData.required=='true'?true:false
    }
  },
  watch: {
    presetData(to) {
      this.label=to.label
      this.tooltip=to.tooltip
      this.path=to.path
      this.required=to.required=='true'?true:false
    }
  },  
  computed: {
    style() {
      var style = {'color': `${this.color}`}
      if(this.underline) {
        style['text-decoration'] = 'underline'
      }
      return style
    },
  },  
  methods: {
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
  padding: 0;
  margin: 8px 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h2 {
  padding: 0;
  margin: 8px 0;
  color: $text_dark;
  text-decoration: none;
  border: none;
}
h3 {
  padding: 0;
  margin: 4px 0;
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
  background-color: #fff;
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
.item {
  display: flex;
  flex-direction: column;
  justify-content: flex-start;
  align-items: flex-start;
  width: 100%;
  > * {
    padding: 0px 0;
    margin: 0px 0;    
  } 
}
#file-input {
  position: absolute;
  z-index: -1;
  top: 6px;
  left: 0;
  font-size: 15px;
  color: rgb(153,153,153);
  display: none;
}
#file-upload-text {

  border:1px solid #2c3e50;
  height: 40px;
  justify-content: center;
  box-shadow: 0 0 2px 1px rgba(0,0,0,0.2);
  width: 100%;
  background: linear-gradient(to right, rgba(0, 255, 0, 0.781) 50%, white 50%);
  background-size: 200% 100%;
  background-position: right bottom;

  > span:first-child {
    margin-right: 5px;
  }
  &:hover {
    box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
  }
  &.active {
  background-position: left bottom;
  transition:all 600ms ease;
  }
} 
.file-tooltip {
  font-size: 12px;
}
</style>
