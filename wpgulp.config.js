/**
 * WPGulp Configuration File
 *
 * 1. Edit the variables as per your project requirements.
 * 2. In paths you can add <<glob or array of globs>>.
 *
 * @package WPGulp
 */

// Project options.

// Local project URL of your already running WordPress site.
// > Could be something like "wpgulp.local" or "localhost"
// > depending upon your local WordPress setup.
const projectURL = 'http://sydney.local/';

// Theme/Plugin URL. Leave it like it is; since our gulpfile.js lives in the root folder.
const productURL = './';
const browserAutoOpen = false;
const injectChanges = true;

//Main CSS file
const mainCSS = './css/styles.css';
const mainCSSSRC = './css/styles.css';
const mainCSSDest = './css';

// >>>>> Style options.
// Path to main .scss file.
const modulesSRC = './css/modules/src/*.scss';
const templatesSRC = './inc/templates-builder/assets/css/src/*.scss';

// Path to place the compiled CSS file. Default set to root folder.
const modulesDestination = './css/modules';
const templatesDestination = './inc/templates-builder/assets/css';

//Woo
const wooSRC 			= './woocommerce/css/src/*.scss';
const wooDestination 	= './woocommerce/css';
const watchWooStyles 	= './woocommerce/css/src/*.scss';

// Available options → 'compact' or 'compressed' or 'nested' or 'expanded'
const outputStyle = 'compressed';
const errLogToConsole = true;
const precision = 10;

// JS Vendor options.

// Path to JS vendor folder.
const jsVendorSRC = './assets/js/vendor/*.js';

// Path to customizer js file
const custSRC = './assets/js/src/customizer.js';

// Path to place the customizer scripts file.
const custDestination = './assets/js/';

//Path to customizer-scripts js file
const custScriptsSRC = './assets/js/src/customizer-scripts.js';

// Path to place the customizer scripts file.
const jsSRC = './assets/js/';

// Path to place the compiled JS vendors file.
const jsVendorDestination = './';

//Modules JS destination 
const jsModulesDestination 		= './js/modules/';

//Reading progress
const jsReadingProgressSRC 		= './js/modules/src/reading-progress.js';
const watchJsReadingProgress 	= jsReadingProgressSRC;

const jsmultistepSRC 			= './js/modules/src/multistep-checkout.js';
const watchJsMultistep 			= jsmultistepSRC;

const jsReviewsAdvSRC 			= './js/modules/src/sydney-reviews-advanced.js';
const watchJsReviewsAdv 			= jsmultistepSRC;

const jsReadingProgressFile 	= 'reading-progress';
const jsMultiStepFile 			= 'multistep-checkout';
const jsReviewsAdvancedFile 	= 'sydney-reviews-advanced';



// Compiled JS vendors file name. Default set to vendors i.e. vendors.js.
const jsVendorFile = 'vendor';

const custFile = 'customizer';

const custScriptsFile = 'customizer-scripts';

// JS Custom options.

// Path to JS carousel.
const jsCustomizerSRC = './js/src/customizer.src.js';

// Path to JS sidebar.
const jsCustomizerScriptsSRC = './js/src/customize-controls.src.js';

// Path to JS custom scripts folder.
const jsCustomSRC = './assets/js/src/custom.js';

// Path to place the compiled JS custom scripts file.
const jsCustomDestination = './assets/js/';

// Compiled JS custom file name. Default set to custom i.e. custom.js.
const jsCustomFile = 'custom';

// Images options.

// Source folder of images which should be optimized and watched.
// > You can also specify types e.g. raw/**.{png,jpg,gif} in the glob.
const imgSRC = './assets/img/raw/**/*';

// Destination folder of optimized images.
// > Must be different from the imagesSRC folder.
const imgDST = './assets/img/';

// >>>>> Watch files paths.
// Path to all *.scss files inside css folder and inside them.
const watchStyles = './sass/**/*.scss';

const watchModuleStyles = './css/modules/src/*.scss';

const watchTemplatesStyles = './inc/templates-builder/assets/css/src/*.scss';


// Path to all vendor JS files.
const watchJsVendor = './assets/js/vendor/*.js';

// Path to all custom JS files.
const watchJsCustom = './assets/js/src/custom.js';

// Path to all custom JS files.
const watchJsCustomizer = './assets/js/src/*.js';

// Path to all PHP files.
const watchPhp = './**/*.php';

// >>>>> Zip file config.
// Must have.zip at the end.
const zipName = 'sydney.zip';

// Must be a folder outside of the zip folder.
const zipDestination = './../'; // Default: Parent folder.

