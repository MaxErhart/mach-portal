<template>
  <div class="graduates">
    <UploadGraduates @preview="preview($event)" @columns="columns=$event" :warning="getWarning()" @commit="commit($event)"/>

    <div class="preview" v-if="uploaded_data">
      <section>
        <Button @click.prevent="commit()" :loading="commit_loading" label="Commit to Database"/>
      </section>
      <JSONToTable :data="table_data" :columns="table_columns" :errors="errors"/>
      <!-- {{uploaded_data}} -->
    </div>

  </div>
</template>

<script>
import JSONToTable from '@/components/JSONToTable2.vue'
import UploadGraduates from '@/components/graduates/UploadGraduates.vue'
import Button from '@/components/inputs_23/Button.vue'
import axios from "axios";
export default {
  name: 'Graduates',
  components: {
    Button,
    JSONToTable,
    UploadGraduates,
  },
  data() {
    return {
      uploaded_data: null,
      columns: null,
      commit_loading: false,
      new_graduates_index: [],
    }
  },
  computed: {
    table_data() {
      const data = []
      this.uploaded_data?.file_data.graduates.forEach((entry)=>{
        const row = {graduate:true}
        this.table_columns.forEach(col=>{
          if(col.id.startsWith('grad_')) {
            row[col.id] = entry[col.id.slice(5)]
          } else {
            row[col.id] = "-"
          }
        })
        data.push(row)
        entry.theses.forEach(thesis=>{
          const sub_row = {graduate:false,server_match:thesis.server_match}
          this.table_columns.forEach(col=>{
            if(col.id.startsWith('theses_')) {
              sub_row[col.id] = thesis[col.id.slice(7)]
            } else {
              sub_row[col.id] = "-"
            }
          })
          data.push(sub_row)
        })

      })
      console.log(data)
      return data
    },
    table_columns() {
      const columns = []
      this.columns.graduates.forEach(column=>{
        columns.push({
          id: `grad_${column}`,
          name: column,
          show:true,
        })
      })
      this.columns.theses.forEach(column=>{
        if(columns.map(col=>col.name).includes(column)) {
          columns.push({
            id: `theses_${column}`,
            name: `theses_${column}`,
            show:true,
          })
          return    
        }
        columns.push({
          id: `theses_${column}`,
          name: column,
          show:true,
        })
      })
      return columns
    },
    errors() {
      const errors = []
      this.table_data.forEach(row=>{
        const error_row = []

        if(row.graduate) {
          const server_row = this.uploaded_data.server_data[row.grad_MatrNr]
          if(row.grad_MatrNr in this.uploaded_data.server_data) {
            this.new_graduates_index.push(false)
            this.table_columns.forEach(column=>{
              if(this.entryIsMatchingServer(column,server_row,row,5)) {
                error_row.push('null')
              } else {
                error_row.push(1)
              }
            })
          } else {
            this.new_graduates_index.push(true)
            this.table_columns.forEach((column)=>{
              if(row[column.id]==null||row[column.id]==undefined ||row[column.id]=="-") {
                error_row.push('null')
              } else {
                error_row.push(0)
              }
            })
          }
        } else {

          if(row.server_match===0) {
            this.table_columns.forEach((column)=>{
              if(row[column.id]==null||row[column.id]==undefined) {
              // if(row[column.id]==null||row[column.id]==undefined ||row[column.id]=="-") {
                error_row.push('null')
              } else {
                error_row.push(0)
              }
            })
          } else if(row.server_match===1) {
            const server_row = this.uploaded_data.server_data[row.theses_MatNr]
            const thesis = server_row.theses.find(thesis=>thesis.Nummer==row.theses_Nummer)
            this.table_columns.forEach((column)=>{
              if(this.entryIsMatchingServer(column,thesis,row,7)) {
                error_row.push('null')
              } else {
                error_row.push(1)
              }
            })
          } else {
            this.table_columns.forEach(()=>{
              error_row.push('null')
            })
          }
        }

        errors.push(error_row)
      })
      return errors
    },
  },
  methods: {
    getWarning() {
      if(this.uploaded_data?.file_data.theses.length==1) {
        return `Warning ${this.uploaded_data?.file_data.theses.length} thesis does not have a matching graduate and will not be included in upload`
      } else if(this.uploaded_data?.file_data.theses.length>1) {
        return `Warning ${this.uploaded_data?.file_data.theses.length} theses do not have a matching graduate and will not be included in upload`
      }
    },
    entryIsMatchingServer(column,server_row,row,slice) {
      if(row[column.id]=="-" || server_row[column.id.slice(slice)]==row[column.id] || (!row[column.id] && !server_row[column.id.slice(slice)])) {
        return true
      }
      return false
    },
    preview(event) {
      console.log(event)
      this.uploaded_data = event
    },
    async commit(event) {
      const form_data = new FormData()
      console.log(this.uploaded_data.file_data.graduates)
      form_data.append('graduates', JSON.stringify(this.uploaded_data.file_data.graduates))
      form_data.append('new_graduates_index', JSON.stringify(this.new_graduates_index))
      const url = `${this.$store.getters.getApiUrl}/graduate/commit`
      const {data,error} = await axios(
        {
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          // onUploadProgress: event=>this.handleFileUploadProgress(event, potentialUpload)
        },
      ).catch(error=>{
        return {data:null,error}
      })
      console.log(data,error,error?.response)
      event.button_submit_loading = false
    }
  }
}
</script>

<style lang="scss" scoped>

</style>