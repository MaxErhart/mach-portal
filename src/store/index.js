import { createStore } from 'vuex'

export default createStore({
  state: {
    currentRoute: {index: 0, route: '/', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z', name: 'Home', signedIn: false},
    routes: [
      {index: 0, route: '/', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z', name: 'Home', signedIn: false},
      {index: 1, route: '/theses', icon: 'm3.9009,11.86368l0,4.55911a7.92787,3.96393 0 0 0 7.92787,3.96394a7.92787,3.96393 0 0 0 7.92787,-3.96394l0,-4.55911l-7.92787,3.56812l-3.96394,-1.78454l0,2.77553l-0.99098,-0.99098l-0.99098,0.99098l0,-3.66683l-1.98196,-0.89227zm3.68819,-8.25041l4.29567,0l11.8918,5.35131l-11.8918,5.35131l-11.8918,-5.35131l11.8918,-5.35131l-4.29567,0z', name: 'Theses', signedIn: false},
      {index: 2, route: '/dashboard', icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z', name: 'Dashboard', signedIn: true},
      {index: 3, route: '/about', icon: 'M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z', name: 'About', signedIn: true}
    ],
    navItemMainHeight: 40,
    selectionsData: [],
    isSignedIn: false,
    loginFormActive: false,
    userInformation: null,
    baseUrl: 'https://www-3.mach.kit.edu/',
    baseFileUrl: 'https://www-3.mach.kit.edu/dfiles/',
  },
  mutations: {
    setCurrentRoute(state, payloade) {
      state.currentRoute = payloade;
    },
    logout(state) {
      state.isSignedIn = false;
    },
    login(state, payload) {
      state.isSignedIn = true;
      state.loginFormActive = false;
      state.userInformation = payload;
    },
    changeLoginFormActive(state, payload) {
      state.loginFormActive = payload;
    },
    addSelection(state, payload) {
      state.selectionsData.push(payload);
    },
    updateSelectionsData(state, payload) {
      state.selectionsData.find(el => el.id == payload.id).data = payload.data;
    },
    updateSelectionsOrder(state, payload) {
      var tempSelections = [];
      for (var i=0; i<payload.length; i++) {
        tempSelections.push(state.selectionsData.find(el => el.id == payload[i].id));
      }
      state.selectionsData = tempSelections;
    },
    deleteSelection(state, payload) {
      const index = state.selectionsData.indexOf(state.selectionsData.find(el => el.id == payload.id));
      state.selectionsData.splice(index, 1);
    },
    updateUserInformation(state, payload) {
      state.userInformation = payload
    }
  },
  actions: {

  },
  modules: {
  },
  getters: {
    getUserInformation(state) {
      return state.userInformation;
    },
    getSelectionsData(state) {
      return state.selectionsData;
    },
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
    },
    getBaseUrl(state){
      return state.baseUrl;
    },
    getBaseFileUrl(state){
      return state.baseFileUrl;
    }
  }
})
