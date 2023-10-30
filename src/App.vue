<template>
  <div id="main">
    <div id="full-screen" v-if="hideHud">
      <ErrorBanner :redirect="errorBanner.redirect" @close="errorBanner.show=false" v-if="errorBanner.show" :error="errorBanner.error" :action="errorBanner.action"/>
      <router-view/>
    </div>
    <template v-else>
      <div id="top-banners" :style="topBannersStyle">
        <div class="banner" v-for="(banner, index) in banners" :key="banner" :style="bannerDynamicStyle(banner)">
          <span class="banner-text"  v-html="banner.text"></span>
          <IconButton :border="true" class="banner-close-icon" @buttonClicked="closeBanner(index)" :noHover="true" icon="closeX" :text="null" :width="14" :height="14" />
        </div>
      </div>

      <div id="top-navigation-app" :style="topNavigationDynamicStyle">
        <IconButton @buttonClicked="toggleSideNavigation()" id="toggle-side-navigation-buttion-app" :noHover="toggleSideNavigationButton.noHover" :icon="toggleSideNavigationButton.icon" :text="toggleSideNavigationButton.text" :width="toggleSideNavigationButton.width" :height="toggleSideNavigationButton.height" />
        <UserOptionsMenu id="user-option-menu" :user="user" @logout="logout()"/>
      </div>

      <div id="side-navigation-app" :style="sideNavigationDynamicStyle">
        <div class="nav-item-group" v-for="group in groupNavItems(filterNavItems(navigationItems))" :key="group">
          <MainNavItem :params="navItem.params" v-for="navItem in group" :ion="navItem.ion" :key="navItem" :active="isNavItemActive(navItem)" :icon="navItem.icon" :text="navItem.text" :route="navItem.route" :height="navItem.height" :width="navItem.width"/>
        </div>
      </div>
      <div id="content-app" :style="contentDynamicStyle" ref="contentApp">
        <ErrorBanner :redirect="errorBanner.redirect" @close="errorBanner.show=false" v-if="errorBanner.show" :error="errorBanner.error" :action="errorBanner.action"/>
        <template v-if="authCheck">
          <template v-if="$route.meta?.reload!==undefined">
            <router-view v-if="$route.meta.reload==='name'" :user="user" @userInfoChange="user=$event"  :key="$route.name"/>
            <router-view v-if="$route.meta.reload===null" :user="user" @userInfoChange="user=$event" />
          </template>
          <router-view v-else :user="user" @userInfoChange="user=$event" :key="$route.fullPath"/>
          <!-- <router-view :user="user" @userInfoChange="user=$event"/> -->

        </template>

      </div>
    </template>
  </div>
</template>

