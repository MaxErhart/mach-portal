<template>
  <div class="input-element">
    <div class="item">
      <label for="input-field">{{labelName}}
        <span class="required-span" v-if="required">*</span>
        <span class="tooltip-element" v-if="tooltip != ''">
          ?
          <div class="tooltip-text">{{tooltip}}</div>
        </span>
      </label>
      <input :name="elementId" :type="inputType" :placeholder="placeholder" v-model="value"/>
    </div>
  </div>
</template>

<script>
export default {
  name: 'InputeElement',
  props: {
    preset: Boolean,
    elementId: String,
    labelName: String,
    inputType: String,
    tag: String,
    tooltip: String,
    placeholder: String,
    required: Boolean,    
  },
  data() {
    return {
      value: null
    }
  },
  mounted() {
    if(this.preset) {
      this.value = this.$store.getters.getFormSubmissionData.data[this.elementId];
    }
  },
  methods: {
   
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
    user-select: auto !important;
    display: block;
    width: 100%;
    height: 40px;
    font-size: 16px;
    border: 1px solid #ccc;
    padding: 15px !important;
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
</style>