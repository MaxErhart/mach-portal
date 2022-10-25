import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import mitt from 'mitt'
const emitter = mitt();
const app = createApp(App)
app.config.globalProperties.emitter = emitter;




const flattenObj = (ob) => {
 
  // The object which contains the
  // final result
  let result = {};

  // loop through the object "ob"
  for (const i in ob) {

      // We check the type of the i using
      // typeof() function and recursively
      // call the function again
      if(ob[i]===null) {
          result[i] = ob[i];
      }
      else if ((typeof ob[i]) === 'object' && !Array.isArray(ob[i])) {
          const temp = flattenObj(ob[i]);
          for (const j in temp) {

              // Store temp in result
              result[i + '.' + j] = temp[j];
          }
      }

      // Else store ob[i] in result directly
      else {
          result[i] = ob[i];
      }
  }
  return result;
};
app.config.globalProperties.$myGlobalVariable = flattenObj

app.use(store).use(router).mount('#app')