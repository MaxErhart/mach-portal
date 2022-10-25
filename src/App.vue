<template>
  <div id="main" v-if="authenticationFinished">
    <div id="full-screen" v-if="isLoginRoute">
      <router-view/>
    </div>
    <template v-else>
      <div id="top-navigation-app" :style="topNavigationDynamicStyle">
        <IconButton @buttonClicked="toggleSideNavigation()" id="toggle-side-navigation-buttion-app" :noHover="toggleSideNavigationButton.noHover" :icon="toggleSideNavigationButton.icon" :text="toggleSideNavigationButton.text" :width="toggleSideNavigationButton.width" :height="toggleSideNavigationButton.height" />
        <UserOptionsMenu id="user-option-menu" :user="user" @logout="logout()"/>
      </div>
      
      <div id="top-banners" :style="topBannersDynamicStyle">
        <div class="banner" v-for="(banner, index) in banners" :key="banner" :style="bannerDynamicStyle(banner)">
          <span class="banner-text"  v-html="banner.text"></span>
          <IconButton :border="true" class="banner-close-icon" @buttonClicked="closeBanner(index)" :noHover="true" icon="closeX" :text="null" :width="14" :height="14" />
        </div>
      </div>

      <div id="side-navigation-app" :style="sideNavigationDynamicStyle">
        <MainNavItem v-for="navItem in filterNavItems(navigationItems)" :key="navItem" :active="isNavItemActive(navItem)" :icon="navItem.icon" :text="navItem.text" :route="navItem.route" :height="navItem.height" :width="navItem.width"/>
      </div>
      <div id="content-app" :style="contentDynamicStyle" ref="contentApp">
        <router-view :key="userFetched" :user="user" @userInfoChange="user=$event"/>
      </div>
    </template>


  </div>
</template>

