
<template>
  <div class="upload-submissions" ref="uploadSubmissions">
    <form class="form-file">
      <template v-if="!loading">
        <FileUploadElement :tiny="false" :uploading="uploading" :uploadKeys="uploadKeys" :uploadError="uploadError" :uploadPercentage="uploadPercentage" name="files" ref="fileUpload" label="Upload Submissions" @fileChange="upload($event)"/>
      </template>
      <template v-else>
        <DataPlaceholder/>
      </template>
    </form>
    <section class="table">
      <div>Columns of the file should match the input fields of the selected form.</div>
      <div>The file should be in a semicolon separated format with a .csv ending and a UTF-8 encoding (not UTF-8-BOM).</div>
      <div>The first line defines the column names.</div>
      <div>Preview of data:</div>
      <JSONToTable @menuClick="handleMenu($event)" :error="errorMap(data)" :data="format(data)" :list="columnList" :rowMenuOptions="rowMenu" :key="table_key"/>
    </section>
  </div>
  <div class="edit-popup" :style="editPopupStyle" v-if="editRow && form && selectedRow">
    <h3>Edit Row</h3> 
    <h4>Form input fields:</h4> 
    <IconButton class="close-button" icon="closeX" :width="16" :height="16" @buttonClicked="closeEdit()"/>
    <SelectElement @selectedEntry="selectType($event)" label="Select Owner Type" :search="false" :data="ownerTypes" :presetValue="selectedRow['owner_type']" />
    <SelectElement @selectedEntry="selectOwner($event)" label="Select Owner" :search="true" :data="owners(ownerType)" :cast="ownerCast(ownerType)" :presetValue="parseInt(selectedRow['owner_id'])" />
    <component v-on="parseEventHandlers(element)" :presetValue="parse(selectedRow[element.data.label], element)" :submissions="references[element.id]?.submissions" v-bind="element.data" :is="element.component" v-for="element in form.form_elements" :key="element" />

    <Button text="Save" @buttonClick="saveRowChanges()"/>
  </div>
  <form ref="form" @submit.prevent="submit(form)">
    <input type="hidden" name="submissions" :value="JSON.stringify(data)">
    <Button ref="submit" :loading="submitLoading" :disabled="submitLoading" text="Upload"/>
  </form>
  <div class="edit-popup-overlay" @click="closeEdit()" v-if="editRow && form && selectedRow"></div>
</template>

