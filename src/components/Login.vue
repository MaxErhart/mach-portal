<template>
  <div id="login">
    <div id="login-overlay" v-if="loginFormActive && !isSignedIn" @click.self="changeLoginFormActive(false)">
      <div id="login-body">
        <section id="tabs">
          <button class="tab" @click="sitchTab('shib')" :class="{active: activeTab=='shib'}">
            Sign In with KIT Account
          </button>
          <button class="tab" @click="sitchTab('guest')" :class="{active: activeTab=='guest'}">
            Sign In as Guest
          </button>
        </section>

        <section id="tab-content">
          <div class="content" id="shib-sign-in" v-if="activeTab =='shib'">
            <label for="shib-sign-in-button">Sign in with KIT Account</label>
            <button class="kit-button" id="shib-sign-in-button" @click="shibLogin()">Sign In</button>
          </div>

          <div class="content" id="guest-sign-in" v-if="activeTab =='guest'">
            <section>
              <label for="username">Username</label>
              <input v-model="username" @keyup.enter="$refs.loginPwInput.focus()" spellcheck="false" id="username" name="username" type="text" placeholder=" " autocomplete="username" required>
            </section>

            <section>        
              <label for="current-password">Password</label>
              <input v-model="password" @keyup.enter="login(username, password)" ref="loginPwInput" id="current-password" name="current-password" type="password" autocomplete="current-password" aria-describedby="password-constraints" required>
            </section>

            <button class="kit-button" id="sign-in" ref="loginSubmitButton" @click="login(username, password)">Sign in</button>          
          </div>
        </section>


      </div>
    </div>
  </div>
</template>

<script>
import axios from "axios";
export default {
  name: 'Login',
  props: {
    isSignedIn: Boolean,
  },
  data() {
    return {
      username: null,
      password: null,
      activeTab: 'shib',
    }
  },

  computed: {
    loginFormActive: function() {
      return this.$store.getters.getLoginForm;
    },
  },
  methods: {
    changeLoginFormActive(isActive){
      this.$store.commit('changeLoginFormActive', isActive);
    },
    login(username, password){
      this.$refs.loginSubmitButton.focus();
			axios({
				method: 'post',
				url: 'https://www-3.mach.kit.edu/api/login.php',
				data: {username: username, password: password},
			}).then((response)=>{
        if(response.data.success) {
          this.$store.commit('login');
        }

      })
    },
    shibLogin() {
      window.location.href = "https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=https://www-3.mach.kit.edu/dist/#";
    },
    sitchTab(tabClicked) {
      this.activeTab = tabClicked
    },        
  }
}
</script>


<style scoped lang="scss">
#login-overlay {
  position: fixed;
  display: flex;
  top: 0;
  bottom: 0;
  left: 0;
  right: 0;
  background-color: rgba(0,0,0, 0.5);
}
label {
  display: block;
  font-size: 16px;
  font-weight: 500;
  margin: 4px 0 2px 0;
}
#sign-in {
  display: block;
  margin: 8px 0 0 0;
  font-size: 16px;
}
#shib-sign-in-button {
  display: block;
  margin: 8px 0 0 0;
  font-size: 16px;
  width: 25%;
  height: 25%;  
}
input {
  border: 1px solid #ccc;
  font-size: 18px;
  padding: 10px;
  width: 100%;
  // width: calc(100% - 20px); /* full width minus padding */
}
input[type=text]:not(:focus):invalid,
input[type=password]:not(:focus):invalid {
  color: red;
  outline-color: red;
}

#login-body {
  height: 250px;
  min-width: 400px;
  width: 100%;
  max-width: 480px;
  margin: auto;
  background-color: white;
  font-family: sans-serif;
  font-weight: 500;
  display: grid;
  grid-template-rows: auto 1fr;
}

#tabs {
  overflow: hidden;
  background-color: #ccc;
  display: grid;
  grid-template-columns: 1fr 1fr;
  .tab {
    background-color: inherit;
    border: none;
    outline: none;
    cursor: pointer;
    font-size: 17px;
    padding: 14px;
    &:hover {
      background-color: #ddd;
    }
    &.active {
      background-color: #f1f1f1;
    }       
  }
}
#tab-content {
  padding: 8px;
}

#shib-sign-in {
  height: 100%;
  display: flex;
  flex-direction: column;
  justify-content: center;
  align-items: center;
}

</style>