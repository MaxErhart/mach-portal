<template>
  <template v-if="form">
    <div class="submitting">
      <div class="submitting-header">
        <h1 class="form-title">{{form.name}}</h1>
      </div>
      <div class="submitting-body">
        <form class="form" @submit.prevent="update ? postUpdate($event) : submit($event)" ref="form">
          <section class="form-element-section" v-for="el in form.form_elements" :key="el.id">
            <component :key="compKey" :is="el.component" :presetValue="presetValue(el)" :id="el.id" :data="el.data" :name="String(el.id)" :formId="el.data.formId" :displayId="el.data.displayId"></component>
          </section>
          <section id="submit-form">
            <Button :loading="submitLoading" :disabled="submitDisabled" :text="update ? 'Update' : 'Submit'" />
          </section>        
        </form>
      </div>
      <div class="submitting-footer"></div>
    </div>
  </template>
</template>


<script>
import Button from '@/components/Button.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import MultiSelectElement from '@/components/inputs/MultiSelectElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import HeaderElement from '@/components/inputs/HeaderElement.vue'
import SectionElement from '@/components/inputs/SectionElement.vue'
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import SelectReferenceElement from '@/components/inputs/SelectReferenceElement.vue'
import axios from "axios";
export default {
  name: 'PublicForm',
  components: {
    Button,
    InputElement,
    MultiSelectElement,
    Checkbox,
    SelectElement,
    HeaderElement,
    SectionElement,
    FileUploadElement,
    SelectReferenceElement,
  },
  props: {
    submission: Object,
  },
  data() {
    return {
      form: null,
      compKey: 0,
      submitLoading: false,
      submitDisabled: false,
    }
  },
  computed: {
    update() {
      if(this.submission) {
        console.log(this.submission)
        return true
      } else {
        return false
      }
    },
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },    
  },
  mounted() {
    this.getForm(this.$route.params.id)
  },
  methods: {
    getForm(id) {
      this.form = null;
      this.awaitData = true;
      const url = `${this.apiUrl}/forms_nologin/${id}`
      axios({
        method: 'get',
        url: url,
        headers: {
          'Content-Type': 'application/json',
        }
      }).then(response=>{
        this.form = response.data
        this.awaitData = false;
        console.log(this.form)
      }).catch(error=>{
        // window.location.href = `https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=${this.encodeUrl(window.location.href)}`;
        if(error.response.status==401) {
          this.handle401()        
        }
        console.log(error.response)
        console.log(error)
      })
    },    
    presetValue(el) {
      if(this.submission==null){
        return null
      }
      const elements = this.submission.form_elements.filter(e=>e.id==el.id)
      if(elements.length==1) {
        return elements[0].pivot.data
      }
    },
    postUpdate() {
      const url = `${this.apiUrl}/submissions/${this.submission.id}`
      var formData = new FormData(this.$refs.form);   
      formData.append("form_id", this.$route.params.id) 
      for(var pair of formData.entries()) {
        console.log(pair)
      }
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        this.$emit("updated", response.data)
        console.log(response.data)
      }).catch(e=>{
        console.log(e.response)
      })      
    },
    submit() {
      const url = `${this.apiUrl}/submit_nologin`
      var formData = new FormData(this.$refs.form);   
      formData.append("form_id", this.$route.params.id)
      // for(var pair of formData.entries()) {
      //   console.log(pair[0]+ ', '+ pair[1]);
      // }       
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        console.log(response)
        if(response.status==200) {
          this.emitter.emit('showResponseMessage', {error: response})    
          this.compKey = this.compKey + 1
          window.scrollTo(0,0)
        }
      }).catch(error=>{
          console.log(error.response)
          this.emitter.emit('showResponseMessage', {error: error.response})
          window.scrollTo(0,0)
      })
    }
  },
}
</script>


<style scoped lang="scss">
h1 {
  border: none;
  padding: none;
  margin: 0px 10px;
}
.submitting-header {
  display: flex;
  align-items: center;
  justify-content: center;
}
.submitting {
  width: 100%;
  height: 100%;
  overflow-x: hidden;
}
.submitting-header {
}
.submitting-body {
  display: flex;
  flex-direction: column;
  align-items: center;
  padding: 10px;
}
.form {
  border: 1px solid rgba(0, 0, 0, 0.6);
  border-radius: 4px;
  padding: 20px;
  width: 100%;
  min-width: 280px;
  max-width: 210mm;
}
.submitting-footer {
  
}
</style>
