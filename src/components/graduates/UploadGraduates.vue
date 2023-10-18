<template>
  <div class="upload-graduates" :style="style">
    <!-- <MultiStepForm :next_text="{1:'Preview'}" submit_text="Commit"  :n_steps="3" :step="step" aspect_ratio="1.3" :max_height="600" :step_titles="step_titles" @newStep="step=$event" :refs="refs"> -->
    <MultiStepForm :next_text="{1:'Preview'}" submit_text="Commit" @submit="commit($event)" :n_steps="3" :step="step" aspect_ratio="1.3" :max_height="550" :step_titles="step_titles" @newStep="step=$event" :refs="refs">
    <!-- <MultiStepForm :submit_text="submit_text"  :n_steps="2" :step="step" aspect_ratio=".9" :max_height="600" :step_titles="step_titles" @newStep="step=$event" :refs="refs"> -->
      <template #step>
        
        <MultiStepFormStep @defocus="defocus($event)" is_step="0" :step="step" ref="step_0" :refs="refs" :ref_names="['graduates_file']" >
          <div class="step">
            <h2>Graduates</h2>
            <p>Upload file containing graduates.</p>
            <FileUploadSingle :upload_progress="graduate_upload_progress" :mime_types="['text/csv']" :required="true" label="Graduates file" ref="graduates_file" name="graduates_file" :fileProcessingFunction="validateGraduates"/>
          </div>

        </MultiStepFormStep>


        <MultiStepFormStep @defocus="defocus($event)" is_step="1" :step="step" ref="step_1"  :refs="refs" :ref_names="['theses_file']" @next="preview($event)">
          <h2>Theses</h2>
          <p>Upload file containing theses</p>
          <FileUploadSingle :upload_progress="theses_upload_progress" :mime_types="['text/csv']" :required="true" label="Theses file" ref="theses_file" name="theses_file" :fileProcessingFunction="validateTheses"/>
        </MultiStepFormStep>

        <MultiStepFormStep @defocus="defocus($event)" is_step="2" :step="step" ref="step_2"  :refs="refs" :ref_names="['theses_file']">
          <h2>Commit to database</h2>
          <p>Upload preview of data to the database.</p>
          <div v-if="warning">
            <div>One or more warnings occured:</div>
            <div class="warning">{{warning}}</div>
          </div>
        </MultiStepFormStep>

      </template>
    </MultiStepForm>
  </div>

</template>

