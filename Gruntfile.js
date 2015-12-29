module.exports = function(grunt) {
 
  grunt.registerTask('watch', [ 'watch' ]);
 
  grunt.initConfig({
    concat: {
      js: {
        options: {
          separator: ';'
        },
        src: [
          'assets/js/main/**/*.js'
        ],
        dest: 'public/js/header.js'
      },
      orgJs: {
        options: {
          separator: ';'
        },
        src: [
          'assets/js/org-management/**/*.js'
        ],
        dest: 'public/js/org-management-header.js'
      },
    },
    uglify: {
      options: {
        mangle: false
      },
      js: {
        files: {
          'public/js/header.js': 'public/js/header.js',
        }
      },
      orgJs: {
        files: {
          'public/js/org-management-header.js': 'public/js/org-management-header.js'
        }
      }
    },
    less: {
      style: {
        files: {
          "public/css/styles.css": "assets/css/main/styles.less"
        }
      },
      orgStyle: {
        files: {
          "public/css/org-management-styles.css": "assets/css/org-management/styles.less"
        }
      },
    },
    cssmin: {
      minify: {
        expand: true,
        cwd: 'public/css/',
        src: ['styles.css','org-management-styles.css'],
        dest: 'public/css'
      }
    },
    filerev: {
      options: {
        encoding: 'utf8',
        algorithm: 'md5',
        length: 8
      },
      css: {
        src: "public/css/**/*.css",
        dest: "public/static"
      },
      js: {
        src: "public/js/**/*.js",
        dest: "public/static"
      },
      fonts: {
        src: "public/fonts/**/*",
        dest: "public/static"
      },
      img: {
        src: "public/img/**/*.{jpg,jpeg,gif,png,webp,bmp}",
        dest: "public/static"
      },
      public: {
        src: "public/*.{ico,png}",
        dest: "public/static"
      }
    },
    filerev_assets: {
      dist: {
        options: {
          dest: 'assets/static.assets.json',
          cwd: 'public'
        }
      }
    },
    watch: {
      js: {
        files: ['assets/js/**/*.js'],
        tasks: ['concat:js', 'concat:orgJs', 'uglify:js', 'uglify:orgJs', 'filerev:js', 'filerev_assets'],
        options: {
          livereload: true
        }
      },
      css: {
        files: ['assets/css/**/*.less'],
        tasks: ['less:style', 'less:orgStyle', 'cssmin', 'filerev:css', 'filerev_assets'],
        options: {
          livereload: true
        }
      },
    }
  });
 
  // load tasks
  grunt.loadNpmTasks('grunt-contrib-concat');
  grunt.loadNpmTasks('grunt-contrib-uglify');
  grunt.loadNpmTasks('grunt-contrib-less');
  grunt.loadNpmTasks('grunt-contrib-cssmin');
  grunt.loadNpmTasks('grunt-filerev');
  grunt.loadNpmTasks('grunt-filerev-assets');
  grunt.loadNpmTasks('grunt-contrib-watch');

  // Default task(s).
  grunt.registerTask('default', ['concat','uglify','less','cssmin','filerev','filerev_assets']);
};