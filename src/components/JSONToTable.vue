<template>
  <div class="table-options-wrapper" ref="optionsBar" v-if="download || reply">
    <div class="table-options-bar" >
      <button class="options-bar-button" ref="downloadButton" @click="openDownload($event)" v-if="download">
        <span class="options-bar-button-text">Download</span>
        <ion-icon class="options-bar-button-icon" name="download-outline"></ion-icon>
      </button>
      <button class="options-bar-button" @click="openReply($event)" v-if="reply">
        <span class="options-bar-button-text">Reply</span>
        <ion-icon class="options-bar-button-icon" name="mail-outline"></ion-icon>
      </button>
    </div>
  <div class="download-form" :class="{active: downloadFormActive}" :style="[downloadFormStyle,{'--card': downloadPage}]" v-if="download">
    <div class="form-header">
      <button class="close-download-form-button" @click="close()">
        <ion-icon class="close-icon" name="close-outline"></ion-icon>
      </button>
      <button class="download-form-go-back" v-if="downloadPage>0" @click="downloadGoBack()">
        <ion-icon class="back-icon" name="arrow-back-outline"></ion-icon>
      </button>
      <button class="download-form-go-forward" v-if="canForward" @click="downloadGoForward()">
        <ion-icon class="forward-icon" name="arrow-forward-outline"></ion-icon>
      </button>
      <span class="text">Download</span>
      <ion-icon class="icon" name="download-outline"></ion-icon>
      <div class="steps-overview">
        <div class="step-indicator step-1" :class="{active: downloadPage>=0}" @click="jumpToStep(0)">Choose rows</div>
        <div class="step-connector" :class="{active: downloadPage>=1}"></div>
        <div class="step-indicator step-2" :class="{active: downloadPage>=1}" @click="jumpToStep(1)">Choose columns</div>
        <div class="step-connector" :class="{active: downloadPage>=2}"></div>
        <div class="step-indicator step-3" :class="{active: downloadPage>=2}" @click="jumpToStep(2)">Download</div>
      </div>
    </div>
    <div class="form-body">
      <div class="card card-1" >
          <h4>Choose which rows to download</h4>
          <button class="card-option" :disabled="selectedRows?.length<=0" :class="{active: downloadRowsOption===0, disabled: selectedRows?.length<=0}" @click="handleCardOption(0,0)">
            <span>Selected rows ({{selectedRows?.length}})</span>
            <span class="error-span" v-if="selectedRows?.length<=0">No rows selected</span>
          </button>
          <button class="card-option" :class="{active: downloadRowsOption===1}" @click="handleCardOption(0,1)">
            <span>All rows</span></button>
      </div>
      <div class="card card-2">
          <h4>Choose which columns to download</h4>
          <button class="card-option" :class="{active: downloadColumnsOption===0}" @click="handleCardOption(1,0)">
            <span>Displayed columns</span></button>
          <button class="card-option" :class="{active: downloadColumnsOption===1}" @click="handleCardOption(1,1)">
            <span>All columns</span></button>
      </div>
      <div class="card card-3">
          <h4>Enter filename</h4>
          <InputElement color="#2C3E50" class="filename-input" @valueChange="filename=$event" :clean="true" label="Filename" type="text" ref="filename"/>
          <a class="download-link" v-if="validData && file" :download="file.name" :href="file.url">
            <span> Download</span></a>
          <div class="download-link" v-else>
              <span> Download</span></div>
      </div>
    </div>
  </div>
  <div v-if="reply" class="reply-form" :class="{active: replyFormActive, 'step-0': replyPage===0, 'step-1': replyPage===1, 'step-2': replyPage===2}" :style="[replyFormStyle,{'--card': replyPage}]">
    <div class="form-header">
      <button class="close-download-form-button" @click="close()">
        <ion-icon class="close-icon" name="close-outline"></ion-icon>
      </button>
      <button class="download-form-go-back" v-if="replyPage>0" @click="replyGoBack()">
        <ion-icon class="back-icon" name="arrow-back-outline"></ion-icon>
      </button>
      <button class="download-form-go-forward" v-if="canForwardReply" @click="replyGoForward()">
        <ion-icon class="forward-icon" name="arrow-forward-outline"></ion-icon>
      </button>
      <span class="text">Reply</span>
      <ion-icon class="icon" name="mail-outline"></ion-icon>
      <div class="steps-overview">
        <div class="step-indicator step-1" :class="{active: replyPage>=0}" @click="jumpToReplyStep(0)">Choose rows</div>
        <div class="step-connector" :class="{active: replyPage>=1}"></div>
        <div class="step-indicator step-2" :class="{active: replyPage>=1}" @click="jumpToReplyStep(1)">Mail options</div>
        <div class="step-connector" :class="{active: replyPage>=2}"></div>
        <div class="step-indicator step-3" :class="{active: replyPage>=2}" @click="jumpToReplyStep(2)">Reply form</div>
      </div>
    </div>
    <div class="form-body">
      <div class="card card-1">
        <h4>Choose which rows to reply to</h4>
        <button class="card-option"  :class="{active: replyRowsOption===0, disabled: selectedRows?.length<=0}" @click="handleReplyRows(0)">
          <span>Selected rows ({{selectedRows?.length}})</span>
          <span class="error-span" v-if="selectedRows?.length<=0">No rows selected</span>
        </button>
        <button class="card-option" :class="{active: replyRowsOption===1}" @click="replyRowsOption=1;replyPage=1">
          <span>All rows</span>
        </button>
      </div>
      <div class="card card-2">
        <h4>Choose Mail options</h4>
        <button class="card-option"  :class="{active: replyMailOption===0}" @click="replyMailOption=0;replyPage=2">
          <span>Only send Mail</span>
        </button>
        <button class="card-option" :class="{active: replyMailOption===1}" @click="replyMailOption=1;replyPage=2">
          <span>Only reply</span>
        </button>
        <button class="card-option" :class="{active: replyMailOption===2}" @click="replyMailOption=2;replyPage=2">
          <span>Reply and Mail</span>
        </button>
      </div>
      <form @submit.prevent="submitReplies()" class="card card-3" ref="replyForm">
        <InputElement tooltip="If no address is specified user.email is used" name="mail_address" label="To Mail address" type="text" :required="false" color="#2C3E50" class="filename-input" ref="email"/>
        <InputElement name="reply_subject" label="Subject" type="text" :required="true" color="#2C3E50" class="filename-input" ref="reply_subject"/>
        <div class="text-area-container">
          <label for="reply-body">Contents of reply</label>
          <textarea id="reply-body" class="reply-textarea" name="reply_content" ref="reply_content" />
        </div>
        <FileUploadElement @fileChange="replyAttachments=$event" class="file-upload" label="Attachments" name="attachments" :tiny="true" ref="attachments"/>
        <Button ref="submitButton" :disabled="replySubmitLoading" :loading="replySubmitLoading" bgColor="rgb(93%,76%,45%)" color="#2C3E50" :stretch="true"  :text="selectedRows?.length==1 && replyRowsOption===0?'Send Reply':'Send Replies'"/>
      </form>
    </div>
  </div>
  </div>

  <div class="download-form-overlay" v-if="downloadFormActive||replyFormActive" @click.self="close()"></div>
  <div class="searchbar" v-if="!((!data || data.length<=0)&&!loading)">
    <input class="searchbar-input" type="text" placeholder="Search table..." v-model="searchString">
    <div>
      Displaying: {{filter(dataDecorated)?.length}} out of {{dataDecorated?.length}} rows.
    </div>
  </div>
  <div class="select-count" v-if="!((!data || data.length<=0)&&!loading) && select">
    Selected: {{selectedRows?.length}} out of {{data?.length}} rows.

  </div>
  <div class="waiting-for-data" v-if="(!data || data.length<=0)&&!loading">
    No data
  </div>
  <div class="json-to-table" v-else :style="gridStyle" ref="grid">
    <template v-if="loading">
      <DataPlaceholder animation="table"/>
    </template>
    <template v-if="!loading">
      <div class="grid-item header" v-if="select">
        <Checkbox :invert="true" :presetValue="isAllRowsSelected()" :clean="true" @inputChange="selectAllRows($event)"/>
      </div>


      <div class="grid-item header" v-for="header in orderColumnHeaders(filterColumnHeaders(columnHeaders))" :key="header">
        <span class="header-name">{{trimString(header, 38)}}</span>
        <button class="icon-button" :class="{active: filterActive(header), open: activeHeader===header}" @click.self="openFilter(header, $event)">
          <ion-icon class="icon" name="chevron-down"></ion-icon>
        </button>
      </div>

      <div class="grid-row" :class="{active: rowIndex==rowDropdownMenuSettings.index}" v-for="(row,rowIndex) in filter(dataDecorated)" :key="row">
        <div :style="rowDropdownMenuOffsetStyle(rowIndex)"  class="grid-item" v-if="select" @click.self="rowClick($event, row, rowIndex)">
          <Checkbox :presetValue="isRowSelected(row)" :clean="true" @inputChange="selectRow($event, row)"/>
          <div class="row-notification" v-if="row.replies && unseen(row).length>0">
            <ion-icon class="row-notification-icon" name="chatbox"></ion-icon>
            <span class="row-notification-text">
              {{notifications(unseen(row).length)}}
            </span>
          </div>
        </div>
        <div v-html="trimString(row[header], 2200)" :class="{'max-width': orderColumnHeaders(filterColumnHeaders(columnHeaders)).length>5,warning: error && error[rowIndex] && error[rowIndex][header]==2, error: error && error[rowIndex] && error[rowIndex][header]==1}" :style="rowDropdownMenuOffsetStyle(rowIndex)" class="grid-item" @click.self="rowClick($event, row, rowIndex)" v-for="(header) in orderColumnHeaders(filterColumnHeaders(columnHeaders))" :key="header">
        </div>
          <!-- {{trimString(row[header], 90)}} -->
      </div>
    </template>


    <div class="row-dropdown-menu" :style="rowDropdownMenuStyle" v-if="!loading &&rowMenuOptions!==null && rowDropdownMenuSettings.index!==null && rowMenuOptions.filter(option=>option.permission<=rowDropdownMenuSettings.row[permissionKey] || option.permission===null).length>0">
      <template  v-for="(option) in rowMenuOptions.filter(option=>option.permission<=rowDropdownMenuSettings.row[permissionKey] || option.permission===null)" :key="option">
        <div :style="{'padding-left': optionOffsetLeft+'px'}"  class="row-option-loading" v-if="optionLoading!==null && optionLoading!==undefined && optionLoading===option.id">Loading...</div>
        <template v-else>

          <button v-if="!option.twoStep || !menuOptionStep[option.id]" :style="{'padding-left': optionOffsetLeft+'px'}"  class="row-dropdown-menu-option" @click="menuOptionClick(option, rowDropdownMenuSettings.row)">
            <ion-icon class="row-dropdown-menu-icon" :name="option.icon"></ion-icon>
            <span class="row-dropdown-menu-text">{{option.text}}</span>
          </button>
          <div class="step-2" v-else>
            <button @click="menuOptionClick(option, rowDropdownMenuSettings.row)" class="row-dropdown-menu-option" :style="{'padding-left': optionOffsetLeft+'px'}">
              <ion-icon class="row-dropdown-menu-icon" name="checkmark-outline"></ion-icon>
              <span class="row-dropdown-menu-text">Confirm</span>
            </button>
            <button @click="delete menuOptionStep[option.id]" class="row-dropdown-menu-option" :style="{'padding-left': optionOffsetLeft+'px'}">
              <ion-icon class="row-dropdown-menu-icon" name="close-outline"></ion-icon>
              <span class="row-dropdown-menu-text">Cancel</span>
            </button>
          </div>

        </template>

      </template>
    </div>



    <div class="edit-filter-window-overlay" v-if="filterWindowActive"  @click.self="closeFilter()"></div>
    <div class="edit-filter-window" v-if="filterWindowActive" :style="filterWindowStyle">
      <div class="filter-window-options-bar">
        <button class="close-filter-window-button">
          <ion-icon class="close-icon" name="close" @click="closeFilter()"></ion-icon>
        </button>
      </div>
      <div class="filter-window-header">
        <!-- <InputElement :optional="false" :presetValue="getFilterString(activeHeader)" @valueChange="setFilterString($event, activeHeader)" :clean="true" label="Filter" type="text"/> -->
        <Checkbox :clean="true" :presetValue="isCheckedAll(activeHeader)" class="select-all" @inputChange="checkAll($event, activeHeader)" label="Select All"/>
      </div>
      <div class="filter-window-body">
        <Checkbox :clean="true" :presetValue="isCheckedSingle(activeHeader,value)" class="select-single" @inputChange="checkSingle($event, activeHeader, value)" v-for="value in uniqueValues(activeHeader)" :key="value" :label="toString(value)"/>
      </div>
    </div>
  </div>
