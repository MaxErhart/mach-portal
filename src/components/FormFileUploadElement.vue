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
      <input :id="id" :name="id" type="file" ref="file" @change="fileU()">
      <label :for="id" id="file-upload-text" :class="{active: fileUploaded}">
        <template v-if="!fileUploaded">
          <span><img :src="require(`@/assets/upload.svg`)"></span>
          <span>Upload File</span>
        </template>
        <template v-else >{{filename}}</template>  
      </label>
    </div>
  </div>
</template>

<script>
export default {
  name: 'FormFileUploadElement',
  props: {
    id: String,
    labelName: String,
    tooltip: String,
    required: Boolean,
  },
  data() {
    return {
      filename: null,
      fileUploaded: false,
    }
  },
  computed: {
  },
  methods: {
    fileU() {
      const file = this.$refs['file'].value.split('\\')
      console.log(file)
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
  #file-upload-text {
    border:1px solid #2c3e50;
    height: 40px;
    justify-content: center;
    box-shadow: 0 0 2px 1px rgba(0,0,0,0.2);

    background: linear-gradient(to right, rgba(0, 255, 0, 0.55) 50%, white 50%);
    background-size: 200% 100%;
    background-position: right bottom;rgba

    > span:first-child {
      margin-right: 5px;
    }
    &:hover {
      box-shadow: inset 0 0 2px 1px rgba(0,0,0,0.2);
    }
    &.active {
    background-position: left bottom;
    transition:all 600ms ease;
    }
  } 
</style>