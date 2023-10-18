<template>
  <div>
    <router-link class="go-back-link" v-if="editActive && $route.name==='UploadEdit'" :to="{ name: 'Upload' }">
      <ion-icon name="arrow-undo-outline"></ion-icon>
      Go back
    </router-link>
    <div class="edit-entry" v-if="editActive && $route.name==='UploadEdit'">
      <InputElement :presetValue="rowData[col.name]" v-for="col in columns" :key="col" :label="col.name" @valueChange="rowData[col.name]=$event"/>
      <Button :loading="false" text="Save changes" @buttonClick="saveChanges()"/>

    </div>
    <div class="upload" :class="{visible: !editActive || $route.name!=='UploadEdit'}">
      <div class="upload-content">
        <section class="step">
          <h2>Step 1</h2>
          <p class="step-description">
            Please select the table name to which you wish to upload new entries or update existing ones.
          </p>
          <div class="step-user-action">
            <SelectElement :required="true" label="Select table" :data="tables" @selectedEntry="setupTable($event)" />
          </div>
        </section>

        <section class="step">
          <h2>Step 2</h2>
          <p class="step-description">
            Please select the column that will be used as the unique identifier for rows in the table.
          </p>
          <div class="step-user-action">
            <SelectElement :required="true" :readonly="!tableName" label="Select identifying column" :data="columns" @selectedEntry="setIdentifier($event)" />
          </div>
        </section>

        <section class="step">
          <h2>Step 3</h2>
          <p class="step-description">
            Please upload files. Each file should have all values separated by a semicolon(;) and should have a .csv extension.
          </p>
          <div class="step-user-action">
            <FileUploadElement :disabled="!identifier" :tiny="false" :uploading="uploading" :uploadPercentage="uploadPercentage" name="files" ref="fileUpload" label="Upload table" @fileChange="upload($event)"/>
          </div>
        </section>

        <section class="step">
          <h2>Commit options</h2>
          <p>This section contains options and the button to commit the changes to the database that are previewed below.</p>
          <Checkbox label="Upload new entries" @inputChange="uploadNew=$event"/>
          <Checkbox label="Update existing entries" @inputChange="updateExisting=$event"/>
          <Button :disabled="fileData?.length<=0" :loading="false" text="Commit changes to database" @click.prevent="commit()"/>
        </section>

        <section  v-if="columns.length>0" class="step">

          <SelectElement label="Transform date" :data="columns" @selectedEntry="toValidDate($event)"/>
        </section>

        <section class="preview" v-if="columns.length>0">
          <h2>Preview of the previously selected table containing the data you uploaded in the previous step</h2>
          <p class="preview-description">
            <ul>
              <li>Yellow highlighted rows indicate rows that already exist in the database based on the unique identifying column selected</li>
              <li>Red cells indicating that the value in the uploaded file differs from the value in the database for that specific cell</li>
              <li>Individual rows can be edited by clicking on them</li>
            </ul>
          </p>
          <JSONToTable @rowClick="editRow($event)" :errors="error" :select="false" :columns="columns" :data="projectOntoExistingTable(fileData)"/>
        </section>
      </div>
    </div>
  </div>

</template>