</template>

<script>
import InputElement from '@/components/inputs/InputElement.vue'
import FileUploadElement from '@/components/inputs/FileUploadElement.vue'
import Checkbox from '@/components/inputs/Checkbox.vue'
import Button from '@/components/Button.vue'
import DataPlaceholder from '@/components/DataPlaceholder.vue'
import axios from "axios";


export default {
  name: 'JSONToTable',
  components: {
    InputElement,
    Button,
    Checkbox,
    FileUploadElement,
    DataPlaceholder,
  },
  props: {
    loading: {
      default: false,
      type: Boolean,
    },
    optionLoading: Number,
    download: {
      default: false,
      type: Boolean,
    },
    permissionKey: {
      default: 'permission',
      type: String,
    },
    reply: {
      default: false,
      type: Boolean,
    },
    error: Array,
    data: Array,
    order: Array,
    rowMenuOptions: Array,
    blacklist: {
      default: true,
      type: Boolean,
    },
    list: Array,
    select: {
      default: false,
      type: Boolean,
    },
    pointer: {
      default: true,
      type: Boolean,
    },
  },
  emits: [
    // Emits Array of selected row from data Array
    'select',

    /*
    Emits Object with keys
      option: Contains the option Object that has been clicked on
      row: Contains the row from the data Array that as been clicked on
    */
    'menuClick',

    // Emits the clicked on row from the data Array
    'rowClick'
  ],
  data() {
    return {
      menuOptionStep: {},
      optionOffsetLeft: 0,
      downloadFormTop:16,
      downloadFormLeft:16,
      downloadFormWidth:144,
      downloadFormHeight:45,
      filename: null,
      filterWindowSettings: {
        width: 240,
        left: 0,
        top: 0,
      },
      searchString: '',
      activeHeader: null,
      dataDecorated: null,
      filters: {},
      selectedRows: [],
      rowDropdownMenuSettings: {top: 0, index: null, height: 0, optionHeight: 36, padding: 12, row: null},
      downloadFormSettings: [
        {height: 280,width: 344},
        {height: 280,width: 344},
        {height: 280,width: 344},
      ],
      replyFormSettings: [
        {height: 280,width: 420},
        {height: 323,width: 420},
        {height: 580,width: 420},
      ],
      downloadRowsOption: null,
      downloadColumnsOption: null,
      downloadPage: 0,
      downloadFormActive: false,

      replyRowsOption: null,
      replyFormTop:16,
      replyFormLeft:176,
      replyFormWidth:144,
      replyFormHeight:45,
      replyPage: 0,
      replyFormActive: false,
      replySubmitLoading: false,
      replySubject: null,
      replyContent: null,
      replyAttachments: null,
      replyMailOption: null,

    }
  },
  mounted() {
    window.addEventListener("resize", this.windowResized);
    window.addEventListener("scroll", this.updateOptionOffsetX);

    this.decorateData(this.data)
  },
  watch: {
    rowMenuOptions: {
      handler(to) {
        console.log(to)
      },
      deep: true,
    },
    replyPage() {
      if(this.replyFormActive) {
        this.replyFormWidth = this.replyFormSettings[this.replyPage].width
        this.replyFormHeight = this.replyFormSettings[this.replyPage].height
      }
    },
    filters: {
      deep: true,
      handler() {
        this.rowDropdownMenuSettings.index = null
        this.rowDropdownMenuSettings.row = null
      }
    },
    selectedRows: {
      deep: true,
      handler(to) {
        if(this.select) {
          this.$emit('select', this.dataDecorated.filter(row=>to.includes(row.table_id)))
        }
      }
    },
    data(to) {
      this.decorateData(to)
      this.rowDropdownMenuSettings.index = null
      this.rowDropdownMenuSettings.row = null
    }
  },
  computed: {
    canForwardReply() {
      if((this.replyPage==0 && this.replyRowsOption!==null) || (this.replyPage==1 && this.replyMailOption!==null)) {
        return true
      }
      return false

    },
    canForward() {
      if(this.downloadPage==0 && this.downloadRowsOption!==null) {
        return true
      }
      if(this.downloadPage==1 && this.downloadColumnsOption!==null) {
        return true
      }
      return false
    },
    replyFormStyle() {
      const x = this.replyFormLeft
      const y = this.replyFormTop
      const width = this.replyFormWidth
      const height = this.replyFormHeight
      return {
        'top':y+'px',
        'left':x+'px',
        'width':width+'px',
        'height':height+'px',
        'overflow-y':this.replyPage!==2?'hidden':'auto',
      }
    },
    file() {
      if(this.decorateData===null) {
        return null
      }
      var data = null
      if(this.downloadRowsOption===0 && this.downloadColumnsOption===0) {
        data = this.dataDecorated.filter(row=>this.selectedRows.includes(row.table_id)).map(row=>{
          const newRow = {}
          this.filterColumnHeaders(this.columnHeaders).forEach(header=>{
            newRow[header] = row[header]
          })
          return newRow
        })
      } else if(this.downloadRowsOption===1 && this.downloadColumnsOption===0) {
        data = this.dataDecorated.map(row=>{
          const newRow = {}
          this.filterColumnHeaders(this.columnHeaders).forEach(header=>{
            newRow[header] = row[header]
          })
          return newRow
        })
      } else if(this.downloadRowsOption===0 && this.downloadColumnsOption===1) {
        data = this.dataDecorated.filter(row=>this.selectedRows.includes(row.table_id))
      } else {
        data = this.dataDecorated
      }
      if(!data || (typeof data==='object' && data.length<=0)) {
        return null
      }
      const csv = this.convertToCSV(data)
      var blob = new Blob([csv], { type: 'text/csv;charset=utf-8;' })
      return {url: URL.createObjectURL(blob), name: this.filename}
    },
    rowDropdownMenuStyle() {
      return {
        'top': this.rowDropdownMenuSettings.top+'px',
        'height': this.rowDropdownMenuSettings.height+'px',
        'padding': this.rowDropdownMenuSettings.padding+'px ' + this.rowDropdownMenuSettings.padding/2+'px',
        '--optionHeight': this.rowDropdownMenuSettings.optionHeight+'px',
        '--background-hover': '#f4f4f4',
        '--optionOffsetLeft': this.optionOffsetLeft+'px',
      }
      
    },
    filterWindowStyle() {
      return {
        'max-height': 1.77*this.filterWindowSettings.width+'px',
        'width': this.filterWindowSettings.width+'px',
        'left': this.filterWindowSettings.left+'px',
        'top': this.filterWindowSettings.top+'px',
      }
    },
    gridStyle() {
      var ncols = this.orderColumnHeaders(this.filterColumnHeaders(this.columnHeaders)).length-1
      if(this.select) {
        ncols += 1
      }
      return {
        'grid-template-columns': `max-content repeat(${ncols}, minmax(max-content, auto))`,
        '--cursor': this.pointer?'pointer':'default'
      }
    },
    validData() {
      if(Array.isArray(this.dataDecorated)) {
        return true
      }
      return false
    },
    columnHeaders() {
      var headers = []
      this.dataDecorated?.forEach(entry=>{
        Object.keys(entry)?.forEach(key=>{
          if(!headers.includes(key) && key!=="table_id") {
            headers.push(key)
          }
        })
      })
      return headers
    },
    filterWindowActive() {
      if(this.activeHeader!==null) {
        return true
      }
      return false
    },
    downloadFormStyle() {
      const x = this.downloadFormLeft
      const y = this.downloadFormTop
      const width = this.downloadFormWidth
      const height = this.downloadFormHeight
      return {
        'top':y+'px',
        'left':x+'px',
        'width':width+'px',
        'height':height+'px',
      }
    },
  },
  methods: {
    updateOptionOffsetX() {
      if(!this.$refs.grid) {
        return
      }
      const x = this.$refs.grid.getBoundingClientRect().x
      if(x<50) {
        this.optionOffsetLeft = 50-x
      } else {
        this.optionOffsetLeft=0
      }
    },
    handleReplyRows(option) {
      if(option==0 && this.selectedRows?.length<=0) {
        return
      }
      this.replyRowsOption=option
      this.replyPage=1
    },
    notifications(nNotifications) {
      if(nNotifications>100) {
        return '+99'
      }
      return nNotifications
    },
    unseen(entry) {
      const user = this.$store.getters.getProfile
      return entry.replies.filter(reply=>!reply.seen.includes(user?.id))
    },
    windowResized() {
      const offsetX = this.$refs.optionsBar?.getBoundingClientRect().x 
      const offsetY = this.$refs.optionsBar?.getBoundingClientRect().y
      var x = 0
      var y = 0
      var width = 0
      var height = 0
      var windowTransformed = {}
      if(this.replyFormActive) {
        x = this.replyFormLeft + offsetX
        y = this.replyFormTop + offsetY
        width = this.replyFormWidth
        height = this.replyFormHeight
        windowTransformed = this.ensureOnscreen(x,y,width,height,this.replyFormSettings[this.replyPage].width, 176, 16)
        x = windowTransformed.x -offsetX
        y = windowTransformed.y -offsetY
        width = windowTransformed.width
        height = windowTransformed.height

        this.replyFormTop = y
        this.replyFormLeft = x
        this.replyFormWidth = width
        this.replyFormHeight = height
      } else if(this.downloadFormActive) {
        x = this.downloadFormLeft + offsetX
        y = this.downloadFormTop + offsetY
        width = this.downloadFormWidth
        height = this.downloadFormHeight
        windowTransformed = this.ensureOnscreen(x,y,width,height,this.downloadFormSettings[this.downloadPage].width, 16, 16)
        x = windowTransformed.x -offsetX
        y = windowTransformed.y -offsetY
        width = windowTransformed.width
        height = windowTransformed.height

        this.downloadFormTop = y
        this.downloadFormLeft = x
        this.downloadFormWidth = width
        this.downloadFormHeight = height
      }
    },
    ensureOnscreen(x,y,width,height,maxWidth,stopX, stopY) {
      const offsetX = this.$refs.optionsBar?.getBoundingClientRect().x 
      const offsetY = this.$refs.optionsBar?.getBoundingClientRect().y
      const windowWidth = window.innerWidth
      const windowHeight = window.innerHeight
      if(height<windowHeight) {
        if(y + height > windowHeight) {
          y -= y + height - windowHeight
        } else {
          if(y-offsetY<stopY) {
            y += windowHeight - y -height
          } else {
            y = stopY+offsetY
          }
        }
      }
      
      if(width<windowWidth && width<maxWidth) {
        x=0
        width -= width - windowWidth
      } else if(width<windowWidth) {
        if(x + width > windowWidth) {
          x -= x + width - windowWidth
        } else {
          if(x-offsetX<stopX) {
            x += windowWidth - x - width
          } else {
            x=stopX+offsetX
          }
        }
      } else {
        x=0
        width -= width - windowWidth
      }
      return {x, y, width, height}
    },
    openReply(event) {
      const offsetX = this.$refs.optionsBar.getBoundingClientRect().x 
      const offsetY = this.$refs.optionsBar.getBoundingClientRect().y
      var y = event.target.getBoundingClientRect().y
      var x = event.target.getBoundingClientRect().x
      var width = this.replyFormSettings[this.replyPage].width
      var height = this.replyFormSettings[this.replyPage].height

      const windowTransformed = this.ensureOnscreen(x,y,width,height,this.replyFormSettings[this.replyPage].width, 176, 16)
      x = windowTransformed.x -offsetX
      y = windowTransformed.y -offsetY
      width = windowTransformed.width
      height = windowTransformed.height

      this.replyFormTop = y
      this.replyFormLeft = x
      this.replyFormWidth = width
      this.replyFormHeight = height
      this.replyFormActive = true
    },
    openDownload(event) {
      const offsetX = this.$refs.optionsBar.getBoundingClientRect().x
      const offsetY = this.$refs.optionsBar.getBoundingClientRect().y
      var y = event.target.getBoundingClientRect().y
      var x = event.target.getBoundingClientRect().x
      var width = this.downloadFormSettings[this.downloadPage].width
      var height = this.downloadFormSettings[this.downloadPage].height

      const windowTransformed = this.ensureOnscreen(x,y,width,height,this.downloadFormSettings[this.downloadPage].width, 16, 16)
      x = windowTransformed.x -offsetX
      y = windowTransformed.y -offsetY
      width = windowTransformed.width
      height = windowTransformed.height
      this.downloadFormTop = y
      this.downloadFormLeft = x
      this.downloadFormWidth = width
      this.downloadFormHeight = height

      this.downloadFormActive = true
    },
    replyGoBack() {
      this.replyPage -= 1
    },
    replyGoForward() {
      this.replyPage += 1
    },
    handleError(error, action) {
      this.$refs.submitButton.error = true
      if(error.response.status==400) {
        error.response.data.elements.forEach(el=>{
          this.$refs[el.id].customError = {message: el.message}
          this.$refs[el.id].deFocusedOnce = true
          this.$refs.submitButton.errorMessage = error.response.data.message
        })
      } else {
        this.emitter.emit('showErrorMessage', {error: error.response, action: action, redirect: null})
      }
    },
    handleSuccess() {
      this.$refs.submitButton.success = true
      this.$refs.reply_subject.clear()
      this.$refs.reply_content.value = ''
      this.$refs.email.clear()
      this.$refs.attachments.clear()
    },
    submitReplies() {
      const url = `${this.$store.getters.getApiUrl}/reply`
      this.replySubmitLoading = true
      var formData = new FormData(this.$refs.replyForm);
      if(this.replyRowsOption===0) {
        formData.append("submission_ids", JSON.stringify(this.dataDecorated.filter(row=>this.selectedRows.includes(row.table_id)).map(row=>row.id)))
      } else {
        formData.append("submission_ids", JSON.stringify(this.dataDecorated.map(row=>row.id)))
      }
      for(var i=0; i<this.replyAttachments?.length; i++) {
        formData.append('reply_attachments[]', this.replyAttachments[i].file)
      }
      formData.append('replyMailMode', this.replyMailOption)
      axios({
        method: 'post',
        url: url,
        data: formData,
        headers: {
          'Content-Type': 'multipart/form-data'
        }        
      }).then(response=>{
        this.replySubmitLoading = false
        console.log(response.data)
        this.handleSuccess()
      }).catch(e=>{
        console.log(e.response)
        this.replySubmitLoading = false
        this.handleError(e, 'Submit replies')
      })
    },
    jumpToReplyStep(step) {
      if((step==1 && this.replyRowsOption!==null) || (step==2 && this.replyMailOption!==null) || step==0) {
        this.replyPage = step
      }
    },
    jumpToStep(step) {
      if((step==1 && this.downloadRowsOption!==null) || (step==2 && this.downloadColumnsOption!==null) || step==0) {
        this.downloadPage = step
      }
    },
    close() {
      this.downloadFormActive = false
      this.replyFormActive = false
      this.downloadFormTop = 16
      this.downloadFormLeft = 16
      this.downloadFormWidth = 144
      this.downloadFormHeight = 45
      this.replyFormTop = 16
      this.replyFormLeft = 176
      this.replyFormWidth = 144
      this.replyFormHeight = 45
      this.downloadRowsOption = null
      this.downloadColumnsOption = null
      this.downloadPage = 0
      this.replyPage = 0
      this.replyRowsOption = null
      this.replyMailOption = null
    },
    downloadGoForward() {
      this.downloadPage += 1
    },
    downloadGoBack() {
      this.downloadPage -= 1
    },
    handleCardOption(card,option) {
      if(card==0){
        if(option===0 && this.selectedRows?.length<=0) {
          return
        }
        this.downloadRowsOption = option
        this.downloadPage =1
      } else if(card==1) {
        this.downloadColumnsOption = option
        this.downloadPage =2
      }
    },
    convertToCSV(objArray) {
        var array = typeof objArray != 'object' ? JSON.parse(objArray) : objArray;
        var str = '';
        Object.keys(array[0]).forEach(key=>{
          if(str!='')str +=';'
          str+=key
        })
        str +='\r\n'
        for (var i = 0; i < array.length; i++) {
            var line = '';
            for (var index in array[i]) {
                if (line != '') line += ';'

                line += array[i][index];
            }

            str += line + '\r\n';
        }

        return str;
    },
    rowDropdownMenuOffsetStyle(index) {
      var offset = 0
      if(this.rowDropdownMenuSettings.index!==null && index==this.rowDropdownMenuSettings.index+1) {
        offset = this.rowDropdownMenuSettings.height
      }
      if(this.rowDropdownMenuSettings.index===index && this.filter(this.dataDecorated).length===this.rowDropdownMenuSettings.index+1) {
        offset = this.rowDropdownMenuSettings.height
        return {
          'margin-bottom': offset+'px',
          'margin-top': '0px',
        }     
      }
      return {
        'margin-bottom': '0px',
        'margin-top': offset+'px',
      }
    },
    menuOptionClick(option, row) {
      if(option.twoStep && !this.menuOptionStep[option.id]) {
        this.menuOptionStep[option.id] = true
        return
      }
      delete this.menuOptionStep[option.id]
      this.$emit('menuClick', {option: option, row: row})
    },
    rowClick(event, row, index) {
      this.$emit('rowClick', row)
      if(this.rowMenuOptions===null || this.rowMenuOptions===undefined) {
        return
      }
      if(this.rowDropdownMenuSettings.index===index) {
        this.rowDropdownMenuSettings.index = null
        this.rowDropdownMenuSettings.row = null
        return
      }
      const offsetTop = this.$refs.grid.getBoundingClientRect().y
      if(this.rowDropdownMenuSettings.index===null || this.rowDropdownMenuSettings.index>index) {
        this.rowDropdownMenuSettings.top = event.target.getBoundingClientRect().y - offsetTop + event.target.getBoundingClientRect().height
      } else if(this.rowDropdownMenuSettings.index<=index) {
        this.rowDropdownMenuSettings.top = event.target.getBoundingClientRect().y - offsetTop + event.target.getBoundingClientRect().height - this.rowDropdownMenuSettings.height
      }
      this.rowDropdownMenuSettings.index = index
      const numOptions = this.rowMenuOptions.filter(option=>option.permission<=row[this.permissionKey] || option.permission===null).length
      const height = numOptions * this.rowDropdownMenuSettings.optionHeight + 2*this.rowDropdownMenuSettings.padding
      if(numOptions>0) {
        this.rowDropdownMenuSettings.height = height
      } else {
        this.rowDropdownMenuSettings.height = 0
      }
      this.rowDropdownMenuSettings.row = row
    },
    isAllRowsSelected() {
      const row_ids = this.filter(this.dataDecorated)?.map(row=>row.table_id)
      if(this.selectedRows?.length === row_ids?.length) {
        return true
      }
      return false
    },
    isRowSelected(row) {
      if(this.selectedRows.includes(row.table_id)) {
        return true
      }
      return false
    },
    selectAllRows(checked) {
      if(checked) {
        this.selectedRows = this.filter(this.dataDecorated).map(row=>row.table_id)
      } else {
        this.selectedRows = []
      }
    },
    selectRow(checked, row) {
      const index = this.selectedRows.indexOf(row.table_id)
      if(checked && index<0) {
        this.selectedRows.push(row.table_id)
      }
      if(!checked && index>=0) {
        this.selectedRows.splice(index, 1)
      }
    },
    filterSelectedRows() {
      this.selectedRows=this.selectedRows.filter(table_id=>this.filter(this.dataDecorated).map(row=>row.table_id).includes(table_id))
    },
    toString(value) {
      if(typeof value !== 'string') {
        return JSON.stringify(value)
      }
      return value
    },
    filter(data) {
      return data?.filter(entry=>{
        var vetoCol = false
        var vetoGlob = true
        this.filterColumnHeaders(this.columnHeaders).forEach(header=>{
          if(vetoCol) {
            return
          }
          const value = this.toString(entry[header])
          if(header in this.filters && this.filters[header].values.includes(value)) {
            vetoCol = true
          }
          if(value && (value.match(new RegExp(this.escapeCharacters(this.searchString), "i")) || this.searchString==='' || this.searchString===null || this.searchString===undefined)) {
            vetoGlob = false
          }
          // if(header in this.filters && (this.filters[header].values.includes(value) || (this.filters[header].string!=='' && this.filters[header].string!==null && !value.match(new RegExp(this.escapeCharacters(this.filters[header].string), "i"))))) {
          //   veto = true
          // }
        })
        return !vetoCol && !vetoGlob
      })
    },
    getFilterString(header) {
      if(header in this.filters) {
        return this.filters[header].string
      }
      return null
    },
    escapeCharacters(string) {
      string=string.replace('[', '\\[')
      string=string.replace(']', '\\]')
      string=string.replace('.', '\\.')
      return string
    },
    setFilterString(string, header) {
      if(header in this.filters) {
        this.filters[header].string=string
      } else {
        this.filters[header] = {values: [], string: string}
      }
      this.filterSelectedRows()
    },
    isCheckedAll(header) {
      if(header in this.filters && this.filters[header].values.length>0) {
        return false
      }
      return true
    },
    isCheckedSingle(header, value) {
      if(header in this.filters && this.filters[header].values.includes(value)) {
        return false
      } 
      return true
    },
    checkAll(checked, header) {
      if(!checked && header in this.filters) {
        this.filters[header].values = this.uniqueValues(header)
      } else if(!checked) {
        this.filters[header] = {values: this.uniqueValues(header), string: null}
      } else if(checked && header in this.filters) {
        this.filters[header].values = []
      }
      this.filterSelectedRows()
    },
    checkSingle(checked, header, value) {
      if(header in this.filters) {
        const index = this.filters[header].values.indexOf(value)
        if(!checked && index<0) {
          this.filters[header].values.push(value)
        }
        if(checked && index>=0) {
          this.filters[header].values.splice(index, 1)
        }
      } else {
        if(!checked) {
          this.filters[header] = {values: [value], string: null}
        }
      }
      this.filterSelectedRows()
    },
    decorateData(data) {
      this.dataDecorated = data?.map((row,index)=>{
        row['table_id'] = index
        return row
      })
    },
    uniqueValues(header) {
      return this.dataDecorated?.map(entry=>{
        if(typeof entry[header]!=='string') {
          return JSON.stringify(entry[header])
        }
        return entry[header]
      }).filter((value,index,self) => self.indexOf(value)==index)
    },
    closeFilter() {
      this.activeHeader = null
    },
    openFilter(header, event) {
      const offsetLeft = this.$refs.grid.getBoundingClientRect().x
      const offsetTop = this.$refs.grid.getBoundingClientRect().y
      if(event.target.getBoundingClientRect().x+this.filterWindowSettings.width>window.innerWidth) {
        this.filterWindowSettings.left = window.innerWidth - this.filterWindowSettings.width-offsetLeft-17
      } else {
        this.filterWindowSettings.left = event.target.getBoundingClientRect().x-offsetLeft
      }
      this.filterWindowSettings.top = event.target.getBoundingClientRect().y-offsetTop+event.target.getBoundingClientRect().height
      this.activeHeader = header
    },
    filterActive(header) {
      if(header in this.filters && (this.filters[header].values.length>0 || (this.filters[header].string!=='' && this.filters[header].string!==null))) {
        return true
      }
      return false
    },
    trimString(string, length) {
      if(Array.isArray(string) || typeof yourVariable==='object') {
        string = JSON.stringify(string)
      }
      if(string && string.length>length) {
        return `${string.slice(0, length-3)}...`
      }
      return string
    },
    filterColumnHeaders(columnHeaders) {
      if(!Array.isArray(this.list)) {
        return columnHeaders
      }
      var headers = []
      if(this.blacklist) {
        headers = columnHeaders.filter(header=>!this.list.includes(header))
      } else {
        headers = columnHeaders.filter(header=>this.list.includes(header))
      }
      return headers
    },
    orderColumnHeaders(columnHeaders) {
      if(!Array.isArray(this.order)) {
        return columnHeaders
      }
      const headers = columnHeaders.sort((a,b) => {
        const indexA = this.order.indexOf(a)
        const indexB = this.order.indexOf(b)
        if(indexA<indexB) {
          return 1
        } else if(indexA>indexB) {
          return -1
        } else {
          return 0
        }
      })
      return headers
    }
  },

}
</script>


