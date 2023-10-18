<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>

    <SelectElement :label="label" :required="required" :tooltip="tooltip" :placeholder="placeholder" :data="selections"/>


    <div class="form-item-buttons no-drag">
      <div class="form-item-option edit-form-item no-drag" @click="toggleEdit()">
        <img class="no-drag" :src="require(`@/assets/edit.svg`)">
      </div>
      <div class="form-item-option delete-form-item no-drag" @click="deleteItem()">
        <img class="no-drag" :src="require(`@/assets/delete.svg`)">
      </div>
    </div>

    <div class="edit-element" :class="{active: edit}">
      <section class="label-section">
        <InputElement label="Edit Label" type="text" :required="true" @valueChange="label=$event" :presetValue="label"/>
      </section> 
      <section class="label-section">
        <InputElement label="Edit Tooltip" type="text" :required="false" @valueChange="tooltip=$event" :presetValue="tooltip"/>
      </section>
      <section class="label-section">
        <InputElement label="Edit Placeholder" type="text" :required="false" @valueChange="placeholder=$event" :presetValue="placeholder"/>
      </section>        
      <section class="label-section">
        <Checkbox label="Required" :required="false" @inputChange="required=$event" :presetValue="required"/>
      </section>
      <section class="show-section">
        <Checkbox label="Show column in submissions" type="text" :required="false" @inputChange="show=$event" :presetValue="show"/>
      </section>
      <section class="show-section">
        <Checkbox label="Enable search options" type="text" :required="false" @inputChange="search=$event" :presetValue="search"/>
      </section> 
      <section class="label-section">
        <CollectionInput label="Options" type="text" :required="true" @valueChange="selections=$event" :presetValue="selections"/>
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
import CollectionInput from '@/components/inputs/CollectionInput.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorSelectElement',
  components: {
    InputElement,
    SelectElement,
    Checkbox,
    CollectionInput,
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
      label: 'Label',
      tooltip: '',
      placeholder: '',
      edit: false,
      required: false,
      selections: [],
      show: true,
      search: false,
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
        component: 'SelectElement',
        position: this.position,
        id: this.id,
        show: this.show,
        input: true,
        data: {show: this.show,search:this.search,label: this.label, required: this.required, data: this.selections, placeholder: this.placeholder, tooltip: this.tooltip},
      }
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
      this.label=value.data.label
      this.tooltip=value.data.tooltip
      this.placeholder=value.data.placeholder
      this.required=value.data.required
      this.show=value.show
      this.search=value.search
      this.selections = []
      if(Array.isArray(value.data.data)) {
        value.data.data.forEach((opt)=>{
          this.selections.push({id:opt.id,name:opt.name})
        })
      } else {
        Object.keys(value.data.data).forEach(id=>{
          this.selections.push({id,name:value.data.data[id]})
        })
      }
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
