<template>
  <div id="store-tag">
    <div id="store-tag-header">
      <template v-if="tag">
        <h4 >Updating Tag: <router-link class="link-style" :to="`/tags/${tag.id}`">{{tag.name}}</router-link></h4>
      </template>
      <h4 v-else>Create Tag:</h4>
    </div>    
    <form id="store-tag-body" @submit.prevent="tag ? updateTag(tag.id) : storeTag()" ref="form">
      <div id="tag-settings">
        <section>
          <InputElement :data="name_input_data" name="name" ref="name" :presetValue="tag ? tag.name : null"/>
        </section>                  
      </div>
      <div class="submit-form">
        <Button :loading="submitLoading" :disabled="submitDisabled" :text="tag ? 'Update' : 'Submit'"/>
      </div>
    </form>
  </div>
</template>

<script>
import Button from '@/components/Button.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import axios from "axios";
export default {
  name: 'StoreTag',
  components: {
    InputElement,
    Button,
  },
  props: {
    tag: Object,
  },
  data() {
    return {
      name: '',
      multipleSubmissions: false,
      deadline: null,

      name_input_data: {label: "Name", type: "text", required: true},

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
    formatTags(tags) {
      return tags.map(t=>t.name)
    },
    validateInputs() {
      const el =  this.$refs.name;
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
    formatFormData(formData) {
      var formData_formatted = new FormData();
      var elements = {}
      var tags = []
      const regex = new RegExp(/^\d/)
      for(var pair of formData.entries()) {
        if(pair[0]=='name' || pair[0]=='deadline') {
          formData_formatted.append(pair[0], pair[1]);
        } else if(pair[0]=='multiple_submissions') {
          formData_formatted.append(pair[0], pair[1]=='true'?'1':'0');
        } else if(pair[0].startsWith('tag')) {
          tags.push(pair[1])
        } else if(regex.test(pair[0])) {
          const fieldname = pair[0].split('_')
          const id = fieldname[0]
          const name = fieldname[2]
          if(!(id in elements)) {
            elements[id] = {}
          }
          if(fieldname[fieldname.length-1]=='data') {
            if(!('data' in elements[id])) {
              elements[id]['data'] = {}
            }
            elements[id]['data'][name] = pair[1]
          } else {
            elements[id][name] = pair[1]
          }
        }
      }
      formData_formatted.append('elements', JSON.stringify(elements))
      formData_formatted.append('tags', JSON.stringify(tags))
      return formData_formatted
    },
    updateTag(id) {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();      
      var formData = new FormData(this.$refs.form);    
      formData = this.formatFormData(formData)
      const data = {}
      for(var entry of formData.entries()) {
        data[entry[0]] = entry[1];
      }
      axios({
        method: 'put',
        url: `${this.apiUrl}/tags/${id}`,
        data: data,
        headers: {
          'Content-Type': 'application/json'
        }
      }).then((response)=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newEntry', response.data);
        this.$router.push({name: 'Tags'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
        console.log(error.response.status)
        console.log(error.response.data)
        console.log(error.response.headers)
      })
    },
    storeTag() {
      this.submitDisabled = true;
      this.submitLoading = true;
      this.enableButton(400);
      this.validateInputs();      
      var formData = new FormData(this.$refs.form);    
      formData = this.formatFormData(formData)
      for(var pair of formData.entries()) {
        console.log(pair)
      }
      axios({
        method: 'post',
        url: `${this.apiUrl}/tags`,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then((response)=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.$emit('newEntry', response.data);
        this.$router.push({name: 'Tags'});        
      }).catch(error=>{
        this.submitLoading = false;
        this.submitDisabled = false;
        this.emitter.emit('showResponseMessage', {error: error.response})    
        console.log(error.response.status)
        console.log(error.response.data)
        console.log(error.response.headers)
      })
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
#store-tag{
  margin: 20px auto;
  display: flex;
  flex-direction: column;
  padding: 10px;
  align-items: center;
  border: 1px solid rgba(0, 0, 0, 0.6);
  border-radius: 4px;   
  width: 100%;
  min-width: 480px;
  max-width: 210mm;   
}
#store-tag-header {
  border-bottom: 2px solid $text_dark;
  width: 100%;
}
#store-tag-body {
 
  padding: 10px;
  width: 100%;
}
.submit-form {
  margin: 5px 0 0 0;
}
</style>
