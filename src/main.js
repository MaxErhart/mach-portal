import { createApp } from 'vue'
import App from './App.vue'
import router from './router'
import store from './store'
import mitt from 'mitt'




// import { IonicVue } from '@ionic/vue';
// /* Core CSS required for Ionic components to work properly */
// import '@ionic/vue/css/core.css';

// /* Basic CSS for apps built with Ionic */
// import '@ionic/vue/css/normalize.css';
// import '@ionic/vue/css/structure.css';
// import '@ionic/vue/css/typography.css';

// /* Optional CSS utils that can be commented out */
// import '@ionic/vue/css/padding.css';
// import '@ionic/vue/css/float-elements.css';
// import '@ionic/vue/css/text-alignment.css';
// import '@ionic/vue/css/text-transformation.css';
// import '@ionic/vue/css/flex-utils.css';
// import '@ionic/vue/css/display.css';





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
app.config.globalProperties.$flattenObj = flattenObj








app.use(store).use(router).mount('#app')
// app.config.ignoredElements = [/^ion-/]