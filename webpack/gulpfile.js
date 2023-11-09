const gulp = require('gulp');
const browserSync = require('browser-sync');
const { file_path } = require('./library/constants/global');
const fg = require('fast-glob');

// Use fast-glob to find files matching the specified patterns
const paths = fg.sync([
  `${file_path}/**/*.scss`,
  `${file_path}/**/*.css`,
  `${file_path}/**/*.php`,
  `${file_path}/**/*.js`,
], {
  ignore: ['node_modules', 'dist', '.git'],
});

const proxy = "https://fly-knoxville-mcgee-tyson.local/";

function watch() {
  browserSync.init({
    proxy,
    port: 800,
  });

  gulp.watch(paths).on('change', (path) => {
    browserSync.reload();
  });
}

exports.default = watch;
