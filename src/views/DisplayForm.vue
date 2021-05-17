<template>
  <form id="form" v-if="form.metadata!==null">
      <div id="form-header" >
        {{form.metadata.formName}}
      </div>
      <div id="form-body">
        
        <section class="form-element" v-for="el in form.elements" :key="el">
          <component class="form-componen" :is="componentDic[el.type]" v-bind="el"></component>
        </section>
      </div>
      <div id="form-footer" >
        <button class="kit-button" @click.prevent="submitForm()">Senden</button>        
      </div>
  </form>
  <div id="loading" v-else>Loading...</div>
</template>

<script>
import axios from "axios";
import FormInputElement from '../components/FormInputElement.vue'
import FormHeaderElement from '../components/FormHeaderElement.vue'
import FormSectionElement from '../components/FormSectionElement.vue'
import FormFileUploadElement from '../components/FormFileUploadElement.vue'
import FormSelectionElement from '../components/FormSelectionElement.vue'
export default {
  name: 'DisplayForm',
  components: {
    FormInputElement,
    FormHeaderElement,
    FormSectionElement,
    FormFileUploadElement,
    FormSelectionElement
  },
  data() {
    return {
      form : {metadata: null, elements: []},
      componentDic: {input: 'FormInputElement', header: 'FormHeaderElement', section: 'FormSectionElement', file: 'FormFileUploadElement', selection: 'FormSelectionElement'},
      hasActiveInput: null,
      id: null,
    }
  },
  beforeRouteUpdate(to, from, next) {
    this.id = to.params.id
    this.getFormData(this.id)
    next()
  },  
  watch: {
    id(newId, oldId) {
      if(this.$route.fullPath.split("/")[this.$route.fullPath.lenght-2] == 'form') {
        if(newId != oldId) {
          this.getFormData(newId);
        }
      }
    }
  },
  mounted() {
    this.id= this.$route.params.id
    this.getFormData(this.id);
  },
  methods: {
    getFormData(id) {
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/getForm.php',
        data: {id: id}
      }).then((response) => {
        console.log(response.data)
        if(response.data.success) {
          this.form = {metadata: null, elements: []}
          this.form.metadata = response.data.metadata
          response.data.elements.forEach(element => {
            const el = JSON.parse(element.data)
            el['id'] = element.elementId 
            el['type'] = element.type
            if(el.type == 'input'){
              this.hasActiveInput = true;
            }      
            this.form.elements.push(el)
          })
        } else {
          this.$router.push({name: 'Home'})
        }
      })
    },
    submitForm() {
      var formData = new FormData(document.getElementById("form"))
      formData.append('formId', this.form.metadata.formId)
      axios.post( 'https://www-3.mach.kit.edu/api/submitForm.php',
        formData,
        {
          headers: {
              'Content-Type': 'multipart/form-data'
          }
        }
      ).then((response) => {
          console.log(response.data)
        }
      )   
    }
  }

}
</script>


<style scoped lang="scss">
  #form {
    background-color: rgb(238, 238, 238);;
    width: 100%;
    max-width: 860px;
    padding: 20px 10px;
    display: flex;
    flex-direction: column;
  }
  #form-header {
    color: #2c3e50;
    font-size: 18px;
    border-bottom: 2px solid #2c3e50;
    padding: 5px 0;
  }
  #form-body {
    height: 100%;
    margin: 20px 0;
    padding: 5px;
  }
  #form-footer {
    padding: 5px;    
  }
  #loading {
    background-color: rgb(238, 238, 238);
    width: 100%;
    max-width: 860px;
    padding: 20px 10px;
  }
</style>