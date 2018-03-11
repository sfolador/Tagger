var gulp         = require("gulp"),
    util         = require('gulp-util'),
    sass         = require("gulp-sass"),
    filter       = require('gulp-filter'),
    sourcemaps   = require('gulp-sourcemaps'),
    shell        = require('gulp-shell'),
    uglify       = require('gulp-uglify'),
    flatten      = require('gulp-flatten'),
    concat       = require('gulp-concat'),
    image        = require('gulp-image'),
    postcss      = require('gulp-postcss'),
    browserify   = require('browserify'),
    source       = require('vinyl-source-stream'),
    buffer       = require('vinyl-buffer'),
    cssnext      = require('postcss-cssnext'),
    autoprefixer = require('autoprefixer'),
    doiuse       = require('doiuse'),
    path         = require('path'),
    fs           = require('fs'),
    callerId     = require('caller-id')
   browserSync  = require("browser-sync");
   // reload       = browserSync.reload;

var mainNpmFiles = module.exports = function (options) {
    function getMainFile(modulePath) {
        var json = JSON.parse(fs.readFileSync(modulePath + '/package.json'));
        return modulePath + "/" + (json.main || "index.js");
    }

    options = options || {};

    if (!options.nodeModulesPath) {
        options.nodeModulesPath = './node_modules';
    }
    else if (!path.isAbsolute(options.nodeModulesPath)) {
        var caller              = callerId.getData();
        options.nodeModulesPath = path.join(path.dirname(caller.filePath), options.nodeModulesPath);
    }

    if (!options.packageJsonPath) {
        options.packageJsonPath = './package.json';
    }
    else if (!path.isAbsolute(options.packageJsonPath)) {
        var caller              = callerId.getData();
        options.packageJsonPath = path.join(path.dirname(caller.filePath), options.packageJsonPath);
    }

    var buffer, packages, keys;
    buffer   = fs.readFileSync(options.packageJsonPath);
    packages = JSON.parse(buffer.toString());
    keys     = [];
    keys.push(config.PATH_SRC + 'js/vendor/*.js');

    var overrides_name_package = [];
    for (var key in packages.overrides) {
        overrides_name_package.push(key);
        var path = packages.overrides[key].main;
        if (typeof path == 'object') {
            for (var childKey in path) {
                keys.push(options.nodeModulesPath + "/" + key + '/' + path[childKey]);
            }
        }
        else {
            keys.push(options.nodeModulesPath + "/" + key + '/' + path);
        }
    }

    for (var key in packages.dependencies) {
        if (overrides_name_package.indexOf(key) < 0) {
            keys.push(getMainFile(options.nodeModulesPath + "/" + key));
        }
    }

    if (options.devDependencies) {
        for (var key in packages.devDependencies) {
            keys.push(getMainFile(options.nodeModulesPath + "/" + key));
        }
    }

    return keys;
};

var config = {
    PATH_SRC              : 'assets/src/',
    PATH_BUILD            : 'assets/dist/',
    production            : true,
   APP_URL : 'http://wordpress.simonefolador.local'
};

var sass_opts = {
    outputStyle : config.production ? 'compressed' : 'nested',
    includePaths: [path.join(__dirname, 'node_modules')]
};

// recupera, concatena e minimizza le librerie (Node Modules) necessarie per l'applicativo
gulp.task('npm', function () {
    gulp.src(mainNpmFiles(), {base: './node_modules'})
        .pipe(filter('**/*.js'))
        .pipe(uglify())
        .pipe(concat('vendor.js'))
        .pipe(gulp.dest(config.PATH_BUILD + 'js'));

    gulp.src(mainNpmFiles(), {base: './node_modules'})
        .pipe(filter('**/*.css'))
        .pipe(concat('vendor.css'))
        .pipe(sass({
            outputStyle: 'compressed'
        }))
        .pipe(flatten({includeParents: 1}))
        .pipe(gulp.dest(config.PATH_BUILD + 'css'));

    return gulp.src(mainNpmFiles(), {base: './node_modules'})
        .pipe(filter('**/*.{otf,eot,svg,ttf,woff,woff2}'))
        .pipe(flatten({includeParents: 1}))
        .pipe(gulp.dest(config.PATH_BUILD + 'fonts'));

    // return gulp.src(mainNpmFiles(), {base: './node_modules'})
    //            .pipe(filter('**/*.js'))
    //            .pipe(flatten({ includeParents: 1}))
    //            .pipe(config.production ? minify({ext: {min: '.min.js'}}) : util.noop())
    //            .pipe(gulp.dest(config.PATH_BUILD + 'js'));
});


// copia i file necessari
gulp.task('copy', function () {

      gulp.src(config.PATH_SRC + 'fonts/**/*.{otf,eot,svg,ttf,woff,woff2}')
         .pipe(flatten({includeParents: 1}))
         .pipe(gulp.dest(config.PATH_BUILD + 'fonts'));

   });


