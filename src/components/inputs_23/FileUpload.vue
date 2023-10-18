<template>
  <div class="file-upload">
    <div class="title" :class="{'show-error':show_error}">
      <span>{{label}}</span>
      <span v-if="required">&nbsp;*</span>
      <span v-else class="optional">(optional)</span>
    </div>
    <label :for="`${name}_file`" class="drag-and-drop" :class="{'show-error':show_error}" @drop.prevent="drop($event)" @dragenter.prevent @dragleave.prevent @dragover.prevent>
      <div class="empty" v-if="empty">
        <span class="icon">
          <ion-icon name="cloud-upload-outline"></ion-icon>
        </span>
      </div>
      <div class="preview-list" v-else>
        <div class="preview-file" v-for="(file,index) in file_list" :key="index" @click.prevent>
          <button class="remove-button" @click.prevent="removeItem(index)">
            <ion-icon name="close-outline"></ion-icon>
          </button>
          <div class="document-icon">
            <ion-icon name="document-outline"></ion-icon>
          </div>
          <span class="file-name">{{trimStr(file.name)}}</span>
        </div>
      </div>
      <span class="info-text">Drag and drop files here or click to browse from device</span>

    </label>
    <div class="footer">
      <div class="error" :class="{'show-error':show_error}">{{error?.message}}</div>
    </div>
    <input :id="`${name}_file`" type="file" ref="file" multiple @change="browse($event)">
  </div>
</template>

<script>
export default {
  name: 'FileUpload',
  props: {
    label: String,
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
    name: String,
  },
  data() {
    return  {
      file_list: [],
      customError: null,
      deFocusedOnce: false,
      internal_custom_error: null,
      valid_file_processing: true,
    }
  },
  computed: {
    empty() {
      return this.file_list.length>0 ? false : true
    },
    error() {
      if(this.customError!==null) {
        return this.customError
      }
      if(this.internal_custom_error!==null) {
        return this.internal_custom_error
      }
      if(this.required && this.empty) {
        return this.validationSettings.default.error_types.required_error
      }
      if(!this.valid_file_processing) {
        return {message: this.file_processing_message}
      }
      return null
    },
    show_error() {
      return this.error && this.deFocusedOnce
    },
  },
  methods: {
    setError(error) {
      this.customError = error
      this.deFocusedOnce = true
    },
    clear() {
      this.file_list = []
      this.deFocusedOnce = false
      this.internal_custom_error = null
    },
    removeItem(index) {
      this.file_list.splice(index, 1)
      this.$emit('fileChange', this.file_list)
    },
    trimStr(str) {
      if(str.length>20) {
        return '...'+str.slice(-17)
      }
      return str
    },
    validateFile(file) {
      // if(file===null || file===undefined) {
      //   this.internal_custom_error = null
      //   this.file = null
      //   return
      // }
      if(!this.mime_types.includes(file.type) && !this.mime_types.includes('any')) {
        this.internal_custom_error = {message: 'Only files of the following type are allowed: '+this.mime_types.join(" ")}
        return false
      }
      if(file.size>this.size*1e6) {
        this.internal_custom_error = {message: 'Maximum files size is 4MB'}
        return false
      }
      if(this.fileProcessingFunction) {
        this.internal_custom_error = this.fileProcessingFunction(file)
        if(this.internal_custom_error!=null) {
          return false
        }
      }
      return true
    },
    browse(event) {
      this.deFocusedOnce = true
      this.internal_custom_error = null
      event.target.files?.forEach(file=>{
        if(!this.validateFile(file)) {
          return
        }
        this.file_list.push(file)
      })
      this.$refs.file.value = null
      this.$emit('fileChange', this.file_list)
    },
    drop(event) {
      this.deFocusedOnce = true
      this.internal_custom_error = null
      event.dataTransfer.files.forEach(file=>{
        if(!this.validateFile(file)) {
          return
        }
        this.file_list.push(file)
      })
      this.$emit('fileChange', this.file_list)
    }
  }
}
</script>


<style scoped lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';
.title {
  font-size: 12px;
  height: 12px;
  &.show-error {
    color: #ff1744;
  }
  display: flex;
  flex-direction: row;
  .optional {
    margin-left: auto;
    color: gray;
  }
}
.footer {
  height: 12px;
  text-align: left;
  font-size: 12px;
}
.file-upload {
  height: 116px;
}
.info-text {
  font-weight: bold;
}
.remove-button {
  border-top-right-radius: 4px;
  position: absolute;
  cursor:pointer;
  top: 0;
  right: 0;
  font-size: 24px;
  height: 24px;
  &:hover {
    background-color: $text_red;
  }
}
.document-icon {
  font-size: 32px;
  padding: 0 20px;
}
.file-name {
  font-size: 14px;
}
.preview-file {
  border: 1px solid rgba(0,0,0,0.3);
  border-radius: 4px;
  padding: 2px;
  position: relative;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  cursor: default;
  &:hover {
    border: 1px solid black;
  }
}
.preview-list {
  gap: 4px;
  display: flex;
  flex-direction: row;
  justify-content: center;
  align-items: center;
}
.error {
  height: 12px;
  font-size: 12px;
  color: #ff1744;
  transform: translateY(-100%);
  opacity: 0;
  &.show-error {
    opacity: 1;
    transform: translateY(0);
    transition: opacity ease .3s, transform ease .3s;
  }
}
.browse {
  border: 1px solid black;
}
.drag-and-drop {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;

  border: 2px dashed black;
  // height: 100px;
  width: 100%;

  padding: 4px;
  cursor: pointer;
  &.show-error {
    border-color: #ff1744;
  }
}
.empty {
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
  gap: 4px;
  font-weight: bold;
}
.icon {
  font-size: 32px;
  height: 62px;
  display: flex;
  justify-content: center;
  align-items: center;
}
.browse {
  height: 10mm;
  padding: 0 8px;
  display: flex;
  text-align: center;
  justify-content: center;
  align-items: center;
}
input {
  display: none;
}
</style>
