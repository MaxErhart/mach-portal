import { createStore } from 'vuex'

export default createStore({
  state: {
    currentRoute: {route: '/', name: 'Home', topic: 'home', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z'},
    routes: [
      {route: '/', name: 'Home', topic: 'home', icon: 'm12,1.25552l-11.96916,10.99779l3.7578,0l0,10.49117l16.42272,0l0,-10.49117l3.7578,0l-11.96916,-10.99779z'},
      {route: '/theses', name: 'Theses', topic: 'theses', icon: 'm3.9009,11.86368l0,4.55911a7.92787,3.96393 0 0 0 7.92787,3.96394a7.92787,3.96393 0 0 0 7.92787,-3.96394l0,-4.55911l-7.92787,3.56812l-3.96394,-1.78454l0,2.77553l-0.99098,-0.99098l-0.99098,0.99098l0,-3.66683l-1.98196,-0.89227zm3.68819,-8.25041l4.29567,0l11.8918,5.35131l-11.8918,5.35131l-11.8918,-5.35131l11.8918,-5.35131l-4.29567,0z'},
      {route: '/dashboard', name: 'Dashboard', topic: 'dashboard', icon: 'M3 13h8V3H3v10zm0 8h8v-6H3v6zm10 0h8V11h-8v10zm0-18v6h8V3h-8z'},
      {route: '/about', name: 'About', topic: 'about', icon: 'M19 3h-4.18C14.4 1.84 13.3 1 12 1c-1.3 0-2.4.84-2.82 2H5c-1.1 0-2 .9-2 2v14c0 1.1.9 2 2 2h14c1.1 0 2-.9 2-2V5c0-1.1-.9-2-2-2zm-7 0c.55 0 1 .45 1 1s-.45 1-1 1-1-.45-1-1 .45-1 1-1zm2 14H7v-2h7v2zm3-4H7v-2h10v2zm0-4H7V7h10v2z'},
      {route: '/rights', name: 'Edit Rights', topic: 'editRights', icon: 'm9.33135,0.24096c-3.44696,0 -6.26053,2.81357 -6.26053,6.26053c0,2.15722 1.10156,4.07118 2.76767,5.19569c-3.18993,1.36777 -5.44813,4.53475 -5.44813,8.2158l1.79003,0c0,-3.96102 3.19452,-7.15555 7.15555,-7.15555c1.91855,0 3.63056,0.78486 4.91571,2.01035l-4.21805,4.21805l-0.05508,0.27998l-0.61504,3.1578l-0.27998,1.31269l1.31269,-0.27998l3.1578,-0.61504l0.27998,-0.05508l9.02361,-9.02361c1.0373,-1.0373 1.0373,-2.76308 0,-3.80038c-0.51865,-0.51865 -1.21172,-0.78027 -1.90019,-0.78027c-0.67471,0 -1.354,0.25703 -1.87265,0.75273l-3.54794,3.54794c-0.78027,-0.74814 -1.70283,-1.35859 -2.71259,-1.79003c1.66611,-1.12451 2.76767,-3.03847 2.76767,-5.1911c0,-3.44696 -2.81357,-6.26053 -6.26053,-6.26053zm0,1.79003c2.47851,0 4.47049,1.99199 4.47049,4.47049s-1.99199,4.47049 -4.47049,4.47049s-4.47049,-1.99199 -4.47049,-4.47049s1.99199,-4.47049 4.47049,-4.47049zm11.62604,8.94099c0.2249,0 0.46357,0.07344 0.64258,0.25244c0.35801,0.35801 0.35801,0.89961 0,1.25761l-8.6656,8.66101l-1.59267,0.33506l0.33506,-1.59267l8.66101,-8.66101c0.18359,-0.179 0.39014,-0.25244 0.61963,-0.25244z'},
      {route: '/form', name: 'View Forms', topic: 'viewForm', icon: 'm6.62307,0.16328l15.90642,0l0,2.39394l-15.90642,0l0,-2.39394zm-5.37307,0l2.97913,0l0,2.39394l-2.97913,0l0,-2.39394zm5.37307,5.31988l15.90642,0l0,2.39394l-15.90642,0l0,-2.39394zm-5.37307,0l2.97913,0l0,2.39394l-2.97913,0l0,-2.39394zm5.37307,5.31987l15.90642,0l0,2.39394l-15.90642,0l0,-2.39394zm-5.37307,0l2.97913,0l0,2.39394l-2.97913,0l0,-2.39394zm0.22052,5.31988l2.97913,0l0,2.39394l-2.97913,0l0,-2.39394zm5.15256,5.31987l15.90642,0l0,2.39394l-15.90642,0l0,-2.39394zm-5.15256,0l2.97913,0l0,2.39394l-2.97913,0l0,-2.39394zm5.37307,-5.31987l15.90642,0l0,2.39394l-15.90642,0l0,-2.39394z'},
      {route: '/createform', name: 'Create Form', topic: 'createForm', icon: 'm16.4695,22.23618l-14.65185,0l0,-20.56184l14.6518,0l0,5.09679l1.56197,-1.56202l0,-4.31577c0.00005,-0.43129 -0.34969,-0.78099 -0.78094,-0.78099l-16.21382,0c-0.43134,0 -0.78113,0.34969 -0.78113,0.78099l0,22.12391c0,0.43129 0.34974,0.78099 0.78113,0.78099l16.21382,0c0.43124,0 0.78099,-0.34969 0.78099,-0.78099l0,-8.59422l-1.56197,1.56202l0,6.25112l0,0.00001z m-2.03,-18.69l-10.04671,0c-0.32942,0 -0.59675,0.26732 -0.59675,0.59685c0,0.32947 0.26732,0.5968 0.59675,0.5968l10.04666,0c0.32952,0 0.5968,-0.26732 0.5968,-0.5968c0.00005,-0.32952 -0.26727,-0.59685 -0.59675,-0.59685z m0.62,3.78c0,-0.32947 -0.26732,-0.59685 -0.5968,-0.59685l-10.04671,0c-0.32942,0 -0.59675,0.26737 -0.59675,0.59685c0,0.32952 0.26732,0.59685 0.59675,0.59685l10.04666,0c0.32952,0 0.59685,-0.26727 0.59685,-0.59685z m-1.43,2.58l-9.20704,0c-0.32942,0 -0.59675,0.26737 -0.59675,0.59685c0,0.32952 0.26732,0.5968 0.59675,0.5968l8.01335,0l1.1937,-1.19365l-0.00001,0z m-3.4,3.96c0.08078,-0.27045 0.19622,-0.52959 0.33684,-0.77362l-6.20533,0c-0.32942,0 -0.59675,0.26732 -0.59675,0.59685c0,0.32947 0.26732,0.59685 0.59675,0.59685l5.74337,0l0.12512,-0.42007l0,-0.00001z m-5.75,2.41c-0.32942,0 -0.59675,0.26732 -0.59675,0.59685c0,0.32942 0.26732,0.5968 0.59675,0.5968l5.24252,0c-0.15468,-0.37531 -0.19468,-0.78994 -0.08545,-1.19365l-5.15706,0l-0.00001,0z m18.8,-11.65c-0.31272,-0.31286 -0.73674,-0.48844 -1.17848,-0.48844c-0.44174,0 -0.86566,0.17557 -1.17805,0.48805l-8.82977,8.83001c-0.24094,0.24085 -0.41767,0.53734 -0.51525,0.86374l-0.70362,2.35861c-0.02994,0.09985 -0.00274,0.20787 0.0711,0.28172c0.05402,0.05407 0.12632,0.08314 0.20046,0.08314c0.02687,0 0.0544,-0.00385 0.08126,-0.01199l2.35846,-0.70372c0.32649,-0.09753 0.62299,-0.27435 0.86379,-0.5152l8.83006,-8.82967c0.31238,-0.31243 0.48805,-0.73631 0.48805,-1.1781c0,-0.44188 -0.17562,-0.86576 -0.488,-1.17814l0,-0.00001l-0.00001,0z'},
    ],
    navItemMainHeight: 40,
    selectionsData: [],
    formSubmissionData: {},
    isSignedIn: false,
    loginFormActive: false,
    userInformation: null,
    baseUrl: 'https://www-3.mach.kit.edu/',
    baseFileUrl: 'https://www-3.mach.kit.edu/dfiles/',
  },
  mutations: {
    setFormSubmissionData(state, payload) {
      state.formSubmissionData = payload;
    },
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
    setSelections(state, payload) {
      state.selectionsData = payload
      console.log(state.selectionsData)
    },
    addSelection(state, payload) {
      state.selectionsData.push(payload);
      console.log(state.selectionsData)
    },
    updateSelectionsData(state, payload) {
      state.selectionsData.find(el => el.elementId == payload.elementId).data = payload.data;
      console.log(state.selectionsData)
    },
    updateSelectionsOrder(state, payload) {
      var tempSelections = [];
      for (var i=0; i<payload.length; i++) {
        tempSelections.push(state.selectionsData.find(el => el.elementId == payload[i].elementId));
      }
      state.selectionsData = tempSelections;
      console.log(state.selectionsData)
    },
    deleteSelection(state, payload) {
      const index = state.selectionsData.indexOf(state.selectionsData.find(el => el.id == payload.id));
      state.selectionsData.splice(index, 1);
    },
    deleteSelections(state) {
      state.selectionsData = []
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
    getFormSubmissionData(state) {
      return state.formSubmissionData;
    },
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
    },
  }
})
