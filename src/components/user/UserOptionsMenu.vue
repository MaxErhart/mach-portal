<template>
  <div class="user-options-menu">

    <button id="login-button" @click="createRipple($event)" v-if="!user">
      Login
      <span v-for="ripple in ripples" :key="ripple" class="ripple" :style="rippleDynamicStyle(ripple)"></span>
    </button>
    <button id="logged-in-user-menu-button" v-else @click.self="openUserMenu()" ref="loggedInUserMenuButton">
      <span @click.self="openUserMenu()" id="user-character">{{userCharacter}}</span>
      <div class="overlay" v-if="userMenuActive" @click="closeUserMenu()"></div>

      <div id="user-menu" v-if="userMenuActive" :style="userMenuDynamitcStyle">
        <div id="menu-header">
          <div id="name">{{user.firstname}} {{user.lastname}}</div>
          <div id="email">{{user.email}}</div>
        </div>
        <div id="menu-options">
          <IconButton @buttonClicked="profileSettings()" class="menu-option" :hoverColor="profileSettingsButton.hoverColor" :hoverBackgroundColor="profileSettingsButton.hoverBackgroundColor" :icon="profileSettingsButton.icon" :text="profileSettingsButton.text" :width="profileSettingsButton.width" :height="profileSettingsButton.height"/>
          <IconButton @buttonClicked="logout()" class="menu-option" :hoverColor="logoutButton.hoverColor" :hoverBackgroundColor="logoutButton.hoverBackgroundColor" :icon="logoutButton.icon" :text="logoutButton.text" :width="logoutButton.width" :height="logoutButton.height"/>
          <!-- <button id="logout-user-button" class="menu-option" >
            <span>Logout</span>
          </button> -->
          
        </div>
      </div>
    </button>
  </div>
</template>

<script>
import IconButton from '@/components/IconButton.vue'
export default {
  name: 'UserOptionsMenu',
  components: {
    IconButton,
  },
  props: {
    user: Object,
  },
  data() {
    return {
      ripples: [],
      userMenuActive: false,
      userMenuOptions: {width: 240, padding: 0},
      logoutButton: {icon: 'logout', text: 'Logout', width: 24, height: 24, hoverColor: "#2c3e50", hoverBackgroundColor: "#f3f3f3"},
      profileSettingsButton: {icon: 'cogwheel', text: 'Profile Settings', width: 24, height: 24, hoverColor: "#2c3e50", hoverBackgroundColor: "#f3f3f3"},
    }
  },
  computed: {
    userMenuDynamitcStyle() {
      return {
        'width': `${this.userMenuOptions.width}px`,
        'left': `-${this.userMenuOptions.width+8+2*this.userMenuOptions.padding}px`,
        'padding': `${this.userMenuOptions.padding}px`,
        'top': `${0}px`,
      }
    },
    userCharacter() {
      if(!this.user) {
        return null
      }
      return this.user.firstname[0].toUpperCase()
    }
  },
  methods: {
    profileSettings() {
      this.$router.push({name: 'Profile'})
    },
    logout() {
      this.$emit('logout')
    },
    closeUserMenu() {
      this.userMenuActive = false
    },
    openUserMenu() {
      this.userMenuActive = true
    },
    rippleDynamicStyle(ripple) {
      return {
        'top': `${ripple.y-ripple.radius/2}px`,
        'left': `${ripple.x-ripple.radius/2}px`,
        'width': `${ripple.radius}px`,
        'height': `${ripple.radius}px`,
      }
    },
    createRipple(event) {
      const button = event.target.getBoundingClientRect()
      const radius = button.height>button.width ? button.height : button.width
      this.ripples.push(
        {
          x: event.offsetX,
          y: event.offsetY,
          radius: radius,
        }
      )
      window.location.href = "https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=https://www-3.mach.kit.edu/dist/%23"+this.$route.path;
      // this.$router.push({name: 'Login', params: {returnRoute: this.$route.name}})
    }
  }
}
</script>


<style scoped lang="scss">

button {
  padding: none;
  background: none;
  outline: none;
  border: none;
  margin: none;
}
#login-button {
  position: relative;
  border: 1px solid #00876c;
  border-radius: 2px;
  color: #00876c;
  font-size: 1.1em;
  padding: 2px 12px;
  overflow: hidden;
  &:hover {
    cursor: pointer;
  }
}
#menu-options {
  padding: 6px 0;
}
.menu-option {
  // width: 100%;
  height: 32px;
  padding: 0 12px;
  font-size: 1em;
  &:hover {
    cursor: pointer;
    background-color: rgb(240,240,240);
    * {
      background-color: rgb(240,240,240);
    }
  }
}
span.ripple {
  position: absolute; 
  border-radius: 50%;
  transform: scale(0);
  animation: ripple 600ms linear;
  background-color: rgba(0, 0, 0, 0.5);
}
@keyframes ripple {
  to {
    transform: scale(4);
    opacity: 0;
  }
}
#logged-in-user-menu-button {
  position: relative;
  overflow: visible;
  display: flex;
  align-items: center;
  justify-content: center;
  border-radius: 50%;
  width: 32px;
  height: 32px;
  background-color: #00876c;
  color: white;
  font-size: 1.1em;
  &:hover {
    cursor: pointer;
  }
}
#user-character {
  transform: translateX(-1%);
}
#user-menu {
  position: absolute;
  z-index: 2;
  color: black;
  border-left: 1px solid rgba(0,0,0,0.4);
  border-right: 1px solid rgba(0,0,0,0.4);
  border-bottom: 1px solid rgba(0,0,0,0.4);
  background-color: white;
  &:hover {
    cursor: default;
  }
  #menu-header {
    color: #2c3e50;

    overflow: hidden;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    border-bottom: 1px solid rgba(0,0,0,0.4);
    padding: 6px 12px;
    >*{
      margin: 2px 0;
    }
    #name {
      font-size: 1em;
      font-weight: bold;
    }
    #email {
      font-size: 1em;
    }
  }
}
.overlay {
  &:hover {
    cursor: default;
  }
  position: fixed;
  top: 0;
  left: 0;
  right: 0;
  bottom: 0;
  z-index: 1;
}
</style>
