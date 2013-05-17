'use strict';

module.exports = function(grunt) {

    grunt.initConfig({

        // javascript linting with jshint
        jshint: {
            options: {
                "bitwise": true,
                "eqeqeq": true,
                "eqnull": true,
                "immed": true,
                "newcap": true,
                "es5": true,
                "esnext": true,
                "latedef": true,
                "noarg": true,
                "node": true,
                "undef": true,
                "browser": true,
                "trailing": true,
                "jquery": true,
                "curly": true
            },
            all: [
                'Gruntfile.js',
                '../js/main.js'
            ]
        },

        // uglify to concat and minify
        uglify: {
            dist: {
                files: {
                    '../js/main.min.js': [
                        '../core/colorbox/js/*.js', // Colorbox
                        '../core/lazyload/js/*.js', // LazyLoad
                        // '../core/photoswipe/js/*.js', // Photoswipe
                        '../core/socialite/js/*.js', // Socialite
                        '../js/jquery.fitvids.min.js', // FitVids
                        '../js/libs/*.js', // Project libs includes
                        '../js/main.js'
                    ]
                }
            }
        },

        // compass and scss
        compass: {
            dist: {
                options: {
                    config: 'config.rb',
                    force: true,
                    outputStyle: 'compressed'
                }
            }
        },

        // watch for changes and trigger compass, jshint and uglify
        watch: {
            compass: {
                files: [
                    '../sass/**'
                ],
                tasks: ['compass']
            },
            js: {
                files: [
                    '<%= jshint.all %>'
                ],
                tasks: ['jshint', 'uglify']
            }
        },

        // image optimization
        imagemin: {
            dist: {
                options: {
                    optimizationLevel: 7,
                    progressive: true
                },
                files: [{
                    expand: true,
                    cwd: '../images/',
                    src: '../images/**',
                    dest: '../images/'
                }]
            }
        },

        // deploy via rsync
        rsync: {
            staging: {
                src: "../",
                dest: "~/PATH/wp-content/themes/odin",
                host: "user@host.com",
                recursive: true,
                syncDest: true,
                exclude: [
                    '**.DS_Store',
                    '**Thumbs.db',
                    '.git/',
                    '.gitignore',
                    'sass/',
                    'src/',
                    'README.md'
                ]
            },
            production: {
                src: "../",
                dest: "~/PATH/wp-content/themes/odin",
                host: "user@host.com",
                recursive: true,
                syncDest: true,
                exclude: '<%= rsync.staging.exclude %>'
            }
        },

        // ftp deploy
        // ref: https://npmjs.org/package/grunt-ftp-deploy
        'ftp-deploy': {
            build: {
                auth: {
                    host: 'ftp.SEU-SITE.com',
                    port: 21,
                    authKey: 'key_for_deploy'
                },
                src: '../',
                dest: '/PATH/wp-content/themes/odin',
                exclusions: [
                    '../**.DS_Store',
                    '../**Thumbs.db',
                    '../.git/*',
                    '../.gitignore',
                    '../sass/*',
                    '../src/*',
                    '../src/.sass-cache/*',
                    '../src/node_modules/*',
                    '../src/.ftppass',
                    '../src/Gruntfile.js',
                    '../src/config.rb',
                    '../src/package.json',
                    '../README.md',
                    '../**/README.md'
                ]
            }
        },

        // downloads dependencies
        curl: {
            bootstrap: {
                src: 'https://github.com/jlong/sass-twitter-bootstrap/archive/master.zip',
                dest: 'tmp/bootstrap.zip'
            }
        },

        // unzip files
        unzip: {
            bootstrap_scss: {
                src: 'tmp/bootstrap.zip',
                dest: 'tmp/'
            }
        },

        // renames and moves directories and files
        rename: {
            bootstrap_scss: {
                src: 'tmp/sass-twitter-bootstrap-master/lib',
                dest: '../sass/bootstrap'
            },
            bootstrap_js: {
                src: 'tmp/sass-twitter-bootstrap-master/js',
                dest: '../js/bootstrap'
            },
            bootstrap_img: {
                src: 'tmp/sass-twitter-bootstrap-master/img',
                dest: '../images/bootstrap'
            },
            bootstrapscss_to_bootstrap_scss: {
                src: '../sass/bootstrap/bootstrap.scss',
                dest: '../sass/bootstrap/_bootstrap.scss'
            },
            responsivescss_to_responsive_scss: {
                src: '../sass/bootstrap/responsive.scss',
                dest: '../sass/bootstrap/_responsive.scss'
            }
        },

        // cleans directories and files
        clean: {
            prepare: [
                "tmp",
                "../sass/bootstrap/",
                "../js/bootstrap/",
                "../images/bootstrap/"
            ],
            bootstrap: [
                "../sass/bootstrap/tests/",
                "../js/bootstrap/tests/",
                "../js/bootstrap/.jshintrc",
                "tmp"
            ]
        }
    });

    // load tasks
    grunt.loadNpmTasks('grunt-contrib-watch');
    grunt.loadNpmTasks('grunt-contrib-jshint');
    grunt.loadNpmTasks('grunt-contrib-uglify');
    grunt.loadNpmTasks('grunt-contrib-compass');
    grunt.loadNpmTasks('grunt-contrib-imagemin');
    grunt.loadNpmTasks('grunt-rsync');
    grunt.loadNpmTasks('grunt-ftp-deploy');

    // extra tasks
    grunt.loadNpmTasks('grunt-curl');
    grunt.loadNpmTasks('grunt-zip');
    grunt.loadNpmTasks('grunt-rename');
    grunt.loadNpmTasks('grunt-clean');

    // register task
    grunt.registerTask('default', [
        'jshint',
        'compass',
        'uglify'
    ]);

    // bootstrap task
    grunt.registerTask('bootstrap', [
        'clean:prepare',
        'curl:bootstrap',
        'unzip:bootstrap_scss',
        'rename:bootstrap_scss',
        'rename:bootstrap_js',
        'rename:bootstrap_img',
        'rename:bootstrapscss_to_bootstrap_scss',
        'rename:responsivescss_to_responsive_scss',
        'clean:bootstrap',
        'compass'
    ]);

};