// recupera, concatena e minimizza i files JS dell'applicativo
gulp.task('js', function () {


    // var b = browserify({
    //     //entries: config.PATH_SRC + 'js/require.js',
    //     debug: true
    // });

    // b.bundle()
    //     .pipe(source('bundle.min.js'))
    //     .pipe(buffer())
    //     .pipe(config.production ? uglify() : util.noop())
    //     .pipe(gulp.dest(config.PATH_BUILD + 'js'));

    gulp.src([config.PATH_SRC + 'js/tagger.js'])
        .pipe(config.production ? uglify({mangle: false}) : util.noop())
        .pipe(concat('application.js'))
        .pipe(gulp.dest(config.PATH_BUILD + 'js'));

    gulp.src([config.PATH_SRC + 'js/jquery.fancybox-1.3.4/fancybox/jquery.fancybox-1.3.4.js'])
        .pipe(config.production ? uglify({mangle: false}) : util.noop())
         .pipe(concat('libs.js'))
        .pipe(gulp.dest(config.PATH_BUILD + 'js/libs'));

});


// scss
gulp.task('sass', function () {


      //  .pipe(browserSync.reload({stream: true}));

    gulp.src([config.PATH_SRC + 'sass/admin/*.scss'])
        .pipe(config.production ? util.noop() : sourcemaps.init())
        .pipe(sass(sass_opts).on('error', sass.logError))
        .pipe(
            postcss(
                [
                    cssnext(),
                    // autoprefixer(),
                    // doiuse({
                    //     browsers   : [
                    //         'ie >= 10'
                    //     ],
                    //     ignore     : ['rem', 'text-size-adjust', 'outline', 'css-appearance', 'css-resize'], // an optional
                    //     ignoreFiles: ['**/normalize.css']
                    // })
                ]
            )
        )
        .pipe(config.production ? util.noop() : sourcemaps.write())
        .pipe(gulp.dest(config.PATH_BUILD + 'css/admin'))
        .pipe(filter('scss**/*.css'));

    gulp.src([config.PATH_SRC + 'js/jquery.fancybox-1.3.4/fancybox/*.css'])
        .pipe(config.production ? util.noop() : sourcemaps.init())
        .pipe(sass(sass_opts).on('error', sass.logError))
        .pipe(
            postcss(
                [
                    cssnext()
                ]
            )
        )
        .pipe(config.production ? util.noop() : sourcemaps.write())
        .pipe(gulp.dest(config.PATH_BUILD + 'css/admin'))
        .pipe(filter('scss**/*.css'));
     //   .pipe(browserSync.reload({stream: true}));

    return gulp.src([config.PATH_SRC + 'sass/app/*.scss'])
        .pipe(config.production ? util.noop() : sourcemaps.init())
        .pipe(sass(sass_opts).on('error', sass.logError))
        .pipe(
            postcss(
                [
                    cssnext(),
                    // autoprefixer(),
                    // doiuse({
                    //     browsers   : [
                    //         'ie >= 10'
                    //     ],
                    //    warn: false,
                    //     ignore     : ['rem', 'text-size-adjust', 'outline', 'css-appearance', 'css-resize'], // an optional
                    //     ignoreFiles: ['**/normalize.css'],
                    // })
                ]
            )
        )
        .pipe(config.production ? util.noop() : sourcemaps.write())
        .pipe(concat('app.css'))
        .pipe(gulp.dest(config.PATH_BUILD + 'css'))
        .pipe(filter('scss**/*.css'));
     //   .pipe(browserSync.reload({stream: true}));
});


// copy
gulp.task('copy', function () {


    return gulp.src(config.PATH_SRC + 'js/vendor/*.json')
        .pipe(gulp.dest(config.PATH_BUILD + 'js/vendor'));

});


// scss
gulp.task('images', function () {

    gulp.src(config.PATH_SRC + 'js/jquery.fancybox-1.3.4/fancybox/**/*.{jpg,png,gif,svg}')
        // .pipe(image(
        //     {
        //         pngquant      : true,
        //         optipng       : false,
        //         zopflipng     : true,
        //         jpegRecompress: false,
        //         mozjpeg       : true,
        //         guetzli       : false,
        //         gifsicle      : true,
        //         svgo          : true,
        //         concurrent    : 10
        //     }
        // ))
        .pipe(gulp.dest(config.PATH_BUILD + 'css/admin'));

    return gulp.src(config.PATH_SRC + 'images/**/*.{jpg,png,gif,svg}')
        .pipe(image(
            {
                pngquant      : true,
                optipng       : false,
                zopflipng     : true,
                jpegRecompress: false,
                mozjpeg       : true,
                guetzli       : false,
                gifsicle      : true,
                svgo          : true,
                concurrent    : 10
            }
        ))
        .pipe(gulp.dest(config.PATH_BUILD + 'images'));

});

//
// // Sincronizzazione Browser
gulp.task('browser-sync', function () {
//     //watch files
    var files = [
//         config.PATH_BUILD + 'css/**/*.css',
//         config.PATH_BUILD + 'js/**/*.js',
//         'resources/**/*.blade.php'
    ];
//
//     //init browsersync
    browserSync.init(files, {
        proxy          : config.APP_URL,
        notify         : true,
        reloadOnRestart: true,
        injectChanges: true
    });
});


gulp.task('watch', function () {
    gulp.watch(config.PATH_SRC + 'sass/**/*.scss', ['sass']);
    gulp.watch(config.PATH_SRC + 'js/**/*.js', ['js']);
});


// Default task to be run with `gulp`
gulp.task('default', ['npm', 'images', 'copy', 'sass', 'js'], function () {

    gulp.start('watch');
    // if (config.production) {
    //     gulp.start('images');
    // }
    // else {
       gulp.start('browser-sync');
    //     gulp.start('watch');
    // }
});