<template>
  <div class="anon-form-settings">
    <label for="anon-title">Title</label>
    <input id="anon-title" type="text" :value="anonTitle" @change="emitChange($event, 'anonTitle')">

    <label for="anon-body">Body</label>
    <input id="anon-body" type="text" :value="anonBody" @change="emitChange($event, 'anonBody')">

    <label for="anon-support-email">Support Email</label>
    <input id="anon-support-email" type="text" :value="anonSupportEmail" @change="emitChange($event, 'anonSupportEmail')">
  </div>
</template>

<script>
export default {
  name: 'CreateAnonFormSettings',
  props: {
    anonTitle: String,
    anonBody: String,
    anonSupportEmail: String
  },
  methods: {
    emitChange(event, name) {
      this.$emit('settings-change', {name: name, value: event.target.value})
    }
  }
}
</script>


<style scoped lang="scss">

</style>
