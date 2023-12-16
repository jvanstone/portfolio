const { src, dest, parallel, series, watch } = require('gulp');
const minifyCss = require('gulp-clean-css');
//const sourceMap = require('gulp-sourcemaps');
const concat = require('gulp-concat');
const uglify = require('gulp-uglify');
const gulpESLintNew = require('gulp-eslint-new');
const sass = require('gulp-sass')(require('sass'));

const styles = () => {
	return src('./resources/scss/style.scss')
    	.pipe(sass().on('error', sass.logError))
		.pipe(minifyCss())
		.pipe(concat('style.css'))
		.pipe(dest('./dist/css/'));
};

const jsFile = () => {
	src('./resources/js/common.js').pipe(uglify()).pipe(dest('./dist/js/'));
};

const runLinter = () => {
	return src(['scripts/*.js']) // Read files.
		.pipe(gulpESLintNew({ fix: true })) // Lint files, create fixes.
		.pipe(gulpESLintNew.fix()) // Fix files if necessary.
		.pipe(gulpESLintNew.format()) // Output lint results to the console.
		.pipe(gulpESLintNew.failAfterError()); // Exit with an error if problems are found.
};


const watchFiles = () => {
	watch('./resources/scss/**/*.scss', styles);
	watch(
		['./resources/js/**.js', '!node_modules/**'],
		parallel(runLinter, styles, jsFile)
	  );	
};	

exports.styles = styles;
exports.jsFile = jsFile;
exports.lint = runLinter;
exports.watchFiles = watchFiles;
exports.default = series(
	watchFiles,
	runLinter,
	parallel(styles, jsFile),
  );
  