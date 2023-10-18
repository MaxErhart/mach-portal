<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>

    <SelectReferenceElement :required="required" :label="label" :formId="sourceForm ? sourceForm.id:null" :formElementIds="sourceElementIds"/>


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
        <Checkbox label="Show Column in Submissions" :required="false" @inputChange="show=$event" :presetValue="show"/>
      </section>
      <section class="show-section">
        <Checkbox label="Enable search options" type="text" :required="false" @inputChange="search=$event" :presetValue="search"/>
      </section>  
      <section class="source-select">
        <SelectElement label="Select source form" :search="true" :required="true" :data="parseForms(forms)"  @selectedEntry="selectSourceForm($event)" :presetValue="sourceForm==null ? null : sourceForm.id"/>
      </section>
      <section>
        <MultiSelectElementBox :presetValue="refFormElPreset" @change="setFormElements($event)" label="Select submission information to display" :cast="formElementCast" :required="true" :data="sourceForm?filterInputElements(sourceForm.form_elements):null"/>
      </section>
      <section class="hidden-section">
        <input type="hidden" :name="name" :value="JSON.stringify(elementData)">
      </section>      
    </div>
  </div>
</template>
<script>
// sourceForm?sourceForm.form_elements.filter(e=>sourceElementIds.includes(e.id)):null

import InputElement from '@/components/inputs/InputElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import SelectReferenceElement from '@/components/inputs/SelectReferenceElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import MultiSelectElementBox from '@/components/inputs/MultiSelectElementBox.vue'
export default {
  name: 'CreatorSelectReferenceElement',
  components: {
    InputElement,
    SelectElement,
    SelectReferenceElement,
    Checkbox,
    MultiSelectElementBox,
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
      numOptions: 1,
      selections: [''],
      show: true,
      forms: null,
      search: true,
      sourceForm: null,
      sourceElementIds: [],
      inputElements: ["InputElement", "SelectElement", "Checkbox", "MultiSelectElementBox","SelectReferenceElement"],
    }
  },
  mounted() {
    this.getForms();
    if(this.presetData) {
      this.setPresets(this.presetData)
    }
  },
  watch: {
    presetData(to) {
      this.setPresets(to)
    }
  },  
  computed: {
    refFormElPreset() {
      const elements = []
      this.sourceElementIds.forEach(id=>{
        const element = this.sourceForm?.form_elements[this.sourceForm.id][id]
        if(element) {
          elements.push(element)
        }
      })
      console.log(this.sourceElementIds,elements)
      return elements
    },
    elementData() {
      return {
        component: 'SelectReferenceElement',
        position: this.position,
        id: this.id,
        show: this.show,
        input: true,
        data: {show: this.show,search:this.search,formId: this.sourceForm?this.sourceForm.id:null, formElementIds: this.sourceElementIds, label: this.label, required: this.required, placeholder: this.placeholder, tooltip: this.tooltip},
      }
    },
    apiUrl() {
      return this.$store.getters.getApiUrl;
    },
  },
  methods: {
    parseForms(forms) {
      const object = {}
      forms?.forEach(form=>{
        object[form.id] = form
      })
      return object
    },
    setPresets(data) {
      this.label=data.data.label
      this.tooltip=data.data.tooltip
      this.placeholder=data.data.placeholder
      this.required=data.data.required
      this.show=data.data.show
      this.search=data.data.search
      this.sourceElementIds=data.data.formElementIds
    },
    setFormElements(elements) {
      this.sourceElementIds = elements.map(el=>el.id)
    },
    filterInputElements(elements) {
      const inputs = []
      Object.keys(elements[this.sourceForm.id]).forEach(el_id=>{
        const element = elements[this.sourceForm.id][el_id]
        if(!element.input || element.form_id!=this.sourceForm?.id) {
          return
        }
        inputs.push(element)
      })
      return inputs
    },
    formElementCast(element) {
      return {id: element.id, name: element.data.label}
    },
    selectSourceForm(e) {
      this.sourceForm = this.forms.find(f=>f.id==e.id)
    },
    async getForms() {
      this.awaitData = true
      const {forms,error} = await this.$store.dispatch('_forms', {method: 'get'})
      console.log(forms,error,error?.response)
      this.forms = forms
      if(this.presetData) {
        this.sourceForm = this.forms.find(f=>f.id==this.presetData.data.formId)
      }
      this.awaitData = false
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
