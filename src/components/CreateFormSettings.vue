<template>
  <div class="create-form-settings">
    <section class="form-name">
      <label for="form-name">Form Name</label>
      <input type="text" name="form-name" class="form-name-input" :value="formName" @change="emitChange($event, 'formName')">
    </section>
    <section class="deadline">
      <label for="deadline">Deadline</label>
      <input type="date" name="deadline" class="deadline-input" :value="deadline" @change="emitChange($event, 'deadline')">
    </section>
    <section class="multiple-submissions-section">
      <label for="multiple-submissions">Multiple Submissions per User</label>
      <input type="checkbox" id="multiple-submissions" :checked="multipleSubmissions" @change="emitChange($event, 'multipleSubmissions')">
    </section>           
  </div>
</template>

<script>

export default {
  name: 'CreateFormSettings',
  components: {
  },
  props: {
    formName: String,
    deadline: String,
    multipleSubmissions: Boolean,
  },
  data() {
    return {

    }
  },
  methods: {
    emitChange(event, name) {
      if(name == 'multipleSubmissions') {
        this.$emit('settings-change', {name: name, value: event.target.checked})
      } else {
        this.$emit('settings-change', {name: name, value: event.target.value})
      }
    },    
  }
}
</script>

<style scoped lang="scss" >
label {
  display: block;
  font-size: 16px;
  font-weight: 500;
  margin: 5px 0;
  text-align: left;
}
input {
  user-select: auto !important;
  display: block;
  height: 40px;
  width: 100%;
  font-size: 16px;
  border: 1px solid #ccc;
  padding: 15px;
  margin: 5px 0;
}
#multiple-submissions {
  width: 40px;
  height: 40px;
}
.create-form-settings {
  width: 544px;
  padding: 5px 10px;
  // border: 1px solid red;
}
</style>