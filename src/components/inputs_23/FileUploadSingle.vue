<template>
  <div class="file-upload-single">


    <div v-if="!required" class="optional">(optional)</div>


    <div class="body">
      <label :for="`${name}_file`" class="file-upload-button" :class="{readonly}" @click="awaitDefocuse=true">
        <div class="icon-wrapper">
          <ion-icon name="cloud-upload-outline"></ion-icon>
        </div>
        <span class="browse">Browse files</span>
        <span class="title" :class="{'show-error':show_error, active:file}" :style="style_title">
          <span>{{label}}</span>
          <span v-if="required">&nbsp;*</span>
        </span>
        <span class="title-size" :class="{active:file}">
          <span>{{label}}</span>
          <span v-if="required">&nbsp;*</span>
        </span>        
      </label>
      <span v-if="file && !awaiting_fileprocessing" class="file-name">{{trim(file.name)}}</span>
      <span v-else class="file-name" :class="{readonly}">No file selected</span>
      <span v-if="file && upload_progress>0 && upload_progress!=null && upload_progress!=undefined">Uploading {{upload_progress*100}}%</span>
      <button v-if="file && !awaiting_fileprocessing" @click.prevent="removeFile()" class="remove-file">
        <ion-icon name="trash-outline"></ion-icon>
      </button>
    </div>
    <div class="footer">
      <div class="error" :class="{'show-error':show_error}" v-html="error"></div>
    </div>


    <input :name="name" type="file" ref="file" :id="`${name}_file`" @change="browse($event)" :disabled="readonly">
  </div>
</template>

<script>
import * as validationSettings from '@/validationSettings.json'
// import DataPlaceholder from '@/components/DataPlaceholder.vue'
export default {
  name: 'FileUploadSingle',
  components: {
    // DataPlaceholder,
  },
  props: {
    label: String,
    name: String,
    upload_progress: Number,
    required: {
      default: false,
      type: Boolean,
    },
    mime_types: {
      default: ()=>['any'],
      type: Array,
    },
    size: {
      default: 4,
      type: Number,
    },
    fileProcessingFunction: Function,
    readonly: Boolean,
  },
  data() {
    return {
      validationSettings,
      file: null,
      customError: null,
      internal_custom_error: null,
      arb_index: 0,
      valid_file_processing: true,
      file_processing_message: null,
      deFocusedOnce: false,
      awaitDefocuse: false,
      awaiting_fileprocessing: false,
    }
  },
  computed: {
    style_title() {
      return {
        // 'transform': 'translateY(-50%)',
        // 'top': 'calc(50%)',
        // 'left': '36px',
        // 'font-size': '16px',
      }
    },
    show_error() {
      return this.error && this.deFocusedOnce
    },
    error() {
      if(this.customError!==null) {
        return this.customError
      }
      if(this.internal_custom_error!==null) {
        return this.internal_custom_error
      }
      if(this.required && this.file===null || this.file===undefined) {
        return this.validationSettings.default.error_types.required_error.message
      }
      return null
    },
  },
  watch: {
    awaiting_fileprocessing(to) {
      if(to) {
        return
      }
      this.internal_custom_error = null
      if(this.valid_file_processing) {
        this.$emit('upload', this.file)
      } else {
        this.internal_custom_error = this.file_processing_message
        this.removeFile()
      }
    }
  },
  methods: {
    clear() {
      this.removeFile()
      this.deFocusedOnce = false
      this.awaitDefocuse = false
      this.awaiting_fileprocessing = false
      this.valid_file_processing = true
      this.file_processing_message = null
      this.customError = null
      this.internal_custom_error = null
    },
    removeFile() {
      this.file = null
      this.$refs.file.value = null
    },
    setError(error) {
      this.customError = error
      this.deFocusedOnce = true
    },
    trim(string) {
      if(string.length>20) {
        return `...${string.slice(-17)}`
      }
      return string
    },
    browse(event) {
      if(this.awaitDefocuse) {
        this.deFocusedOnce = true
      }
      if(event.target.files[0]===null || event.target.files[0]===undefined) {
        this.internal_custom_error = null
        this.removeFile()
        return
      }
      if(!this.mime_types.includes(event.target.files[0].type) && !this.mime_types.includes('any')) {
        this.internal_custom_error = 'Only files of the following type are allowed: '+this.mime_types.join(" ")
        this.removeFile()
        return
      }
      if(event.target.files[0].size>this.size*1e6) {
        this.internal_custom_error = 'Maximum files size is 4MB'
        this.removeFile()
        return
      }
      if(this.fileProcessingFunction) {
        this.file=event.target.files[0]
        this.fileProcessingFunction(event.target.files[0], event)
        return
      }
      this.internal_custom_error = null
      this.file=event.target.files[0]
      this.$emit('upload', this.file)

    }
  }

}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.file-upload-single {
  position: relative;
}

.optional {
  position: absolute;
  color: gray;
  right: 0px;
  font-size: 12px;
}
.body {
  margin-top: 12px;
  display: flex;
  position: relative;
  align-items: center;
  gap: 16px;
  // &:hover {
    .browse {
      color: inherit;
    }
    .title {
      left: 0;
      top: -6px;
      font-size: 12px;
      &.active {
        color: $kit_green;
      }
    }
  // }
}
.browse {
  position: absolute;
  color: transparent;
  transition: color .3s ease;
  left: 36px;
}
.title {
  position: absolute;
  left: 36px;
  top: 50%;
  color: inherit;
  transform: translateY(-50%);
  transition: top .3s ease, left .3s ease, font-size .3s ease;
  &.show-error {
    color: #ff1744 !important;
  }
}

.title-size {
  position: relative;
  visibility: hidden;
}
// .title {
//   position: absolute;
//   font-size: 12px;
//   height: 12px;
//   &.show-error {
//     color: #ff1744;
//   }
// }
.file-name {
  min-width: 138px;
  white-space: nowrap;
  font-weight: bold;
  &.readonly {
    color: gray;
    cursor: not-allowed;
  }
}
.footer {
  height: 12px;
  font-size: 12px;
  pointer-events: none;
}
.remove-file {
  display: flex;
  align-items: center;
  justify-content: center;
  width: 38px;
  height: 38px;
  font-size: 32px;
  border-radius: 4px;
  border: 1px solid #ccc;
  backdrop-filter: invert(5%);
  cursor: pointer;
  &:hover {
    color: #ff1744 !important;
  }
}
.error {
  height: 12px;
  font-size: 12px;
  color: #ff1744;
  transform: translateY(-100%);
  opacity: 0;
  white-space: pre-line;
  &.show-error {
    opacity: 1;
    transform: translateY(0);
    transition: opacity ease .2s, transform ease .2s;
  }
}
.file-upload-button {
  &.readonly {
    color: gray;
    cursor: not-allowed;
  }
  min-width: 132px;
  position: relative;
  border-radius: 2px;
  height: 38px;
  border: 1px solid black;
  display: flex;
  flex-direction: row;
  justify-content: flex-start;
  align-items: center;
  gap: 4px;
  padding: 0 8px;
  cursor: pointer;
  div {
    font-size: 24px;
    width: 24px;
    height: 24px;
    display: inline-block;
  }
  &:hover {
    backdrop-filter: invert(15%);
  }
}
input {
  display: none;
}
</style>