<script>
// import axios from "axios";
import IconButton from '@/components/IconButton.vue'
import MainNavItem from '@/components/MainNavItemTEMP.vue'
import UserOptionsMenu from '@/components/user/UserOptionsMenu.vue'
import axios from "axios";
export default {
  name: 'App',
  components: {
    IconButton,
    MainNavItem,
    UserOptionsMenu,
  },
  data() {
    return {
      isMounted: false,
      innerWidth: 0,

      user: null,
      apps: null,
      userFetched: false,
      authenticationFinished: false,

      toggleSideNavigationButton: {icon: 'threeHorizontalBars', width: 24, height: 24, text: '', noHover: true},
      sideNavigationOn: true,
      sideNavigationOptions: {width: 180, transitionTime: 300, top:38, color: {r:255, g:255, b:255}, padding: 16},
      contentOptions: {padding: {top: 14, right: 14, bottom: 0, left: 14}},
      navigationItems: [
        {name: 'Home', icon: 'home', text: 'Home', route: 'Home', height: 24, width: 24, public: true},
        {name: 'Forms', icon: 'forms', text: 'Forms', route: 'Forms', height: 24, width: 24 , public: false},
        {name: 'Create Form', icon: 'createforms', text: 'Create Form', route: 'CreateForms', height: 24, width: 24, public: false},
        {name: 'Permissions', icon: 'cogwheel', text: 'Permissions', route: 'Permissions', height: 24, width: 24, public: false},
      ],
      topBannerOptions: {height: 38},
      banners: []
    }
  },
  created() {
    window.addEventListener("resize", this.windowResized);
    this.innerWidth=window.innerWidth
  },
  computed: {
    isLoginRoute() {
      if(this.$route.name=='Login') {
        return true
      }
      return false
    },
    contentDynamicStyle() {
      var paddingRight = 0
      var paddingLeft = 0
      if(this.innerWidth>460) {
        paddingRight = this.contentOptions.padding.right
        paddingLeft = this.contentOptions.padding.left
      }
      return {
        'top': `${this.sideNavigationOptions.top + this.banners.length*this.topBannerOptions.height}px`,
        'padding': `${this.contentOptions.padding.top}px ${paddingRight}px ${this.contentOptions.padding.bottom}px ${paddingLeft}px`,
        'left': this.sideNavigationOn ? `${this.sideNavigationOptions.width}px` : 0,
        'min-width': this.sideNavigationOn ? `calc(100vw - ${this.sideNavigationOptions.width}px - ${this.yScrollBarCorrection}px)` : `calc(100vw - ${this.yScrollBarCorrection}px)`,
        // 'width': '100%',
        'min-height': `calc(100vh - ${this.sideNavigationOptions.top + this.xScrollBarCorrection + this.contentOptions.padding.top + this.banners.length*this.topBannerOptions.height}px`,
      }
    },
    topBannersDynamicStyle() {
      return {
        'top': `${this.sideNavigationOptions.top}px`,
      }
    },
    topNavigationDynamicStyle() {
      return {
        'height': `${this.sideNavigationOptions.top}px`,
        'background-color': `rgb(${this.sideNavigationOptions.color.r},${this.sideNavigationOptions.color.g},${this.sideNavigationOptions.color.b})`
      }
    },
    sideNavigationDynamicStyle() {
      return {
        'top': `${this.sideNavigationOptions.top + this.banners.length*this.topBannerOptions.height}px`,
        'width': this.sideNavigationOn ? `${this.sideNavigationOptions.width}px` : 0,
        // 'transition': `width ${this.sideNavigationOptions.transitionTime}ms ease-in`,
        'height': `calc(100vh - ${this.sideNavigationOptions.top}px - ${2*this.sideNavigationOptions.padding + this.banners.length*this.topBannerOptions.height}px)`,
        'background-color': `rgb(${this.sideNavigationOptions.color.r},${this.sideNavigationOptions.color.g},${this.sideNavigationOptions.color.b})`,
        'padding': `${this.sideNavigationOptions.padding}px 0`,
        
      }
    },
    xScrollBarCorrection() {
      if(!this.isMounted) {
        return 0
      }
      // return this.$refs.contentApp.getBoundingClientRect().width>window.innerWidth ? 17 : 0
      return 0
    },
    yScrollBarCorrection() {
      if(!this.isMounted) {
        return 0
      }
      // return this.$refs.contentApp.getBoundingClientRect().height>window.innerHeight ? 17 : 0
      return 0
    },
  },
  methods: {
    closeBanner(index) {
      this.banners.splice(index, 1)
    },
    bannerDynamicStyle(banner) {
      return {
        'height': `${this.topBannerOptions.height}px`,
        'background-color': banner.backgroundColor,
        'color': banner.color,
      }
    },
    createTopBanners(user) {
      if(!user) {
        return
      }
      if(user.address_street && user.address_postalcode && user.address_city && user.address_country && user.private_email) {
        return
      }
      var banner = {}
      // banner['backgroundColor'] = 'rgb(251 213 213)'
      banner['backgroundColor'] = 'rgb(188 240 218)'
      
      // banner['color'] = 'rgb(155 28 28)'
      banner['color'] = 'rgb(3 84 63)'
      
      banner['duration'] = -1
      banner['text'] = 'Please fill out all user information in your <a href="https://www-3.mach.kit.edu/dist/#/profile">profile settings</>.'
      this.banners.push(banner)
    },
    windowResized() {
      this.innerWidth=window.innerWidth
    },
    filterNavItems(navItems) {
      return navItems.filter(navItem=>{
        if(navItem.public) {
          return true
        }

        if(this.user && this.user.rightsOnApps && this.user.rightsOnApps.map(app=>app.name).includes(navItem.name)) {
          return true
        }
        return false
      })
    },
    logout() {
      this.user=null
      this.$router.push({name: 'Home'})
      const url = this.$store.getters.getApiAuthUrl+'/logout'
      const shibLogout = 'https://www-3.mach.kit.edu/Shibboleth.sso/Logout'
      axios({
        method: 'get',
        url: url
      }).then(response=>{
        this.userFetched = false
        this.$store.commit('setUserFetched', false)
        console.log(response.data)
        localStorage.clear()
        // window.location.href = "https://www-3.mach.kit.edu/Shibboleth.sso/Logout"
      })
      axios({
        method: 'get',
        url: shibLogout
      }).then(response=>{
        console.log(response.data)
      }) 

    },
    isNavItemActive(navItem) {
      if(this.$route.name==navItem.route) {
        return true
      }
      return false
    },
    toggleSideNavigation() {
      this.sideNavigationOn = !this.sideNavigationOn
    },
    getUserAppInfo() {
      var url = this.$store.getters.getApiAuthUrl+'/login'
      if(window.location.host.startsWith('localhost')) {
        url = this.$store.getters.getApiUrl+'/login'
      }
      axios({
        method: 'get',
        url: url,
      }).then(response=>{
        console.log(response.data)
        this.authenticationFinished = true
        this.user=response.data
        this.userFetched = true
        this.$store.commit('setUserFetched', true)
        this.isMounted=true
        this.createTopBanners(this.user)
      }).catch(error=>{
        console.log(error)
        this.authenticationFinished = true
        this.isMounted=true
        this.userFetched = true
        this.$store.commit('setUserFetched', true)
        this.createTopBanners(this.user)
      })
      return this.user
    }
  },
  mounted() {
    this.getUserAppInfo()
  },

}
</script>

<style lang="scss">
#top-banners {
  position: fixed;
  width: 100%;
  z-index: 11;
  background-color: white;
  > .banner {
    display: grid;
    grid-template-columns: 5% 90% 5%;
    > .banner-text {
      grid-column-start: 2;
      justify-self: center;
      align-self: center;
    }
    > .banner-close-icon {
      align-self: flex-start;
      justify-self: flex-end;
      fill:#2c3e50;
      stroke:#2c3e50;
      padding: 2px;
      margin: 2px;
      &:hover {
        cursor: pointer;
      }
    }
  }
}
*,*:before,*:after {
  box-sizing: border-box;
}
body {
  margin: 0;
  padding: 0;
}
#app {
  // font-family: Helvetica, Arial, sans-serif;
  font-family: "Roboto","Arial",sans-serif;
  -webkit-font-smoothing: antialiased;
  -moz-osx-font-smoothing: grayscale;
  color: #2c3e50;
}
#main {
  position: relative;
}
#full-screen {
  min-width: 100vw;
  min-height: 100vh;
}
#top-navigation-app {
  position: fixed;
  display: flex;
  flex-direction: row;
  justify-content: space-between;
  align-items: center;
  width: 100%;
  z-index: 12;
  > * {
    margin: 0 14px;
  }
}
#toggle-side-navigation-buttion-app {
  display: inline;
}

#side-navigation-app {
  overflow: hidden;
  position: fixed;
  z-index: 1;
}
// #content-
#content-app {
  position: absolute;
  display: inline-block;
  box-shadow: inset 2px 2px 4px -2px rgba(0,0,0,0.2);
  background-color: rgba(249, 249, 249, 1);
}


</style>