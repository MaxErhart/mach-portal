<template>
  <div class="input-element" :class="{'has-tooltip':tooltip}">
    <div class="before" :style="{right: 100 - uploadPercentage+'%'}"></div>
    <label class="file-upload-label" :for="elementId" :class="{'focus': isFocused, 'error': showError, }">
      {{labelName}}&nbsp;<span class="required-span" v-if="required">*</span>
    </label>
    <input :id="elementId" :name="elementId" type="file" ref="file" @change="fileU()">
    <label :for="elementId" class="email-content-lable" :class="{'file': fileUploaded}">
      <template v-if="!fileUploaded">
        <span><img :src="require(`@/assets/upload.svg`)"></span>
        <span>Upload File</span>
      </template>
      <template v-else >{{filename}}</template>
    </label>
    <span class="tooltip-element">{{tooltip}}</span>
    <span class="element-error" :class="{'error': showError, 'has-tooltip':tooltip}">Field required</span>

  </div>
</template>

<script>
import * as validationSettings from '../validationSettings.json'

export default {
  name: 'FileUploadElement',
  props: {
    preset: Boolean,
    elementId: String,
    labelName: String,
    tooltip: String,
    required: Boolean,
    uploadPercentage: Number,
  },
  data() {
    return {
      validationSettings,
      filename: null,
      fileUploaded: false,
      isFocused: false,
      deFocusedOnce: false,
    }
  },
  computed: {
    error() {
      const valid = this.validationSettings.default.valid
      if(this.required && !this.filename) {
        return validationSettings.default.error_types.required_error
      } else {
        return valid
      }
    },
    hasError() {    
      const valid = this.validationSettings.default.valid
      if(this.error != valid) {
        return true
      } else {
        return false
      }
    },
    showError() {
      if(this.deFocusedOnce && this.hasError) {
        return true
      } else {
        return false
      }
    }
  },
  methods: {  
        mounted() {
      let recaptchaScript = document.createElement('script')
      recaptchaScript.setAttribute('src', 'https://cdn.jsdelivr.net/npm/vue/dist/vue.js')
      document.head.appendChild(recaptchaScript)
    }, 
    focus() {
      this.$refs.file.focus()
    }, 
    blur() {
      this.deFocusedOnce = true
    },
    fileU() {
      const file = this.$refs['file'].value.split('\\')
      if(file[file.length-1] == "") {
        this.fileUploaded = false
      } else {
        this.fileUploaded = true
        this.filename = file[file.length-1]
      }
    }
  },
}
</script>


<style scoped lang="scss">
.input-element {
  position: relative;
  display: flex;
  font-family: inherit;
  margin: 4px 12px 24px 12px;
  padding-top: 16px;
  height: 100%;
  min-height: 48px;
  z-index: 1;
  &.has-tooltip {
    margin: 4px 12px 36px 12px;
  }  
}
.before {
  position: absolute;
  bottom: 0;
  left: 0;
  z-index: 2;
  background-color: #00876c;
  height: 2px;
}
.file-upload-label {
  position: absolute;
  top: 0px;
  left: 0px;
  pointer-events: none;
  transition: .4s cubic-bezier(.25,.8,.25,1);
  transition-duration: .3s;
  font-size: 12px;
  line-height: 20px;
  color: rgba(0,0,0,.54);
  &.error {
    color: #ff1744;
  }
}
.element-error {
  display: block!important;
  left: 0;
  opacity: 0;
  transform: translate3d(0,-8px,0);
  pointer-events: none;
  height: 20px;
  position: absolute;
  bottom: -22px;
  font-size: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  &.error {
    color: #ff1744;
    opacity: 1;
    transform: translateZ(0);      
  }
  &.has-tooltip {
    bottom: -36px;
  }
}

.tooltip-element {
  display: block!important;
  left: 0;
  opacity: 1;
  transform: translate3d(0,-8px,0);
  pointer-events: none;
  height: 20px;
  position: absolute;
  bottom: -22px;
  font-size: 12px;
  transition: .3s cubic-bezier(.4,0,.2,1);
  transform: translateZ(0);
}
input {
  opacity: 0;
  position: absolute;
}
label {
  display: flex;
  align-items: center;
  margin: 2px 0;
  text-align: start;
  width: 100%;
}

.email-content-lable {
  position: relative;
  height: 32px;
  display: flex;
  align-items: center;
  background-color: #fafafa;

  text-align: start;
  box-shadow: 0 3px 1px -2px rgba(0,0,0,.2),0 2px 2px 0 rgba(0,0,0,.14),0 1px 5px 0 rgba(0,0,0,.12);    
  cursor: pointer;
  justify-content: center;
  &.file {
    color:#00876c;
  }
  > span:first-child {
    margin-right: 5px;
  }
  &:hover {
    filter: brightness(90%);
  }  
}

</style>