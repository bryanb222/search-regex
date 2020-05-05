/** @format */

const path = require( 'path' );
const webpack = require( 'webpack' );

// PostCSS plugins
const postcssPresetEnv = require( 'postcss-preset-env' );
const postcssFocus = require( 'postcss-focus' );
const postcssReporter = require( 'postcss-reporter' );

const isProduction = () => process.env.NODE_ENV === 'production';
const getDevUrl = 'http://localhost:3312/';
const pkg = require( './package.json' );

const config = {
	entry: [ path.join( __dirname, 'client', 'index.js' ) ],
	output: {
		path: path.join( __dirname ),
		filename: 'search-regex.js',
		chunkFilename: 'search-regex-[name]-[chunkhash].js',
	},
	module: {
		rules: [
			{
				test: /\.js$/,
				exclude: /node_modules/,
				loader: 'babel-loader?cacheDirectory',
			},
			{
				test: /\.json$/,
				loader: 'json-loader',
			},
			{
				test: /\.scss$/,
				exclude: /node_modules/,
				use: [ 'style-loader', 'css-loader', 'postcss-loader', 'sass-loader' ],
			},
			{
				test: [ path.resolve( __dirname, 'node_modules/redbox-react' ) ],
				use: 'null-loader',
			},
		],
	},
	resolve: {
		extensions: [ '.js', '.jsx', '.json', '.scss', '.css' ],
		modules: [ path.resolve( __dirname, 'client' ), 'node_modules' ],
	},
	plugins: [
		new webpack.BannerPlugin( 'Search Regex v' + pkg.version ),
		new webpack.DefinePlugin( {
			'process.env': { NODE_ENV: JSON.stringify( process.env.NODE_ENV || 'development' ) },
			SEARCHREGEX_VERSION: "'" + pkg.version + "'",
		} ),
		new webpack.LoaderOptionsPlugin( {
			options: {
				postcss: [
					postcssFocus(),
					postcssPresetEnv( {
						browsers: [ 'last 2 versions', 'IE > 10' ],
					} ),
					postcssReporter( {
						clearMessages: true,
					} ),
				],
			},
		} ),
	],
	watchOptions: {
		ignored: [ /node_modules/ ],
	},
	performance: {
		hints: false,
	},
};

if ( isProduction() ) {
	config.plugins.push( new webpack.LoaderOptionsPlugin( { minimize: true } ) );
	config.module.rules.push( { test: /\.js$/, loader: 'webpack-remove-debug' } );
} else {
	config.output.publicPath = getDevUrl;
	config.devtool = 'inline-source-map';
	config.resolve.alias = {
		'react-dom': '@hot-loader/react-dom',
	};
	config.devServer = {
		historyApiFallback: {
			index: '/',
		},
		contentBase: path.resolve( __dirname ),
		publicPath: getDevUrl,
		headers: { 'Access-Control-Allow-Origin': '*' },
		stats: {
			colors: true,
			hash: false,
			version: true,
			timings: true,
			assets: true,
			chunks: false,
			modules: false,
			reasons: false,
			children: false,
			source: false,
			errors: true,
			errorDetails: true,
			warnings: false,
			publicPath: false,
		},
		disableHostCheck: true,
	};
}

module.exports = config;
