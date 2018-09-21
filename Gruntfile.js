/*
            ____                  _     _
           / ___|_   _  __ _  ___| |__ (_)
          | |  _| | | |/ _` |/ __| '_ \| |
          | |_| | |_| | (_| | (__| | | | |
           \____|\__,_|\__,_|\___|_| |_|_|
Copyright (c) 2014  Díaz  Víctor  aka  (Máster Vitronic)
Copyright (c) 2018  Díaz  Víctor  aka  (Máster Vitronic)
<vitronic2@gmail.com>   <mastervitronic@vitronic.com.ve>
*/

/*
 * Guachi (Lightweight and very simple web development framework)
 * https://gitlab.com/vitronic/Guachi_Framework
 *
 * Copyright (c) 2018 Díaz Devera Víctor (Máster Vitronic)
 * Licensed under the MIT license.
 */

module.exports = function(grunt) {
    'use strict';
    /* Configuracion general de las tareas*/
    grunt.initConfig({
        views   : 'views/',     /*views directory*/
        src_sass: 'public/css/themes/',/*sources sass*/
        src_less: 'public/css/themes/',/*sources less*/
        src_js  : 'public/js/', /*sources javascript*/
        /*
         * directorios a limpiar
         *
         * @see https://github.com/gruntjs/grunt-contrib-clean
         * */
        clean: [
            'public/css/dist/',
            'public/js/dist/'
        ],
        /*
         * Compilar sass
         *
         * @see https://github.com/gruntjs/grunt-contrib-sass
         * */
        sass: {
            dist: {
                options: {
                    sourcemap: 'none',       /*no sourcemap*/
                    style    : 'compressed', /*comprimir*/
                    noCache  : true          /*no cache*/
                },
                files: [{
                    expand: true,
                    cwd: '<%= src_sass %>',
                    src: ['**/**/**/**/*.scss'],
                    dest: 'public/css/themes',
                    ext: '.css'
                }]
            }
        },
        /*
         * Compilar less
         *
         * @see https://github.com/gruntjs/grunt-contrib-less
         * */
        less: {
            options: {
              //paths: ['extras/pruebas/less/'],
              plugins: [
                //new (require('less-plugin-autoprefix'))({browsers: ["last 2 versions"]}),
                //new (require('less-plugin-clean-css'))
              ]
            },
            files: {
                expand: true,
                cwd: '<%= src_less %>',
                src: ['**/**/**/**/*.less'],
                dest: 'public/css/themes',
                ext: ".css"
            }
        },
        /*
         * Comprimir los js
         *
         * @see https://github.com/gruntjs/grunt-contrib-uglify
         * */
        uglify: {
            options: {
                mangle: false,
                compress: {
                    drop_console: false
                }
            },
            js: {
                files: [{
                    expand: true,
                    cwd: '<%= src_js %>',
                    src: 'guachi.js',
                    dest: '<%= src_js %>'
                }]               
            }
        },
        /*
         * Browserify hacer todo esto compatible con el browser
         *
         * @see https://github.com/jmreidy/grunt-browserify
         * */
        browserify: {
          dist: {
            files: {
              'public/js/guachi.js': ['<%= src_js %>/**/**/*.src.js']
            },
            options: {
            }
          }
        },
        /*
         * watch para monitorizar los cambios
         *
         * @see https://github.com/gruntjs/grunt-contrib-watch
         * */
        watch: {
            browserify: {
              files: ['<%= src_js %>/**/**/*.src.js'],
              tasks: ['browserify','uglify'],
              options: {
                livereload: {
                    host: 'localhost',
                    //port: 35729,
                    //key: grunt.file.read('path/to/ssl.key'),
                    //cert: grunt.file.read('path/to/ssl.crt')
                }
              }
            },
            sass: {
              files: ['<%= src_sass %>/**/**/**/**/*.scss'],
              tasks: ['sass'],
              options: {
                livereload: {
                    host: 'localhost',
                    //port: 35729,
                    //key: grunt.file.read('path/to/ssl.key'),
                    //cert: grunt.file.read('path/to/ssl.crt')
                }
              }
            },
            less: {
              files: ['<%= src_less %>/**/**/**/**/*.less'],
              tasks: ['less'],
              options: {
                livereload: {
                    host: 'localhost',
                    //port: 35729,
                    //key: grunt.file.read('path/to/ssl.key'),
                    //cert: grunt.file.read('path/to/ssl.crt')
                }
              }
            },
            views: {
              files: ['<%= views %>/**/**/**/*.html'],
              options: {
                livereload: {
                    host: 'localhost',
                    //port: 35729,
                    //key: grunt.file.read('path/to/ssl.key'),
                    //cert: grunt.file.read('path/to/ssl.crt')
                }
              }
            }
        }
    });
    /* loadNpmTasks carga todas las tareas */
    /*cargo uglify */
    grunt.loadNpmTasks('grunt-contrib-uglify');
    /*cargo sass   */
    grunt.loadNpmTasks('grunt-contrib-sass');
    /*cargo less   */
    grunt.loadNpmTasks('grunt-contrib-less');
    /*cargo watch*/
    grunt.loadNpmTasks('grunt-contrib-watch');
    /*cargo browserify*/
    grunt.loadNpmTasks('grunt-browserify');
    /*cargo clean*/
    grunt.loadNpmTasks('grunt-contrib-clean');

    /*el builder  <grunt build> */
    grunt.registerTask('build', ['browserify','uglify','sass','less']);
    /*el watcher  <grunt monitor>*/
    grunt.registerTask('monitor', ['browserify','uglify','sass','less','watch']);
};