<style scoped lang="scss">
.reply-submit-button {
  width: 100%;
}
.file-upload {
  width: 100%;
}
.text-area-container {
  label {
    display: inline-block;
    width: 100%;
    text-align: start;
  }
  .reply-textarea {
    height: 5.9rem;
    width: 100%;
    font-family: inherit;
  }
  width: 100%;
}
.steps-overview {
  bottom: 0;
  padding: 1rem .5rem;
  transform: translateY(100%);
  position: absolute;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  z-index: 21;
  .step-indicator {
    position: relative;
    font-size: 0.75rem;
    color: #2C3E50;
    cursor: pointer;
    // border: 1px solid #2C3E50;
    font-weight: bold;
    white-space:nowrap;
    border-radius: 16px;
    &.active {
      // background-color: rgb(93%,76%,45%);
      color: #E39E21;
      &:hover {
        &::before {
          border: 1px solid #E39E21;
        }
      }

    }
    &:hover {
      &::before {
      width: 100%;
      border: 1px solid #2C3E50;
      max-width: 100%;
      }
    }
    &::before {
      transition: width .2s ease-in-out;
      content: '';
      position: absolute;
      border: 0px solid #2C3E50;
      bottom: -2px;
      left: 0;
      width: 0;
      max-width: 40%;
    }
  }
  .step-connector {
    border: 1px solid #2C3E50;
    height: 0;
    width: 100%;
    margin: 0 4px;
    border-radius: 10px;
    &.active {
      border: 1px solid #E39E21;
    }
  }
}
// .step-2 {
//   display: flex;
//   flex-direction: row;
//   gap: 20px;
//   max-width: 220px;
// }
.card-option {
  position: relative;
  &.disabled {
    cursor: not-allowed;
  }
  .error-span {
    position: absolute;
    bottom: 0;
    left: 0;
    transform: translateY(100%);
    color: #ff1744;
    font-size: .75rem;
  }
  font-size: 1em;
  border: 2px solid  rgba(44, 62, 80, .2);
  width: 100%;
  color: #2C3E50;
  padding: .5rem;
  border-radius: 0px;
  height: 38px;
  display: flex;
  justify-content: center;
  align-items: center;
  border-radius: 8px;
  cursor: pointer;
  &.active {
    background: rgba(213, 244, 255, 1);
  }
  &:hover:not(.disabled) {

    border: 2px solid  rgba(44, 62, 80, 1);
  }
}
.download-form-go-forward {
  position: absolute;
  margin: 0;
  bottom: 2px;
  right: 2px;
  width: 24px;
  height: 24px;
  z-index: 22;
  cursor: pointer;
  .forward-icon {
    position: absolute;
    color: white;
    top: 50%;
    left: 50%;
    width: 23px;
    height: 23px;
    transform: translate(-50%, -50%);
  }
}
.download-form-go-back {
  position: absolute;
  margin: 0;
  bottom: 2px;
  left: 2px;
  width: 24px;
  height: 24px;
  z-index: 22;
  cursor: pointer;
  .back-icon {
    position: absolute;
    color: white;
    top: 50%;
    left: 50%;
    width: 23px;
    height: 23px;
    transform: translate(-50%, -50%);
  }
}
.download-link {
  width: 100%;
  display: flex;
  justify-content: center;
  align-items: center;
  text-decoration: none;
  color: #2C3E50;
  // background: #E39E21;
  background: rgb(93%,76%,45%);
  // outline: 1px solid rgb(93%,76%,45%);
  height: 38px;
  font-size: 1em;
  font-weight: 500;
  text-align: center;
  &:hover {
    background: rgb(94%,79%,53%);
  }
}
.download-form-overlay {
  position: fixed;
  left: 0;
  top: 0;
  right: 0;
  bottom: 0;
  z-index: 20;
  // background: rgba(0,0,0,0.1);
  // backdrop-filter: blur(1px);
}
.download-form.active {
  visibility: visible;
  .form-header {
    height: 60px;
    font-weight: 500;
    .text {
      color: white;
      font-size: 24px;
    }
    .icon {
      font-size: 32px;
      color: white;
    }
  }
}
.reply-form.active {
  visibility: visible;
  overflow-y: auto; 
  .form-header {
    height: 60px;
    font-weight: 500;
    .text {
      color: white;
      font-size: 24px;
    }
    .icon {
      font-size: 32px;
      color: white;
    }
  }
}
.download-form,.reply-form {
  
  .filename-input {
    width: 100%;
  }
  .close-download-form-button {
    position: absolute;
    margin: 0;
    top: 2px;
    right: 2px;
    width: 24px;
    height: 24px;
    z-index: 22;
    cursor: pointer;
    .close-icon {
      position: absolute;
      color: white;
      top: 50%;
      left: 50%;
      width: 28px;
      height: 28px;
      transform: translate(-50%, -50%);
    }
  }
  position: absolute;
  z-index: 21;
  background: rgba( 255, 255, 255, 1);
  box-shadow: 0 8px 8px -4px rgba( 31, 38, 135, 0.37 );
  overflow-x:hidden;
  // width: 144px;
  // height: 45px;
  border: 1px solid #2C3E50;
  transition: width .2s ease-in-out, height .2s ease-in-out, visibility .2s ease-in-out, left .2s ease-in-out, top .2s ease-in-out;
  visibility: hidden;
  .form-header {
    z-index: 22;
    transition: height .2s ease-in-out;
    position: relative;
    height: 45px;
    display: flex;
    flex-direction: row;
    align-items: center;
    justify-content: center;
    padding: .5rem 1rem;
    gap: .5rem;
    background: #2C3E50;
    color: white;
    h2 {
      font-size: 1.5rem;
      margin: 0px;
    }
    .text {
      transition: font-size .2s ease-in-out;
      color: white;
    }
    .icon {
      transition: font-size .2s ease-in-out;
      color: white;
      font-size: 25px;
    }
    color: white;
  }
  .form-body {
    position: relative;
    width: 100%;
    margin-top: 46px;
    height: calc(100% - 46px - 60px);
    // display: grid;
    // grid-template-columns: 100% 100% 100%;
    // grid-template-rows: 100%;
    // overflow: hidden;
  }
  .card {
    transform: translate(calc( (-100 * var(--card)) * 1% ), 0%);
    transition: transform .4s ease-in;
    position: absolute;
    width: 100%;
    padding: 1rem;
    h4 {
      font-size: 1rem;
      margin: 0;
      padding: 0;
      text-align: start;
      width: 100%;
    }
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
    gap: 1rem;
  }
  .card-1 {

    // background: rgba(255,0,0,0.2);
  }
  .card-2 {
    right: -100%;
    // background: rgba(0,255,0,0.2);
  }
  .card-3 {
    right: -200%;
    // background: rgba(0,255,0,0.2);
  }
}
.table-options-wrapper {
  position: relative;
  display: flex;
  padding: 1rem;
}
.table-options-bar {
  position: relative;
  display: flex;
  flex-direction: row;
  align-items: center;
  gap: 1rem;
}
.options-bar-button {
  position: relative;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
  color: #2C3E50;
  aspect-ratio: 3.2;
  width: 144px;
  border-radius: 0px;
  padding: 0.5rem 1rem;
  gap: 0.5rem;
  // border: 1px solid rgba(255,255,255,0.2);
  outline: 1px solid rgba(0,0,0,0.1);
  &:hover {
    // // // background: rgba(255,255,255,0.2);
    background: #2C3E50;
    color: white;
    cursor: pointer;
  }
  .options-bar-button-text {
    font-size: 1rem;
    pointer-events: none;
  }
  .options-bar-button-icon {
    font-size: 25px;
    pointer-events: none;
  }
}

