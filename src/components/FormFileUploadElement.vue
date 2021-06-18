<template>
  <div class="input-element">
    <div class="item">
      <label id="file-upload-label" :for="id">{{labelName}}
        <span class="required-span" v-if="required">*</span>
        <span class="tooltip-element" v-if="tooltip != ''">
          ?
          <div class="tooltip-text">{{tooltip}}</div>
        </span>
      </label>
      <input :id="elementId" :name="elementId" type="file" ref="file" @change="fileU()">
      <label :for="elementId" class="email-content-lable" :class="{active: fileUploaded}">
        <template v-if="!fileUploaded">
          <span><img :src="require(`@/assets/upload.svg`)"></span>
          <span>Upload File</span>
        </template>
        <template v-else >{{filename}}</template>
        <div class="progress-bar" v-if="uploadPercentage>0 && fileUploaded">
          <div class="progress-percent">{{uploadPercentage+'%'}}</div>
          <div class="progress-bar-backdrop" :style="{width: uploadPercentage+'%'}"></div>
        </div>
      </label>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormFileUploadElement',
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
      filename: null,
      fileUploaded: false,
    }
  },
  methods: {
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
      margin: 0 5px;
      font-size: 12px;
      cursor: default;
      pointer-events: auto;
    > .tooltip-text {
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
    margin: 2px 0;
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
  position: relative;
  height: 40px;
  // width: 250px;
  display: flex;
  align-items: center;
  margin: 2px 0 18px 0;
  text-align: start;
  border: 1px solid rgba(0, 0, 0, 0.2);
  justify-content: center;

  > .progress-bar {
    > .progress-percent {
      width: 100%;
      position: absolute;
      text-align: center;
      transform: translateY(-2px);
      z-index: 2;
    }
    > .progress-bar-backdrop {
      position: absolute;
      background-color: #00876c;
      height: 100%;
      border-radius: 10px;
    }
    position: absolute;
    height: 12px;
    left: 0;
    border-radius: 10px;
    bottom: -18px;
    width: 100%;
    box-shadow: 0 0 2px 1px rgba(0,0,0,0.2)
  }
  > span:first-child {
    margin-right: 5px;
  }
  &:hover {
    box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
  }  
}  
</style>