<template>
  <div class="test-the-api">
    
  </div>

</template>

<script>
import axios from "axios";


export default {
  name: 'TestView',
  components: {

  },
  data() {
    return {

    }
  },
  computed: {

  },
  mounted() {
    axios({
      method: 'get',
      url: 'https://www-3.mach.kit.edu/api/shib/mach-api/public/index.php/api/apps'
    }).then(response=>{
      console.log(response.data)
    })
  },
  methods: {
    
  }
}
</script>

<style scoped lang="scss">

.kit-button {
  cursor: pointer;
  // animation: wobble 0.5s ease;
  &.animate {
    animation: wobble 0.4s ease;
  }
}
.about {
  width: 680px;
  background-color: #fafafa;
}
@keyframes wobble {
  0% { transform: translateX(0px);}
  15% { transform: translateX(8px);}
  30% { transform: translateX(0px);}
  45% { transform: translateX(-6px);}
  60% { transform: translateX(0px);}
  70% { transform: translateX(4px);}
  80% { transform: translateX(0px);}
  90% { transform: translateX(-2px);}
  100% { transform: translateX(0px);}

  
}
</style>