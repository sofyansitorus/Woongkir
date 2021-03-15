/**
 * Import modules
 */
const packageJSON = require('./package.json');
const semver = require('semver');
const argv = require('yargs').argv;
const browserSync = require('browser-sync').create();

const gulp = require('gulp');
const gulpAutoprefixer = require('gulp-autoprefixer');
const gulpCleanCSS = require('gulp-clean-css');
const gulpConcat = require('gulp-concat');
const gulpIf = require('gulp-if');
const gulpIife = require('gulp-iife');
const gulpNotify = require('gulp-notify');
const gulpPhpcs = require('gulp-phpcs');
const gulpPlumber = require('gulp-plumber');
const gulpRename = require('gulp-rename');
const gulpReplace = require('gulp-replace');
const gulpSass = require('gulp-sass');
const gulpSourcemaps = require('gulp-sourcemaps');
const gulpUglify = require('gulp-uglify');
const gulpWpPot = require('gulp-wp-pot');

/**
 * Local variables
 */
const prefix = 'woongkir';
const project = 'Woongkir';

const assets = [
	{
		type: 'scripts',
		target: 'backend',
		sources: [
			'polyfill.js',
			'shared.js',
			'backend.js',
		],
		targetDir: 'assets/js/',
		sourcesDir: 'assets/src/js/',
		isPrefixed: true,
		isIife: true,
		isSourceMap: false,
	},
	{
		type: 'scripts',
		target: 'frontend',
		sources: [
			'polyfill.js',
			'shared.js',
			'frontend.js',
		],
		targetDir: 'assets/js/',
		sourcesDir: 'assets/src/js/',
		isPrefixed: true,
		isIife: true,
		isSourceMap: false,
	},
	{
		type: 'scripts',
		target: 'lockr',
		sources: [
			'plugins/lockr.js',
		],
		targetDir: 'assets/js/',
		sourcesDir: 'assets/src/js/',
		isPrefixed: false,
		isIife: false,
		isSourceMap: false,
	},
	{
		type: 'styles',
		target: 'backend',
		sources: [
			'backend.scss',
		],
		targetDir: 'assets/css/',
		sourcesDir: 'assets/src/scss/',
		isPrefixed: true,
		isSourceMap: false,
	},
	{
		type: 'php',
		target: 'php',
		sources: [
			'*.php',
			'**/*.php',
			'!vendor/',
			'!vendor/**',
			'!dist/',
			'!dist/**',
			'!node_modules/',
			'!node_modules/**',
			'!index.php',
			'!**/index.php',
		],
	},
];

/**
 * Custom error handler
 */
const errorHandler = function () {
	return gulpPlumber(function (err) {
		gulpNotify.onError({
			title: 'Gulp error in ' + err.plugin,
			message: err.toString()
		})(err);
	});
};

/**
 * Script task handler
 */
const scriptsHandler = function (asset, isMinify) {
	const srcParam = asset.sources.map(function (sources) {
		const sourcesDir = asset.sourcesDir || '';
		return sourcesDir + sources;
	});

	return gulp.src(srcParam)
		.pipe(errorHandler())
		.pipe(gulpConcat(asset.target + '.js'))
		.pipe(gulpIf(asset.isIife, gulpIife({
			useStrict: true,
			trimCode: true,
			prependSemicolon: false,
			bindThis: false,
			params: ['$', 'wc_checkout_params'],
			args: ['jQuery', 'window.wc_checkout_params']
		})))
		.pipe(gulpIf(asset.isPrefixed, gulpRename({
			prefix: prefix + '-',
		})))
		.pipe(gulp.dest(asset.targetDir))
		.pipe(gulpIf(isMinify, gulpRename({
			suffix: '.min',
		})))
		.pipe(gulpIf(asset.isSourceMap, gulpSourcemaps.init()))
		.pipe(gulpIf(isMinify, gulpUglify()))
		.pipe(gulpIf(asset.isSourceMap, gulpSourcemaps.write()))
		.pipe(gulpIf(isMinify, gulp.dest(asset.targetDir)));
}

