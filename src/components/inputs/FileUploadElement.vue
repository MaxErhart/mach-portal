<template>
  <div class="input-element" :class="{disabled}">
    <div class="item">
      <label class="file-upload-label" :class="{error: customError, disabled: disabled}" :for="name">{{label}}
        <span class="required-span" v-if="required">*</span>
      </label>
      <input :disabled="disabled" :id="name" type="file" ref="file" @change="fileU($event)" multiple>
      <div class="drag-and-drop" :class="{tiny,disabled}" @drop.prevent="dropLink($event)" @dragenter.prevent @dragleave.prevent @dragover.prevent>

        <div class="drag-and-drop-preview">
          <div v-if="files.length<=0" class="text" :class="{disabled}">drag and drop files</div>
          <div v-for="(file, key) in files" class="file-listing" :key="file" :class="{tiny, uploading: uploadKeys?.includes(file.id)&&uploadPercentage<100, success: uploadPercentage>=100, error: uploadError?uploadError[key]:false}">
            <img class="delete" :src="require(`@/assets/delete.svg`)" @click="removeFile(key)">
            <img class="preview" :src="fileImagePreview[key]" v-if="!tiny">
            <div class="file-name">{{trimName(file.file.name)}}</div>


            <div class="svg-loading-container" v-if="uploadKeys?.includes(file.id)&&uploadPercentage<100">
              <svg width="98" height="98" viewBox="0 0 98 98" fill="none" xmlns="http://www.w3.org/2000/svg">
                <circle class="circle-background" cx="49" cy="49" r="40" stroke="white" stroke-width="18"/>
                <path stroke-linecap="round" class="spinner-second-half" :style="{'--progress': progress(1)}" d="M49 89C38.3913 89 28.2172 84.7857 20.7157 77.2843C13.2143 69.7828 9 59.6087 9 49C9 38.3913 13.2143 28.2172 20.7157 20.7157C28.2172 13.2143 38.3913 9 49 9" stroke="url(#paint0_linear_2_9)" stroke-width="10"/>
                <path stroke-linecap="round" class="spinner-first-half" :style="{'--progress': progress(0)}" d="M49 9C54.2529 9 59.4543 10.0346 64.3073 12.0448C69.1604 14.055 73.5699 17.0014 77.2843 20.7157C80.9986 24.4301 83.945 28.8396 85.9552 33.6927C87.9654 38.5457 89 43.7471 89 49C89 54.2529 87.9654 59.4543 85.9552 64.3073C83.945 69.1604 80.9986 73.5699 77.2843 77.2843C73.5699 80.9986 69.1604 83.945 64.3073 85.9552C59.4543 87.9654 54.2529 89 49 89" stroke="url(#paint1_linear_2_9)" stroke-width="10"/>
                <defs>
                  <linearGradient id="paint0_linear_2_9" x1="49" y1="9" x2="49" y2="89" gradientUnits="userSpaceOnUse">
                    <stop offset="0.2" stop-color="#A59BFF" stop-opacity="1"/>
                    <stop offset="1" stop-color="#5ed8ff" stop-opacity="1" />
                  </linearGradient>
                  <linearGradient id="paint1_linear_2_9" x1="49" y1="9" x2="49" y2="89" gradientUnits="userSpaceOnUse">
                    <stop offset="0" stop-color="#C8FCEA" stop-opacity="1"/>
                    <stop offset=".8" stop-color="#61dbff" stop-opacity="1"/>
                  </linearGradient>
                </defs>
              </svg>
            </div>
            <div class="loading-text" v-if="uploadKeys?.includes(file.id)&&uploadPercentage<100">loading...</div>


          </div>
        </div>
        <div class="upload-button-container">
          <label :for="name" class="email-content-lable" :class="{disabled}" >
            <span>Select Files</span>
          </label> 
        </div>
      </div>
    </div>
    <div class="input-tooltip" :style="tooltipStyle" v-if="tooltip">{{tooltip}}</div>
    <Transition name="slide">
      <div class="input-error" v-if="customError">
        {{customError.message}}
      </div>
    </Transition>
  </div>    
</template>

<script>
export default {
  name: 'FileUploadElement',
  props: {
    label: String,
    uploadKeys: Array,
    required: Boolean,
    tooltip: String,
    uploadPercentage: Number,
    uploading: Boolean,
    name: String,
    uploadError: Object,
    disabled: {
      default: false,
      type: Boolean,
    },
    tiny: {
      default: false,
      type: Boolean,
    },
  },
  emits: [
    'fileChange'
  ],
  data() {
    return {
      files:  [],
      fileImagePreview: [],
      deFocusedOnce: false,
      customError: null,
    }
  },
  computed: {
    tooltipStyle() {
      var margin = 16
      if(this.customError) {
        margin = 0
      }
      return {
        'margin': `0 0 ${margin}px 0`,
      }
    },
  },
  methods: {
    progress( half) {
      if(half==0) {
        if(this.uploadPercentage<50){
          return `${this.uploadPercentage}`
        } else {
          return `${50}`
        }
      } else {
        if(this.uploadPercentage<50){
          return `${0}`
        } else {
          return `${this.uploadPercentage-50}`
        }
      }
    },
    fileUploading() {
      if(this.uploadPercentage>0 && this.uploadPercentage<100) {
        return true
      }
      return false
    },
    clear() {
      this.files = []
      this.fileImagePreview = []
    },
    trimName(fileName) {
      if(fileName.length>20) {
        return `...${fileName.slice(-17)}`
      }
      return fileName
    },
    removeFiles(files) {
      this.files = this.files.filter(f=>!files.map(remove=>remove.id).includes(f.id))
      this.$emit('fileChange', this.files)
    },
    removeFile(key) {
      this.files.splice(key,1)
      this.$emit('fileChange', this.files)
    },
    handleFile(file) {
      if(this.uploading) {
        return
      }
      this.files.push({file: file, id: `${file.lastModified}+${file.name}+${file.size}+${file.type}`})
      if(/\.(jpe?g|png|gif)$/i.test(file.name)) {
        let reader = new FileReader();
        reader.readAsDataURL(file)
        reader.onload = function() {
          this.fileImagePreview.push(reader.result)
        }.bind(this)     
      } else {
        this.fileImagePreview.push(require('@/assets/file.svg'))
      }
    },
    dropLink(event) {
      if(this.disabled) {
        return
      }
      if(this.uploading) {
        return
      }
      for(let i = 0; i<event.dataTransfer.files.length; i++) {
        this.handleFile(event.dataTransfer.files[i])
      }
      this.$emit('fileChange', this.files)
    },    
    fileU(event) {
      if(this.uploading) {
        return
      }
      for(let i = 0; i<event.target.files.length; i++) {
        this.handleFile(event.target.files[i])
      }
      this.$emit('fileChange', this.files)
    }
  },
}
</script>


