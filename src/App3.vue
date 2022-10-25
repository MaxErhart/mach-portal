<template>
  <!-- <div id="main" :style="{'grid-template-columns': hideNavSidebar ? 'auto' : windowWidth>750 ? '200px auto' : '64px auto'}"> -->
  <div id="main">
    <!-- <div id="main-nav" v-if="!hideNavSidebar" :style="{'padding': styles.mainNavPadding, 'width': (windowWidth>750 ? 200 : 64) + 'px'}"> -->
    <div id="main-nav" v-if="!hideNavSidebar" :style="{'width': navWidth}">
      <div id="active-indicator" v-if="windowWidth>750 && routes.length >0" :style="{top: (currentAktiveNavBarIndex + 0.25)*navItemHeight - 3  + 'px', padding: styles.mainNavPadding}">
        <svg>
          <circle cx="3" cy="3" r="3" stroke="black" stroke-width="0" />
        </svg> 
      </div>
      <MainNavItem :showText="windowWidth>750" v-for="(route, index) in routes" :route="route.routeName" :key="route" :text="route.name" :isActive="currentAktiveNavBarIndex == index" :path="route.icon"/>
      <div class="main-nav-login" v-if="!loggedIn" @click="changeLoginFormActive(true)" :style="{'width': windowWidth>750 ? null: '64px'}">
        <img src="@/assets/signIn.svg" alt="Sign In">
        <span class="button-span" v-if="windowWidth>750">Sign In Mach-Portal</span>        
      </div>
      <template v-if="loggedIn">
        <div class="main-nav-logout" @click="logout()" :style="{'width': windowWidth>750 ? null: '64px'}">
          <img src="@/assets/signOut.svg" alt="Sign Out">
          <span class="button-span" v-if="windowWidth>750">Sign Out</span>
        </div>        
        <div id="main-nav-user" >
          <div id="user-icon">
            <img src="@/assets/user.svg">
          </div>
          <div id="user-name" v-if="windowWidth>750">{{user.firstname}} {{user.lastname}}</div>
        </div>
      </template>

    </div>
    <!-- <div id="main-body" :style="{'width': bodyWidth}"> -->
    <div id="main-body">
      <transition name="fade">
        <ResponseMessage v-if="response.active" :response="response.error"/>
      </transition>
      <router-view/>
    </div>
    <Login :isSignedIn="loggedIn"/>
  </div>
</template>


<script>
import MainNavItem from '@/components/MainNavItem.vue'
import Login from '@/components/Login.vue'
import axios from "axios";
import ResponseMessage from '@/components/ResponseMessage.vue'
import appSettings from '@/appSettings.json'

export default {
    
  name: 'App',
  components: {
    MainNavItem,
    Login,
    ResponseMessage
  },
  data() {
    return {
      routes: [],
      appSettings: appSettings,
      apps: null,
      settings: null,
      styles: {mainNavPadding: '10px 0 10px 0'},
      windowWidth: window.innerWidth,
      username: '',
      password: '',
      currentAktiveNavBarIndex: 0,
      isSignedIn: false,
      user: null,
      response: {active: false, title: '', body: '', error: null},
    }
  },
  created() {
      if(window.location.host.startsWith('localhost')) {
          this.setup(this.apiUrl+'/login')
      } else {
          this.setup(this.apiAuthUrl+'/login')
      }
  },
  mounted() {
    window.addEventListener('resize', ()=>{
        this.windowWidth = window.innerWidth;
    });

    this.emitter.on('showResponseMessage', this.showResponseMessage)
    this.routes = this.appSettings.routes.filter(s=>s.public)
  },
  computed: {
    navWidth() {
      return `${(this.windowWidth>750 ? 200 : 64)}px`
    },
    loggedIn() {
      return this.$store.state.loggedIn;
    },
    responseMessage() {
      return this.$store.getters.getResponseMessage;
    },
    routeName() {
      return this.$route.name;
    },
    hideNavSidebar() {
      if(this.$route.name=='AnonFormSubmit' || this.$route.name=='Bewerberportal'  || this.$route.name=='BewerberportalRegistration' || this.$route.path.split("/").includes("public")){
        return true
      } else {
        return false
      }
    },
    currentRoute() {
      return this.$store.getters.getCurrentRoute;
    },
    apiUrl() {
        return this.$store.getters.getApiUrl;
    },
    apiAuthUrl() {
        return this.$store.getters.getApiAuthUrl;
    },
    navItemHeight() {
      return this.$store.getters.getNavItemMainHeight;
    },
    loginFormActive() {
      return this.$store.getters.getLoginForm;
    },
    userInformation() {
      return this.$store.getters.getUserInformation
    },  
  },
  watch: {
    $route(to) {
      this.currentAktiveNavBarIndex = this.routes.map(r=>r.routeName).indexOf(this.routes.map(r=>r.routeName).filter(e=>e.includes(to.name))[0])
    },
  },
  methods: {
    encodeUrl(url){
      url = url.replaceAll(":", "%3A")
      url = url.replaceAll("/", "%2F")
      url = url.replaceAll("#", "%23")
      console.log(url)
      return url
    },    
    setupFromLocalStorage() {
      if(localStorage.user) {
        this.user = JSON.parse(localStorage.user)
      }
      if(localStorage.apps) {
        this.user = JSON.parse(localStorage.apps)
      }
      if(localStorage.settings) {
        this.user = JSON.parse(localStorage.settings)
      }            
      this.routes = this.routes.concat(this.appSettings.routes.filter(r=>this.apps.map(a=>a.name).includes(r.name) && !r.public))
      this.currentAktiveNavBarIndex = this.routes.map(r=>r.routeName).indexOf(this.routes.map(r=>r.routeName).filter(e=>e.includes(this.$route.name))[0])
    },
    updateLocalStorage(user=null, apps=null, settings=null) {
      if(user) {
        localStorage.user = JSON.stringify(user)
      }
      if(apps) {
        localStorage.apps = JSON.stringify(apps)
      }
      if(settings) {
        localStorage.settings = JSON.stringify(settings)
      }
    },
    clearLocalStorage() {
      localStorage.clear()
    },
    showResponseMessage(event) {
      this.response = {active: true, error: event.error};
      setTimeout(()=>this.response.active=false, 5000)
    },
    setup(url) {
      axios({
        method: 'get',
        url: url,
      }).then(response=>{
        if(!response.data) {
            return;
        }
        console.log(response.data)
        this.user=response.data.user
        this.apps=response.data.apps
        this.routes = this.routes.concat(this.appSettings.routes.filter(r=>this.apps.map(a=>a.name).includes(r.name) && !r.public))
        this.currentAktiveNavBarIndex = this.routes.map(r=>r.routeName).indexOf(this.routes.map(r=>r.routeName).filter(e=>e.includes(this.$route.name))[0])
        this.settings=response.data.settings
        this.$store.commit('login', response.data)
      }).catch(error=>{
        console.log(error);
      });
    },
    logout(){
      this.$store.commit('logout');
      this.user=null
      this.apps=null
      this.settings=null
      this.routes = this.appSettings.routes.filter(s=>s.public)
      this.$router.push({name: 'Home'})
      const url = this.apiAuthUrl+'/logout'
      const shibLogout = 'https://www-3.mach.kit.edu/Shibboleth.sso/Logout'
      this.clearLocalStorage()
      axios({
        method: 'get',
        url: url
      }).then(response=>{
        console.log(response.data)
      })
      axios({
        method: 'get',
        url: shibLogout
      }).then(response=>{
        console.log(response.data)
      })      
    },
    changeLoginFormActive(isActive){
        this.$store.commit('changeLoginFormActive', isActive);
    },
  }
    
}

