<template>
  <div class="view-submissions">
    <dialog ref="archive" class="archive-form">
      <h3>Archive selected submissions</h3>
      <div class="form-elements">
        <Checkbox ref="is_archived" label="Move to submissions to Archive / Remove submissions from Archive"/>
        <SelectElement tooltip="Name by which archived submissions will be grouped" ref="archive_group" label="Archive group" :inputTypeable="true" :data="Object.keys(archive)" :search="false"/>
        <Button text="Move Submissions" @click.prevent="archiveSelectedSubmissions()"/>

      </div>
    </dialog>
    
    <div class="view-options" v-if="form?.permission>=3">
      <button @click="openArchiveOptions()">Manage Archive</button>
    </div>

    <div class="archives">
      <h3>Archived submissions</h3>
      <!-- <div class="archive" :class="{open: key in open_archives}" v-for="(value, key) in archive" :key="key" > -->
      <div class="archive" :class="{open: key in open_archives}" v-for="(value, key) in submission_data_resolved.archive" :key="key" >
        <div class="title" :class="{open: key in open_archives}" @click="openArchive(key)">
          <span>{{key}}</span>
        </div>
        <div class="body" :class="{open: key in open_archives}">
          <JSONToTable :dataset_permission="form?.permission" :ref="key" :select="true" :menuOptions="submissionOptionsArchive" :use_header_id="true" :header="columnSettings.items" :data="value" @optionClick="submissionMenuItemClicked($event)"/>
          <JSONToTable: :use_header_id="true" :header="columnSettings.items" :data="value"/>
        </div>

      </div>
    </div>
    <div class="view-submissions-body" ref="body">
      <!-- <JSONToTable permissionKey="permission" :loading="loading" :optionLoading="optionLoading" :download="true" :reply="true" :select="true" :rowMenuOptions="submissionOptions" :data="table_data_from_submissions" :blacklist="false" :list="columnSettings.items" :order="columnSettings.order" @menuClick="submissionMenuItemClicked($event)"/> -->
      <!-- <JSONToTable :annotations="annotations" :use_header_id="true" ref="table" :loading="loading" :header="columnSettings.items" :data="submission_data_decoded" :menuOptions="submissionOptions" @optionClick="submissionMenuItemClicked($event)"/> -->
      <!-- <JSONToTable :dataset_permission="form?.permission" :select="true" :annotations="annotations" :use_header_id="true" ref="table" :loading="loading" :header="columnSettings.items" :data="submission_data_decoded" :menuOptions="submissionOptions" @optionClick="submissionMenuItemClicked($event)"/> -->
      <JSONToTable :dataset_permission="form?.permission" :menuOptions="submissionOptions" :use_header_id="true" :header="columnSettings.items" :data="submission_data_resolved.live" ref="table" @optionClick="submissionMenuItemClicked($event)"/>
    </div>
  </div>
</template>

<script>
// import JSONToTable from '@/components/JSONToTable.vue'
import JSONToTable from '@/components/JSONToTable2.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import SelectElement from '@/components/inputs/SelectElement.vue'
import Button from '@/components/Button.vue'

