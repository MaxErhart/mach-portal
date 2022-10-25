<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>
    <Checkbox class="element-preview" :data="{label: label, required: required, tooltip: tooltip}"/>
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
        <InputElement :data="{label: 'Edit Label', type: 'text', required: true}" :name="`${name}_label_data`" @valueChange="label=$event" :presetValue="String(label)"/>
      </section> 
      <section class="tooltip-section">
        <InputElement :data="{label: 'Edit Tooltip', type: 'text', required: false}" :name="`${name}_tooltip_data`" @valueChange="tooltip=$event" :presetValue="String(tooltip)"/>
      </section> 

      <section class="required-section">
        <Checkbox :data="{label: 'Input Required', required: false}" :name="`${name}_required_data`" @inputChange="required=$event" :presetValue="required"/>
      </section>

      <section class="show-section">
        <Checkbox :data="{label: 'Show Column for Submissions', required: false}" :name="`${name}_show_data`" @inputChange="show=$event" :presetValue="show"/>
      </section>      
      <section class="hidden-section">
        <input type="hidden" :name="`${name}_component`" value="Checkbox">
        <input type="hidden" :name="`${name}_position`" :value="position">
        <input type="hidden" :name="`${name}_id`" :value="id ? id : null">
      </section>      
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorInputElement',
  components: {
    InputElement,
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
      label: 'Element Label',
      tooltip: '',
      required: false,
      show: true,
      edit: false,
    }
  },
  computed: {
    selectData() {
      var data = {label: 'Input Type', required: true}
      this.typeSelect.forEach((v, i)=>{
        data[String(i)]=v.name
      })
      return data
    },
  },
  mounted() {
    if(this.presetData) {
      this.label=this.presetData.label
      this.tooltip=this.presetData.tooltip
      this.placeholder=this.presetData.placeholder
      this.required=Boolean(Number(this.presetData.required))
      this.show=Boolean(Number(this.presetData.show))
    }
  },
  watch: {
    presetData(to) {
      this.label=to.label
      this.type=to.type
      this.tooltip=to.tooltip
      this.placeholder=to.placeholder
      this.required=Boolean(Number(to.required))
      this.show=Boolean(Number(this.presetData.show))
    }
  },  
  methods: {
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
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
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
</style>
