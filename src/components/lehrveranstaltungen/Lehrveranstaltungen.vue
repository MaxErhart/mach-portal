<template>
  <div class="lv">
    LvLehrveranstaltungen
    <form ref="form_file" @submit.prevent="create()">
      <FileUploadElement :data="fileUpload_data" name="file" @fileChange="upload($event)"/>
      <Button :loading="buttonElementData_upload.loading" :disabled="buttonElementData_upload.disabled" :text="buttonElementData_upload.text" />
    </form>
    <JSONToTable :data="fileData" :rowMenu="rowMenu" @itemClicked="test($event)" @menuItemClicked="test($event)" :vetoColumn="['Ergebnis']"/>
  </div>
</template>

<script>
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import Button from '@/components/Button.vue'
import axios from "axios";
import JSONToTable from '@/components/JSONToTable.vue'
export default {
  name: 'LvLehrveranstaltungen',
  components: {
    FileUploadElement,
    Button,
    JSONToTable,
  },
  data() {
    return {
      rowMenu: [
        {icon: 'cogwheel', text: 'Edit Form', widht: 24, height: 24, name: 'editForm'},
        {icon: 'cogwheel', text: 'Edit Form', widht: 24, height: 24, name: 'editForm'},
      ],
      fileUpload_data: {label: "Upload File", required: true, files: null},
      buttonElementData_upload: {loading: false, disabled: false, text: "Upload"},
      fileData: null,
    }
  },
  computed: {
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
  },
  methods: {
    test(e) {
      console.log(e)
    },
    upload(event) {
      this.fileUpload_data.files=event      
      const url = `${this.apiUrl}/lv/upload`
      var formData = new FormData(this.$refs.form_file)
      for(var i=0; i<this.fileUpload_data.files.length; i++) {
        formData.append('file[]', this.fileUpload_data.files[i])
      }
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.fileData=response.data
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })      
    },
  },
}
</script>


<style scoped lang="scss">
.lv {
  margin: 10px;
}
</style>
