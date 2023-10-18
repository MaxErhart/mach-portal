<template>
  <div class="select-reference-element">
    <SelectElement ref="input" :presetValue="selected" @selectedEntry="select($event)" :data="format(data)" :label="label" :search="search" :required="required" :tooltip="tooltip" :placeholder="placeholder" :readonly="readonly"/>
    <input type="hidden" :name="name" :value="JSON.parse(selected)">
  </div>
</template>

<script>
import SelectElement from '@/components/inputs/SelectElement.vue'
// import axios from "axios";
export default {
  name: 'SelectReferenceElement',
  components: {
    SelectElement,
  },
  props: {
    id: [String,Number],
    label: String,
    search: Boolean,
    required: Boolean,
    tooltip: String,
    placeholder: String,
    readonly: Boolean,
    formId: Number,
    formElementIds: Array,
    name: String,
    submissions: [Array,Object],
    presetValue: Number,
    form: [Array,Object],
  },
  data() {
    return {
      awaitData: false,
      selected: null,
      deFocusedOnce: false,
      customError: null,
      archive_subs: [],
    }
  },
  mounted() {
    this.handlePreset(this.presetValue)
    this.getArchiveSubmissions()
  },
  watch: {
    presetValue(to) {
      this.handlePreset(to)
    },
    deFocusedOnce(to) {
      this.$refs.input.deFocusedOnce = to
    }
  },
  computed: {
    get_preset_value() {
      return this.selected
    },
    data() {
      if(!this.submissions?.[this.formId]) {
        return []
      }
      var data = this.submissions[this.formId].concat(this.archive_subs)
      data = data.sort((a,b)=>{
        if(a.created_at>=b.created_at) {
          return 1
        }
        return -1
      })
      return data

    },
  },
  methods: {

    

    async getArchiveSubmissions() {
      if(!this.formId) {
        return
      }
      const {submissions} = await this.$store.dispatch('_archive', {method:'get',form_id:this.formId})
      this.archive_subs = submissions
      console.log(submissions)

    },
    setError(error) {
      this.customError = error
      this.deFocusedOnce = true
    },
    handlePreset(value) {
      if(value===null || value===undefined || value==='') {
        this.clear()
        return
      }
      this.selected = value
    },
    clear() {
      this.$refs.input.clear()
    },
    select(event) {
      if(event===null) {
        this.selected=null
        this.deSelected=true
      } else {
        this.selected=event.id
      }
      this.$emit('selectedEntry', event)
    },
    getValueReferenceRecursively(element,value) {
      if(element==undefined || value===null || value===undefined) {
        return null
      }
      if(element.component!='SelectReferenceElement') {
        if(element.component=='SelectElement') {
          return [element.data.data.find(option=>option.id==value).name]
        }
        return [value]
      }
      const ref_sub = this.submissions[element.data.formId].find(sub=>sub.id==value)
      var data = []
      element.data.formElementIds.forEach(ref_form_el_id=>{
        const ref_el = this.form.form_elements[element.data.formId][ref_form_el_id]
        const ref_value = ref_sub?._data[ref_el.id]
        data = data.concat(this.getValueReferenceRecursively(ref_el,ref_value))
      })
      return data
    },
    getElementReferencesRecursively(element, path_prefix=[], prefix='') {
      if(!element) {
        return []
      }
      var label = prefix+element.data.label
      if(element.component!=="SelectReferenceElement") {
        return [{...element,label,path:path_prefix}]
      }
      var data = []
      var path = JSON.parse(JSON.stringify(path_prefix))
      path.push({el_id:element.id,form_id:element.data.formId})
      element.data.formElementIds.forEach(id=>{
        data = data.concat(this.getElementReferencesRecursively(this.form.form_elements[element.data.formId][id],path,label+'.'))
      })
      return data
    },
    // getArchiveData(submission,el_id) {
// 
      // submission.
// 
    // },
    format(submissions) {
      if(!this.form?.form_elements?.[this.form.id]) {
        return []
      }
      const elements = this.getElementReferencesRecursively(this.form.form_elements[this.form.id][this.id])
      console.log(elements)
      const options = {}
      submissions.forEach(submission=>{
        const option = []
        if(submission.is_archived) {
          elements.forEach(el=>{
            option.push(submission.archive_data[el.id])
          })
          options[submission.id] = option.join(" ")
          return
        }
        
        elements.forEach(el=>{
          var curr_sub = submission
          if(curr_sub===null || curr_sub===undefined) {
            return
          }
          for(var i=1; i<el.path.length; i++) {
            const ref_val = curr_sub._data[el.path[i].el_id]
            curr_sub = this.submissions[el.path[i].form_id].find(sub=>sub.id==ref_val)
            if(ref_val===null || ref_val===undefined || curr_sub===null || curr_sub===undefined) {
              return
            }
          }
          var value = curr_sub._data[el.id]

          if(el.component=="SelectElement") {
            value = el.data.data.find(option=>option.id==curr_sub._data[el.id])?.name
          }
          option.push(value)
        })
        options[submission.id] = option.join(" ")

      })
      return options
    },
  },
}
</script>


<style scoped lang="scss">

</style>
