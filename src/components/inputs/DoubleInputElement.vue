<template>
  <div class="double-input-element">
    <InputElement :placeholder="placeholder_url" :required="required" :label="label_url" :tooltip="tooltip" @valueChange="url=$event"/>
    <InputElement :placeholder="placeholder_alias" tooltip="URL Anzeigename" :label="label_alias" @valueChange="alias=$event"/>
  </div>
  <input type="hidden" :name="name" :value="value">
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'

export default {
  name: 'DoubleInputElement',
  components: {
    InputElement,
  },
  props: {
    name: String,
    label_url: String,
    label_alias: String,
    tooltip: String,
    placeholder_url: String,
    placeholder_alias: String,
    required: Boolean,
  },
  data() {
    return {
      url: null,
      alias: null,
    }
  },
  mounted() {
  },
  watch: {

  },
  computed: {
    value() {
      return JSON.stringify({
        url:this.url,
        alias:this.alias,
      })
    }
  },
  methods: {
  },

}
</script>


<style scoped lang="scss">
.double-input-element {
  display: grid;
  grid-template-columns: 1fr 1fr;
  gap: 4%;
}
</style>
