<template>
  <template v-if="data">
    <div class="input-element">
      <div class="item">
        <label id="file-upload-label" :for="name">{{data.label}}
          <span class="required-span" v-if="data.required">*</span>
          <span class="tooltip-element" v-if="data.tooltip">
            ?
            <div class="tooltip-text">{{data.tooltip}}</div>
          </span>
        </label>
        <input :id="name" type="file" ref="file" @change="fileU($event)" multiple>
        <div class="drag-and-drop" @drop.prevent="dropLink($event)" @dragenter.prevent @dragleave.prevent @dragover.prevent>

          <div class="drag-and-drop-preview">
            <div v-if="files.length<=0" class="text">drag and drop files</div>
            <div v-for="(file, key) in files" class="file-listing" :key="file">
              <img class="delete" :src="require(`@/assets/delete.svg`)" @click="removeFile(key)">
              <img class="preview" :src="fileImagePreview[key]">
              <div class="file-name">{{trimName(file.name)}}</div>
            </div>
          </div>

          <div class="upload-button-container">

            <label :for="name" class="email-content-lable" >
              <img :src="require(`@/assets/upload.svg`)">
              <span>Upload Files</span>
            </label> 

          </div>

 

        </div>
      </div>
    </div>    
  </template>
</template>

<script>
export default {
  name: 'FileUploadElement',
  props: {
    data: Object,
    uploadPercentage: Number,
    name: String,
  },
  data() {
    return {
      files:  [],
      fileImagePreview: [],
    }
  },

  methods: {
    trimName(fileName) {
      if(fileName.length>20) {
        return `...${fileName.slice(-17)}`
      }
      return fileName
    },
    removeFile(key) {
      this.files.splice(key,1)
      this.$emit('fileChange', this.files)
    },
    handleFile(file) {
      this.files.push(file)
      if(/\.(jpe?g|png|gif)$/i.test(file.name)) {
        let reader = new FileReader();
        reader.readAsDataURL(file)
        reader.onload = function() {
          this.fileImagePreview.push(reader.result)
          console.log(reader.result)
        }.bind(this)     
      } else {
        this.fileImagePreview.push(require('@/assets/file.svg'))
      }

      this.$emit('fileChange', this.files)

    },
    dropLink(event) {
      for(let i = 0; i<event.dataTransfer.files.length; i++) {
        this.handleFile(event.dataTransfer.files[i])
      }
    },    
    fileU(event) {
      for(let i = 0; i<event.target.files.length; i++) {
        this.handleFile(event.target.files[i])
      }
    }
  },
}
</script>


<style scoped lang="scss">
.input-element {
  margin: 4px 0;
}
.drag-and-drop {
  height: 128px;
  background-color: rgba(0, 135, 108, .1);
  border-radius: 4px;
  .drag-and-drop-preview {
    display: flex;
    justify-content: center;
    align-items: center;
    .file-listing {
      padding: 8px;
      background-color: #ffffff;
      border-radius: 4px;
      position: relative;
      display: flex;
      flex-direction: column;
      justify-content: center;
      margin: 0 8px;
    }
    .delete {
      position: absolute;
      height: 24px;
      top: 0;
      right: 0;
      &:hover {
        cursor: pointer;
        filter: invert(11%) sepia(99%) saturate(7116%) hue-rotate(1deg) brightness(104%) contrast(109%);
      }
    }    
    .preview {
      margin: 0 auto;
      height: 44px;
    }
    height: 66%;
  }
  .upload-button-container {
    display: flex;
    flex-direction: column;
    justify-content: center;
    align-items: center;
    height: 34%;
  }
}



.required-span {
  color: red;
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
    margin: 0px 0;    
  } 
}

.email-content-lable {
  background-color: white;
  cursor: pointer;
  border: 1px solid black;
  width: 140px;
  font-size: 16px;
  height: 32px;
  display: flex;
  flex-direction: row;
  justify-content: center;
  img {
    height: 24px;
  }

  &:hover {
    box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
  }  
} 

</style>