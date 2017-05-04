var fs = require('fs')
var browserify = require('browserify')
var vueify = require('vueify')
var gulp = require('gulp');
var babelify = require('babelify');

gulp.task('default', function () {
    return browserify('templates/vue/main.js')
    .add('templates/vue/environment.js')
    .transform(babelify, { presets: ['es2015'], plugins: ['transform-runtime'] })
    .transform(vueify, { presets: ['es2015'] })
    .bundle()
    .pipe(fs.createWriteStream('public/build.js'))
});
