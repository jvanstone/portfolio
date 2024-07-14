const path = require('path');
const webpack = require('webpack');

module.exports = {
	entry: './resources/js/common.js',
	resolve: {
		extensions: ['.js'],
		modules: [path.resolve(__dirname, 'src'), 'node_modules']
    },
    output: {
        filename: 'bundle.js',
        path: path.resolve(__dirname, './dist/js'),
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
    mode: 'production'
};