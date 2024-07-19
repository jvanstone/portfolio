const { src, dest, parallel, series, watch } = require('gulp');
const minifyCss = require('gulp-clean-css');
const concat = require('gulp-concat');
const uglify = require('gulp-terser');
const gulpESLintNew = require('gulp-eslint-new');
const sass = require('gulp-sass')(require('sass'));
const webpack = require('webpack');
const gulpWebpack = require('webpack-stream');
const path = require('path');

// Paths constants
const paths = {
    js: {
        src: './resources/js/common.js',
        dest: './dist/js/'
    },
    scss: {
        src: './resources/scss/style.scss'
    },
    html: {
        src: './**/*.html'
    }
};

// Function to compile Sass into CSS, minify it, concatenate, and output to dist/css
function styles() {
    return src(paths.scss.src)
        .pipe(sass().on('error', sass.logError))
        .pipe(minifyCss())
        .pipe(concat('style.css'))
        .pipe(dest('./dist/css/'));
}

// Function to bundle JS using Webpack and output to dist/js
function bundleJS(env) {
    return src(paths.js.src)
		.pipe(gulpWebpack(require('./webpack.config.js')({ NODE_ENV: env }), webpack))
        .pipe(dest(paths.js.dest));
}

//Set Webpack Enviroment names.
const webpackDevelopment = () => bundleJS('development');
const webpackProduction = () => bundleJS('production');


// Function to minify JS files
function jsFile() {
    return src(paths.js.src)
        .pipe(uglify())
        .pipe(dest(paths.js.dest));
}

// Function to run ESLint
function runLinter() {
    return src(['./resources/js/**/*.js', '!node_modules/**'])
        .pipe(gulpESLintNew({ fix: true }))
        .pipe(gulpESLintNew.format())
        .pipe(gulpESLintNew.failAfterError());
}

// Function to watch files for changes
function watchFiles() {
    watch('./resources/scss/**/*.scss', styles);
    watch(['./resources/js/**/*.js', '!node_modules/**'], parallel(runLinter, jsFile, webpackDevelopment));
}

// Define Gulp tasks
exports.styles = styles;
exports.jsFile = jsFile;
exports.bundleJS = webpackProduction;
exports.lint = runLinter;
exports.build = series(runLinter, styles, jsFile, webpackProduction);
exports.default = watchFiles;
