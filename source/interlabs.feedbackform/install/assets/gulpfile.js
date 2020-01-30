/**
 * Created by akorolev on 08.08.2018.
 */
var gulp = require('gulp');
var concat = require('gulp-concat');
var sourcemaps = require('gulp-sourcemaps');
var babel = require('gulp-babel');
var less = require('gulp-less');
var path = require('path');
var LessAutoprefix = require('less-plugin-autoprefix');
var autoprefix = new LessAutoprefix({browsers: ['last 2 versions']});


// for popup mode
gulp.task('popup', function () {
    return gulp.src([
        './scripts/popup.js'
    ])
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['env']
        }))
        .pipe(concat('script.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('../components/interlabs/feedbackform/templates/.popup'));

});
gulp.task('lessPopup', function () {
    return gulp.src('./styles/popup.less')
        .pipe(less({
            paths: [path.join(__dirname)],
            plugins: [autoprefix]
        }))
        .pipe(concat('style.css'))
        .pipe(gulp.dest('../components/interlabs/feedbackform/templates/.popup'))
        .pipe(gulp.dest('../../../../../bitrix/components/interlabs/feedbackform/templates/.popup'));
});

// for form mode

gulp.task('form', function () {
    return gulp.src([
        './scripts/form.js'
    ])
        .pipe(sourcemaps.init())
        .pipe(babel({
            presets: ['env']
        }))
        .pipe(concat('script.js'))
        .pipe(sourcemaps.write('.'))
        .pipe(gulp.dest('../components/interlabs/feedbackform/templates/.default'));

});
gulp.task('lessForm', function () {
    return gulp.src('./styles/form.less')
        .pipe(less({
            paths: [path.join(__dirname)],
            plugins: [autoprefix]
        }))
        .pipe(concat('style.css'))
        .pipe(gulp.dest('../components/interlabs/feedbackform/templates/.default'))
        .pipe(gulp.dest('../../../../../bitrix/components/interlabs/feedbackform/templates/.default'));
});


gulp.task('default', ['popup', 'lessPopup','form', 'lessForm']);


