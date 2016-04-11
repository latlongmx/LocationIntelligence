var gulp = require('gulp');
var browserSync = require('browser-sync').create();
var wiredep = require('wiredep').stream;
var $ = require('gulp-load-plugins')({lazy: true});
var args = require('yargs').argv;
var del = require('del');


/*** To Dev ***/


gulp.task('inject', function(){
	return gulp.src('./client/index.html')
		.pipe(wiredep({
			bowerJson: require('./bower.json'),
			directory: 'bower_components',
			ignorePath: '../../'
		}))
		.pipe($.inject(gulp.src([
			'./client/styles/styles.css'
		]), {ignorePath: '../../', relative: true}))
		.pipe($.inject(gulp.src([
			'./client/components/**/*.module.js',
			'./client/components/**/*.js'
		]), {ignorePath: '../../', relative: true}))
		.pipe(gulp.dest('./client/'));
});


gulp.task('js', function(){
	log('Analizyng components...');
	gulp.src(['./client/components/**/*.js'])
	.pipe($.jshint())
	.pipe($.jshint.reporter('jshint-stylish', {verbose: true}))
});

gulp.task('sass', ['cleaning-styles'], function () {
	log('Compiling sass to css');
	return gulp.src('./client/sass/config.scss')
		.pipe($.sass().on('error', $.sass.logError))
		.pipe($.sass({outputStyle: 'compressed'}))
		.pipe($.concat('styles.css'))
		.pipe(gulp.dest('./client/styles/'));
});



/* To Production */

gulp.task('join', function(){
	log('Joining all js/css files');
	var assets = $.useref.assets({searchPath: ['./', './client/']});
	var cssFilter = $.filter('**/*.css', {restore: true});
	var jsLibFilter = $.filter('**/lib.js', {restore: true});
	var jsAppFilter = $.filter('**/app.js', {restore: true});

	return gulp.src('./client/index.html')
		.pipe($.inject(gulp.src(
			'tmp/templates.js',{read: false}
		),{starttag: '<!-- inject:template:js -->'}))
		.pipe(assets)
		.pipe(cssFilter)
		.pipe($.csso())
		.pipe(cssFilter.restore)
		.pipe(jsLibFilter)
		.pipe($.uglify())
		.pipe(jsLibFilter.restore)
		.pipe(jsAppFilter)
		.pipe($.ngAnnotate())
		.pipe($.uglify())
		.pipe(jsAppFilter.restore)
		.pipe(assets.restore())
		.pipe($.useref())
		.pipe(gulp.dest('build'));
});


gulp.task('templatecache', ['clean-templatecache'], function(){
	log('Angularjs template files!');
	var options = {
		module: 'walmex',
		root: 'components/'
	}
	return gulp.src('./client/components/**/*.html')
		.pipe($.minifyHtml({empty: true}))
		.pipe($.angularTemplatecache(
			'templates.js',
			options
		))
		.pipe(gulp.dest('tmp'));
});

gulp.task('html', ['cleaning-components'], function() {
	log('Copying html files');
	return gulp.src('./client/components/**/*.html')
		.pipe(gulp.dest('build/components/'));
});


/* Cleaners */
gulp.task('cleaning-components', function(done){
	clean('build/components/*.*', done);
});
gulp.task('clean-templatecache', function(done){
	clean('tmp', done);
});
gulp.task('cleaning-styles', function(){
	var files = './client/styles/*.css';
	clean(files);
});

/* Dev Server */
gulp.task('dev-server', function(){
	log('Dev server running...');

	browserSync.init({
		files: [
			"./client/index.html",
			"./client/components/**/*.*",
			"./client/components/**/**/*.scss",
			"./client/sass/**/*.scss",
			"./client/sass/config.scss"
		],
		ghostMode: {
			clicks: false,
			forms: true,
			scroll: false
		},
		logFileChanges: true,
		logPrefix: "Walmex Project",
		notify: true,
		port: 2016,
		reloadDelay: 1500,
		server: {
			baseDir: './client',
			routes: {
				"/bower_components": "bower_components",
				"./client": "client"
			}
		}
	});
});

/* Watch files */
gulp.task('watch', function(){
	log('Watching files!');
	gulp.watch('client/components/**/*.js', ['js']);
	gulp.watch('client/components/**/**/*.scss', ['sass']);
	gulp.watch('client/sass/**/*.scss', ['sass']);
	gulp.watch('client/sass/config.scss', ['sass']);
});


gulp.task('dev',['dev-server', 'watch']);
//gulp.task('prod', ['prod-server']);


function clean(path, done){
	log('Cleaning: '+ $.util.colors.blue(path));
	del(path, done)
}

function log(msg) {
	if (typeof(msg) === 'object') {
		for (var item in msg) {
			if (msg.hasOwnProperty(item)) {
				$.util.log($.util.colors.green(msg[item]));
			}
		}
	}
	else {
		$.util.log($.util.colors.green(msg));
	}
}
