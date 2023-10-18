<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>
    <Checkbox class="element-preview" :label="label" :required="required" :tooltip="tooltip"/>
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
        <InputElement label="Edit Label" type="text" :required="true" @valueChange="label=$event" :presetValue="String(label)"/>
      </section> 
      <section class="tooltip-section">
        <InputElement label="Edit Tooltip" type="text" :required="false" @valueChange="tooltip=$event" :presetValue="String(tooltip)"/>
      </section> 

      <section class="required-section">
        <Checkbox label="Input Required" :required="false" @inputChange="required=$event" :presetValue="required"/>
      </section>

      <section class="show-section">
        <Checkbox label="Show Column for Submissions" :required="false" @inputChange="show=$event" :presetValue="show"/>
      </section>      
      <section class="hidden-section">
        <input type="hidden" :name="name" :value="JSON.stringify(elementData)">
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
    elementData() {
      return {
        component: 'Checkbox',
        position: this.position,
        id: this.id,
        show: this.show,
        input: true,
        data: {label: this.label, required: this.required, tooltip: this.tooltip},
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
      this.label=value.data.label
      this.tooltip=value.data.tooltip
      this.required=value.data.required
      this.show=value.show
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
