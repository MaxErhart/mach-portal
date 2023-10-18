<template>
  <div class="double-input">
    <InputElement :placeholder="placeholder_1" :required="required" :label="label_1" @valueChange="url=$event" :name="name_1" ref="input_1" :presetValue="preset_value_1"/>
    <span class="separator">{{separator}}</span>
    <InputElement :placeholder="placeholder_2" :required="required" :label="label_2" @valueChange="alias=$event" :name="name_2" ref="input_2" :presetValue="preset_value_2"/>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
export default {
  name: 'DoubleInput',
  components: {
    InputElement,
  },
  props: {
    name_1: String,
    name_2: String,
    label_1: String,
    label_2: String,
    placeholder_1: String,
    placeholder_2: String,
    required: Boolean,
    separator: String,
    preset_value_1: String,
    preset_value_2: String,
  },
  data() {
    return {
      deFocusedOnce: false,
      customError: null,
    }
  },
  watch: {
    deFocusedOnce(to) {
      this.$refs.input_1.deFocusedOnce = to
      // this.$refs.input_2.deFocusedOnce = to
    },
    customError(to) {
      this.$refs.input_1.customError = to
      // this.$refs.input_2.customError = ' '
    },
  },
  computed: {
    value_1() {
      return this.$refs.input_1.value
    },
    value_2() {
      return this.$refs.input_2.value
    },
  },
  methods: {
    clear() {
      this.$refs.input_1.clear()
      this.$refs.input_2.clear()
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.double-input {
  display: grid;
  grid-template-columns: 1fr auto 1fr;
}
.separator {
  margin: 0 0.75rem;
  font-weight: bold;
  height: 1rem;
  align-self:flex-end;
  transform: translateY(-14px);
}
</style>