<script>
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import JSONToTable from '@/components/JSONToTable2.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import Button from '@/components/Button.vue'
import axios from "axios";
export default {
  name: 'Upload',
  components: {
    InputElement,
    FileUploadElement,
    JSONToTable,
    SelectElement,
    Checkbox,
    Button,
  },
  props: {
    user: Object,
  },
  data() {
    return {
      editActive: false,
      rowData: null,

      uploadNew: false,
      updateExisting: false,
      uploading: false,
      uploaded: [],
      uploadPercentage: 0,
      fileData: [],
      tables: [],
      tableData: [],
      identifier: null,
      tableName: null,
      dbData: [],
      columns: [],
    } 
  },
  mounted() {
    if(this.$route.name==='UploadEdit' && !this.rowData) {
      this.$router.push({name:'Upload'})
    }
    this.getTables()
  },
  computed: {
    error() {
      var error = []
      this.fileData.forEach(row=>{
        var errorRow = {}
        const match = this.dbData.find(r=>r[this.identifier]==row[this.identifier] || JSON.stringify(r[this.identifier])==row[this.identifier])
        if(match) {
          this.columns.forEach(col=>{
            if(match[col.name]===row[col.name] || JSON.stringify(match[col.name])===row[col.name] || (match[col.name]===null && row[col.name]==='')) {
              errorRow[col.name] = 2
            } else {
              errorRow[col.name] = 1
            }
          })
        } else {
          this.columns.forEach(col=>{
            errorRow[col.name] = 0
          })
        } 
        error.push(errorRow)
      })
      return error
    },
  },
  methods: {
    toValidDate(column) {
      var us = new RegExp("\\b(0?[1-9]|1[012])([\\/\\-])(0?[1-9]|[12]\\d|3[01])\\2(\\d{4})");
      var eu = new RegExp("^(0[1-9]|[12][0-9]|3[01])[\\.\\/](0[1-9]|1[012])[\\.\\/](19|20)\\d{2}$");
      this.fileData = this.fileData.map(row=>{
        if(us.test(row[column.name])) {
          const parts = row[column.name].split('/')
          const newString = `${parts[2]}-${parts[0]}-${parts[1]}`
          row[column.name] = newString
        } else if(eu.test(row[column.name])) {
          const parts = row[column.name].split('.')
          const newString = `${parts[2]}-${parts[1]}-${parts[0]}`
          row[column.name] = newString
        }
        return row
      })
    },
    saveChanges() {
      this.fileData[this.rowIndex] = this.rowData
      this.$router.push({name:'Upload'})
    },
    editRow(row) {
      const index = this.fileData.findIndex(entry=>entry.fileId==row.fileId && entry.fileEntryId==row.fileEntryId)
      if(index===undefined || index===null) {
        return
      }
      this.editActive = true
      this.rowData = JSON.parse(JSON.stringify(this.fileData[index]))
      this.rowIndex = index
      window.scrollTo({ top: 0, behavior: 'smooth' });
      this.$router.push({
        name: 'UploadEdit',
      })
    },
    commit() {
      const url = `${this.$store.getters.getApiUrl}/upload/commit`
      var formData = new FormData()
      formData.append('table_name', this.tableName)
      formData.append('table_identifier', this.identifier)
      formData.append('uploadNew', this.uploadNew)
      formData.append('updateExisting', this.updateExisting)
      formData.append('fileData', JSON.stringify(this.fileData))
      formData.append('existing', JSON.stringify(this.dbData))
      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        console.log(response.data)
        this.dbData = response.data
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
    getTableData() {
      if(this.fileData.length<=0) {
        return
      }
      const url = `${this.$store.getters.getApiUrl}/upload/get_rows`
      var formData = new FormData()
      formData.append('table_name', this.tableName)
      formData.append('table_identifier', this.identifier)
      formData.append('fileData', JSON.stringify(this.fileData))

      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        this.dbData = response.data
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
    setIdentifier(identifier) {
      this.identifier = identifier.name
    },
    toSelectData(data) {
      var options = []
      data.forEach((entry,index)=>{
        options.push({
          id: index, name: entry
        })
      })
      return options
    },
    projectOntoExistingTable(data) {
      if(!data || data.length<=0) {
        const obj = {};
        for (let i = 0; i < this.columns.length; i++) {
          obj[this.columns[i].name] = '';
        }
        return [obj];
      }
      const res = []
      data.forEach(row=>{
        const obj = {fileEntryId: row.fileEntryId, fileId:row.fileId}
        this.columns.forEach(col=>{
          
          obj[col.id] = row[col.name]
        })
        res.push(obj)
      })
      return res
    },
    setupTable(tableName) {
      this.tableName = tableName.name
      const url = `${this.$store.getters.getApiUrl}/upload/get_columns`
      var formData = new FormData()
      formData.append('table_name', tableName.name)

      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        console.log(response.data)
        this.columns = this.toSelectData(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
    getTables() {
      const url = `${this.$store.getters.getApiUrl}/upload/get_tables`
      axios({
        method: 'get',
        url: url
      }).then(response=>{
        this.tables = this.toSelectData(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
    handleError(error, action) {
      this.emitter.emit('showErrorMessage', {error: error.response, action: action, redirect: null})
    },
    handleFileUploadProgress(event, files) {
      this.uploadKeys = files.map(f=>f.id)
      this.uploadPercentage = event.loaded / event.total * 100
    },
    upload(files) {
      this.uploading = true
      const url = `${this.$store.getters.getApiUrl}/upload/table`
      var formData = new FormData()
      var potentialUpload = []
      files.forEach((file)=>{
        if(this.uploaded.map(f=>f.id).includes(file.id)) {
          return
        }
        formData.append('file[]', file.file)
        formData.append('fileId[]', file.id)
        potentialUpload.push({
          file: file.file,
          id: file.id,
        })
      })
      const potentialRemove = this.uploaded.filter(file=>!files.map(f=>f.id).includes(file.id))
      axios(
        {
          method: 'post',
          url: url,
          data: formData,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: event=>this.handleFileUploadProgress(event, potentialUpload)
        },
      ).then(response=>{
        console.log(response.data)
        this.uploading = false
        this.uploaded = this.uploaded.concat(potentialUpload)
        this.fileData=this.fileData.concat(response.data)
        potentialRemove.forEach(file=>{
          this.uploaded = this.uploaded.filter(uploadedFile=>uploadedFile.id!==file.id)
          this.fileData = this.fileData.filter(row=>row.fileId!==file.id)
        })
        this.getTableData()
      }).catch(error=>{
        this.uploading = false
        this.$refs.fileUpload.removeFiles(potentialUpload)
        this.handleError(error, 'Upload file')
      })

      
    },
  },
}
</script>

<style lang="scss" scoped>
.edit-entry {
  padding: 20px;
  background: white;
  width: 100%;
  max-width: 210mm;
}
ul {
  padding: 0 0 0 20px;
  li {
    margin: 10px 0;
  }
}
.upload {
  color: #2c3e50;
  flex-direction: column;
  align-items: center;
  display: none;
  &.visible{
    display: flex;
  }
}
.upload-content {
  width: 100%;
  text-align: center;
  display: flex;
  flex-direction: column;
  gap: 10px;
}
section {
  * {
    text-align: left;
  }
}
.step {
  max-width: 210mm;
  padding: 20px;
  border: 1px solid #ccc;
}
.go-back-link {
  * {
    font-size: 24px;
  }
  text-decoration: none;
  display: flex;
  width: 126px;
  gap: 6px;
  flex-direction: row;
  align-items: center;
  font-size: 24px;
  color: #00876c;
  &:visited {
    color: #00876c;
  }
}
</style>