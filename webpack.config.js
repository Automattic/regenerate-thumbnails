const BrowserSyncConfig = require('./browsersync-config.json');
const path = require('path');
const webpack = require('webpack');
const BrowserSyncPlugin = require('browser-sync-webpack-plugin');
const CleanWebpackPlugin = require('clean-webpack-plugin');

module.exports = {
	entry      : './src/main.js',
	output     : {
		path      : path.resolve(__dirname, './dist'),
		publicPath: '/dist/',
		filename  : 'build.js'
	},
	module     : {
		rules: [
			{
				test   : /\.vue$/,
				loader : 'vue-loader',
				options: {
					loaders: {
						// Since sass-loader (weirdly) has SCSS as its default parse mode, we map
						// the "scss" and "sass" values for the lang attribute to the right configs here.
						// other preprocessors should work out of the box, no loader config like this necessary.
						'scss': 'vue-style-loader!css-loader?-url!sass-loader',
						'sass': 'vue-style-loader!css-loader?-url!sass-loader?indentedSyntax'
					}
				}
			},
			{
				test   : /\.js$/,
				exclude: /node_modules/,
				use    : {
					loader : 'babel-loader',
					options: {
						presets: ['@babel/preset-env']
					}
				}
			},
			{
				test   : /\.(png|jpg|gif|svg)$/,
				loader : 'file-loader',
				options: {
					name: '[name].[ext]?[hash]'
				}
			}
		]
	},
	resolve    : {
		alias: {
			'vue$': 'vue/dist/vue.esm.js'
		}
	},
	performance: {
		hints: false
	},
	devtool    : '#eval-source-map',
	plugins    : [
		new BrowserSyncPlugin({
				proxy      : BrowserSyncConfig.WordPressInstallURL + '/wp-admin/tools.php?page=regenerate-thumbnails',
				host       : BrowserSyncConfig.proxyHost,
				port       : BrowserSyncConfig.proxyPort,
				files      : [
					'**/*.php',
					'css/progressbar.css'
				],
				reloadDelay: 0,
				notify     : {
					styles: {
						top   : 'auto',
						bottom: '0',
					}
				}
			}
		),
	],
};

if (process.env.NODE_ENV === 'production') {
	module.exports.devtool = '#source-map';
	// http://vue-loader.vuejs.org/en/workflow/production.html
	module.exports.plugins = (module.exports.plugins || []).concat([
		new webpack.DefinePlugin({
			'process.env': {
				NODE_ENV: '"production"'
			}
		}),
		new CleanWebpackPlugin([
			path.resolve(__dirname, './dist'),
		]),
		new webpack.optimize.UglifyJsPlugin({
			sourceMap: false,
			compress : {
				warnings: false
			}
		}),
		new webpack.LoaderOptionsPlugin({
			minimize: true
		})
	])
}
