<template>
  <form id="form" v-if="form.metadata!==null">
      <div id="form-header">
        {{form.metadata.name}}
      </div>
      <div id="form-body">
        <section class="form-element" v-for="el in form.elements" :key="el">
          <component class="form-componen" :is="componentDic[el.tag]" v-bind="el">{{el.data.content}}</component>
        </section>
      </div>
      <div id="form-footer">
        <button @click.prevent="submitForm()">Senden</button>
      </div>
  </form>
</template>

<script>
import axios from "axios";
import FormInputElement from '../components/FormInputElement.vue'

export default {
  name: 'DisplayForm',
  components: {
    FormInputElement
  },
  data() {
    return {
      form : {metadata: null, elements: []},
      componentDic: {input: 'FormInputElement'},
      hasActiveInput: null,
    }
  },
  computed: {
    id: function() {
      return this.$route.params.id;
    }
  },
  watch: {
    id(newId, oldId) {
      if(this.$route.fullPath.split("/")[this.$route.fullPath.lenght-2] == 'form') {
        if(newId != oldId) {
          console.log(this.$route.fullPath.split("/"))
          this.getFormData(newId);
        }
      }
    }
  },
  mounted() {
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
      var data = {}
      var formData = new FormData(document.getElementById("form"))
      for(var pair of formData.entries()) {
        data[`${pair[0]}`] = pair[1]
      }      
      axios({
        method: 'post',
        url: 'https://www-3.mach.kit.edu/api/submitForm.php',
        data: {metadata: this.form.metadata, data: data},
      }).then(response => {
        console.log(response.data)
      })
    }
  }

}
</script>


<style scoped lang="scss">
  #form {
    background-color: white;
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
  }
  // #form-footer {
  // }
</style>