/**
 * Style task handler
 */
const stylesHandler = function (asset, isMinify) {
	const srcParam = asset.sources.map(function (sourcesFile) {
		const sourcesDir = asset.sourcesDir || '';
		return sourcesDir + sourcesFile;
	});

	return gulp.src(srcParam)
		.pipe(errorHandler())
		.pipe(gulpIf(asset.isSourceMap, gulpSourcemaps.init()))
		.pipe(gulpSass().on('error', gulpSass.logError))
		.pipe(gulpAutoprefixer(
			'last 2 version',
			'> 1%',
			'safari 5',
			'ie 8',
			'ie 9',
			'opera 12.1',
			'ios 6',
			'android 4'))
		.pipe(gulpIf(asset.isPrefixed, gulpRename({
			prefix: prefix + '-',
		})))
		.pipe(gulp.dest(asset.targetDir))
		.pipe(gulpIf(isMinify, gulpRename({
			suffix: '.min',
		})))
		.pipe(gulpIf(isMinify, gulpCleanCSS({
			compatibility: 'ie8',
		})))
		.pipe(gulpIf(asset.isSourceMap, gulpSourcemaps.write()))
		.pipe(gulpIf(isMinify, gulp.dest(asset.targetDir)))
		.pipe(gulpIf(!isMinify, browserSync.stream()));
}

/**
 * PHPCS tasks handler
 */
const phpcsHandler = function (asset) {
	const srcParam = asset.sources.map(function (sourcesFile) {
		const sourcesDir = asset.sourcesDir || '';
		return sourcesDir + sourcesFile;
	});

	const config = Object.assign({}, asset.config, {
		bin: 'vendor/bin/phpcs',
		standard: 'phpcs.xml',
		warningSeverity: 0,
	});

	return gulp.src(srcParam)
		.pipe(errorHandler())
		.pipe(gulpPhpcs(config))
		.pipe(gulpPhpcs.reporter('log'));
}

/**
 * Internationalization task handler
 */
const i18nHandler = function (asset) {
	const srcParam = asset.sources.map(function (sourcesFile) {
		const sourcesDir = asset.sourcesDir || '';
		return sourcesDir + sourcesFile;
	});

	const config = Object.assign({}, asset.config, {
		domain: prefix,
		package: project,
	});

	return gulp.src(srcParam)
		.pipe(gulpWpPot(config))
		.pipe(gulp.dest('languages/' + config.domain + '.pot'));
}

/**
 * Build tasks list
 */
const tasksListBuild = [];

assets.forEach(function (asset) {
	/**
	 * Minify Scripts Task
	 */
	if (asset.type === 'scripts') {
		const taskName = asset.target + '-scripts-minify';

		gulp.task(taskName, function () {
			return scriptsHandler(asset, true);
		});

		tasksListBuild.push(taskName);
	}

	/**
	 * Minify Styles Task
	 */
	if (asset.type === 'styles') {
		const taskName = asset.target + '-styles-minify';

		gulp.task(taskName, function () {
			return stylesHandler(asset, true);
		});

		tasksListBuild.push(taskName);
	}

	/**
	 * Internationalization Task
	 */
	if (asset.type === 'php') {
		const taskName = asset.target + '-i18n';

		gulp.task(taskName, function () {
			return i18nHandler(asset);
		});

		tasksListBuild.push(taskName);
	}
});

/**
 * Build task
 */
gulp.task('build', tasksListBuild);

/**
 * Default tasks list
 */
const tasksListDefault = [];

