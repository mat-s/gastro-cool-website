const gulp = require('gulp');
const sass = require('gulp-sass')(require('sass'));
const browserSync = require('browser-sync').create();
const header = require('gulp-header');
const cleanCSS = require('gulp-clean-css');
const rename = require('gulp-rename');
const sourcemaps = require('gulp-sourcemaps');

// WordPress Theme Header für CSS
const themeHeader = `/*
Theme Name: Gastro Cool Theme
Description: Astra Child Theme for Gastro-Cool, built with SCSS and Gulp
Author: Matthias Seidel
Author URI: https://gastro-cool.de
Version: 1.0.0
Template: astra
Text Domain: gastro-cool-theme
*/

`;

// Pfade definieren (Gastro-Cool Theme)
const paths = {
  scss: {
    src: 'wp-content/themes/gastro-cool-theme/assets/scss/**/*.scss',
    main: 'wp-content/themes/gastro-cool-theme/assets/scss/main.scss',
    // style.css soll klassisch im Theme-Root liegen
    dest: 'wp-content/themes/gastro-cool-theme/'
  },
  js: {
    src: 'wp-content/themes/gastro-cool-theme/assets/js/**/*.js',
    dest: 'wp-content/themes/gastro-cool-theme/assets/js/'
  },
  php: {
    src: 'wp-content/themes/gastro-cool-theme/**/*.php'
  }
};

// Zusätzliche Pfade: gastro-cool-theme (Child-Theme auf Astra)
const gastroPaths = {
  scss: {
    src: 'wp-content/themes/gastro-cool-theme/assets/scss/**/*.scss',
    main: 'wp-content/themes/gastro-cool-theme/assets/scss/main.scss',
    dest: 'wp-content/themes/gastro-cool-theme/assets/css/',
    outFile: 'theme.css'
  },
  js: {
    src: 'wp-content/themes/gastro-cool-theme/assets/js/**/*.js',
    dest: 'wp-content/themes/gastro-cool-theme/assets/js/'
  },
  php: {
    src: 'wp-content/themes/gastro-cool-theme/**/*.php'
  }
};

// SCSS Kompilierung mit WordPress Header
function compileSCSS() {
  return gulp.src(paths.scss.main)
    .pipe(sourcemaps.init())
    .pipe(sass({
      outputStyle: 'expanded',
      includePaths: ['node_modules']
    }).on('error', sass.logError))
    .pipe(header(themeHeader))
    .pipe(rename('style.css'))
    .pipe(sourcemaps.write('.'))
    .pipe(gulp.dest(paths.scss.dest))
    .pipe(browserSync.stream());
}

// SCSS Kompilierung für Production (minified)
function compileSCSSProd() {
  return gulp.src(paths.scss.main)
    .pipe(sass({
      outputStyle: 'compressed',
      includePaths: ['node_modules']
    }).on('error', sass.logError))
    .pipe(cleanCSS({ level: 2 }))
    .pipe(header(themeHeader))
    .pipe(rename('style.css'))
    .pipe(gulp.dest(paths.scss.dest));
}

// JavaScript kopieren
function copyJS() {
  return gulp.src(paths.js.src)
    .pipe(gulp.dest(paths.js.dest))
    .pipe(browserSync.stream());
}

// LiveReload Server starten (BrowserSync)
function startServer(done) {
  browserSync.init({
    proxy: "localhost:8080", // Passe ggf. an deine lokale WP-URL an
    open: false,
    notify: false
  });
  done();
}

// Watch Files für Änderungen
function watchFiles() {
  gulp.watch(paths.scss.src, compileSCSS);
  gulp.watch(paths.js.src, copyJS);
  gulp.watch(paths.php.src).on('change', browserSync.reload);
}

// Task Definitionen
gulp.task('scss', compileSCSS);
gulp.task('scss:prod', compileSCSSProd);
gulp.task('js', copyJS);
gulp.task('server', startServer);
gulp.task('watch', gulp.series(startServer, watchFiles));

// Build Tasks
gulp.task('build', gulp.series(
  gulp.parallel(compileSCSS, copyJS)
));

gulp.task('build:prod', gulp.series(
  gulp.parallel(compileSCSSProd, copyJS)
));

// Development Task (default)
gulp.task('default', gulp.series(
  gulp.parallel(compileSCSS, copyJS),
  startServer,
  watchFiles
));

// Einzelne Tasks für Testing (Platzhalter)
gulp.task('test:scss', (done) => {
  // Hier könnten SCSS-Tests integriert werden
  done();
});

gulp.task('test:build', (done) => {
  // Hier könnten Build-Tests integriert werden
  done();
});

// Entfernt: separate Gastro-Tasks – der Standard-SCSS-Task baut jetzt direkt style.css im Theme-Root
