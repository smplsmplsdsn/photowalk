const gulp = require('gulp'),
  rename = require("gulp-rename"),
  concat = require("gulp-concat"),
  notify = require('gulp-notify'),
  plumber = require('gulp-plumber'),
  sass = require('gulp-sass')(require('sass')),
  fastGlob = require('fast-glob'),
  postcss = require('gulp-postcss'),
  uglifycss = require('gulp-uglifycss'),
  autoprefixer = require('autoprefixer'),
  sortMediaQueries = require('postcss-sort-media-queries'),
  uglify = require("gulp-uglify")

const assets_path = './assets'


/**
 * 複数のSASSファイル(.scss)を、一枚のCSSファイルにする
 */

// 複数のSASSファイル(.scss)を結合する
gulp.task('css.concat', () => {
  return gulp.src(['common/scss/var.scss', 'common/scss/setting/**/*.scss', 'common/scss/base/**/*.scss', 'common/scss/common/**/*.scss', 'individual/scss/var.scss', 'individual/scss/individual/**/*.scss'])
    .pipe(concat('common.uncompressed.scss'))
    .pipe(gulp.dest('./dist/css'))
})

// SASSファイル(.scss)をコンパイルする
gulp.task('sass', () => {
  return gulp
    .src(fastGlob.sync('./dist/css/common.uncompressed.scss'))    // fastGlob: cssファイル内のimportを有効にする
    .pipe(plumber({ errorHandler: notify.onError("Error: <%= error.message %>") }))

    // コンパイルする(出力形式： expanded, nested, campact, compressed)
    .pipe(sass({
      outputStyle: 'expanded'
    }))

    // ベンダープレフィックスを付与する(IEは11以上、Androidは4以上、それ以外は最新2バージョンを対象にしている)
    .pipe(postcss([
      autoprefixer({
        "overrideBrowserslist": [
          "last 2 versions"
        ],
        cascade: false
      })
    ]))

    // メディアクエリをまとめる
    .pipe(postcss([sortMediaQueries]))

    // 出力先を指定する
    .pipe(gulp.dest('./dist/css'))
})

// CSSファイルを最小化する(ワンライン)
gulp.task('css.min', () => {
  return gulp
    .src('./dist/css/common.uncompressed.css')
    .pipe(uglifycss({
      "uglyComments": true
    }))
    .pipe(rename('common.min.css'))

    //コンパイル後の出力先
    .pipe(gulp.dest(assets_path + '/css'))
})

/**
 * 複数のJSファイルを難読化した一ファイルにまとめる
 */

// JSファイルを結合する
gulp.task('js.concat', () => {
  return gulp.src(['common/js/first.js', 'common/js/common/**/*.js', 'individual/js/individual/**/*.js', 'individual/js/init.js'])
    .pipe(concat('common.uncompressed.js'))
    .pipe(gulp.dest('./dist/js/'))
})


// JSファイルを難読化する
gulp.task('js.uglify', () => {
  return gulp.src('./dist/js/common.uncompressed.js')
    .pipe(plumber())
    .pipe(uglify())
    .pipe(rename('common.min.js'))
    .pipe(gulp.dest(assets_path + '/js/'))
})


/**
 * 監視する
 * NOTE: { usePolling: true } は Dockerでwatchを実行するために記述している
 */
gulp.task('watch', () => {
  gulp.watch('./common/scss/**/*.scss', { usePolling: true }, gulp.task('css.concat'))
  gulp.watch('./individual/scss/**/*.scss', { usePolling: true }, gulp.task('css.concat'))
  gulp.watch('./dist/css/common.uncompressed.scss', { usePolling: true }, gulp.task('sass'))
  gulp.watch('./dist/css/common.uncompressed.css', { usePolling: true }, gulp.task('css.min'))
  gulp.watch('./common/js/**/*.js', { usePolling: true }, gulp.task('js.concat'))
  gulp.watch('./individual/js/**/*.js', { usePolling: true }, gulp.task('js.concat'))
  gulp.watch('./dist/js/common.uncompressed.js', { usePolling: true }, gulp.task('js.uglify'))
})

// default
gulp.task('default', gulp.series(gulp.parallel('watch')))