<script>
import moment from 'moment'
import Checkbox from '@/components/inputs/Checkbox.vue'
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import SelectReferenceElement from '@/components/inputs/SelectReferenceElement.vue'
import InputElement from '@/components/inputs/InputElement.vue'
import JSONToTable from '@/components/JSONToTable.vue'
import IconButton from '@/components/IconButton.vue'
import Button from '@/components/Button.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import axios from "axios";
export default {
  name: 'UploadSubmissions',
  components: {
    FileUploadElement,
    DataPlaceholder,
    Checkbox,
    SelectReferenceElement,
    JSONToTable,
    Button,
    InputElement,
    IconButton,
    SelectElement,
  },
  props: {
    form: Object,
  },
  data() {
    return {
      uploaded: [],
      columnList: ["fileId", "fileEntryId"],
      ownerTypes: [
        {id: 0, name: 'User', db: 'App\\Models\\User'},
        {id: 1, name: 'Group', db: 'App\\Models\\Group'},
      ],
      editRow: true,
      submitLoading: false,
      newVals: null,
      selectedRow: null,
      ownerType: null,
      users: null,
      editPopupTop: 0,
      groups: null,
      uploadError: {},
      uploadPercentage: 0,
      uploading: false,
      uploadKeys: [],
      usersRefresh: null,
      selectedRowIndex: null,
      rowMenu: [
        {icon: 'create-outline', text: 'Edit Row', name: 'editRow', permission: null},
        {icon: 'trash-outline', text: 'Delete Row', name: 'deleteRow', permission: null},
      ],
      fileData: [],
      inputElements: ['Checkbox', 'InputElement', 'SelectElement', 'SelectReferenceElement'],
      data: [],
      old_submissions: null,
      references: null,
      get_submissions_loading: false,
      get_users_loading: false,
      get_groups_loading: false,
      table_key:0,
    }
  },
  computed: {
    loading() {
      if(this.get_submissions_loading||this.get_users_loading||this.get_groups_loading) {
        return true
      }
      return false
    },
    editPopupStyle() {
      console.log(window.scrollY)
      return {
        // 'top': this.editPopupTop+'px',
      }
    },
    submissions() {
      return this.fileData
    },
    formInputs() {
      if(!this.form){
        return []
      }
      const inputs = []
      Object.keys(this.form.form_elements[this.form.id]).forEach(el_id=>{
        if(this.form.form_elements[this.form.id][el_id].input && this.form.form_elements[this.form.id][el_id].component!='FileUploadElement') {
          return
        }
        inputs.push(this.form.form_elements[this.form.id][el_id])
      })
      return inputs
      // return this.form.form_elements.filter(el=>el.input && el.component!='FileUploadElement')
    },
  },
  mounted() {
    this.getUsers()
    this.getGroups()
    this.getSubmissions()
    this.preprocessData(this.fileData)

  },
  watch: {
    form() {
      this.getSubmissions()
    },
    fileData: {
      deep: true,
      handler(to) {
        this.preprocessData(to)
      }
    },
  },
  methods: {
    ownerCast(ownerType) {
      if(ownerType===null) {
        return (owner)=>{return {id: owner.id, name: owner.name}}
      } else if(ownerType==0) {
        return this.userCast
      } else if(ownerType==1) {
        return (owner)=>{return {id: owner.id, name: owner.name}}
      }
      return (owner)=>{return {id: owner.id, name: owner.name}}   
    },
    owners(ownerType) {
      if(ownerType===null) {
        return []
      } else if(ownerType==0) {
        return this.users
      } else if(ownerType==1) {
        return this.groups
      }
      return []
    },
    parse(value, element) {
      if(element.component=="SelectElement") {
        var optionMatch = element.data.data.find(el=>el.id==parseInt(value))
        if(optionMatch) {
          return optionMatch.id
        }
        optionMatch = element.data.data.find(el=>el.name==value)
        if(optionMatch) {
          return optionMatch.id
        }
        return null
      } else if(element.component=="SelectReferenceElement") {
        console.log(this.references)
        var submissionMatch = this.references[element.id].submissions.find(submission=>submission.id==parseInt(value))
        if(submissionMatch) {
          return submissionMatch.id
        }
        submissionMatch = this.references[element.id].submissions.find(submission=>{
          var strRepr = []
          element.data.formElementIds.forEach(id=>{
            strRepr.push(submission._data[id])
          })
          if(strRepr.join(' ')===value) {
            return true
          }
          return false
        })
        if(submissionMatch) {
          return submissionMatch.id
        }
        return null
      } else if(element.component=="Checkbox") {
        return this.parseBoolean(value)
      }
      return value
    },
    preprocessData(data) {
      if(!this.form || !this.users || !this.groups) {
        return
      }
      if(!data || data.length<1) {
        const emptyRow = {
          owner_type: null,
          owner_id: null,
          fileId: null,
          fileEntryId: null,
        }
        this.formInputs.forEach(el=>{
          emptyRow[el.data.label] = null
        })

        this.data = [emptyRow]
        return
      }
      const keep = this.data.filter(row=>data.map(newRow=>newRow.fileId).includes(row.fileId))
      const add = data.filter(newRow=>!this.data.map(row=>row.fileId).includes(newRow.fileId))
      this.data = keep
      this.data = this.data.concat(add.map(entry=>{
        const ownerTypeById = this.ownerTypes.find(type=>type.id==entry['owner_type'])
        const ownerTypeByName = this.ownerTypes.find(type=>type.name.match(new RegExp(entry['owner_type'], "i")))
        const ownerTypeByDb = this.ownerTypes.find(type=>type.db == entry['owner_type'])
        const owner_type = ownerTypeById?ownerTypeById.id:ownerTypeByName?ownerTypeByName.id:ownerTypeByDb?ownerTypeByDb.id:null
        var owner_id = null
        console.log(entry)
        if(typeof entry['owner']==='number') {
          owner_id=entry['owner']
        } else if(!isNaN(parseInt(entry['owner']))) {
          owner_id=parseInt(entry['owner'])
        } else if(owner_type===0){
          var user = this.users.find(u=>u.firstname==entry['owner'])
          if(user===null || user===undefined) {
            user = this.users.find(u=>u.lastname==entry['owner'])
          } 
          if(user===null || user===undefined) {
            user = this.users.find(u=>`${u.firstname} ${u.lastname}`==entry['owner'])
          }
          if(user!==null && user!==undefined) {
            owner_id = user.id
          } 
        } else if(owner_type===1) {
          var group = this.groups.find(g=>g.name==entry['owner'])
          if(group!==null && group!==undefined) {
            owner_id = group.id
          }   
        }
        const retEntry = {
          owner_type: owner_type,
          owner_id: owner_id,
          fileId: entry['fileId'],
          fileEntryId: entry['fileEntryId'],
        }
        Object.keys(this.form.form_elements[this.form.id]).filter(el_id=>this.form.form_elements[this.form.id][el_id].input && this.form.form_elements[this.form.id][el_id].component!=='FileUploadElement').forEach(el_id=>{
          if(this.form.form_elements[this.form.id][el_id].data.label in entry) {
            retEntry[this.form.form_elements[this.form.id][el_id].data.label] = this.parse(entry[this.form.form_elements[this.form.id][el_id].data.label], this.form.form_elements[this.form.id][el_id])
            return
          }
          retEntry[this.form.form_elements[this.form.id][el_id].data.label] = null
        })
        return retEntry

      }))
    },
    parseEventHandlers(element) {
      if(element.component=="SelectElement" || element.component=="SelectReferenceElement") {
        return {selectedEntry: ($event)=>this.handleSelect($event, element)}
      } else if(element.component=="InputElement") {
        return {valueChange: ($event)=>this.handleValueChange($event, element)}
      } else if(element.component=="Checkbox") {
        return {inputChange: ($event)=>this.handleValueChange($event, element)}
      }
      return {}
    },
    handleSelect(data, element) {
      this.newRow[element.data.label] = data.id
    },
    handleValueChange(data, element) {
      this.newRow[element.data.label] = data
    },
    parseBoolean(string) {
      if(typeof string!=='string') {
        return string
      }
      switch(string?.toLowerCase()?.trim()){
          case "true": 
          case "yes": 
          case "1": 
            return true;

          case "false": 
          case "no": 
          case "0": 
          case null: 
          case undefined:
            return false;

          default: 
            return string;
      }
    },
    handleMenu(event) {
      const index = this.fileData.findIndex(row=>row.fileId==event.row.fileId && row.fileEntryId==event.row.fileEntryId)
      if(event.option.name=="deleteRow") {
        this.fileData.splice(index, 1)
      } else if(event.option.name=="editRow") {
        this.editRow = true
        this.selectedRow = this.data[index]
        this.ownerType = this.selectedRow.owner_type
        this.selectedRowIndex = index
        this.editPopupTop = - this.$refs.uploadSubmissions.getBoundingClientRect().y + 40
        this.newRow = JSON.parse(JSON.stringify(this.selectedRow))
      }
    },
    selectOwner(owner) {
      this.newRow.owner_id = owner.id
    },
    selectType(type) {
      this.newRow.owner_type = type.id
      this.ownerType=type.id
    },
    errorMap(data) {
      if(!this.fileData || this.fileData.length<1) {
        return null
      }
      var map = []
      data.forEach(row=>{
        var mapRow = []
        if(row['owner_type']===null) {
          mapRow['owner_type'] = 1
          mapRow['owner_id'] = 1
        } else if(row['owner_type']===0 && !this.users.map(u=>u.id).includes(parseInt(row.owner_id))) {
          mapRow['owner_type'] = 0
          mapRow['owner_id'] = 1
        } else if(row['owner_type']===1 && !this.groups.map(g=>g.id).includes(parseInt(row.owner_id))) {
          mapRow['owner_type'] = 0
          mapRow['owner_id'] = 1
        } else {
          mapRow['owner_type'] = 0
          mapRow['owner_id'] = 0
        }
        this.formInputs.forEach(input=>{
          if(row[input.data.label]==='' || row[input.data.label]===undefined || row[input.data.label]===null) {
            mapRow[input.data.label] = 2
          } else if(input.component=="InputElement") {
            if(input.data.type=="number" && typeof row[input.data.label]!="number") {
              const number = parseInt(row[input.data.label])
              if(isNaN(number)) {
                mapRow[input.data.label] = 1
              } else {
                mapRow[input.data.label] = 0
              }
            } else {
              mapRow[input.data.label] = 0
            }
          } else if(input.component=="SelectElement") {
            if(!input.data.data.map(el=>el.id).includes(parseInt(row[input.data.label]))) {
              mapRow[input.data.label] = 1
            } else {
              mapRow[input.data.label] = 0
            }
          } else if(input.component=="Checkbox") {
            if(row[input.data.label]===false ||  row[input.data.label]===true) {
              mapRow[input.data.label] = 0
            } else {
              mapRow[input.data.label] = 1
            }
          } else if(input.component=="SelectReferenceElement") {

            

            if(!this.references[input.id].submissions.map(el=>el.id).includes(parseInt(row[input.data.label]))) {
              mapRow[input.data.label] = 1
            } else {
              mapRow[input.data.label] = 0
            }
          } else {
            mapRow[input.data.label] = 0
          }
        })
        map.push(mapRow)
      })
      return map
    },
    parseDate(dateString) {
      const formats = ['YYYY-MM-DD', 'D.M.YYYY','M/D/YYYY']
      var date = null
      formats.forEach(format=>{
        const tmp = moment(dateString, format,true)
        if(tmp.isValid()) {
          date = tmp
        }
      })
      return date
    },
    format(data) {
      if(!data) {
        return []
      }
      return data
    },
    submit(form) {
      if(!form) {
        return
      }
      this.submitLoading = true
      const url = `${this.$store.getters.getApiUrl}/forms/upload/${form.id}`
      var formData = new FormData(this.$refs.form)
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }
      }).then(response=>{
        this.submitLoading = false
        this.$refs.submit.success = true
        this.$refs.fileUpload.clear()
        this.fileData = []
        this.uploaded = []
        console.log(response.data)
      }).catch(error=>{
        this.submitLoading = false
        this.$refs.submit.error = true
        this.handleError(error, 'Submit data')
        console.log(error.response)
      }) 
    },
    userCast(user) {
      return {id: user.id, name: `(${user.id}) ${user.firstname} ${user.lastname}`}
    },
    async getSubmissions() {
      if(!this.form) {
        return
      }
      this.get_submissions_loading = true
      const {submissions} = await this.$store.dispatch('submissions', {key: this.form.id, get:{form_id:this.form.id},deep:'submissions'})
      this.get_submissions_loading = false
      this.old_submissions = submissions.submissions
      this.references = submissions.references
    },
    async getGroups() {
      this.get_groups_loading = true
      const {groups} = await this.$store.dispatch('groups')
      this.get_groups_loading = false
      this.groups = groups
    },
    async getUsers() {
      this.get_users_loading = true
      const {users} = await this.$store.dispatch('users')
      this.get_users_loading = false
      this.users = users
    },
    closeEdit() {
      this.editRow = false
    },
    saveRowChanges() {
      this.data[this.selectedRowIndex] = this.newRow
      this.table_key += 1
      console.log(this.newRow)
      console.log(this.data)
      this.closeEdit()
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
      const url = `${this.$store.getters.getApiUrl}/lv/upload`
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
        this.uploading = false
        this.uploaded = this.uploaded.concat(potentialUpload)
        this.fileData=this.fileData.concat(response.data)
        console.log(this.fileData)
        potentialRemove.forEach(file=>{
          this.uploaded = this.uploaded.filter(uploadedFile=>uploadedFile.id!==file.id)
          this.fileData = this.fileData.filter(row=>row.fileId!==file.id)
        })
      }).catch(error=>{
        this.uploading = false
        this.$refs.fileUpload.removeFiles(potentialUpload)
        this.handleError(error, 'Upload file')
      })
    },
  }

}
</script>


<style scoped lang="scss">
.table {
  // margin-top: 40mm;
}
.upload-submissions {
  display: flex;
  flex-direction: column;
}
.form-file {
  max-width: 210mm;
  width: 100%;
}
.close-button {
  position: absolute;
  top: 10px;
  right: 10px;
  border-radius: 10%;
  padding: 2px;
}
.edit-popup {
  position: fixed;
  top:50%;
  left:50%;
  transform: translate(-50%,-50%);
  max-width: 210mm;
  max-height: 90vh;
  width: 100%;
  background-color: white;
  z-index: 100;
  overflow-y: auto;
  overflow-x: hidden;
  padding: 40px 20px 140px 20px;
  border-radius: 4px;
  h3 {
    margin-bottom: 40px;
  }
}
.edit-popup-overlay {
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 99;
  background-color: rgba(0,0,0,0.3);
  backdrop-filter: blur(1px);
}
</style>
