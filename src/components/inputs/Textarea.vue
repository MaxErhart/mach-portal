<template>
  <div class="textarea">
    <label :for="name" :style="{ color: textareaFocus ? '#00876c' : 'inherit' }">{{label}}</label>
    <textarea v-model="message" :id="name" @focus="textareaFocus = true" @blur="textareaFocus = false" :name="name" ref="input"></textarea>
  </div>
</template>

<script>
export default {
  name: 'Textarea',
  props: {
    label: String,
    name: String,
    presetValue: String,
  },
  data() {
    return {
      message: '',
      textareaFocus: false,
    }
  },
  mounted() {
    if(this.presetValue) {
      this.message=this.presetValue
    }
  },
  watch: {
    presetValue(to) {
      this.message=to
    }
  },
  methods: {
    clear() {
      this.message = ''
      this.textareaFocus = false
    }
  }
}
</script>


<style scoped lang="scss">
textarea {
  width: 100%;
  height: 6rem;
}
</style>
