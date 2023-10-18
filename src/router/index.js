import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import CreateForms from '../views/CreateForms.vue'
import Forms from '../views/Forms.vue'
import SubmitForm from '../views/SubmitForm.vue'
// import Permissions from '../views/Permissions.vue'
import Profile from '../views/Profile.vue'
import PublicForm from '../views/PublicForm.vue'
import TestView from '../views/TestView.vue'
import Bewerberportal from '../views/Bewerberportal.vue'
import BewerberportalAdmin from '../views/BewerberportalAdmin.vue'
import Upload from '../views/Upload2.vue'
import MetaSettings from '../views/MetaSettings.vue'
import History from '../views/History.vue'
import SendMail from '../views/SendMail.vue'
import Graduates from '../views/Graduates.vue'
import Form from '../views/Form.vue'
import Archive from '../views/Archive.vue'
// import FRat from '../views/FRat.vue'

const routes = [

  {
    path: '/frat/:sub_route*',
    name: 'FRat',
    component: Archive,
    meta: {root: "frat", reload: "name"}
  },

  {
    path: '/archive/:sub_route*',
    name: 'Archive',
    component: Archive,
    meta: {root: "", reload: "name"}
  },

  // Manage graduates
  {
    path: '/graduates',
    name: 'Graduates',
    component: Graduates,
  },

  // Testing submit form (unfinished)
  {
    path: '/form',
    name: 'Form',
    component: Form,
  },

  // Routes related to form submissions
  {
    path: '/submit/:id',
    name: 'submissions',
    component: SubmitForm,
  },
  {
    path: '/submit/:id/submit/:submission_id?',
    name: 'submit',
    component: SubmitForm,
  },
  {
    path: '/submit/:id/bescheide',
    name: 'bescheide',
    component: SubmitForm,
  },
  {
    path: '/submit/:id/info',
    name: 'info',
    component: SubmitForm,
  },
  {
    path: '/submit/:id/upload',
    name: 'upload_submissions',
    component: SubmitForm,
  },

  // Routes related to action sending mails
  {
    path: '/send_mail',
    name: 'SendMail',
    component: SendMail,
  },

  // Routes related to action history
  {
    path: '/history',
    name: 'History',
    component: History,
  },

  // Routes related to MACH-Portal meta settings
  {
    path: '/admin/metasettings',
    name: 'MetaSettings',
    component: MetaSettings,
  },
  {
    path: '/admin/permissions',
    name: 'Permissions',
    component: MetaSettings,
  },



  {
    path: '/upload',
    name: 'Upload',
    component: Upload,
  },
  {
    path: '/upload/edit',
    name: 'UploadEdit',
    component: Upload,
  },

  {
    path: '/bewerberportal',
    name: 'Bewerberportal',
    component: Bewerberportal,
  },
  {
    path: '/admin/bewerberportal',
    name: 'Exams',
    component: BewerberportalAdmin,
  },
  {
    path: '/admin/bewerberportal',
    name: 'NewExam',
    component: BewerberportalAdmin,
    meta: {new: true},
  },

  {
    path: '/profile',
    name: 'Profile',
    component: Profile,
    meta: {
      requiresAuth: true,
    }
  },
  {
    path: '/tmp',
    name: 'temp',
    component: TestView
  },

  


  // routes related to forms
  {
    path: '/forms/:id?',
    name: 'Forms',
    component: Forms,
  },
  {
    path: '/createforms',
    name: 'CreateForms',
    component: CreateForms,
    meta: {new: false, reload: null}
  },
  {
    path: '/createforms/new/:id?',
    name: 'NewForm',
    component: CreateForms,
    meta: {new: true, reload: null}
  },
  {
    path: '/createforms/upload/:id',
    name: 'UploadSubmissions',
    component: CreateForms,
    meta: {upload: true, reload: null}
  },
  {
    path: '/public/form/:id',
    name: 'PublicForm',
    component: PublicForm,
    meta: {hideHud: true, login: false},
  },

  {
    path: '/',
    name: 'Home',
    component: Home
  },


]
const router = createRouter({
  // history: createWebHistory(),
  history: createWebHashHistory(process.env.BASE_URL),

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
