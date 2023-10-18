<template>
  <div class="error-banner">
    <div class="error-banner-header">
      <h4 class="error-banner-titel">Error {{error?.response?.status}}</h4>
    </div>
    <div class="error-banner-body">
      <h4 class="error-banner-response-title">{{error?.response?.data?.message}}</h4>
      <div class="error-banner-response-errors" v-for="(sub_error,key) in error?.response?.data?.errors" :key="key">
        <!-- {{sub_error}} -->
        <div v-if="'message' in sub_error">{{sub_error?.message}}</div>
        <div v-else>{{key}}: {{sub_error[0]}}</div>
      </div>
    </div>
    <div class="error-banner-footer">
      <div class="error-banner-action">Error caused by action:</div> <span class="error-action">{{action}}</span>
      <div class="error-banner-action" v-if="redirect">Redirection to route: </div> <span v-if="redirect?.name" class="error-action">{{redirect.name}}</span><span v-if="redirect && !redirect.name" class="error-action">Login</span>
    </div>
    <!-- <button @click="close()" class="error-banner-button" @mouseover="animateGradient(true)" @mouseleave="animateGradient(false)" :style="buttonStyle"> -->
    <button @click="close()" class="error-banner-button" :style="buttonStyle">
      <span>Ok</span>
      <!-- <div class="wave" :style="waveStyle"></div> -->
    </button>
  </div>
</template>

<script>
export default {
  name: 'ErrorBanner',
  props: {
    error: Object,
    action: String,
    redirect: [String,Object],
  },
  data() {
    return {
      hover: false,
      start: 100,
      end: 100,
      deg: 0,
      animation: null,
    }
  },
  mounted() {
    console.log(this.error?.response)
  },
  methods: {
    close() {
      this.$emit('close')
      if(!this.redirect) {
        return
      }
      if(typeof this.redirect === 'object') {
        this.$router.push(this.redirect)
      } else if(typeof this.redirect === 'string') {
        window.location.href = this.redirect;
      }
    },
    // animateGradient(hover) {
    //   clearInterval(this.animation)
    //   const speed = 2
    //   const offset = 100
    //   if(hover) {
    //     this.animation = setInterval(()=>{
    //       if(this.end-this.start<offset && this.end<100) {
    //         this.end+=speed
    //       } else if(this.end-this.start>=offset && this.end<100 && this.start<100) {
    //         this.end+=speed
    //         this.start+=speed
    //       } else if(this.end>=100 && this.start<100) {
    //         this.start+=speed
    //       }
    //     }, 16.66)
    //   } else {
    //     this.animation = setInterval(()=>{
    //       if(this.end-this.start<offset && this.start>0) {
    //         this.start-=speed
    //       } else if(this.end-this.start>=offset && this.end>0 && this.start>0) {
    //         this.start-=speed
    //         this.end-=speed
    //       } else if(this.end>0 && this.start<=0) {
    //         this.end-=speed
    //       }
    //     }, 16.66)
    //   }
    // }
  },
  computed: {
    buttonStyle() {
      return {
        // 'background': `linear-gradient(120deg, rgba(44,62,80,1) ${this.start}%, rgba(220,53,69,1) ${this.end}%)`
        'background': `linear-gradient(120deg, rgba(44,62,80,1) ${this.start}%, rgba(220,53,69,1) ${this.end}%)`,
        'color': 'white',
      }
    },
    // waveStyle() {
    //   return {
    //     'transform': `rotate(${this.deg}deg)`,
    //     'background': `linear-gradient(-${this.deg-90}deg, rgba(220,53,69,1) ${this.start}%, rgba(44,62,80,1) ${this.end}%)`,
    //   }
    // },
  },

}
</script>


<style scoped lang="scss">
.error-banner-button {
  position: relative;
  padding: 6px;
  margin: 0;
  border: none;
  width: 220px;
  font-size: 1.1em;
  align-self: center;
  margin: 20px 20px;
  border-radius: 12px;
  overflow: hidden;
  cursor: pointer;
  span {
    position: relative;
    z-index: 1;
  }
  .wave {
    position: absolute;
    transform: translate(-50%, -75%);

    left: -268px;
    top: -736px;
    content: '';
    border-radius: 47%;
    width: 744px;
    // background-color: rgba(0,0,0,1);
    height: 752px;
    // border: 1px solid black;
    // background: linear-gradient(120deg, rgba(44,62,80,1) 0%, rgba(220,53,69,1) 100%);
  }
}
// @keyframes animate {
//   0% {
//     transform: 
//       rotate(0deg);
//   }
//   100% {
//     transform: 
//       rotate(360deg);
//   }
// }
.error-banner {
  display: flex;
  flex-direction: column;
  position: fixed;
  max-width: 210mm;
  width: 100%;
  border: 1px solid rgba(220,53,69,1);
  min-height: 30mm;
  background-color: white;
  border-radius: 12px;
  z-index: 100;
  left: 50%;
  transform: translate(-50%, 0%);
  top: 10%;
  padding: 0 20px;
}
.error-banner-titel {
  margin: 0;
  padding: 0;
}
.error-banner-header {
  padding: 20px 0 10px 0;
  border-bottom: 1px solid rgba(44,62,80,1);
}
.error-banner-response-title {
  margin: 6px 0;
}
error-banner-response-errors {

}
.error-banner-body {
  padding: 10px 0 10px 0;
  margin: 0 0 10px 0;
  border-top: 1px solid black;
}
.error-banner-footer {
  display: grid;
  grid-template-columns: auto 1fr;
  column-gap: 8px;
  row-gap: 2px;
  font-size: .8em;
}
.error-banner-action {
  justify-self: flex-start;
}
.error-action {
  font-weight: 600;
}
</style>
