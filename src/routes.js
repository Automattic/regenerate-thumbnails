import Home from './routes/Home.vue';
import RegenerateSingle from './routes/RegenerateSingle.vue';

export default [
	{
		path     : '/',
		name     : 'home',
		component: Home,
	},
	{
		path     : '/regenerate/:id(\\d+)',
		name     : 'regenerate-single',
		component: RegenerateSingle,
		props    : true,
	},
	{
		path    : '*',
		redirect: '/',
	}
];
