<template>
  <div id="store-app">
    <div id="store-app-header">
      <template v-if="app">
        <h4 >Updating App: <router-link class="link-style" :to="`/apps/${app.id}`">{{app.name}}</router-link></h4>
      </template>
      <h4 v-else>App Registration</h4>
    </div>
    <div id="store-app-body">
      <form @submit.prevent="app ? updateForm(app.id) : submitForm()" id="app-form" ref="appForm">
        <section id="app-name">
          <InputElement :data="{label: 'Name', type: 'text', required: true}" name="name" ref="appName" :presetValue="app ? app.name : null" />
        </section>
        <section id="submit-form">
          <Button :loading="submitLoading" :disabled="submitDisabled" :text="app ? 'Update' : 'Submit'" />
        </section>
      </form>
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import Button from '@/components/Button.vue'
import axios from "axios";


export default {
  name: 'StoreApp',
  components: {
    InputElement,
    Button
  },
  props: {
    app: Object,
  },
  data() {
    return {
      submitLoading: false,
      submitDisabled: false,
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },    
  },
  methods: {
    validateInputs() {
      const el =  this.$refs.appName;
      if(el.hasError) {
        el.deFocusedOnce = true;
        return false;
      }
      return true;
    },    
    enableButton(time) {
      return new Promise(()=>{
        setTimeout(()=>{
          if(!this.submitLoading) {
            this.disableButton = false
          }
        }, time)
      })
    },
    updateForm(id) {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();
      var formData = new FormData(this.$refs.appForm);
      const data = {}
      for(var entry of formData.entries()) {
        console.log(entry)
        data[entry[0]] = entry[1];
      }
      console.log(data)
      axios({
        method: 'put',
        url: `${this.apiUrl}/apps/${id}`,
        data: data,
        headers: {
          'Content-Type': 'application/json'
        }
      }).then(response=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('editApp', {data: response.data, id: id});
        this.$router.push({name: 'Apps'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;        
        console.log(error)
      })
    },
    submitForm() {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();
      var formData = new FormData(this.$refs.appForm);
      axios({
        method: 'post',
        url: `${this.apiUrl}/apps`,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newApp', response.data);
        this.$router.push({name: 'Apps'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
        console.log(error.response.status)
        console.log(error.response.data)
        console.log(error.response.headers)
      })

    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#store-app {
  display: flex;
  flex-direction: column;
  width: 100%;
  align-items: center;
  padding: 10px;
}
#store-app-header {
  border-bottom: 2px solid $text_dark;
  width: 100%;
  max-width: 640px;
}
#store-app-body {
  width: 100%;
  max-width: 640px;
}
h4 {
  margin-top: 12px;
  margin-bottom: 12px;
  margin-left: 0;
  margin-right: 0;
  font-weight: bold;
}
</style>
