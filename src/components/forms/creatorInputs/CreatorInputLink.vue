<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>
    <DoubleInputElement class="element-preview" :label_url="label_url" :label_alias="label_alias" :required="required" :placeholder_url="placeholder_url" :placeholder_alias="placeholder_alias" :tooltip="tooltip"/>
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
        <InputElement label='Edit Label URL' type='text' :required="true" @valueChange="label_url=$event" :presetValue="String(label_url)"/>
      </section>
      <section class="label-section">
        <InputElement label='Edit Label ALIAS' type='text' :required="true" @valueChange="label_alias=$event" :presetValue="String(label_alias)"/>
      </section> 
      <section class="tooltip-section">
        <InputElement label='Edit Tooltip' type='text' :required="false"  @valueChange="tooltip=$event" :presetValue="String(tooltip)"/>
      </section> 
      <section class="placeholder-section">
        <InputElement label='Edit Placeholder URL' type='text' :required="false"  :presetValue="placeholder_url" @valueChange="placeholder_url=$event"/>
      </section>
      <section class="placeholder-section">
        <InputElement label='Edit Placeholder ALIAS' type='text' :required="false"  :presetValue="placeholder_alias" @valueChange="placeholder_alias=$event"/>
      </section>
      <section class="required-section">
        <Checkbox label='Input Required' :required="false" @inputChange="required=$event" :presetValue="required"/>
      </section>
      <section class="show-section">
        <Checkbox label='Show Column for Submissions' :required="false" @inputChange="show=$event" :presetValue="show"/>
      </section>
      <section class="required-section">
        <Checkbox label='Show URL as extra column' :required="false" @inputChange="show_url=$event" :presetValue="show_url"/>
      </section>    
      <section class="hidden-section">
        <input type="hidden" :name="name" :value="JSON.stringify(elementData)">
      </section>      
    </div>
  </div>
</template>

<script>
import DoubleInputElement from '@/components/inputs/DoubleInputElement.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorInputLink',
  components: {
    DoubleInputElement,
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
      label_url: 'Element Label URL',
      label_alias: 'Element Label ALIAS',
      type: {id: 0, name: 'text'},
      tooltip: '',
      placeholder_url: '',
      placeholder_alias: '',
      required: false,
      show_url: false,
      typeSelect: [
        {id: 0, name: 'text'},
        {id: 1, name: 'number'},
        {id: 2, name: 'integer'},
        {id: 3, name: 'float'},
        {id: 4, name: 'email'},
        {id: 5, name: 'date'},
      ],
      edit: false,
      show: true,
    }
  },
  computed: {
    elementData() {
      return {
        component: 'DoubleInputElement',
        position: this.position,
        id: this.id,
        show: this.show,
        input: true,
        data: {
          show:this.show,
          type: this.type.name,
          label_url: this.label_url,
          label_alias: this.label_alias,
          show_url: this.show_url,
          tooltip: this.tooltip,
          required: this.required,
          placeholder_url: this.placeholder_url,
          placeholder_alias: this.placeholder_alias,

        }
      }
    },
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
  methods: {
    matchPresets(value) {
      console.log(value)
      this.label_url=value.data.label_url
      this.label_alias=value.data.label_alias
      this.tooltip=value.data.tooltip
      this.placeholder_url=value.data.placeholder_url
      this.placeholder_alias=value.data.placeholder_alias
      this.required=value.data.required
      this.show=value.show
      this.show_url=value.data.show_url
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
