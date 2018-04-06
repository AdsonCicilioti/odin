/**
 * Packages
 */

// General
const fs          = require('fs');
const path        = require('path');
const browserSync = require('browser-sync').create();
const gulp        = require('gulp');
const fileInclude = require('gulp-file-include');
const flatten     = require('gulp-flatten');
const header      = require('gulp-header');
const plumber     = require('gulp-plumber');
const rename      = require('gulp-rename');
const package     = require('./package.json');
const env         = process.env.NODE_ENV === 'production' ? 'production' : 'development';

// Scripts
const jshint      = require('gulp-jshint');
const stylish     = require('jshint-stylish');
const uglify      = require('gulp-uglify');

// Styles
const sass        = require('gulp-sass');
const sassGlob    = require('gulp-sass-glob');

// Media.
const imagemin    = require('gulp-imagemin');

/**
 * Config to project
 */
const config = {
  "banner":
      "/*!\n" +
      " * <%= package.name %> v<%= package.version %> <<%= package.homepage %>>\n"+
      " * <%= package.title %> : <%= package.description %>\n" +
      " * (c) " + new Date().getFullYear() + " <%= package.author.name %> <<%= package.author.url %>>\n" +
      " * <%= package.license.type %> License <<%= package.license.url %>>\n" +
      " * <%= package.repository.url %>\n" +
      " */\n\n",
  "js": {
      "src": "{src,components}/**/*.js",
      "dest": "dist/js/"
  },
  "css": {
      "src": "{src,components}/**/*.{scss,sass}",
      "dest": "dist/css/",
      "includePaths": [
          path.resolve(__dirname, "./"),
          path.resolve(__dirname, "./src/css/"),
          path.resolve(__dirname, "./node_modules/")
      ]
  },
  "images": {
      "src": "src/img/**/*",
      "dest": "dist/img/"
  }
}

/**
 * Gulp Taks
 */

// Lint scripts
gulp.task('js:lint', function() {
    return gulp.src(config.js.src)
        .pipe(plumber())
        .pipe(jshint())
        .pipe(jshint.reporter('jshint-stylish'));
});

// File include and concatenate scripts
gulp.task('js:compile', ['js:lint'], function() {
    return gulp.src(config.js.src)
        .pipe(plumber())
        .pipe(fileInclude({
            prefix: '@@',
            basepath: '@file'
        }))
        .pipe(flatten())
        .pipe(gulp.dest(config.js.dest))
        .pipe(browserSync.stream());
});

// Compress and add banner scripts
gulp.task('js:dist', ['js:compile'], function() {
    return gulp.src([config.js.dest+'*.js','!'+config.js.dest+'*.min.js'])
        .pipe(plumber())
        .pipe(uglify())
        .pipe(rename({suffix: ".min"}))
        .pipe(header(config.banner, {package: package}))
        .pipe(flatten())
        .pipe(gulp.dest(config.js.dest));
});


// Process SASS files styles
gulp.task('css:compile', function() {
    return gulp.src(config.css.src)
        .pipe(plumber())
        .pipe(sassGlob())
        .pipe(sass({
            outputStyle: 'expanded',
            sourceComments: true,
            includePaths: config.css.includePaths,
            indentedSyntax: false
        }).on('error', sass.logError))
        .pipe(flatten())
        .pipe(gulp.dest(config.css.dest))
        .pipe(browserSync.stream());
});

// Compress and add banner scripts
gulp.task('css:dist', ['css:compile'], function() {
    return gulp.src(config.css.src)
        .pipe(plumber())
        .pipe(sassGlob())
        .pipe(sass({
            outputStyle: 'compressed',
            sourceComments: false,
            includePaths: config.css.includePaths,
            indentedSyntax: false
        }).on('error', sass.logError))
        .pipe(rename({suffix: '.min'}))
        .pipe(header(config.banner, {package: package}))
        .pipe(flatten())
        .pipe(gulp.dest(config.css.dest));
});

// Copy image src -> dist to watch
gulp.task('img:compile', function() {
    return gulp.src(config.images.src)
        .pipe(plumber())
        .pipe(gulp.dest(config.images.dest))
        .pipe(browserSync.stream());
});

// Compress image files
gulp.task('img:dist', function() {
    return gulp.src(config.images.src)
        .pipe(plumber())
        .pipe(imagemin())
        .pipe(gulp.dest(config.images.dest));
});

// Starts a BrowerSync instance
gulp.task('serve', function(){
    browserSync.init(package.serveDev);
});

// Watch files for changes
gulp.task('watch', ['default'], function(done) {
	gulp.watch(config.images.src, ['img:compile']);
	gulp.watch(config.css.src, ['css:compile']);
	gulp.watch(config.js.src, ['js:compile']);
	gulp.watch(['**/*.php']).on('change', browserSync.reload);
});

// Compile and compress js, css and img. Move others files to dist, like app/fonts directory (default)
gulp.task('default', [
	'js:dist',
	'css:dist',
	'img:dist'
]);

// Compile, init serve and watch files
gulp.task('dev-server', [
    'default',
    'serve',
    'watch'
]);