<style scoped lang="scss">
.loading-text {
  position: absolute;
  left: 50%;
  transform: translateX(-50%);
  color: white;
  font-size: 1em;
  z-index: 1;
  top: 75px;
}
.svg-loading-container {
  position: absolute;
  top: 6px;
  left: 50%;
  width: 24px;
  height: 24px;
  display: flex;
  justify-content: center;
  align-items: center;
  transform: translateX(-50%);
  z-index: 1;
  .spinner-first-half {
    stroke-dasharray: 251.327;
    stroke-dashoffset: calc(251.327 * (1 - var(--progress)/100)); 
  }
  .spinner-second-half {
    stroke-dasharray: 251.327;
    stroke-dashoffset: calc(251.327 * (1 - var(--progress)/100)); 
  }
}
.circle-background {
  stroke: #222222;
}

.file-name {
  font-size: .8em;
}
.file-upload-label.error {
  color: #ff1744;
}
.input-tooltip {
  font-size: 12px;
  height: 12px;
  text-align: start;
}
.input-error {
  position: relative;
  font-size: 12px;
  color: #ff1744;
  text-align: start;
  height: 12px;
}
.slide-enter-active {
  transition: .3s cubic-bezier(.4,0,.2,1);
}
.slide-enter-from {
  opacity: 0;
  transform: translateY(-100%);
}
.slide-leave-to {
  opacity: 1;
  transform: translateY(0%);
}
.input-element {
  position: relative;
  margin: 4px 0;
  &.disabled {
    filter: grayscale(75%);
  }
}
.drag-and-drop {
  height: 88px;
  &.disabled {
    * {
      cursor: not-allowed !important;
    }
  }
  &.tiny{
    height: 68px;
  }
  background-color: rgba(0, 135, 108, .1);
  border-radius: 4px;
  .drag-and-drop-preview {
    height: calc(100% - 30px);
    position: relative;
    display: flex;
    justify-content: center;
    align-items: center;
    .file-listing {
      display: flex;
      justify-content: center;
      align-items: center;
      .preview {
        height: 32px;
        width: 32px;
      }
      &.tiny {
        padding: 8px 26px 8px 8px;
      }
      &.uploading {
        :not(.svg-loading-container,svg,circle,path,.loading-text) {
          filter: blur(2px);
        }
        &::after {
          content: '';
          position: absolute;
          top: 0;
          left: 0;
          bottom: 0;
          right: 0;
          background-color: rgba(0,0,0,0.55);
          border-radius: 4px;
        }
      }
      top: 0;
      background-color: #ffffff;
      border-radius: 4px;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
    }
    .delete {
      position: absolute;
      height: 20px;
      top: 4px;
      right: 4px;
      &:hover {
        cursor: pointer;
        filter: invert(11%) sepia(99%) saturate(7116%) hue-rotate(1deg) brightness(104%) contrast(109%);
      }
    }    
  }
  .upload-button-container {
    display: flex;
    justify-content: center;
    align-items: center;
    .email-content-lable {
      background-color: white;
      cursor: pointer;
      // border: 1px solid black;
      font-size: 16px;
      width: 96px;
      aspect-ratio: 3.2;
      display: flex;
      flex-direction: row;
      justify-content: center;
      border: 1px solid #dfdfdf;
      &:hover:not(.disabled) {
        background:#dfdfdf;
      }
    }
  }
}

.tooltip-element {
    visibility: visible;
    color: #fff;
    background: #4664aa;
    width: 16px;
    height: 16px;
    border-radius: 8px;
    display: inline-block;
    text-align: center;
    line-height: 16px;
    margin: 0;
    font-size: 12px;
    cursor: default;
    pointer-events: auto;
  > .tooltip-text {
    z-index: 2;
    max-width: 320px;
    white-space: pre-line;     
    border: 1px solid white;
    border-radius: 4px;
    display: inline-block;
    font-size: 16px;
    transform: translateY(-2.5em);
    padding: 3px 8px;
    position: relative;
    background: #4664aa;
    visibility: hidden;
  }      
  &:hover {
    > .tooltip-text {
      visibility: visible;
    }
  }
}
input {
  display: none;
}
label {
  display: flex;
  align-items: center;
  margin: 0;
  text-align: start;
  width: 100%;
}
.item {
  > * {
    padding: 0px 0;
  } 
}
</style>