var gulp = require('gulp');
var $    = require('gulp-load-plugins')();

var sassPaths = [
  'bower_components/foundation-sites/scss',
  'bower_components/motion-ui/src'
];

var config = {
    jsPath: 'javascript',
     sassPath: 'scss',
     bowerDir: 'bower_components' 
}

var DO_WATCH = ($.util.env.watch ? true : false);

var sources = {
  admin: {
    scripts: [
        config.bowerDir + '/jquery/dist/jquery.min.js',
        config.bowerDir + '/bootstrap-validator/dist/validator.js',
        config.bowerDir + '/select2/dist/js/select2.js',
        config.bowerDir + '/bootstrap-maxlength/src/bootstrap-maxlength.js',             
        config.bowerDir + '/bootstrap-sass-official/assets/javascripts/bootstrap.min.js',
        config.bowerDir + '/bootstrap-datepicker/js/bootstrap-datepicker.js',
        config.bowerDir + '/datatables/media/js/jquery.dataTables.js',
        config.bowerDir + '/datatables-select/js/dataTables.select.js',
        config.bowerDir + '/datatables-buttons/js/buttons.bootstrap.select.js',
        config.bowerDir + '/mjolnic-bootstrap-colorpicker/dist/js/bootstrap-colorpicker.min.js',        
        config.bowerDir + '/jstree/dist/jstree.min.js',
        config.bowerDir + '/summernote/dist/summernote.min.js',   
        config.bowerDir + '/iCheck/icheck.min.js',
        config.jsPath   + '/main.js',
        config.bowerDir + '/codemirror/lib/codemirror.js',
        config.bowerDir + '/sweetalert/dist/sweetalert.min.js',
        config.bowerDir + '/wchar/wchar.min.js',

    ],
    resources: [
      'resources/assets/**/*'
    ],
    fonts: [
        config.bowerDir + '/font-awesome/fonts/**/**.*', 
        config.bowerDir + '/bootstrap-sass-official/assets/fonts/bootstrap/**/**.*',
        config.bowerDir + '/jstree/src/themes/default/**.*',
        config.bowerDir + '/open-sans/fonts/**/**.*'
    ]
  }

};


gulp.task('icons', function() { 
    return gulp.src(sources.admin.fonts) 
        .pipe(gulp.dest('../../../../public/hideyo/fonts')); 
});


gulp.task('adminjavascript', function() { 
    return gulp.src(sources.admin.scripts) 
        .pipe($.plumber())
        .pipe($.concat('site.js'))
        .pipe($.uglify())
        .pipe(gulp.dest('../../../../public/hideyo/javascript/'))

});

gulp.task('bower', function() { 
    return $.bower()
         .pipe(gulp.dest(config.bowerDir)) 
});

gulp.task('jstree', function() { 
    return gulp.src(config.bowerDir + '/jstree/src/themes/default/**.*') 
        .pipe(gulp.dest('../../../../public/hideyo/javascript/jstree')); 
});


gulp.task('adminsass', function() {
    return gulp.src('./scss/style.scss')
        .pipe($.sass({
          includePaths: [sassPaths]
        })
          .on('error', $.sass.logError))
        .pipe($.autoprefixer({
          browsers: ['last 2 versions', 'ie >= 9']
        }))
        //.pipe($.cleanCss())
        .pipe(gulp.dest('../../../../public/hideyo/css/'));
});


gulp.task('default', ['bower', 'jstree', 'icons', 'adminsass',  'adminjavascript'], function() {
if (DO_WATCH) {
    gulp.watch(['./resources/scss/**/*.scss'], ['adminsass']);
    gulp.watch([config.jsPath + '/admin/main.js'], ['adminjavascript']);

}

});