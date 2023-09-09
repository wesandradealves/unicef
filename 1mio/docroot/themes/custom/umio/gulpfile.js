'use strict';

const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const minifyCSS = require('gulp-minify-css');
const localPath = "sass/main.scss";
const destinyPath = "css";


function build() {
  return gulp.src(localPath)
    .pipe(sass().on('error', sass.logError))
    .pipe(minifyCSS())
    .pipe(gulp.dest(destinyPath));
};
exports.build = build;


function buildDevelopment() {
  return gulp.src(localPath)
    .pipe(sass().on('error', sass.logError))
    .pipe(gulp.dest(destinyPath));
};
exports.buildDevelopment = buildDevelopment;


function buildDev() {
  gulp.watch(["sass/*.scss", "sass/**/*.scss"], gulp.series('buildDevelopment'));
}
exports.buildDev = buildDev;
