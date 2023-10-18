<template>
  <form class="upload-submissions" ref="form">
    <div class="section">
      <section>
        <h3>Upload File</h3>
      </section>
      <section>
        <p>
          Select file containing submissions.
          The file should be a semicolon(;) separated csv file containing the form input labels as column names.
          Furthermore owner_id and owner_type can be specified to asign the submission owner.
          A owner_type of 0 corresponds to users and a owner_type of 1 corresponds to groups.
          The owner_id can either be the primery db id of the owner or the group name (only in cases where owner_type is a group).
        </p>
        <FileUploadSingle label="Submission File"  :fileProcessingFunction="getFileData"/>
      </section>
      <section>
        <p>
          In cases where submissions are uniquely identifyed there is the option to update existing submissions.
          This is done by checking the box and selecting the column which contains the uniquely identifying information.
        </p>
        <Checkbox label="File has unique submission identifier" @change="has_unique=$event" :presetValue="has_unique" name="use_unique_identifyer"/>
        <SelectElement :data="form_input_labels" label="Select unique identifyer column" v-if="has_unique" name="unique_identifyer"/>
      </section>
    </div>
    <div class="section commit-section">
      <section>
        <Button label="Upload to database" @click.prevent="commit()"/>
      </section>
    </div>
    <div>
      <JSONToTable :data="preview" :columns="columnSort(columns)"/>
    </div>
    
  </form>
</template>

<script>
import FileUploadSingle from '@/components/inputs_23/FileUploadSingle.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Checkbox from '@/components/inputs_23/Checkbox.vue'
import Button from '@/components/inputs_23/Button.vue'
import JSONToTable from '@/components/JSONToTable2.vue'
import axios from 'axios'

export default {
  name: 'UploadSubmissions',
  props: {
    form: Object,
  },
  components: {
    FileUploadSingle,
    SelectElement,
    Checkbox,
    Button,
    JSONToTable,
  },
  data() {
    return {
      has_unique: false,
      preview: [],
      commit_loading: false,
    }
  },
  computed: {
    form_input_labels() {
      const inputs = []
      Object.keys(this.form.form_elements[this.form.id]).forEach(el_id=>{
        const el = this.form.form_elements[this.form.id][el_id]
        if(!el.input) {
          return
        }
        inputs.push({id: el.id, name: el.data.label})
      })
      return inputs
    },
    columns() {
      const columns = this.form_input_labels
      columns.push({id:'owner_type',name:'owner_type'})
      columns.push({id:'owner_id',name:'owner_id'})
      return columns
    }
  },
  mounted() {
    console.log(this.form)
  },
  methods: {
    async commit() {
      this.commit_loading = true
      const url = `${this.$store.getters.getApiUrl}/forms/upload/${this.form.id}`
      var formData = new FormData(this.$refs.form)
      formData.append('data', JSON.stringify(this.preview))
      const {data,error} = await axios({
        method: 'post',
        url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).catch(error=>{
        return {data:null,error}
      })
      console.log(data,error?.response)
      console.log('commit')
    },
    columnSort(columns) {
      return columns.sort((a,b)=>{
        if(a.id>b.id) {
          return 1
        }
        return -1
      })
    },
    async getFileData(file) {
      const reader = new FileReader()
      reader.addEventListener('load', event => {
        this.preview = this.convertCSVToArray(event.target.result)
        console.log(this.preview)
      })
      await reader.readAsText(file)
    },
    convertCSVToArray(text, separator=';') {
      const json = []
      const rows = text.split('\r\n')
      var n_columns = null
      var header = []
      rows.forEach(row=>{
        var row_entries = row.split(separator)
        if(n_columns===null) {
          n_columns = row_entries.length
          header = row_entries
          return
        } else if(row_entries.length!==n_columns) {
          return
        }
        const column_entry = {}

        row_entries = row_entries.forEach((entry,index)=>{
          const match = this.columns.find(column => column.name===header[index])
          if(!match) {
            column_entry[header[index]] = entry
          } else {
            column_entry[match.id] = entry
          }
        })
        json.push(column_entry)
      })
      return json
    }
  },
}
</script>

<style lang="scss" scoped>
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.upload-submissions {
  display: flex;
  flex-direction: column;
  gap: 30px;
}
.section {
  padding: 16px;
  border: 1px solid #cccccc;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  width: 210mm;
  gap: 8px;
}
section {
  display: flex;
  align-items: stretch;
  gap: 4px;
  width: 100%;
  flex-direction: column;
}
p,h3 {
  text-align: left;
}
</style>