.row-dropdown-menu {
  > * {
    padding-left: var(--optionOffsetLeft)
  }
  position: absolute;
  width: 100%;
  box-shadow: inset 2px 2px 4px 0px #b3b3b3,
            inset -2px -2px 5px -1px #ffffff;
  z-index:1;
  background-color: white;
  .row-dropdown-menu-option {
    width: 100%;
    display: flex;
    flex-direction: row;
    align-items: center;
    height: var(--optionHeight);
    cursor: pointer;
    border-radius: 4px;
    padding: 0 4px;

    &:hover {
      background: var(--background-hover);
    }
    .row-dropdown-menu-icon {
      font-size: calc(var(--optionHeight) - 8px);
    }
    .row-dropdown-menu-text {
      margin-left: 8px;
      font-size: 1em;
    }
  }
}
.json-to-table {
  position: relative;
  display: grid;
  z-index: 1;
}
.grid-row {
  &.active {
    > .grid-item {
      background: rgb(97%,89%,74%) !important;
    }
  }
  display: contents;
  &:nth-child(2n+1) {
    >.grid-item {
      background-color: #eeeeee;
      &.warning {
        background: rgba(255, 193, 7, 0.9);
      }
      &.error {
        background: rgba(220, 53, 69, 0.9);
      }
    }
  }
  &:nth-child(2n) {
    >.grid-item {
      background-color: #ffffff;
      &.warning {
        background: rgba(255, 193, 7, 0.6);
      }
      &.error {
        background: rgba(220, 53, 69, 0.6);
      }
    }
  }
}
.grid-item {
  position: relative;
  padding: 12px 39px;
  min-width: 26mm;
  width: 100%;
  // text-align: start;
  &.max-width {
    max-width: 24rem;
  }
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: flex-start;
  z-index: 2;
  // word-wrap: break-word;
  text-align: left;
  transition: margin .3s ease;
  overflow: hidden;
  max-height: 10rem;
  cursor: var(--cursor);
}
.edit-filter-window {
  background-color: white;
  border-radius: 8px;
  position: absolute;
  color: #2C3E50;
  overflow-x: hidden;
  z-index: 4;
  > .filter-window-header,.filter-window-body {
    padding-left: 8px;
    padding-right: 8px;
  }
  box-shadow:  5px 5px 10px rgba(102,102,102,0.2),
              -5px -5px 10px rgba(255, 255, 255, 0.2);
  .filter-window-header {
    position: relative;
    background-color: white;
    // box-shadow:  0px 4px 4px -2px rgba(165, 155, 255, .2);
    // z-index: -1;
    // padding-bottom: 6px;
  }
  .select-single {
    margin: 8px 0;
  }
  .select-all {
    margin-top: 4px;
  }
  .filter-window-options-bar {
    position: relative;
    z-index: 20;
    background-color: white;
    display: flex;
    flex-direction: row;
    justify-content: flex-end;
  }
  .close-filter-window-button {
    position: relative;
    margin: 0;
    width: 24px;
    height: 24px;
    cursor: pointer;
    .close-icon {
      position: absolute;
      color: #2C3E50;
      top: 50%;
      left: 50%;
      width: 28px;
      height: 28px;
      transform: translate(-50%, -50%);
    }
  }

}
.edit-filter-window-overlay {
  position: fixed;
  top: 0;
  left: 0;
  bottom: 0;
  right: 0;
  z-index: 3;
}
.header {
  background-color: #00876c;
  color: white;
  .icon-button {
    position: absolute;
    right: 4px;
    display: flex;
    justify-content: center;
    align-items: center;
    padding: 0 4px;
    border-radius: 2px;
    border: 1px solid white;
    cursor: pointer;
    &.active {
      background-color: white;
      .icon {
        color: #00876c;
      }
    }
    &.open {
      .icon {
        transform: rotate(180deg);
      }
    }
    .icon {
      pointer-events: none;
      transition: transform .3s cubic-bezier(.4,0,.2,1);
      width: 19px;
      height: 19px;
    }
  }

}
.row-notification {
  position: absolute;
  z-index: 3;
  left: 4px;
  top: -8px;
  .row-notification-icon {
    color: rgba(220, 53, 69, 1);
    transform: scaleX(-1);
    font-size: 2rem;
  }
  .row-notification-text {
    color: white;
    position: absolute;
    // border: 1px solid black;
    font-size: 0.9rem;
    text-align: center;
    width: 2rem;
    top: 4.5px;
    left: 0px;
  }
}
.row-option-loading {
  text-align: left;
}
.searchbar {
  // border: 1px solid red;
  align-items: flex-start;
  text-align: left;
  width: 100%;
  display:flex;
  flex-direction: column;
  gap: 4px;
  .searchbar-input {
    width: 100%;
    max-width: 210mm;
    font-size: 18px;
    padding: 8px 16px;
    border-radius: 50px;
    // outline: none;
    border: 1px solid black;
  }
}
</style>
