const { src, dest, watch, series } = require('gulp')
const rename = require('gulp-rename')
const concat = require('gulp-concat')
const notify = require('gulp-notify')
const plumber = require('gulp-plumber')
const sass = require('gulp-sass')(require('sass'))
const fastGlob = require('fast-glob')
const postcss = require('gulp-postcss')
const uglifycss = require('gulp-uglifycss')
const autoprefixer = require('autoprefixer')
const sortMediaQueries = require('postcss-sort-media-queries')
const uglify = require('gulp-uglify')

const assets_path = './assets'


/* =====================
  CSS
===================== */
function cssConcat() {
  return src([
    'common/scss/var.scss',
    'common/scss/setting/**/*.scss',
    'common/scss/base/**/*.scss',
    'common/scss/common/**/*.scss',
    'individual/scss/var.scss',
    'individual/scss/individual/**/*.scss'
  ])
    .pipe(concat('common.uncompressed.scss'))
    .pipe(dest('./dist/css'))
}

function sassCompile() {
  return src(fastGlob.sync('./dist/css/common.uncompressed.scss'))
    .pipe(plumber({ errorHandler: notify.onError("Error: <%= error.message %>") }))
    .pipe(sass({ outputStyle: 'expanded' }))
    .pipe(postcss([
      autoprefixer({
        overrideBrowserslist: ['last 2 versions'],
        cascade: false
      }),
      sortMediaQueries
    ]))
    .pipe(dest('./dist/css'))
}

function cssMin() {
  return src('./dist/css/common.uncompressed.css')
    .pipe(uglifycss({ uglyComments: true }))
    .pipe(rename('common.min.css'))
    .pipe(dest(`${assets_path}/css`))
}

/* =====================
  JS
===================== */
function jsConcat() {
  return src([
    'common/js/first.js',
    'common/js/common/**/*.js',
    'individual/js/init.js',
    'individual/js/individual/**/*.js',
  ])
    .pipe(concat('common.uncompressed.js'))
    .pipe(dest('./dist/js/'))
}

function jsUglify() {
  return src('./dist/js/common.uncompressed.js')
    .pipe(plumber())
    .pipe(uglify())
    .pipe(rename('common.min.js'))
    .pipe(dest(`${assets_path}/js/`))
}

/* =====================
  Watch（Docker安定版）
===================== */
function watchFiles() {

  const watchOptions = {
    usePolling: true,
    interval: 300,
    awaitWriteFinish: {
      stabilityThreshold: 200,
      pollInterval: 100
    }
  }

  // scss
  const scssWatcher = watch(
    ['common/scss/**/*.scss', 'individual/scss/**/*.scss'],
    watchOptions
  )

  scssWatcher.on('all', (event, path) => {
    console.log(`SCSS: ${event} -> ${path}`)
    series(cssConcat, sassCompile, cssMin)()
  })

  // js
  const jsWatcher = watch(
    ['common/js/**/*.js', 'individual/js/**/*.js'],
    watchOptions
  )

  jsWatcher.on('all', (event, path) => {
    console.log(`JS: ${event} -> ${path}`)
    series(jsConcat, jsUglify)()
  })
}


/* =====================
  Exports
===================== */
exports.css = series(cssConcat, sassCompile, cssMin)
exports.js = series(jsConcat, jsUglify)
exports.watch = watchFiles
exports.default = watchFiles
