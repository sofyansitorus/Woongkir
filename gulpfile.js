var gulp = require('gulp');
var rename = require("gulp-rename");
var uglify = require('gulp-uglify');
var iife = require("gulp-iife");
var concat = require('gulp-concat');

var frontendSrc = ['assets/src/js/shared.js', 'assets/src/js/frontend.js'];
var frontendDest = 'assets/js';

var backendSrc = ['assets/src/js/shared.js', 'assets/src/js/backend.js'];
var backendDest = 'assets/js';

var pluginsSrc = ['assets/src/js/plugins/*.js'];
var pluginsDest = 'assets/js';

var minifySrc = ['assets/js/*.js', '!assets/js/*.min.js'];
var minifyDest = 'assets/js';

// Frontend area scripts
gulp.task('frontend-scripts', function () {
    return gulp.src(frontendSrc)
        .pipe(concat('woongkir-frontend.js'))
        .pipe(iife({
            useStrict: true,
            trimCode: true,
            prependSemicolon: true,
            params: ["$"],
            args: ["jQuery"]
        }))
        .pipe(gulp.dest(frontendDest));
});

// Backend area scripts
gulp.task('backend-scripts', function () {
    return gulp.src(backendSrc)
        .pipe(concat('woongkir-backend.js'))
        .pipe(iife({
            useStrict: true,
            trimCode: true,
            prependSemicolon: true,
            params: ["$"],
            args: ["jQuery"]
        }))
        .pipe(gulp.dest(backendDest));
});

// Plugins scripts
gulp.task('plugins-scripts', function () {
    return gulp.src(pluginsSrc)
        .pipe(gulp.dest(pluginsDest));
});

// Minify scripts
gulp.task('minify-scripts', function () {
    return gulp.src(minifySrc)
        .pipe(rename({
            suffix: ".min"
        }))
        .pipe(uglify())
        .pipe(gulp.dest(minifyDest));
});

// Default task
gulp.task('default', ['frontend-scripts', 'backend-scripts', 'plugins-scripts', 'minify-scripts']);

// Dev task with watch
gulp.task('watch', ['frontend-scripts', 'backend-scripts', 'plugins-scripts', 'minify-scripts'], function () {
    gulp.watch([frontendSrc], ['frontend-scripts']);
    gulp.watch([backendSrc], ['backend-scripts']);
    gulp.watch([pluginsSrc], ['plugins-scripts']);
    gulp.watch([minifySrc], ['minify-scripts']);
});