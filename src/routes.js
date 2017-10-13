import Home from './routes/Home.vue';
import Settings from './routes/Settings.vue';
import RegenerateSingle from './routes/RegenerateSingle.vue';

export default [
	{
		path     : '/',
		name     : 'home',
		component: Home,
	},
	{
		path     : '/settings',
		name     : 'settings',
		component: Settings,
	},
	{
		path     : '/regenerate/:id(\\d+)',
		name     : 'regenerate-single',
		component: RegenerateSingle,
	},
	{
		path    : '*',
		redirect: '/',
	}
];
