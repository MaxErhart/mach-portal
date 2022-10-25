import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Dashboard from '../views/Dashboard.vue'
// import CreateForm from '../views/CreateForm.vue'
import DisplayForm from '../views/DisplayForm.vue'
// import AllForms from '../views/AllForms.vue'
import Submission from '../views/Submission.vue'
import Theses from '../views/Theses.vue'
import Rights from '../views/Rights.vue'
// import Email from '../views/Email.vue'
import AnonFormSubmit from '../views/AnonFormSubmit.vue'
import MachFormSubmit from '../views/MachFormSubmit.vue'
import MatwerkFormSubmit from '../views/MatwerkFormSubmit.vue'
import TestView from '../views/TestView.vue'
import Stundenzettel from '../views/Stundenzettel.vue'
import Testsheet from '../views/Testsheet.vue'
import Apps from '../views/Apps.vue'
import GroupAppSettings from '../views/GroupAppSettings.vue'
import CreateForms from '../views/CreateForms.vue'
import Forms from '../views/Forms.vue'
import Tags from '../views/Tags.vue'
import SubmitForm from '../views/SubmitForm.vue'
import PublicForm from '../views/PublicForm.vue'
import Bewerberportal from '../views/Bewerberportal.vue'
import BewerberportalAdmin from '../views/BewerberportalAdmin.vue'
import Lehrveranstaltungen from '../views/Lehrveranstaltungen.vue'
import Login from '../views/Login.vue'
import Permissions from '../views/Permissions.vue'
import Profile from '../views/Profile.vue'
//import ListAnonForm from '../views/DisplayListAnonForm.vue'
// import store from '../store';
const routes = [
  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: {
      requiresAuth: true,
    }
  },
  {
    path: '/permissions',
    name: 'Permissions',
    component: Permissions,
  },
  {
    path: '/login',
    name: 'Login',
    component: Login,
  },
  {
    path: '/lv/users',
    name: 'LvUsers',
    component: Lehrveranstaltungen,
  },
  {
    path: '/lv',
    name: 'LvLehrveranstaltungen',
    component: Lehrveranstaltungen,
  },
  {
    path: '/lv/exams',
    name: 'LvExams',
    component: Lehrveranstaltungen,
  },  



  {
    path: '/admin/bewerberportal',
    name: 'Exams',
    component: BewerberportalAdmin,
  },
  {
    path: '/admin/bewerberportal/new/:id?',
    name: 'NewExam',
    component: BewerberportalAdmin,
    meta: {new: true}
  },


  {
    path: '/bewerberportal',
    name: 'Bewerberportal',
    component: Bewerberportal,
    meta: {new: false}
  },
  {
    path: '/bewerberportal/new',
    name: 'BewerberportalRegistration',
    component: Bewerberportal,
    meta: {new: true}
  },

  {
    path: '/Testsheet',
    name: 'Testsheet',
    component: Testsheet
  },
    {
        path: '/stundenzettel',
        name: 'stundenzettel',
        component: Stundenzettel
    },
  {
    path: '/tmp',
    name: 'temp',
    component: TestView
  },


  // routes related to apps
  {
    path: '/apps/:id?',
    name: 'Apps',
    component: Apps,
    meta: {new: false}

  },
  {
    path: '/apps/new/:id?',
    name: 'NewApp',
    component: Apps,
    meta: {new: true}
  },


  // routes related to group app settings
  {
    path: '/group_app_settings/:id?',
    name: 'GroupAppSettings',
    component: GroupAppSettings,
    meta: {new: false}

  },
  {
    path: '/group_app_settings/new/:id?',
    name: 'NewGroupAppSettings',
    component: GroupAppSettings,
    meta: {new: true}

  },   


  // routes related to forms
  {
    path: '/forms/:id?',
    name: 'Forms',
    component: Forms,
  },
  {
    path: '/createforms/:id?',
    name: 'CreateForms',
    component: CreateForms,
    meta: {new: false}
  },
  {
    path: '/createforms/new/:id?',
    name: 'NewForm',
    component: CreateForms,
    meta: {new: true}
  },
  // route related to form submissions
  {
    path: '/submit/:id',
    name: 'SubmitForm',
    component: SubmitForm,
  },
  {
    path: '/public/form/:id',
    name: 'PublicForm',
    component: PublicForm,
  },  
  // {
  //   path: '/submit/:id',
  //   name: 'ViewSubmissions',
  //   component: SubmitForm,
  // },


  // routes related to tags
  {
    path: '/tags/:id?',
    name: 'Tags',
    component: Tags,
    meta: {new: false}
  },
  {
    path: '/tags/new/:id?',
    name: 'NewTag',
    component: Tags,
    meta: {new: true}
  },


  {
    path: '/',
    name: 'Home',
    component: Home
  },
  {
    path: '/about',
    name: 'About',
    // route level code-splitting
    // this generates a separate chunk (about.[hash].js) for this route
    // which is lazy-loaded when the route is visited.
    component: () => import(/* webpackChunkName: "about" */ '../views/About.vue')
  },
  {
    path: '/dashboard',
    name: 'Dashboard',
    component: Dashboard
  },

  // {
  //   path: '/createform/:id?',
  //   name: 'CreateForm',
  //   component: CreateForm
  // },
  // {
  //   path: '/email/:id?',
  //   name: 'Email',
  //   component: Email
  // },  
  // {
  //   path: '/form',
  //   name: 'AllForms',
  //   component: AllForms,
  // },

  {
    path: '/anon/:id',
    name: 'AnonFormSubmit',
    component: AnonFormSubmit
  },


  {
    path: '/mach_entrance_exam/',
    meta: {
      id: '70'
    },
    name: 'MachEntranceExam',
    component: MachFormSubmit
  },
  {
    path: '/matwerk_entrance_exam/',
    meta: {
      id: '74'
    },
    name: 'MatwerkEntranceExam',
    component: MatwerkFormSubmit
  },  
  {
    path: '/submissions/:id',
    component: Submission
  },   
  {
    path: '/form/:id/:submissionId?',
    component: DisplayForm,   
  },
  {
    path: '/theses',
    name: Theses,
    component: Theses,
    children: [
      {
        path: 'newthesis/:id',
        component: DisplayForm
      }
    ]
  },
  {
    path: '/rights',
    name: Rights,
    component: Rights
  }

]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})


// router.beforeEach((to)=>{
//   store.dispatch('authenticate').then(()=>{
//     console.log('K')
//   })
//   if(to.meta.requiresAuth && !store.getters.getIsAuthenticated) {
//     store.dispatch('authenticate').then(()=>{
//       console.log('K')
//     }).catch(()=>{
//       var url = window.location.href
//       url = url.replaceAll(":", "%3A")
//       url = url.replaceAll("/", "%2F")
//       url = url.replaceAll("#", "%23")
//       url = `https://www-3.mach.kit.edu/Shibboleth.sso/Login?target=${this.encodeUrl(window.location.href)}`
//       window.location.href = url;
//     })

//   }
// })

export default router
