import { createStore } from 'vuex'

export default createStore({
  state: {
    currentRoute: {index: 0, route: '/', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z', name: 'Home', signedIn: false},
    routes: [{index: 0, route: '/', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z', name: 'Home', signedIn: false}, {index: 1, route: '/dashboard', icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', name: 'Dashboard', signedIn: true}, {index: 2, route: '/about', icon: 'M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z', name: 'About', signedIn: true}],
    navItemMainHeight: 40,
    isSignedIn: true,
    loginFormActive: false,
  },
  mutations: {
    setCurrentRoute(state, payloade) {
      // console.log(payloade)
      state.currentRoute = payloade;
    },
    logout(state) {
      state.isSignedIn = false;
      state.currentRoute = state.routes[0];
    },
    login(state, payload) {
      state.isSignedIn = true;
      console.log(payload);
    },
    changeLoginFormActive(state, payload) {
      state.loginFormActive = payload;
    }
  },
  actions: {

  },
  modules: {
  },
  getters: {
    getRoutes(state) {
      return state.routes;
    },
    getCurrentRoute(state) {
      return state.currentRoute;
    },
    getNavItemMainHeight(state) {
      return state.navItemMainHeight;
    },
    getIsSignedIn(state) {
      return state.isSignedIn;
    },
    getLoginForm(state){
      return state.loginFormActive;
    }
  }
})
