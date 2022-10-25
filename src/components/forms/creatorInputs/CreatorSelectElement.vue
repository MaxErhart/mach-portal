<template>
  <div class="creator-input-element" :class="{edit: edit}">
    <div class="clickable-overlay" :class="{edit: edit}"></div>

    <SelectElement :data="selectData" :nameAsValue="true"/>


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
        <InputElement :data="{label: 'Edit Label', type: 'text', required: true}" :name="`${name}_label_data`" @valueChange="label=$event" :presetValue="label"/>
      </section> 
      <section class="label-section">
        <InputElement :data="{label: 'Edit Tooltip', type: 'text', required: false}" :name="`${name}_tooltip_data`" @valueChange="tooltip=$event" :presetValue="tooltip"/>
      </section>
      <section class="label-section">
        <InputElement :data="{label: 'Edit Placeholder', type: 'text', required: false}" :name="`${name}_placeholder_data`" @valueChange="placeholder=$event" :presetValue="placeholder"/>
      </section>        
      <section class="label-section">
        <Checkbox :data="{label: 'Required', required: false}" :name="`${name}_required_data`" @inputChange="required=$event" :presetValue="required"/>
      </section>
      <section class="show-section">
        <Checkbox :data="{label: 'Show Column for Submissions', required: false}" :name="`${name}_show_data`" @inputChange="show=$event" :presetValue="show"/>
      </section>      
      <section class="label-section">
        <InputElement :data="{label: 'Number of Options', type: 'number', required: false}" :name="`${name}_numoptions_data`" @valueChange="updateNumOptions($event)" :presetValue="String(numOptions)"/>
      </section>      
      <section class="label-section" v-for="index in numOptions" :key="index">
        <InputElement :data="{label: `Edit Option ${index}`, type: 'text', required: false}" :name="`${name}_${index-1}_data`" @valueChange="selections[index-1]=$event" :presetValue="selections[index-1]"/>
      </section>             
      <section class="hidden-section">
        <input type="hidden" :name="`${name}_component`" value="SelectElement">
        <input type="hidden" :name="`${name}_position`" :value="position">
        <input type="hidden" :name="`${name}_id`" :value="id ? id : null">
      </section>      
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
export default {
  name: 'CreatorSelectElement',
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
      label: 'Label',
      tooltip: '',
      placeholder: '',
      edit: false,
      required: false,
      numOptions: 1,
      selections: [''],
      show: true,
    }
  },
  mounted() {
    if(this.presetData) {
      this.label=this.presetData.label
      this.tooltip=this.presetData.tooltip
      this.placeholder=this.presetData.placeholder
      this.numOptions=Number(this.presetData.numoptions)
      this.required=this.presetData.required=='true'?true:false
      this.show=Boolean(Number(this.presetData.show))
      if(this.numOptions>0) {
        this.selections[0] = this.presetData['0']
        for(var i=1; i<this.numOptions; i++) {
          this.selections.push(this.presetData[`${i}`]) 
        }        
      }
    }
  },
  watch: {
    presetData(to) {
      this.label=to.label
      this.tooltip=to.tooltip
      this.placeholder=this.presetData.placeholder
      this.numOptions=Number(to.numoptions)
      this.required=to.required=='true'?true:false
      this.show=Boolean(Number(this.presetData.show))
      if(this.numOptions>0) {
        this.selections[0] = this.presetData['1']
        for(var i=1; i<this.numOptions; i++) {
          this.selections.push(this.presetData[`${i+1}`]) 
        }        
      }

    }
  },  
  computed: {
    selectData() {
      var data = {label: this.label, required: this.required, placeholder: this.placeholder, tooltip: this.tooltip}
      this.options.forEach((v, i)=>{
        data[String(i)]=v
      })
      return data
    },    
    options() {
      var options = []
      var i = 0
      this.selections.forEach(e=>{
        var newOption = {id: i, name: e}
        options.push(newOption)
        i++
      })
      return options
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
    updateNumOptions(e){
      if(Number(e)>this.numOptions.length) {
        for(var i=this.numOptions.length; i<Number(e);i++){
          this.selections.push('')
        }
      }
      this.numOptions=Number(e)
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