</script>


<style lang="scss">
@import 'D:\\inetpub\\MPortal\\src\\_variables';

.fade-leave-active {
  transition: opacity 0.5s ease;
}

.fade-enter-from,
.fade-leave-to {
  opacity: 0;
}
body {
  margin: 0;
  // overflow-x: hidden;
}

*, :after, :before {
  box-sizing: border-box;
}

.kit-button {
  margin: 5px 0 5px 0;
  box-shadow: none;
  border: none;
  display: block;
  cursor: pointer;
  padding: 10px;
  background-color: #00876c;
  color: white;
  &:hover {
    background-color: #007755;
  }
}

#app {
  font-family: Avenir, Helvetica, Arial, sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}
h1 {
  color: #00876c;
  display: inline-block;
  // padding-bottom: 10px;
}
h2 {
  color: #00876c;
  display: inline-block;
  // padding-bottom: 10px;
}
#main {
  position: relative;
  min-height: 100vh;
  min-width: 100vw;
  // display: grid;
  display: flex;
  // grid-template-rows: auto;
}


#main-nav {
  display: flex;
  flex-direction: column;
  position: sticky;
  top: 0;
  z-index: 1;
  height: 100vh;
  box-shadow: 2px 0 10px rgba(0,0,0,.1);
  // width: 200px;
  flex: 0 0 200px;
}

#active-indicator {
  fill: #00876c;
  position: absolute;
  left: 8px;
  pointer-events: none;
  transition: all 200ms ease;
}

.main-nav-login {
  margin: 5px auto;
  cursor: pointer;
  display: flex;
  height: 32px;
  width: 90%;
  border-radius: 0px;
  justify-content: center;
  align-items: center;
  background-color: #00876c;
  color: white;
  &:hover {
    background-color: #007755;
  }
  > img {
    width: 32px;
    margin: 0px -6px 0 -6px;
  }
}

#main-nav-user {
  // border: 1px solid black;
  margin-top: auto;
  margin-bottom: 20%;
  padding: 15px 10px;
  font-size: 16px;
  font-weight: 500;
  color:#2c3e50;
  display: flex;
  flex-direction: row;
  padding-left: 16px;
  > #user-icon {
    margin-right: 8px;
  }
  > #user-name {
    display: flex;
    align-items: center;
  }
}

#main-body {
  flex: 1;
  position: relative;
  min-height: 100vh;
  display: flex;
  // border: 1px solid green;
}
.button-span{
  margin-left:2px;
}

.main-nav-logout {
  margin: 5px auto;
  cursor: pointer;
  display: flex;
  flex-direction:row;
  height: 32px;
  width: 90%;
  border-radius: 0px;
  justify-content: center;
  align-items: center;
  background-color: #00876c;
  color: white;
  &:hover {
    background-color: #007755;
  }
  > * {
    margin: 0px 6px 0 3px;
  }
  > img {
    width: 24px;
  }
}
</style>
