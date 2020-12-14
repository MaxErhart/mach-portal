<template>
  <div id="main" :style="{'grid-template-columns': windowWidth>750 ? '200px auto' : '64px auto'}">
    <div id="main-nav" :style="{'padding': styles.mainNavPadding, 'width': (windowWidth>750 ? 200 : 64) + 'px'}">
      <div id="active-indicator" v-if="signedIn && windowWidth>750" :style="{top: (currentRoute.index + 0.5)*navItemHeight - 3  + 'px', padding: styles.mainNavPadding}">
        <svg>
          <circle cx="3" cy="3" r="3" stroke="black" stroke-width="0" />
        </svg> 
      </div>
      <MainNavItem @click="changePage(route)" :showText="windowWidth>750" v-for="route in routes" :route="route.route" :key="route" :text="route.name" :isActive="currentRoute.index == route.index" :path="route.icon"/>
      <div class="main-nav-login" v-if="!signedIn" @click="changeLoginFormActive(true)" :style="{'width': windowWidth>750 ? '75%': '64px'}">
        <img src="@/assets/signIn.svg" alt="Sign In">
        <span class="button-span" v-if="windowWidth>750">Sign In</span>        
      </div>
      <div class="main-nav-login" v-if="signedIn" @click="logout()" :style="{'width': windowWidth>750 ? '75%': '64px'}">
        <img src="@/assets/signOut.svg" alt="Sign Out">
        <span class="button-span" v-if="windowWidth>750">Sign Out</span>
      </div>
    </div>
    <div id="main-body">
      <router-view/>
    </div>
  </div>
  <div id="login-overlay" v-if="loginFormActive && !signedIn" @click.self="changeLoginFormActive(false)">
      <div id="login-body">
        <!-- <form> -->
          <h1>Sign In</h1>
          <section>
            <label for="username">Username</label>
            <input v-model="username" spellcheck="false" id="username" name="username" type="text" placeholder=" " autocomplete="username" required>
          </section>

          <section>        
            <label for="current-password">Password</label>
            <input v-model="password" id="current-password" name="current-password" type="password" autocomplete="current-password" aria-describedby="password-constraints" required>
          </section>

          <button id="sign-in" @click="login(username, password)">Sign in</button>
        <!-- </form> -->
      </div>
  </div>
</template>


<script>
import MainNavItem from '@/components/MainNavItem.vue'
import axios from "axios";

export default {
  name: 'App',
  components: {
    MainNavItem
  },
  data() {
    return {
      styles: {mainNavPadding: '10px 0 10px 0'},
      windowWidth: window.innerWidth,
      username: '',
      password: '',
    }
  },
  mounted() {
    window.addEventListener('resize', this.onResize);
  },
  computed: {
    currentRoute() {
      return this.$store.getters.getCurrentRoute;
    },
    signedIn() {
      return this.$store.getters.getIsSignedIn;
    },
    routes() {
      if(this.signedIn){
         return this.$store.getters.getRoutes;
      }
      return this.$store.getters.getRoutes.filter(i => i.signedIn == false);
    },
    navItemHeight() {
      return this.$store.getters.getNavItemMainHeight;
    },
    loginFormActive() {
      return this.$store.getters.getLoginForm;
    },
  },
  methods: {
    login(username, password){
			axios({
				method: 'post',
				url: 'https://www-3.mach.kit.edu/mach-portal/login.php',
				data: {username: username, password: password},
			}).then((response)=>{
				console.log(response.data)
			})  
    },
    onResize(){
      this.windowWidth = window.innerWidth;
    },
    changePage(route) {
      this.$store.commit('setCurrentRoute', route);
    },
    logout(){
      this.$store.commit('logout');
      this.$router.push({name: 'Home'})
    },
    // login(username, password){
    //   this.$store.commit('login', {username: username, password: password});
    // },
    changeLoginFormActive(isActive){
      this.$store.commit('changeLoginFormActive', isActive);
    },
  }
}

</script>


<style lang="scss">

body {
  margin: 0;
}

*, :after, :before {
    box-sizing: border-box;
}

button {
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

#main {
  display: grid;
  // flex-direction: row;
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

#main-body {
  width: 100%;
  background-color: #b1ded4;
  padding: 20px;
}

#login-overlay {
  position: fixed;
  display: flex;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0,0,0, 0.5);
}

#login-body {
  width: 600px;
  padding: 10px;
  margin: auto;
  background-color: white;
  font-family: sans-serif;
  font-weight: 500;
  // > * {
    
  // }
  button {
    margin: 30px 0;
  }

  h1 {
    border-bottom: 1px solid #ccc;
    font-size: 28px;
    font-weight: 600;
    padding: 0 0 5px 0;
    margin: 30px 0;
  }  
  form {
    margin: 0 auto;
    max-width: 500px;
  }  
  label {
    display: block;
    font-size: 16px;
    // font-size: var(--mobile-font-size);
    font-weight: 500;
    margin: 0 0 3px 0;
  }
  input {
    border: 1px solid #ccc;
    font-size: 22px; /* fallback */
    font-size: var(--mobile-font-size);
    padding: 15px;
    width: 90%; /* fallback */
    width: calc(100% - 30px); /* full width minus padding */
  }
  input[type=text]:not(:focus):invalid,
  input[type=password]:not(:focus):invalid {
    color: red;
    outline-color: red;
  }
  section {
    margin: 0 0 20px 0;
    position: relative; /* for password toggle positioning */
  }  
}
  .button-span{
    margin-left:8px;
  }


</style>