assets.forEach(function (asset) {
	/**
	 * Scripts Task
	 */
	if (asset.type === 'scripts') {
		const taskName = asset.target + '-scripts';

		gulp.task(taskName, function () {
			return scriptsHandler(asset);
		});

		tasksListDefault.push(taskName);
	}

	/**
	 * Styles Task
	 */
	if (asset.type === 'styles') {
		const taskName = asset.target + '-styles';

		gulp.task(taskName, function () {
			return stylesHandler(asset, false);
		});

		tasksListDefault.push(taskName);
	}

	/**
	 * PHPCS Task
	 */
	if (asset.type === 'php') {
		const taskName = asset.target + '-phpcs';

		gulp.task(taskName, function () {
			return phpcsHandler(asset);
		});

		tasksListDefault.push(taskName);
	}
});


/**
 * Default task
 */
gulp.task('default', tasksListDefault, function () {
	if (argv.hasOwnProperty('proxy')) {
		browserSync.init({
			proxy: argv.proxy
		});
	}

	assets.forEach(function (asset) {
		/**
		 * Watch styles sources files
		 */
		if (asset.type === 'styles') {
			const watchStylesSrc = asset.sources.map(function (sourcesFile) {
				const sourcesDir = asset.sourcesDir || '';
				return sourcesDir + sourcesFile;
			});

			gulp.watch(watchStylesSrc, [asset.target + '-styles']);
		}

		/**
		 * Watch scripts sources files
		 */
		if (asset.type === 'scripts') {
			const watchScriptsSrc = asset.sources.map(function (sourcesFile) {
				const sourcesDir = asset.sourcesDir || '';
				return sourcesDir + sourcesFile;
			});

			gulp.watch(watchScriptsSrc, [asset.target + '-scripts']).on('change', function () {
				if (argv.hasOwnProperty('proxy')) {
					browserSync.reload();
				}
			});
		}

		/**
		 * Watch php sources files
		 */
		if (asset.type === 'php') {
			const watchSrc = asset.sources.map(function (sourcesFile) {
				const sourcesDir = asset.sourcesDir || '';
				return sourcesDir + sourcesFile;
			});

			gulp.watch(watchSrc, [asset.target + '-phpcs']).on('change', function () {
				if (argv.hasOwnProperty('proxy')) {
					browserSync.reload();
				}
			});
		}
	});
});

gulp.task('bump', function () {
	const versionCurrent = packageJSON.version;
	const versionBump = semver.inc(packageJSON.version, (argv.semver || 'patch'));

	const assets = [
		{
			src: ['./includes/**/*.php'],
			dest: './includes/',
			search: ' ??\n',
			replaceWith: ' {versionBump}\n',
		},
		{
			src: ['./woongkir.php'],
			dest: './',
			search: '{versionCurrent}',
			replaceWith: '{versionBump}',
		},
		{
			src: ['./README.txt'],
			dest: './',
			search: 'Stable tag: {versionCurrent}',
			replaceWith: 'Stable tag: {versionBump}',
		},
		{
			src: ['./package.json'],
			dest: './',
			search: '{versionCurrent}',
			replaceWith: '{versionBump}',
		},
	];

	assets.forEach(function (asset) {
		gulp.src(asset.src)
			.pipe(gulpReplace(asset.search.replace('{versionCurrent}', versionCurrent), asset.replaceWith.replace('{versionBump}', versionBump)))
			.pipe(gulp.dest(asset.dest));
	});
});

// Export task
gulp.task('dist', ['build'], function () {
	gulp.src([
		'./**',
		'!tests/',
		'!bin/',
		'!vendor/',
		'!dist/',
		'!node_modules/',
		'!assets/src/',
		'!tests/**',
		'!bin/**',
		'!vendor/**',
		'!dist/**',
		'!node_modules/**',
		'!assets/src/**',
		'!gulpfile.js',
		'!package-lock.json',
		'!package.json',
		'!composer.lock',
		'!composer.json',
		'!yarn.lock',
		'!phpcs.xml'
	])
		.pipe(gulp.dest('./dist/trunk'))
		.pipe(gulp.dest('./dist/tags/' + packageJSON.version));
});