<script>
import ErrorBanner from '@/components/ErrorBanner.vue'
import IconButton from '@/components/IconButton.vue'
import MainNavItem from '@/components/MainNavItemTEMP.vue'
import UserOptionsMenu from '@/components/user/UserOptionsMenu.vue'
import axios from "axios";
export default {
  name: 'App',
  components: {
    ErrorBanner,
    IconButton,
    MainNavItem,
    UserOptionsMenu,
  },
  data() {
    return {
      errorBanner: {show: false, error: null, action: null},
      authCheck: false,
      innerWidth: 0,

      user: null,
      apps: null,

      toggleSideNavigationButton: {icon: 'threeHorizontalBars', width: 24, height: 24, text: '', noHover: true},
      sideNavigationOn: true,
      sideNavigationOptions: {iconOnlyWidth: 50, width: 200, transitionTime: 200, top:38, color: {r:255, g:255, b:255}, padding: 16},
      contentOptions: {padding: {top: 14, right: 14, bottom: 0, left: 14}},
      navigationItems: [
        {group: 0,name: 'Home', icon: 'home', text: 'Home', route: 'Home', height: 24, width: 24, public: true},
        {group: 1,name: 'Forms', icon: 'forms', text: 'Forms', route: 'Forms', height: 24, width: 24 , public: false},
        {group: 1,name: 'Create Form', icon: 'createforms', text: 'Create Form', route: 'CreateForms', height: 24, width: 24, public: false},
        {group: 2,name: 'Bewerberportal', icon: 'log-in-outline', text: 'Application Portal', route: 'Bewerberportal', height: 24, width: 24, public: true, ion: true},
        {group: 2,name: 'Exams', icon: 'lock-closed-outline', text: 'Application Admin', route: 'Exams', height: 24, width: 24, public: false, ion: true},
        {group: 3,name: 'Fakultätsrat', icon: 'file-tray-full-outline', text: 'Fakultätsrat', route: 'FRat', height: 24, width: 24, public: false, ion: true, params: {sub_route: []}},
        {group: 4,name: 'MetaSettings', icon: 'cogwheel', text: 'Portal Settings', route: 'MetaSettings', height: 24, width: 24, public: false},
        {group: 4,name: 'SendMail', icon: 'mail-outline', text: 'Send Mail', route: 'SendMail', height: 24, width: 24, public: false, ion: true},
        {group: 4,name: 'Upload', icon: 'cloud-upload-outline', text: 'Upload', route: 'Upload', height: 24, width: 24, public: false, ion: true},
        // {group: 3,name: 'History', icon: 'time-outline', text: 'History', route: 'History', height: 24, width: 24, public: false, ion: true},
        {group: 4,name: 'Graduates', icon: 'school-outline', text: 'Graduates', route: 'Graduates', height: 24, width: 24, public: false, ion: true},
      ],
      topBannerOptions: {height: 38},
      banners: [],
      bannersTop: 0,
      topNaviagtionTop: 0,
      sideNavigationLeft: 0,
      sideNavigationWidth: 0,
      scrollX: 0,
    }
  },
  created() {
    window.addEventListener("resize", this.windowResized);
    window.addEventListener("scroll", this.updateScrollVariables);
    this.innerWidth=window.innerWidth
    this.$store.commit('setScreenWidth', this.innerWidth)
  },
  computed: {
    // is_public() {
    //   // const fragments = window.location.href.split("/")
    //   // if(fragments.indexOf('#')+1==fragments.indexOf('public') || fragments.indexOf('#')+1==fragments.indexOf('bewerberportal')) {
    //   //   return true
    //   // }
    //   return false
    // },
    // redirect_on_error_403() {
    //   if(this.is_public || window.location.href.endsWith('https://www-3.mach.kit.edu/dist/') ||  window.location.href.endsWith('https://www-3.mach.kit.edu/dist')) {
    //     return false
    //   }
    //   return true
    // },
    is_public() {
      const fragments = window.location.href.split("/")
      if(fragments.indexOf('#')+1==fragments.indexOf('public') || fragments.indexOf('#')+1==fragments.indexOf('bewerberportal')) {
        return true
      }
      return false
    },
    redirect_on_error_403() {
      if(this.is_public || window.location.href.endsWith('dist/#/') ||  window.location.href.endsWith('dist/#') ||  window.location.href.endsWith('dist/') ||  window.location.href.endsWith('dist')) {
        return false
      }
      return true
    },
    topBannersStyle() {
      return {
        top: this.bannersTop+'px',
      }
    },
    hideHud() {
      return this.$route.meta.hideHud
    },
    contentDynamicStyle() {
      var paddingRight = 0
      var paddingLeft = 0
      if(this.innerWidth>620) {
        paddingRight = this.contentOptions.padding.right
        paddingLeft = this.contentOptions.padding.left
      }
      return {
        'top': `${this.sideNavigationOptions.top + this.banners.length*this.topBannerOptions.height}px`,
        'padding': `${this.contentOptions.padding.top}px ${paddingRight}px ${this.contentOptions.padding.bottom}px ${paddingLeft}px`,
        'left': this.sideNavigationOn ? `${this.sideNavigationWidth}px` : 0,
        'min-width': this.sideNavigationOn ? `calc(100vw - ${this.sideNavigationOptions.width}px)` : `calc(100vw)`,
        'min-height': `calc(100vh - ${this.sideNavigationOptions.top + this.contentOptions.padding.top + this.banners.length*this.topBannerOptions.height}px`,
      }
    },
    topNavigationDynamicStyle() {
      return {
        'top': this.topNaviagtionTop+'px',
        'height': `${this.sideNavigationOptions.top}px`,
        'background-color': `rgb(${this.sideNavigationOptions.color.r},${this.sideNavigationOptions.color.g},${this.sideNavigationOptions.color.b})`
      }
    },
    sideNavigationDynamicStyle() {
      return {
        'top': `${this.sideNavigationOptions.top+this.topNaviagtionTop}px`,
        'left': this.sideNavigationLeft+'px',
        'width': this.sideNavigationOn ? `${this.sideNavigationWidth}px` : 0,
        // 'width': this.sideNavigationOn ? `${this.sideNavigationOptions.width}px` : 0,
        'transition': `width ${this.sideNavigationOptions.transitionTime}ms ease-in`,
        // 'height': `calc(100vh - ${this.sideNavigationOptions.top}px - ${2*this.sideNavigationOptions.padding + this.banners.length*this.topBannerOptions.height}px)`,
        'background-color': `rgb(${this.sideNavigationOptions.color.r},${this.sideNavigationOptions.color.g},${this.sideNavigationOptions.color.b})`,
        'padding': `${this.sideNavigationOptions.padding}px 0`,
        'box-shadow': this.scrollX>10?'2px 2px 4px -2px rgb(0,0,0,20%)':null,
        
      }
    },
  },
  mounted() {
    // document.title = 'MACH-Portal'
    this.getProfile()
    this.updateScrollVariables()
    this.emitter.on('showErrorMessage', this.handleErrorBanner)
    this.emitter.on('handle403', this.handle403)
  },
  methods: {
    groupNavItems(navItems) {
      const groups = navItems.reduce((acc, curr) => {
        (acc[curr['group']] = acc[curr['group']] || []).push(curr);
        return acc;
      }, {})
      return Object.values(groups)
    },
    updateScrollVariables() {
      this.bannersTop = -window.scrollY
      this.scrollX = window.scrollX
      if(window.scrollX>this.sideNavigationOptions.width-50) {
        this.sideNavigationWidth = this.sideNavigationOptions.iconOnlyWidth
      } else if(window.scrollX<=0){
        this.sideNavigationWidth = this.sideNavigationOptions.width
      }
      // window
      const offset = this.bannersTop + this.banners.length*this.topBannerOptions.height
      this.topNaviagtionTop = offset>0?offset:0
      this.$store.commit('setSideNavigationWidth', this.sideNavigationWidth)
    },
    closeBanner(index) {
      this.banners.splice(index, 1)
      this.updateScrollVariables()
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
      banner['backgroundColor'] = 'rgb(188 240 218)'
      banner['color'] = 'rgb(3 84 63)'
      banner['duration'] = -1
      banner['text'] = 'Please fill out all user information in your <a href="https://www-3.mach.kit.edu/profile">profile settings</>.'
      this.banners.push(banner)
      this.updateScrollVariables()
    },
    windowResized() {
      this.innerWidth=window.innerWidth
      this.$store.commit('setScreenWidth', this.innerWidth)
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
      this.$store.commit('logout')
      this.$router.push({name: 'Home'})
      const url = this.$store.getters.getApiAuthUrl+'/logout'
      const shibLogout = 'https://www-3.mach.kit.edu/Shibboleth.sso/Logout'
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
    isNavItemActive(navItem) {
      if(this.$route.name==navItem.route) {
        return true
      }
      return false
    },
    toggleSideNavigation() {
      window.scrollTo(0,window.scrollY)
      this.sideNavigationOn = !this.sideNavigationOn
      this.$store.commit('setSideNavigationOn', this.sidenavigationOn)

    },
    async getProfile() {
      var url = this.$store.getters.getApiAuthUrl+'login'
      if(window.location.host.startsWith('localhost')) {
        url = this.$store.getters.getApiUrl+'login'
      }
      console.log(url)
      axios({
        method: 'get',
        url: url,
      }).then(response=>{
        this.user=response.data
        console.log(this.user)
        this.createTopBanners(this.user)
        this.$store.commit('profile', this.user)
        this.authCheck=true
      }).catch(error=>{
        console.log(error?.response)
        this.authCheck=true
        if(error.response?.status!==403 && error.response?.status!==undefined) {
          this.emitter.emit('showErrorMessage', {error: error.response, action: 'Get user information', redirect: null})
        } else {
          this.handle403()
        }
      })
      return this.user
    },
    handle403(){
      if(!this.redirect_on_error_403){
        return
      }
      var url = `https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=${this.encodeUrl(window.location.href)}`
      // console.log(url)
      window.location.href = url;
    },
    encodeUrl(url){
      url = url.replaceAll(":", "%3A")
      url = url.replaceAll("/", "%2F")
      url = url.replaceAll("#", "%23")
      return url
    },
    handleErrorBanner(event) {
      this.errorBanner.show = true
      this.errorBanner.error = event.error
      this.errorBanner.action = event.action
      this.errorBanner.redirect = event.redirect
    },
  },

}
</script>

<style lang="scss">
html {
  // font-size: 62.5%;
}
button {
  background: none;
  margin: 0;
  padding: 0;
  color: inherit;
  border: none;
  outline: none;
  font-family: inherit;
  font-size: inherit;
}
#top-banners {
  position: fixed;
  width: 100%;
  z-index: 110;
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
  height: 100vh;
  width: 100%;
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
  position:fixed;
  z-index: 1000;
  height: 100%;
  display: flex;
  flex-direction: column;
  gap: 14px;
}
// #content-
#content-app {
  // position: relative;
  position: absolute;
  display: inline-block;
  box-shadow: inset 2px 2px 4px -2px rgba(0,0,0,0.2);
  background-color: rgba(249, 249, 249, 1);
}


</style>