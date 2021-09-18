let mix = require('laravel-mix');
var tailwindcss = require('tailwindcss');
const webpack = require('webpack');
const autoprefixer = require('autoprefixer');
const path = require('path');
const rootPath = process.cwd();
let glob = require("glob-all");
let PurgecssPlugin = require("purgecss-webpack-plugin");

// Used to remove bluebird promise warnings emitted from KSS
const promise = require('bluebird');
promise.config({
	warnings: false,
});

// KSS settings
const KssWebpackPlugin = require('kss-webpack-plugin');
const KssConfig = {
	source: ['./resources/sass'],
	destination: `public/styleguide`,
	homepage: '../../../readme.md',
	title: 'Shyft Styleguide',
	css:['/css/app.css'],
	//js: ['/js/manifest.js','/js/vendor.js','/js/app.js']
};

const config = {
	paths: {
		assets: path.join(rootPath, 'sass'),
		dist: path.join(rootPath, 'css'),
	},
	browsers: [
		"last 2 versions",
		"android 4",
		"opera 12"
	],
};


// Custom PurgeCSS extractor for Tailwind that allows special characters in
// class names.
//
// https://github.com/FullHuman/purgecss#extractor
class TailwindExtractor {
  static extract(content) {
    return content.match(/[A-z0-9-:\/]+/g) || [];
  }
}
// Only run PurgeCSS during production builds for faster development builds
// and so you still have the full set of utilities available during
// development.


let webpackConfig = {
	externals: {
		// require("jquery") is external and available
		//  on the global var jQuery
		//"jquery": "jQuery"
	},
	plugins: [
		new webpack.LoaderOptionsPlugin({
			test: /\.s?css$/,
			options: {
				output: { path: config.paths.dist },
				context: config.paths.assets,
				postcss: [
					autoprefixer({ browsers: config.browsers }),
				],
			},
		}),
		new KssWebpackPlugin(KssConfig),
	],
};


// Only run PurgeCSS during production builds for faster development builds
// and so you still have the full set of utilities available during
// development.
if (mix.inProduction()) {
	webpackConfig.plugins.push(new PurgecssPlugin({
			// Whitelist of classes to not extractor
			whitelist: [],
      // Specify the locations of any files you want to scan for class names.
      paths: glob.sync([
				path.join(__dirname, "resources/views/**/*.php"),
				path.join(__dirname, "resources/js/**/*.vue"),
      ]),
      extractors: [
        {
          extractor: TailwindExtractor,

          // Specify the file extensions to include when scanning for
          // class names.
          extensions: ["html", "js", "php", "vue"]
        }
      ]
    })
	);
}

mix.webpackConfig(webpackConfig);

/*
 |--------------------------------------------------------------------------
 | Mix Asset Management
 |--------------------------------------------------------------------------
 |
 | Mix provides a clean, fluent API for defining some Webpack build steps
 | for your Laravel application. By default, we are compiling the Sass
 | file for the application as well as bundling up all the JS files.
 |
 */

mix.js('resources/js/app.js', 'public/js')
   .vue()
   .sass('resources/sass/app.scss', 'public/css')
	 .options({
     processCssUrls: false,
     postCss: [ tailwindcss(path.resolve(__dirname, 'tailwind-config.js')) ],
   });
