import Main from './components/Main.vue';
import Regenerate from './components/Regenerate.vue';

export default [
	{
		path     : '/',
		component: Main,
	},
	{
		path     : '/regenerate/:id',
		component: Regenerate,
	},
	{
		path    : '*',
		redirect: '/',
	}
];