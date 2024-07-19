const path = require('path');
const webpack = require('webpack');

module.exports = (env) => {
	const isDevelopment = env.NODE_ENV === 'development';

	return {
		mode: isDevelopment ? 'development' : 'production',
		entry: './resources/js/common.js',
		output: {
			filename: 'bundle.js',
			path: path.resolve(__dirname, './dist/js'),
		},
		devtool: isDevelopment ? 'source-map' : false,
		resolve: {
			extensions: ['.js'],
            modules: [path.resolve(__dirname, 'node_modules'), 'node_modules'] // Ensure node_modules are resolved
        },
		module: {
			rules: [
				{
					test: /\.js$/,
					exclude: /node_modules/,
					use: 'babel-loader'
				},
				{
					test: /\.css$/,
					use: ['style-loader', 'css-loader']
				}
			]
		},
		plugins: [
			new webpack.ProvidePlugin({
				$: 'jquery',
				jQuery: 'jquery'
			})
		],
	};
};