//Include all files/folders in current directory.
const zipIncludeGlob = ['../@(Sydney|sydney)/**/*'];

// Default ignored files and folders for the zip file.
const zipIgnoreGlob = [
	'!../@(Sydney|sydney)/**/*{node_modules,node_modules/**/*}',
	'!../@(Sydney|sydney)/**/*.git',
	'!../@(Sydney|sydney)/**/*.svn',
	'!../@(Sydney|sydney)/**/*gulpfile.babel.js',
	'!../@(Sydney|sydney)/**/*wpgulp.config.js',
	'!../@(Sydney|sydney)/**/*playwright.config.js',
	'!../@(Sydney|sydney)/**/*phpcs.xml',
	'!../@(Sydney|sydney)/**/*test-results/**/*',
	'!../@(Sydney|sydney)/**/vendor/**/*',
	'!../@(Sydney|sydney)/**/vendor',
	'../@(Sydney|sydney)/**/inc/vendor/**/*',
	'../@(Sydney|sydney)/**/inc/vendor',
	'!../@(Sydney|sydney)/**/*test-results',
	'!../@(Sydney|sydney)/**/*playwright-report/**/*',
	'!../@(Sydney|sydney)/**/*playwright-report',
	'!../@(Sydney|sydney)/**/*{tests,tests/**/*}',
	'!../@(Sydney|sydney)/**/*.eslintrc.js',
	'!../@(Sydney|sydney)/**/*.eslintignore',
	'!../@(Sydney|sydney)/**/*.editorconfig',
	'!../@(Sydney|sydney)/**/*phpcs.xml.dist',
	'!../@(Sydney|sydney)/**/*phpunit.xml.dist',
	'!../@(Sydney|sydney)/**/*vscode',
	'!../@(Sydney|sydney)/**/*package.json',
	'!../@(Sydney|sydney)/**/*package-lock.json',
	'!../@(Sydney|sydney)/**/*assets/img/raw/**/*',
	'!../@(Sydney|sydney)/**/*assets/img/raw',
	'!../@(Sydney|sydney)/**/*assets/js/src/**/*',
	'!../@(Sydney|sydney)/**/*assets/js/src',
	'!../@(Sydney|sydney)/**/*playwright-mcp/**/*',
	'!../@(Sydney|sydney)/**/*.temporary.json',
	'!../@(Sydney|sydney)/**/*.cursor/**/*',
];

// >>>>> Translation options.
// Your text domain here.
const textDomain = 'sydney';

// Name of the translation file.
const translationFile = 'sydney.pot';

// Where to save the translation files.
const translationDestination = './languages';

// Package name.
const packageName = 'sydney';

// Where can users report bugs.
const bugReport = 'https://athemes.com/contact/';

// Last translator Email ID.
const lastTranslator = 'aThemes <team@athemes.com>';

// Team's Email ID.
const team = 'aThemes <team@athemes.com>';

// Browsers you care about for auto-prefixing. Browserlist https://github.com/ai/browserslist
// The following list is set as per WordPress requirements. Though; Feel free to change.
const BROWSERS_LIST = ['last 2 version', '> 1%'];

// Export.
module.exports = {
	projectURL,
	productURL,
	browserAutoOpen,
	injectChanges,
	modulesDestination,
	outputStyle,
	errLogToConsole,
	precision,
	modulesSRC,
	templatesSRC,
	templatesDestination,
	jsVendorSRC,
	custSRC,
	custDestination,
	custScriptsSRC,
	jsVendorDestination,
	jsVendorFile,
	custScriptsFile,
	custFile,
	jsCustomSRC,
	jsCustomDestination,
	jsSRC,
	jsCustomFile,
	imgSRC,
	imgDST,
	watchStyles,
	watchModuleStyles,
	watchTemplatesStyles,
	watchJsVendor,
	watchJsCustom,
	watchJsCustomizer,
	watchPhp,
	zipName,
	zipDestination,
	zipIncludeGlob,
	zipIgnoreGlob,
	textDomain,
	translationFile,
	translationDestination,
	packageName,
	bugReport,
	lastTranslator,
	team,
	BROWSERS_LIST,
	jsReadingProgressFile,
	jsModulesDestination,
	jsCustomizerSRC,
	jsCustomizerScriptsSRC,
	wooSRC,
	wooDestination,
	watchWooStyles,
	jsMultiStepFile,
	mainCSS,
	jsReadingProgressSRC,
	watchJsReadingProgress,
	jsmultistepSRC,
	watchJsMultistep,
	jsReviewsAdvancedFile,
	jsReviewsAdvSRC,
	watchJsReviewsAdv,
	mainCSSSRC,
	mainCSSDest
};