import Vue from 'vue';
import VueRouter from 'vue-router';
import Vuelidate from 'vuelidate'
Vue.use(Vuelidate)
Vue.use(VueRouter);

import App from './src/App.vue';
import ListProjects from './src/ListProjects.vue';
import ViewProject from './src/ViewProject.vue';
import EditProject from './src/EditProject.vue';
import ListContacts from './src/ListContacts.vue';

import './vendor/bulma.min.css';
import './src/app.css';

new Vue({
  el: '#app',
  render: make => make(App),
  router: new VueRouter({
    routes: [
      {
        path: '',
        redirect: '/projects',
      },
      {
        path: '/projects',
        component: ListProjects,
      },
      {
        path: '/projects/:project_id',
        component: ViewProject,
      },
      {
        path: '/projects/:project_id/edit',
        component: EditProject,
      },
      {
        path: '/contacts',
        component: ListContacts,
      },
    ],
    mode: 'history',
  }),
});

document.title = 'Pylon Demo App';
