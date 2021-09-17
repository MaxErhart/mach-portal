import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Dashboard from '../views/Dashboard.vue'
import CreateForm from '../views/CreateForm.vue'
import DisplayForm from '../views/DisplayForm.vue'
import AllForms from '../views/AllForms.vue'
import Submission from '../views/Submission.vue'
import Theses from '../views/Theses.vue'
import Rights from '../views/Rights.vue'
import Email from '../views/Email.vue'
import AnonFormSubmit from '../views/AnonFormSubmit.vue'
import MachFormSubmit from '../views/MachFormSubmit.vue'
import MatwerkFormSubmit from '../views/MatwerkFormSubmit.vue'
import TestView from '../views/TestView.vue'
//import ListAnonForm from '../views/DisplayListAnonForm.vue'
const routes = [
  {
    path: '/tmp',
    name: 'temp',
    component: TestView
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
//  {
//    path: '/listanonform',
//    name: 'ListAnonForm',
//    component: DisplayAnonForm
//  },
  {
    path: '/createform/:id?',
    name: 'CreateForm',
    component: CreateForm
  },
  {
    path: '/email/:id?',
    name: 'Email',
    component: Email
  },  
  {
    path: '/form',
    name: 'AllForms',
    component: AllForms,
  },

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

export default router
