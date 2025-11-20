/**
 * Gulpfile.
 *
 * Gulp with WordPress.
 *
 * Implements:
 *      1. Live reloads browser with BrowserSync.
 *      2. CSS: Sass to CSS conversion, error catching, Autoprefixing, Sourcemaps,
 *         CSS minification, and Merge Media Queries.
 *      3. JS: Concatenates & uglifies Vendor and Custom JS files.
 *      4. Images: Minifies PNG, JPEG, GIF and SVG images.
 *      5. Watches files for changes in CSS or JS.
 *      6. Watches files for changes in PHP.
 *      7. Corrects the line endings.
 *      8. InjectCSS instead of browser page reload.
 *      9. Generates .pot file for i18n and l10n.
 *
 * @tutorial https://github.com/ahmadawais/WPGulp
 * @author Ahmad Awais <https://twitter.com/MrAhmadAwais/>
 */

/**
 * Load WPGulp Configuration.
 *
 * TODO: Customize your project in the wpgulp.js file.
 */
 const config = require('./wpgulp.config.js');

 /**
  * Load Plugins.
  *
  * Load gulp plugins and passing them semantic names.
  */
 const gulp = require('gulp'); // Gulp of-course.
 const { exec } = require('child_process');
 
 // CSS related plugins.
 var nodesass = require('sass')
 var sass = require('gulp-sass')(nodesass); // Gulp plugin for Sass compilation.
 const minifycss = require('gulp-uglifycss'); // Minifies CSS files.
 const autoprefixer = require('gulp-autoprefixer'); // Autoprefixing magic.
 const mmq = require('gulp-merge-media-queries'); // Combine matching media queries into one.
 const rtlcss = require('gulp-rtlcss'); // Generates RTL stylesheet.
 
 // JS related plugins.
 const concat = require('gulp-concat'); // Concatenates JS files.
 const uglify = require('gulp-uglify'); // Minifies JS files.
 const babel = require('gulp-babel'); // Compiles ESNext to browser compatible JS.
 
 // Image related plugins.
 const imagemin = require('gulp-imagemin'); // Minify PNG, JPEG, GIF and SVG images with imagemin.
 
 // Utility related plugins.
 const rename = require('gulp-rename'); // Renames files E.g. style.css -> style.min.css.
 const lineec = require('gulp-line-ending-corrector'); // Consistent Line Endings for non UNIX systems. Gulp Plugin for Line Ending Corrector (A utility that makes sure your files have consistent line endings).
 const filter = require('gulp-filter'); // Enables you to work on a subset of the original files by filtering them using a glob.
 //const sourcemaps = require('gulp-sourcemaps'); // Maps code in a compressed file (E.g. style.css) back to it’s original position in a source file (E.g. structure.scss, which was later combined with other css files to generate style.css).
 const notify = require('gulp-notify'); // Sends message notification to you.
 const browserSync = require('browser-sync').create(); // Reloads browser and injects CSS. Time-saving synchronized browser testing.
 const wpPot = require('gulp-wp-pot'); // For generating the .pot file.
 const sort = require('gulp-sort'); // Recommended to prevent unnecessary changes in pot-file.
 const cache = require('gulp-cache'); // Cache files in stream for later use.
 const remember = require('gulp-remember'); //  Adds all the files it has ever seen back into the stream.
 const plumber = require('gulp-plumber'); // Prevent pipe breaking caused by errors from gulp plugins.
 const beep = require('beepbeep');
 const zip = require('gulp-zip'); // Zip plugin or theme file.
 
 /**
  * Custom Error Handler.
  *
  * @param Mixed err
  */
 const errorHandler = r => {
	 notify.onError('\n\n❌  ===> ERROR: <%= error.message %>\n')(r);
	 beep();
 
	 // this.emit('end');
 };
 
 /**
  * Task: `browser-sync`.
  *
  * Live Reloads, CSS injections, Localhost tunneling.
  * @link http://www.browsersync.io/docs/options/
  *
  * @param {Mixed} done Done.
  */
 const browsersync = done => {
	 browserSync.init({
		 proxy: config.projectURL,
		 open: config.browserAutoOpen,
		 injectChanges: config.injectChanges,
		 watchEvents: ['change', 'add', 'unlink', 'addDir', 'unlinkDir']
	 });
	 done();
 };
 
 // Helper function to allow browser reload with Gulp 4.
 const reload = done => {
	 browserSync.reload();
	 done();
 };

 /*  
 * Watch main styles
 */
 gulp.task('mainStyles', () => {
	return gulp
		.src(config.mainCSS, {allowEmpty: true})
		.pipe(browserSync.stream()) // Reloads style.css if that is enqueued.
		.pipe(
			notify({
				message: '\n\n✅  ===> Main styles!\n',
				onLast: true
			})
		);
});

gulp.task('stylesMin', () => {
	return gulp
		.src(config.mainCSSSRC, {allowEmpty: true})
		.pipe(plumber(errorHandler))
		.pipe(
			sass({
				errLogToConsole: config.errLogToConsole,
				outputStyle: 'compressed',
				precision: config.precision
			})
		)
		.on('error', sass.logError)
		.pipe(autoprefixer(config.BROWSERS_LIST))
		.pipe(rename({suffix: '.min'}))
		.pipe(lineec()) // Consistent Line Endings for non UNIX systems.
		.pipe(gulp.dest(config.mainCSSDest))
		.pipe(filter('**/*.css')) // Filtering stream to only css files.
		.pipe(mmq({log: true})) // Merge Media Queries only for .min.css version.
		.pipe(browserSync.stream()) // Reloads style.css if that is enqueued.
		.pipe(
			notify({
				message: '\n\n✅  ===> STYLES Minified — completed!\n',
				onLast: true
			})
		);
});

