import Vue from 'vue'
import VueRouter from 'vue-router'
import routes from './routes.js'

Vue.use(VueRouter);

const router = new VueRouter({
	routes: routes
});

const app = new Vue({
	router
}).$mount('#regenerate-thumbnails-app');
