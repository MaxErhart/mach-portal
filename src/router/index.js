import { createRouter, createWebHashHistory } from 'vue-router'
import Home from '../views/Home.vue'
import Dashboard from '../views/Dashboard.vue'
import CreateForm from '../views/CreateForm.vue'
import DisplayForm from '../views/DisplayForm.vue'
import AllForms from '../views/AllForms.vue'
import Submission from '../views/Submission.vue'

const routes = [
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
  {
    path: '/createform',
    name: 'CreateForm',
    component: CreateForm
  },
  {
    path: '/form',
    name: 'AllForms',
    component: AllForms
  },
  {
    path: '/submissions/:id',
    component: Submission
  },   
  {
    path: '/form/:id',
    component: DisplayForm
  }
]

const router = createRouter({
  history: createWebHashHistory(),
  routes
})

export default router
