<template>
  <div class="multi-step-form-step">
    <slot/>
  </div>
</template>

<script>
export default {
  name: 'MultiStepFormStep',
  props: {
    step: Number,
    is_step: [Number,String],
    refs: Object,
    ref_names: Array,
    customValidationFunction: Function,
  },
  data() {
    return  {
      error_message: null,
    }
  },
  methods: {
    next(event) {
      this.$emit("next",event)
    },
    isValid() {
      var valid = true
      this.error_message = null
      this.ref_names?.forEach(ref_name=>{
        const input_component = this.refs?.[`${ref_name}`]
        input_component.customError=null
        if(input_component?.error!==null && input_component?.error!==undefined && input_component.required) {
          valid = false
          this.error_message = "One or more required inputs have an error"
          this.$emit('defocus', ref_name)
        }
      })
      if(this.customValidationFunction && valid) {
        const validation = this.customValidationFunction()
        // console.log(validation)
        if(!validation.is_valid) {
          this.error_message = validation.message 
          valid = false
        }
      }
      return valid
    }
  },

}
</script>


<style scoped lang="scss">
.multi-step-form-step{
  height: 100%;
}
</style>
