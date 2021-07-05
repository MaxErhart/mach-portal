<template>
  <div id="main" :style="{'grid-template-columns': windowWidth>750 ? '200px auto' : '64px auto'}">
    <div id="main-nav" :style="{'padding': styles.mainNavPadding, 'width': (windowWidth>750 ? 200 : 64) + 'px'}">
      <div id="active-indicator" v-if="windowWidth>750" :style="{top: (currentActiveRouteIndex + 0.5)*navItemHeight - 3  + 'px', padding: styles.mainNavPadding}">
        <svg>
          <circle cx="3" cy="3" r="3" stroke="black" stroke-width="0" />
        </svg> 
      </div>
      <MainNavItem @click="changePage(route, index)" :showText="windowWidth>750" v-for="(route, index) in routes" :route="route.route" :key="route" :text="route.name" :isActive="currentActiveRouteIndex == index" :path="route.icon"/>
      <div class="main-nav-login" v-if="!isSignedIn" @click="changeLoginFormActive(true)" :style="{'width': windowWidth>750 ? '75%': '64px'}">
        <img src="@/assets/signIn.svg" alt="Sign In">
        <span class="button-span" v-if="windowWidth>750">Sign In</span>        
      </div>
      <template v-if="isSignedIn">
        <div class="main-nav-login" @click="logout()" :style="{'width': windowWidth>750 ? '75%': '64px'}">
          <img src="@/assets/signOut.svg" alt="Sign Out">
          <span class="button-span" v-if="windowWidth>750">Sign Out</span>
        </div>        
        <div id="main-nav-user" >
          <div id="user-icon">
            <img src="@/assets/user.svg">
          </div>
          <div id="user-name" v-if="windowWidth>750">{{user.fname}} {{user.lname}}</div>
        </div>
      </template>

    </div>
    <div id="main-body">
      <router-view/>
    </div>
    <Login :isSignedIn="isSignedIn"/>
  </div>
</template>


<script>
import MainNavItem from '@/components/MainNavItem.vue'
import Login from '@/components/Login.vue'
import axios from "axios";

export default {
  name: 'App',
  components: {
    MainNavItem,
    Login
  },
  data() {
    return {
      styles: {mainNavPadding: '10px 0 10px 0'},
      windowWidth: window.innerWidth,
      username: '',
      password: '',
      currentActiveRouteIndex: 0,
      isSignedIn: false,
      user: null,
      webuserTopics: ["home", "about", "theses", "dashboard"],
    }
  },
  created() {
    this.setup()
  },
  mounted() {
    window.addEventListener('resize', this.onResize);
    if(localStorage.isLoggedIn) {
      this.$store.commit('login');
    }
  },
  computed: {
    currentRoute() {
      return this.$store.getters.getCurrentRoute;
    },

    routes() {
      if(this.user) {
        return this.$store.getters.getRoutes.filter(i => i.topic in this.user.rights);
      } else {
        return this.$store.getters.getRoutes.filter(i => this.webuserTopics.includes(i.topic));
      }
      
    },
    navItemHeight() {
      return this.$store.getters.getNavItemMainHeight;
    },
    loginFormActive() {
      return this.$store.getters.getLoginForm;
    },
    userInformation() {
      return this.$store.getters.getUserInformation
    }
  },
  watch: {
    currentRoute(newRoute, oldRoute) {
      if(newRoute != oldRoute) {
        this.currentActiveRouteIndex = this.routes.indexOf(newRoute)
      }
    }
  },
  methods: {
    setup() {
      axios({
        method: 'get',
        url: 'https://www-3.mach.kit.edu/api/login.php'
      }).then(response => {
        console.log(response.data)
        if(response.data.error==null) {
          localStorage.isSignedIn = true
          this.isSignedIn = true
          this.user = response.data.user
          localStorage.user = JSON.stringify(response.data.user)
        } else {
          localStorage.isSignedIn = false
          this.isSignedIn = false
          this.user = response.data.user
          localStorage.user = JSON.stringify(response.data.user)
        }
      })
     
    },
    onResize(){
      this.windowWidth = window.innerWidth;
    },
    changePage(route, index) {
      this.currentActiveRouteIndex = index
      this.$store.commit('setCurrentRoute', route);
    },
    logout(){
      this.$router.push({name: 'Home'})
      localStorage.isSignedIn = false    
			axios({
				method: 'post',
				url: 'https://www-3.mach.kit.edu/api/logout.php',
			})
    },
    changeLoginFormActive(isActive){
      this.$store.commit('changeLoginFormActive', isActive);
    },
  }
}

</script>


<style lang="scss">

body {
  margin: 0;
  overflow-x: hidden;
}

*, :after, :before {
    box-sizing: border-box;
}

.kit-button {
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
  // color: #00876c;
  border-bottom: 1px solid #ddd;
  display: inline-block;
  padding-bottom: 10px;
}
#main {
  display: grid;
  grid-template-rows: auto;
}

#main-nav {
  display: flex;
  flex-direction: column;
  position: -webkit-sticky;
  position: sticky;
  top: 0;
  height: 100vh;
  box-shadow: 2px 0 10px rgba(0,0,0,.1);
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
  width: 75%;
  border-radius: 0px;
  justify-content: center;
  align-items: center;
  background-color: #00876c;
  color: white;
  &:hover {
    background-color: #007755;
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
  width: 100%;
  // background-color: rgba(0, 119, 85, 0.1);
  background-color: #b2dbd2;
  // background-color: #00876c;
  padding: 20px 20px 0 20px;
  display: flex;
  justify-content: center;
  align-items: flex-start;
}
.button-span{
  margin-left:8px;
}


</style>