export default {
  name: 'ViewSubmissions',
  components:  {
    JSONToTable,
    SelectElement,
    Checkbox,
    Button,
  },
  emits: ['editSubmission', 'deleteSubmission','copySubmission','archive'],
  props: {
    submissions: [Array,Object],
    optionLoading: Number,
    loading: Boolean,
    form: Object,
    showUser: {
      default: true,
      type: Boolean,
    },
  },
  data() {
    return {
      submissionOptions: [
        {id: 0,icon: 'create-outline', text: 'View / Edit Submission', name: 'editSubmission', permission: 2, dataset_permission: 2},
        {id: 1,icon: 'copy-outline', text: 'Copy Submission', name: 'copySubmission', permission: 1, twoStep: false, dataset_permission: 2},
        {id: 1,icon: 'lock-closed-outline', text: 'Archive', name: 'archiveSubmission', permission: 1, twoStep: false, dataset_permission: 3},
        {id: 2,icon: 'trash-outline', text: 'Delete Submission', name: 'deleteSubmission', permission: 3, twoStep: true, dataset_permission: 2},
      ],
      submissionOptionsArchive: [
        {id: 0,icon: 'create-outline', text: 'View / Edit Submission', name: 'editSubmission', permission: 2, dataset_permission: 3},
        {id: 1,icon: 'copy-outline', text: 'Copy Submission', name: 'copySubmission', permission: 1, twoStep: false, dataset_permission: 2},
        {id: 1,icon: 'lock-open-outline', text: 'Remove from Archive', name: 'dearchiveSubmission', permission: 1, twoStep: false, dataset_permission: 3},
        {id: 2,icon: 'trash-outline', text: 'Delete Submission', name: 'deleteSubmission', permission: 3, twoStep: true, dataset_permission: 3},
      ],
      activeRow: {index: null, top: null, optionsHeight: 80},
      bodyRect: {top: null},
      hide_archive: true,
      open_archives: {},
    }
  },
  computed: {

    annotations() {
      const annotations = {}
      const user = this.$store.getters.getProfile

      this.submissions?.[this.form?.id]?.forEach(submission=>{
        submission.replies.forEach(reply=>{
          if(reply.seen.includes(user.id)) {
            return
          }
          if(!(submission.id in annotations)) {
            annotations[submission.id] = 0
          }
          annotations[submission.id] ++
        })
      })
      return annotations
    },
    submission_data_decoded() {
      const data = []
      this.submissions?.[this.form?.id]?.forEach(submission=>{
        if(submission.is_archived) {
          return
        }
        const row = {
          id: submission?.id,
          is_archived: submission?.is_archived,
          archive_group: submission?.archive_group,
          permission: submission?.permission,
          '-2': submission?.replies?.length,
          '-1': submission?.owner.name,
        }
        
        Object.keys(this.form?.form_elements).forEach(el_id=>{
          const form_element = this.form.form_elements[el_id]
          if(!form_element.input || form_element.form_id!=this.form.id || !form_element.show) {
            return
          }
          var submission_value = submission._data[form_element.id]
          if(submission_value===null || submission_value===undefined) {
            row[form_element.id] = null
            return
          }
          if(form_element.component==='SelectReferenceElement') {
            var key_val_refs = null
            key_val_refs = this.solveReferenceRecursively(form_element, submission_value)
            console.log(key_val_refs)
            key_val_refs.forEach(key_val_ref=>{
              row[key_val_ref.id] = key_val_ref.value
            })
          } else if(form_element.component==='SelectElement') {
            row[form_element.id] = form_element.data['data'][submission_value]
          } else if(form_element.component==='DoubleInputElement') {
            if(form_element.data.show_url) {
              row[form_element.id+'_url'] = submission_value.url
            }
            if(submission_value.alias) {
              row[form_element.id+'_alias'] = `<a target="_blank" href=${submission_value.url}>${submission_value.alias}</a>`
            } else {
              row[form_element.id+'_alias'] = `<a target="_blank" href=${submission_value.url}>${submission_value.url}</a>`
            }

            

          } else if(form_element.component==='FileUploadElement') {
            row[form_element.id] = []
            submission_value.forEach(file=>{
              row[form_element.id].push( `<a target="_blank" href=${file?.url}>${file?.name}</a>`)
            })
            row[form_element.id].join(" ")
          } else {
            row[form_element.id] = submission_value
          }
        })
        data.push(row)
      })
      return data
    },
    columnSettings() {
      var items = []
      if(this.form?.form_elements) {
        Object.keys(this.form.form_elements).forEach(el_id=>{
          const form_element = this.form.form_elements[el_id]
          if(!form_element.input || form_element.form_id!=this.form.id || !form_element.show) {
            return
          }
          if(form_element.component==='SelectReferenceElement') {
            const elements = this.labelReferencesRecursivelyWithId(form_element)
            elements.forEach(element=>{
              element.position = form_element.position
            })
            items = items.concat(elements)
          } else if(form_element.component=='DoubleInputElement') {
            if(form_element.data.show_url) {
              items.push({id:form_element.id+'_url',name: `${form_element.data.label_url}`,position:form_element.position})
            }
            items.push({id:form_element.id+'_alias',name: `${form_element.data.label_alias}`,position:form_element.position})
          } else {
            items.push({id:form_element.id,name:form_element.data.label,position:form_element.position})
          }
        })
      }
      items = this.orderByFormPosition(items)
      return {
        type: 'whitelist',
        items: [...items,{id:-1,name:'owner.name'},{id:-2,name:'number of replies'}],
      }
    },
    archive() {
      const archive = {}
      this.submissions?.[this.form?.id]?.forEach(submission=>{
        if(!submission.is_archived) {
          return
        }
        const archive_group = submission.archive_group ? submission.archive_group : 'default'
        if(!(archive_group in archive)) {
          archive[archive_group] = {archive:[],header: {}}
        }
        const row = this.resolveArchiveRecursively(submission.archive_data, archive[archive_group].header)
        row.data[-1] = submission?.archive_owner?.name
        row.data[-2] = submission.replies.length
        row.data['id'] = submission.id
        row.data['permission'] = submission.permission
        row.data['is_archived'] = submission.is_archived
        row.data['archive_group'] = submission.archive_group
        archive[archive_group].archive.push(row.data)
        archive[archive_group].header = row.unique_keys
      })
      Object.keys(archive).forEach(archive_group=>{
        var header = []
        Object.keys(archive[archive_group].header).forEach(id=>{
          header.push({id,name:archive[archive_group].header[id].label})
        })
        header.sort((a,b)=>{
          if(archive[archive_group].header[a.id].pos>archive[archive_group].header[b.id].pos) {
            return 1
          }
          if(archive[archive_group].header[a.id].pos==archive[archive_group].header[b.id].pos) {
            return 0
          }
          return -1
        })
        header.push({id:-1,name:'owner.name'}, {id:-2,name:'number of replies'})
        archive[archive_group].header = header
      })
      return archive
    },
    submission_data_resolved() {
      const data = {archive:{},live:[]}
      this.submissions?.[this.form?.id]?.forEach(submission=>{

        var ret = this.deReferenceData(this.form.form_elements,this.submissions, submission)
        // console.log(submission)
        ret['permission'] = submission.permission
        ret['id'] = submission.id
        if(submission.is_archived) {
          if(!(submission.archive_group in data.archive)) {
            data.archive[submission.archive_group] = []
          }
          data.archive[submission.archive_group].push(ret)
          return
        }
        data.live.push(ret)
      })
      console.log(this.columnSettings.items)
      return data
    },
  },
  methods: {
    deReferenceData(form_elements,submissions,submission,prefix='') {
      var dereferenced_data = {}
      var data = submission.input_data
      Object.keys(form_elements).forEach(el_id=>{
        if(!data || !(el_id in data)) {
          return
        }
        var value = data[el_id]
        var label = null
        if(submission.is_archived) {
          label = prefix+submission.archive_element_label[el_id]
          dereferenced_data[label] = value
          return
        } else {
          label = prefix+el_id
        }

        if(form_elements[el_id].component==='SelectReferenceElement') {

          const ref_submission = submissions[form_elements[el_id].data.formId].find(submission=>submission.id==value)
          // console.log(el_id, data,ref_submission)

          dereferenced_data = {...dereferenced_data,...this.deReferenceData(form_elements,submissions,ref_submission,label+'.')}
          return
        }
        if(form_elements[el_id].component==='SelectElement') {
          dereferenced_data[label] = form_elements[el_id].data.data[value]
          return
        }
        dereferenced_data[label] = value
      })
      // console.log(this.columnSettings.items)
      return dereferenced_data
    },
    deciverLabelKey(label_key,form,fallback) {
      const ids = label_key.split('.')
      const label = []
      ids.forEach(id=>{
        if(id in form.form_elements) {
          label.push(form.form_elements[id].data.label)
        } else {
          label.push(fallback)
        }
      })
      return label.join('.')
    },
    resolveArchiveRecursively(data, unique_keys={},prefix=null,prepos=null,prekey=null) {
      var resolved_data = {}

      if(data===null) {
        return null
      }

      const keys = Object.keys(data)
      var pos = 0
      if(prepos!==null) {
        pos += prepos
      }

      keys.forEach(key=>{

        var label = ''
        if(prefix!==null) {
          label = `${prefix}.`
        }
        label += data[key]["label"]


        var label_key = ''
        if(prekey!==null) {
          label_key = `${prekey}.`
        }
        label_key += key

        if(typeof data[key]["value"] === 'object' && data[key]["value"]!==null) {

          
          const rec_resolved_data = this.resolveArchiveRecursively(data[key]["value"],unique_keys,label,pos,label_key)
          resolved_data = {...resolved_data,...rec_resolved_data.data}
          pos += rec_resolved_data.end


        } else {
          pos += data[key]["position"]

          resolved_data[label_key] = data[key]["value"]
          if(!(label_key in unique_keys)) {
            const decivered_label = this.deciverLabelKey(label_key,this.form,label)
            unique_keys[label_key] = {pos,label:decivered_label}
          }
        }
      })
      return {data:resolved_data,unique_keys,end:pos}
    },
    orderByFormPosition(items) {
      return items.sort((a,b)=>{
        if(a.position>b.position) {
          return 1
        }
        if(a.position==b.position) {
          return 0
        }
        return -1
      })
    },
    labelReferencesRecursivelyWithId(element, prelabel=null) {
      if(element.component!=='SelectReferenceElement') {
        return null
      }
      var labels = []
      element.data['formElementIds'].forEach(ref_form_el_id=>{
        const ref_el = this.form.form_elements[ref_form_el_id]
        if(ref_el==undefined) {
          return
        }
        var label = ''
        if(prelabel!=null) {
          label = `${prelabel}.${element.data.label}`
        } else {
          label = `${element.data.label}`
        }
        if(ref_el?.component==='SelectReferenceElement') {
          labels = labels.concat(this.labelReferencesRecursivelyWithId(ref_el, label))
        } else {
          label += `.${ref_el?.data.label}`
          labels.push({id:ref_el?.id,name:label})
        }
      })
      return labels
    },
    labelReferencesRecursively(element, prelabel=null) {
      if(element.component!=='SelectReferenceElement') {
        return null
      }
      var labels = []
      element.data['formElementIds'].forEach(ref_form_el_id=>{
        const ref_el = this.form.form_elements[ref_form_el_id]
        var label = ''
        if(prelabel!=null) {
          label = `${prelabel}.${element.data.label}`
        } else {
          label = `${element.data.label}`
        }
        if(ref_el?.component==='SelectReferenceElement') {
          labels = labels.concat(this.labelReferencesRecursively(ref_el, label))
        } else {
          label += `.${ref_el?.data.label}`
          labels.push(label)
        }
      })
      return labels
    },
    solveReferenceRecursivelyArchive(element, archive) {
      var data = []
      if(element.component!=='SelectReferenceElement' || archive===null) {
        return [{id: element.id, value: archive}]
      }
      element.data.formElementIds.forEach(ref_el_id=>{
        data = data.concat(this.solveReferenceRecursivelyArchive(this.form.form_elements[ref_el_id], archive[ref_el_id].value))
      })
      return data
    },
    solveReferenceRecursively(element, value,prelabel=null) {
      if(element.component!=='SelectReferenceElement') {
        return null
      }
      const ref_sub = this.submissions[element.data['formId']].find(ref_sub=>ref_sub.id==value)
      var data = []
      element.data['formElementIds'].forEach(ref_form_el_id=>{
        const ref_el = this.form.form_elements[ref_form_el_id]
        var ref_value = ref_sub?._data[ref_el.id]
        var label = ''
        if(prelabel!=null) {
          label = `${prelabel}.${element.data.label}`
        } else {
          label = `${element.data.label}`
        }
        if(ref_el.component==='SelectReferenceElement') {
          data = data.concat(this.solveReferenceRecursively(ref_el,ref_value, label))
        } else if(ref_el.component==='SelectElement') {
          label += `.${ref_el.data.label}`
          data.push({id:ref_el.id,label, value:ref_el.data.data[ref_value]})
        } else {
          label += `.${ref_el.data.label}`
          data.push({id:ref_el.id,label, value:ref_value})
        }
      })
      return data
    },
    highlight(submission) {
      this.$refs.table.highlight(submission)
    },
    getSubmissionData(submission) {
      var data = {}
      this.form?.form_elements?.filter(el=>el.input).forEach(el=>{
        const subElement = submission.form_elements?.find(subEl=>el.id==subEl.id)
        if(subElement==null || subElement==undefined) {
          data[el.data.label] = null
        } else if(el.component=="SelectElement") {
          data[el.data.label] = el.data.data?.find(option=>option.id==subElement.pivot.data)?.name
        } else if(el.component=="SelectReferenceElement") {
          
          if(subElement.pivot.data) {
            subElement.pivot.data.forEach(reference_data_element=>{
              data[`${el.data.label}_${reference_data_element.label}`] = reference_data_element.value
            })
          } else {
            data[`${el.data.label}`] = null
          }
        } else if(el.component=="FileUploadElement") {
          var content = []
          subElement.pivot.data.forEach(file=>{
              content.push({url: this.getUrl(file['path']), name: file['name'], type: 'file'})
          })
          data[el.data.label] = content
        } else if(el.component=="InputElement") {
          if(el.data.type=="date") {
            const date = new Date(subElement.pivot.data)
            const year = date.getFullYear()
            const month = String(date.getMonth()+1).padStart(2, 0)
            const day = String(date.getDate()).padStart(2, 0)
            data[el.data.label] = `${day}.${month}.${year}`
          } else {
            data[el.data.label] = subElement.pivot.data
          }
        } else {
          data[el.data.label] = subElement.pivot.data
        }
      })
      return data
    },
    getUrl(file) {
      return file.replace('D:\\inetpub\\MPortal', 'https://www-3.mach.kit.edu/')
    },
    submissionMenuItemClicked(event) {
      if(event.option.name=='editSubmission') {
        this.editSubmission(event.row_id)
      } else if(event.option.name=='deleteSubmission') {
        this.deleteSubmission(event.row_id)
      } else if(event.option.name=='copySubmission') {
        this.copySubmission(event.row_id)
      } else if(event.option.name=='archiveSubmission') {
        const obj = {}
        obj[event.row_id] = {is_archived:1,archive_group:null}
        this.archiveSubmissions(obj)
      } else if(event.option.name=='dearchiveSubmission') {
        const obj = {}
        obj[event.row_id] = {is_archived:0,archive_group:null}
        console.log(obj)
        this.archiveSubmissions(obj)
      }
    },
    archiveSelectedSubmissions() {
      const object = {}
      Object.keys(this.archive).forEach(key=>{
        Object.keys(this.$refs[key].selected).forEach(id=>{
          object[id] = {is_archived:this.$refs.is_archived.checked,archive_group:this.$refs.archive_group.$refs.input.value}
        })
        this.$refs[key].selected = {}
      })
      Object.keys(this.$refs.table.selected).forEach(id=>{
        object[id] = {is_archived:this.$refs.is_archived.checked,archive_group:this.$refs.archive_group.$refs.input.value}
      })
      this.$refs.table.selected = {}
      this.archiveSubmissions(object)
      this.closeArchiveOptions()
    },
    closeArchiveOptions() {
      this.$refs.archive.close()
    },
    openArchiveOptions() {
      this.$refs.archive.showModal()
      this.$refs.archive_group.blur()
      this.$refs.archive.addEventListener("mousedown", e=>{
        const dialog_rect = this.$refs.archive.getBoundingClientRect()
        if(e.clientX < dialog_rect.left || e.clientX > dialog_rect.right || e.clientY < dialog_rect.top || e.clientY > dialog_rect.bottom) {
          this.closeArchiveOptions()
        }
      })
    },
    async archiveSubmissions(object) {
      const formData = new FormData()
      formData.append('ids', JSON.stringify(object))
      formData.append('form_id', this.form.id)
      const {submissions, error} = await this.$store.dispatch('_archive', {form_id:this.form.id,formData})
      this.$emit('archive', submissions)
      console.log(submissions)
      console.log(error?.response)
    },
    open(event, index) {
      var correction = 0
      if(this.bodyRect.top==null) {
        this.bodyRect.top = this.$refs.body.getBoundingClientRect().top
      }
      if(index>this.activeRow.index && this.activeRow.index!=null) {
        correction = -this.activeRow.optionsHeight
      }
      this.indexOpen = index
      const top = event.target.getBoundingClientRect().top + event.target.getBoundingClientRect().height - this.bodyRect.top + correction
      this.activeRow.index = index
      this.activeRow.top = top

    },
    close() {
      this.activeRow = {index: null, top: null, optionsHeight: 80}
    },
    deleteSubmission(row_id) {
      this.close()
      this.$emit("deleteSubmission", row_id)
    },
    copySubmission(row_id) {
      this.$emit("copySubmission", row_id)
    },
    editSubmission(row_id) {
      this.$emit("editSubmission", row_id)
    },
    openArchive(key) {
      if(key in this.open_archives) {
        delete this.open_archives[key]
        return
      }
      this.open_archives[key] = true
    },
  },

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.archive-form {
  padding-top:48px;
  padding-bottom:48px;
  color: $text-dark;
  .form-elements {
    display: flex;
    flex-direction: column;
    gap: 16px;
  }
}
.view-options {
  text-align: left;
  button {
    padding: 16px;
    border: 1px solid black;
    cursor: pointer;
    &:hover {
      background-color: $text-dark;
      color: white; 
    }
  }
}
.archives {
  text-align: left;
  .archive {
    position: relative;
    overflow: hidden;
    z-index: 3;
    border: 1px solid #cccccc;
    &.open {
      overflow: visible;
      outline: 2px solid black;
      border-radius: 4px;
    }
    .title {
      position: relative;
      background-color: #dddddd;
      z-index: 1;
      padding: 8px;
      cursor: pointer;
      &:hover {
        background-color: #ccc;
      }
      &.open {
        // border: 1px solid green;
      }
    }
    .body {
      position: relative;
      z-index: 0;
      height: 0;
      padding: 0;
      &.open {
      padding: 16px 0;
        height: 100%;
      }
    }
  }
}
#file-download {
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
}
.view-submissions {
  position: relative;
  width: 100%;
  height: 100%;
}
.view-submissions-body {
  position: relative;
}
.submissions-columnames {
  position: relative;
  width: 100%;
  text-align: center;
  background-color: $kit_green;
  color: $text_light;
  padding: 8px 18px;
}
.submission-row {
  position: relative;
  display: contents;
  :first-child {
    &.active {
      border-top-left-radius: 4px;
      border-left: 1px solid black;
    }
  }
  :last-child {
    &.active {
      border-top-right-radius: 4px;
      border-right: 1px solid black;
    }
  }
  &:nth-child(2n) {
    > * {
      background-color: #eee;
    }
  }
  &:nth-child(2n+1) {
    > * {
      background-color: #f9f9f9;
    }
  }  
  &:hover {
    > * {
      cursor: pointer;
    }
  }
}
.submission-item {
  position: relative;
  padding: 8px;
  top: 0;
  transition: top .4s ease;
  z-index: 1;
  &.active {
    border-top: 1px solid black;
  }

}
.row-options {
  padding: 5px;
  position: absolute;
  display: flex;
  flex-direction: column;
  align-items: flex-start;
  justify-content: center;
  // width: 100%;
  z-index: -1;
  height: 0;
  transition: height .4s ease;
  box-shadow: 0px 0px 4px 0px inset rgba(0, 0, 0, 0.2);
  overflow: hidden;
  border-bottom-right-radius: 4px;
  border-bottom-left-radius: 4px;
  padding: 4px 0;
  > * {
    padding: 4px;
    width: 100%;
  }
}
</style>