<script>
import MultiStepForm from '@/components/form/MultiStepForm.vue'
import MultiStepFormStep from '@/components/form/MultiStepFormStep.vue'
// import InputElement from '@/components/inputs/InputElement.vue'
import FileUploadSingle from '@/components/inputs_23/FileUploadSingle.vue'
// import JSONToTable from '@/components/JSONToTable2.vue'
import axios from "axios";
// import Button from '@/components/Button.vue'
// import Button from '@/components/inputs_23/Button.vue'
export default {
  name: 'UploadGraduates',
  components: {
    MultiStepForm,
    MultiStepFormStep,
    // InputElement,
    FileUploadSingle,
    // JSONToTable,
    // Button,
  },
  props: {
    warning: String,
  },
  data() {
    return  {
      step: 0,
      step_titles: ['Upload Graduates','Upload Theses','Commit'],
      refs: null,
      valid: true,
      csv_data_object: null,
      gratuates_columns: null,
      graduate_upload_progress: 0,
      theses_upload_progress: 0,
      graduates: null,
      these: null,
    }
  },
  async mounted() {
    this.refs = this.$refs
    this.gratuates_columns = await this.columns('graduates')
    this.theses_columns = await this.columns('graduate_theses')
    this.$emit('columns',{graduates:this.gratuates_columns,theses:this.theses_columns})

  },
  computed: {
    style() {
      console.log(this.$store.getters.getSideNavigationWidth)
      return {
        'width': `calc(100vw - 28px - ${this.$store.getters.getSideNavigationWidth}px)`
      }
    }
  },
  methods: {
    commit(event) {
      this.$emit("commit", event)
    },
    keysContainArray(array,object) {
      var valid = true
      array.forEach(entry=>{
        if(entry in object) {
          return
        }
        valid = false
      })
      return valid
    },
    async validateTheses() {
      const url = `${this.$store.getters.getApiUrl}/upload/table`
      const form_data = new FormData()
      form_data.append('file[]', this.$refs.theses_file.file)
      form_data.append('fileId[]', 'theses_file')
      this.refs.theses_file.awaiting_fileprocessing = true
      const {data,error} = await axios(
        {
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: event=>{this.theses_upload_progress = event.loaded/event.total}
        },
      ).catch(error=>{
        return {data:null,error}
      })
      if(error) {
        return
      }
      if(!this.keysContainArray(this.theses_columns,data?.[0])) {
        this.refs.theses_file.valid_file_processing = false
        this.refs.theses_file.file_processing_message = `Uploaded file has to include the following column(s): ${this.theses_columns.join(', ')}`
      } else {
        this.theses = data
        this.refs.theses_file.valid_file_processing = true
      }
      this.refs.theses_file.awaiting_fileprocessing = false
    },
    async validateGraduates() {
      const url = `${this.$store.getters.getApiUrl}/upload/table`
      const form_data = new FormData()
      form_data.append('file[]', this.$refs.graduates_file.file)
      form_data.append('fileId[]', 'graduates_file')
      this.refs.graduates_file.awaiting_fileprocessing = true
      const {data,error} = await axios(
        {
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          onUploadProgress: event=>{this.graduate_upload_progress = event.loaded/event.total}
        },
      ).catch(error=>{
        return {data:null,error}
      })
      if(error) {
        return
      }

      if(!this.keysContainArray(this.gratuates_columns,data?.[0])) {
        this.refs.graduates_file.valid_file_processing = false
        this.refs.graduates_file.file_processing_message = `Uploaded file has to include the following column(s): ${this.gratuates_columns.join(', ')}`
      } else {
        this.graduates = data
        this.refs.graduates_file.valid_file_processing = true
      }
      this.refs.graduates_file.awaiting_fileprocessing = false
    },
    defocus(ref_name) {
      this.$refs[`${ref_name}`].deFocusedOnce = true
    },
    async columns(table_name) {
      const url = `${this.$store.getters.getApiUrl}/upload/get_columns`
      const form_data = new FormData()
      form_data.append('table_name', table_name)
      const {data,error} = await axios(
        {
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
        },
      ).catch(error=>{
        return {data:null,error}
      })
      if(error) {
        return null
      }
      return data
    },

    async preview(event) {
      console.log(event)
      const url = `${this.$store.getters.getApiUrl}/graduate/get_by_matr`
      const form_data = new FormData()
      form_data.append('numbers', JSON.stringify(this.graduates.map(graduate=>graduate.MatrNr)))
      const {data,error} = await axios(
        {
          method: 'post',
          url: url,
          data: form_data,
          headers: {
            'Content-Type': 'multipart/form-data'
          },
          // onUploadProgress: 
        },
      ).catch(error=>{
        return {data:null,error}
      })
      console.log(data,error,error?.response)
      this.$emit('preview', {server_data:data, file_data:this.joinGraduatesAndTheses(this.graduates,this.theses,data)})
      event.button_submit_loading = false
    },
    joinGraduatesAndTheses(graduates,theses,data) {
      graduates.map(grad=>{
        grad['theses'] = []
        const server_match = data[grad.MatrNr]
        if(server_match) {
          grad.theses = server_match.theses.map(thesis=>{return {...thesis,server_match:2}})
        }
        const remove_theses_idx = []
        theses.forEach((thesis,idx)=>{
          if(thesis.MatNr==grad.MatrNr) {
            const index = grad.theses.map(server_thesis=>server_thesis.Nummer).indexOf(thesis.Nummer)
            if(index>=0) {
              grad.theses[index] = {...thesis,server_match:1}
            } else {
              grad.theses.push({...thesis,server_match:0})
            }
            remove_theses_idx.push(idx)
          }
        })
        theses = theses.filter((_,idx)=>{
          if(remove_theses_idx.includes(idx)) {
            return false
          }
          return true
        })
        return grad
      })
      return {graduates,theses}
    },
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.upload-graduates {
  display: flex;
  align-items: center;
  justify-content: center;
}
.step {
  height: 100%;
}
h2 {
  text-align: center;
}
p {
  margin: 4rem 0;
}
.warning {
  color: rgba(255, 23, 68,1);
}
</style>