/*  
 * Customizer Styles
 */
 gulp.task('wooStyles', () => {
	return gulp
		.src(config.wooSRC, {allowEmpty: true})
		.pipe(plumber(errorHandler))
		.pipe(
			sass({
				errLogToConsole: config.errLogToConsole,
				outputStyle: 'expanded',
				precision: config.precision
			})
		)
		.on('error', sass.logError)
		.pipe(autoprefixer(config.BROWSERS_LIST))
		.pipe(lineec()) // Consistent Line Endings for non UNIX systems.
		.pipe(gulp.dest(config.wooDestination))
		.pipe(filter('**/*.css')) // Filtering stream to only css files.
		.pipe(mmq({log: true})) // Merge Media Queries only for .min.css version.
		.pipe(browserSync.stream()) // Reloads style.css if that is enqueued.
		.pipe(
			notify({
				message: '\n\n✅  ===> Woo Expanded — completed!\n',
				onLast: true
			})
		);
});

gulp.task('wooStylesMin', () => {
	return gulp
		.src(config.wooSRC, {allowEmpty: true})
		.pipe(plumber(errorHandler))
		.pipe(
			sass({
				errLogToConsole: config.errLogToConsole,
				outputStyle: 'compressed',
				precision: config.precision
			})
		)
		.on('error', sass.logError)
		.pipe(autoprefixer(config.BROWSERS_LIST))
		.pipe(rename({suffix: '.min'}))
		.pipe(lineec()) // Consistent Line Endings for non UNIX systems.
		.pipe(gulp.dest(config.wooDestination))
		.pipe(filter('**/*.css')) // Filtering stream to only css files.
		.pipe(mmq({log: true})) // Merge Media Queries only for .min.css version.
		.pipe(browserSync.stream()) // Reloads style.css if that is enqueued.
		.pipe(
			notify({
				message: '\n\n✅  ===> Woo Minified — completed!\n',
				onLast: true
			})
		);
});
 
 /**
  * WP POT Translation File Generator.
  *
  * This task does the following:
  * 1. Gets the source of all the PHP files
  * 2. Sort files in stream by path or any custom sort comparator
  * 3. Applies wpPot with the variable set at the top of this file
  * 4. Generate a .pot file of i18n that can be used for l10n to build .mo file
  */
 gulp.task('translate', () => {
	 return gulp
		 .src(config.watchPhp)
		 .pipe(sort())
		 .pipe(
			 wpPot({
				 domain: config.textDomain,
				 package: config.packageName,
				 bugReport: config.bugReport,
				 lastTranslator: config.lastTranslator,
				 team: config.team
			 })
		 )
		 .pipe(gulp.dest(config.translationDestination + '/' + config.translationFile))
		 .pipe(
			 notify({
				 message: '\n\n✅  ===> TRANSLATE — completed!\n',
				 onLast: true
			 })
		 );
 });
 
 /**
  * Zips theme or plugin and places in the parent directory
  *
  * zipIncludeGlob: Files to be included in the zip file
  * zipIgnoreGlob: Files to be ignored from the zip file
  * zipDestination: Must be a folder outside of the zip folder.
  * zipName: theme.zip or plugin.zip
  */
 gulp.task('zip', () => {
	 const src = [...config.zipIncludeGlob, ...config.zipIgnoreGlob];
	 return gulp.src(src).pipe(zip(config.zipName)).pipe(gulp.dest(config.zipDestination));
 });

/**
 * Task: `copy-file-to-remote`.
 * Command args: --local-path /home/rodrigo/docker/athemes/dev/wp-content/themes/ --remote-path /var/www/html/wp-content/themes/ --file-name sydney.zip
 */
 gulp.task('copy-file-to-remote', async function () {
	const localPath = process.argv[4];
	const remotePath = process.argv[6];
	const fileName = process.argv[8];

	exec(`docker cp ${ localPath }${ fileName } ddev-athemesdev-web:${ remotePath }${ fileName }`, (err, stdout, stderr) => {
		if (err) {
			console.error(`Error copying file: ${stderr}`);
		} else {
			console.log(`File copied successfully: ${stdout}`);
		}
	})
});

 /**
 * Task: `unzip-remote-file`.
 * Command args: --local-path /home/rodrigo/docker/athemes/athemesdev/ --remote-path /var/www/html/wp-content/themes/ --file-name sydney.zip
 */
gulp.task('unzip-remote-file', async function () {
	const localPath = process.argv[4];
	const remotePath = process.argv[6];
	const fileName = process.argv[8];

	exec(`cd ${ localPath }; ddev exec "unzip -o ${ remotePath }${ fileName } -d ${ remotePath }"`, (err, stdout, stderr) => {
		if (err) {
			console.error(`Error copying file: ${stderr}`);
		} else {
			console.log(`File copied successfully: ${stdout}`);
		}
	})
});

 /**
  * Watch Tasks.
  *
  * Watches for file changes and runs specific tasks.
  */
 gulp.task(
	 'default',
	 gulp.parallel( 'stylesMin', 'wooStyles', 'wooStylesMin', 'mainStyles', browsersync, (done) => {
		 gulp.watch( config.watchPhp, reload); // Reload on PHP file changes.
		 gulp.watch( config.watchWooStyles, gulp.parallel('wooStyles') );
		 gulp.watch( config.watchWooStyles, gulp.parallel('wooStylesMin') );
		 gulp.watch( config.mainCSS, gulp.parallel('mainStyles') );
		 gulp.watch( config.mainCSS, gulp.parallel('stylesMin') );
		 done();
	 })
 ); 