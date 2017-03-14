'use strict';

var gulp = require('gulp'),

		path = require('path'),

		minifyHtml = require('gulp-minify-html'),

		less = require('gulp-less'),
		minifyCss = require('gulp-minify-css'),

		LessPluginAutoPrefix = require('less-plugin-autoprefix'),
		autoprefix = new LessPluginAutoPrefix({browsers: ["last 2 versions"]}),

		mergeJs = require('gulp-merge'),
		concat = require('gulp-concat'),
		uglify = require('gulp-uglify'),

		settings = {
			appFolder: '../app/',
			buildFolder : '../build/',
			assetsFolder : '../assets/',
			assetsPlugins: '../assets/globals/plugins/'
		};

// Minify HTML
gulp.task('minify-html', function () {
	gulp.src(settings.appFolder+'admin1/*.html')
		.pipe(minifyHtml())
		.pipe(gulp.dest(settings.appFolder+'admin1/min/'));
});

// Compile Less
gulp.task('compile-less', function () {
	gulp.src(settings.buildFolder+'admin1/less/admin1.less')
		.pipe(less({
			plugins: [autoprefix]
		}))
		.pipe(gulp.dest(settings.assetsFolder+'admin1/css/'));

	gulp.src(settings.buildFolder+'elements/less/elements.less')
		.pipe(less({
			plugins: [autoprefix]
		}))
		.pipe(gulp.dest(settings.assetsFolder+'globals/css/'));

	gulp.src(settings.buildFolder+'globals/less/plugins.less')
		.pipe(less({
			plugins: [autoprefix]
		}))
		.pipe(gulp.dest(settings.assetsFolder+'globals/css/'));
});

// Minify Css
gulp.task('minify-css', function () {
	gulp.src(settings.assetsFolder+'admin1/css/*.css')
		.pipe(minifyCss())
		.pipe(gulp.dest(settings.assetsFolder+'admin1/css/min/'));
});

gulp.task('global-vendors', function () {
	gulp.src([
		settings.assetsPlugins+'jquery/dist/jquery.min.js',
		settings.assetsPlugins+'jquery-ui/jquery-ui.min.js',
		settings.assetsPlugins+'bootstrap/dist/js/bootstrap.min.js',
		settings.assetsPlugins+'velocity/velocity.min.js',
		settings.assetsPlugins+'moment/min/moment.min.js',
		settings.assetsPlugins+'toastr/toastr.min.js',
		settings.assetsPlugins+'scrollMonitor/scrollMonitor.js',
		settings.assetsPlugins+'textarea-autosize/dist/jquery.textarea_autosize.min.js',
		settings.assetsPlugins+'bootstrap-select/dist/js/bootstrap-select.min.js',
		settings.assetsPlugins+'fastclick/lib/fastclick.js'
	])
	.pipe(concat('global-vendors.js'))
	.pipe(gulp.dest(settings.assetsFolder+'globals/js/'));
});

// Minify JavaScript
gulp.task('minify-js', function () {
	gulp.src(settings.assetsFolder+'globals/js/*.js')
		.pipe(uglify())
		.pipe(gulp.dest(settings.assetsFolder+'globals/js/min/'));

	gulp.src(settings.assetsFolder+'admin1/js/*.js')
		.pipe(uglify())
		.pipe(gulp.dest(settings.assetsFolder+'admin1/js/min/'));
});

gulp.task('default',
	[
		'global-vendors',
		'compile-less',
		'minify-css',
		'minify-html',
		'minify-js'
	]
);