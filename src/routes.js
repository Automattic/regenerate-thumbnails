import Main from './routes/Main.vue';
import RegenerateSingle from './routes/RegenerateSingle.vue';

export default [
	{
		path     : '/',
		component: Main,
	},
	{
		path     : '/regenerate/:id',
		component: RegenerateSingle,
	},
	{
		path    : '*',
		redirect: '/',
	}
];