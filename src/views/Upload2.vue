<template>
  <div class="upload">

    <section class="step" :class="{valid: stepIsValid(0)}">
      <h2>Step 1</h2>
      <p>
        Please select the table name to which you wish to upload new entries or update existing ones.
      </p>
      <div class="step-user-action">
        <SelectElement :required="true" label="Select table" :data="db_tables" @selectedEntry="handleTableSelect($event)" />
      </div>
    </section>

    <section class="step" :class="{valid: stepIsValid(1)}">
      <h2>Step 2</h2>
      <p class="step-description">
        Check to use a table column as an unqiue identifier of individual rows.
      </p>
      <div class="step-user-action">
        <Checkbox label="Use unqiue identifier" :readonly="!stepIsValid(1)" @change="use_unique_identifier=$event"/>
      </div>
      <p v-if="use_unique_identifier">
        Select the specific column that serves as the unqiue identifier.
      </p>
      <div class="step-user-action" v-if="use_unique_identifier">
        <SelectElement :required="true" :readonly="!stepIsValid(1)" label="Select identifying column" :data="db_columns" @selectedEntry="setIdentifyingColumn($event)" />
      </div>
    </section>


    <section class="step" :class="{valid: stepIsValid(2)}">
      <h2>Step 3</h2>
      <p>
        Please upload file.
        The file should have all values separated by a semicolon(;) and should have a .csv extension.
      </p>
      <div class="step-user-action">
        <FileUploadSingle @upload="upload($event)" :readonly="!stepIsValid(2)" label="Upload table" :required="true"/>
      </div>
    </section>

    <section class="step" :class="{valid: stepIsValid(3)}">
      <h3>Commit to database</h3>
      <Checkbox :readonly="!stepIsValid(3)" label="Upload new entries" @change="upload_new=$event" v-if="use_unique_identifier"/>
      <Checkbox :readonly="!stepIsValid(3)" label="Update existing entries" @change="update_existing=$event" v-if="use_unique_identifier"/>
      <Button :disabled="!stepIsValid(3)" :loading="false" label="Commit changes" @click.prevent="commit()"/>
    </section>

    <section class="preview">
      <JSONToTable :select="false" :errors="errors" :columns="db_columns" :data="file_data"/>
    </section>

  </div>
</template>

<script>
import axios from "axios";
import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs_23/Checkbox.vue'
import FileUploadSingle from '@/components/inputs_23/FileUploadSingle.vue'
import Button from '@/components/inputs_23/Button.vue'
import JSONToTable from '@/components/JSONToTable2.vue'
export default {
  name: 'Upload',
  components: {
    SelectElement,
    Checkbox,
    FileUploadSingle,
    Button,
    JSONToTable,
  },
  data() {
    return {
      db_tables: [],
      db_columns: [],
      db_data: [],
      table: null,
      use_unique_identifier: false,
      identifying_column: null,
      upload_new: false,
      update_existing: false,

      file_data: [],
    }
  },
  mounted() {
    this.getDBTables()
  },
  computed: {
    errors() {
      if(!this.use_unique_identifier || this.db_data.length<=0) {
        return []
      }
      const ids = this.db_data.map(row=>row[this.identifying_column.name])
      const errors = []
      this.file_data.forEach(row=>{
        const matching_db_row_index = ids.indexOf(row[this.identifying_column.name])
        if(matching_db_row_index<0) {
          errors.push(Array(this.db_columns.length).fill(0))
          return
        }
        const matching_db_row = this.db_data[matching_db_row_index]
        const errors_row = []
        this.db_columns.forEach(column=>{
          if(column.name in row && row[column.name]!==matching_db_row[column.name]) {
            errors_row.push(2)
            return
          }
          errors_row.push(null)
        })
        errors.push(errors_row)
      })
      return errors
    },
  },
  methods: {
    commit() {
      console.log('commit')
      const url = `${this.$store.getters.getApiUrl}/upload/commit`
      var formData = new FormData()
      formData.append('db_columns', JSON.stringify(this.db_columns))
      formData.append('table_name', this.table.name)
      formData.append('use_unique_identifier', Number(this.use_unique_identifier))
      formData.append('table_identifier', this.identifying_column?.name)
      formData.append('upload_new', Number(this.upload_new))
      formData.append('update_existing', Number(this.update_existing))
      formData.append('file_data', JSON.stringify(this.file_data))
      formData.append('existing', JSON.stringify(this.db_data))
      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        console.log(response.data)
        this.dbData = response.data
      }).catch(error=>{
        console.log(error.response)
      })
    },
    stepIsValid(step) {
      if(step===0) {
        return true
      }
      if(step===1 && this.table) {
        return true
      }
      if(step===2 && this.stepIsValid(1) && (!this.use_unique_identifier || (this.use_unique_identifier && this.identifying_column))) {
        return true
      }
      if(step===3 && this.stepIsValid(2)) {
        return true
      }
    },
    getDBTables() {
      const url = `${this.$store.getters.getApiUrl}/upload/get_tables`
      axios({
        method: 'get',
        url: url
      }).then(response=>{
        this.db_tables = response.data.map((table_name,index) => {
          return {id:index,name:table_name}
        })
      }).catch(error=>{
        console.log(error.response)
      })
    },
    handleTableSelect(table) {
      this.table = table
      this.getDBTableColumns(table.name)
    },
    getDBTableColumns(table_name) {
      const url = `${this.$store.getters.getApiUrl}/upload/get_columns`
      var formData = new FormData()
      formData.append('table_name', table_name)
      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        this.db_columns = response.data.map((column_name) => {
          return {id:column_name,name:column_name}
        })
      }).catch(error=>{
        console.log(error.response)
      })
    },
    setIdentifyingColumn(column) {
      this.identifying_column = column
      console.log(column)
    },
    upload(file) {
      console.log(file)
      this.uploading = true
      const url = `${this.$store.getters.getApiUrl}/upload/table`
      var formData = new FormData()
      formData.append('file[]', file)
      axios(
        {
          method: 'post',
          url: url,
          data: formData,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
        },
      ).then(response=>{
        this.uploading = false
        this.file_data=response.data
        this.removeDBColumnsNotFoundInFile(this.file_data[0])
        console.log(this.file_data)
        if(this.use_unique_identifier) {
          this.getTableDBData()
        }
      }).catch(error=>{
        this.uploading = false
        console.log(error?.response)
      })
    },
    removeDBColumnsNotFoundInFile(data_row) {
      this.db_columns = this.db_columns.filter(column=>{
        return column.name in data_row
      })
    },
    getTableDBData() {
      const url = `${this.$store.getters.getApiUrl}/upload/get_rows`
      var formData = new FormData()
      formData.append('table_name', this.table.name)
      formData.append('table_identifier', this.identifying_column.name)
      formData.append('file_data', JSON.stringify(this.file_data))
      axios({
        method: 'post',
        url: url,
        data: formData,
      }).then(response=>{
        this.db_data = response.data
        console.log(response.data)
      }).catch(error=>{
        console.log(error.response)
      })
    },
  },
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.upload {
  display: flex;
  flex-direction: column;
  gap: 20px;
}
.step {
  width: 210mm;
  border: 1px solid #ccc;
  padding: 20px;
  &.valid {
    border-color: $kit_green;
  }
}
</style>