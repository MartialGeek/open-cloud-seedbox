var exec = require('child_process').execFile;

module.exports = function(grunt) {
  grunt.registerTask('modernizr', 'Build the modernizr script', function() {
    grunt.log.writeln('Running the build...');
    exec('./node_modules/modernizr/bin/modernizr', [
      '-c',
      'node_modules/modernizr/lib/config-all.json'
    ], function(error, stdout, stderr) {
      if (error) {
        grunt.log.error(stderr);
        throw error;
      }
    });
  });

  grunt.initConfig({
    pkg: grunt.file.readJSON('package.json'),

    sass: {
      options: {
        includePaths: ['node_modules/zurb-foundation-5/scss']
      },
      dist: {
        options: {
          outputStyle: 'compressed'
        },
        files: {
          'css/app.css': '../src/Front/View/Home/scss/app.scss'
        }
      }
    },

    watch: {
      grunt: { files: ['Gruntfile.js'] },

      sass: {
        files: '../src/Front/View/Home/scss/**/*.scss',
        tasks: ['sass']
      }
    }
  });

  grunt.loadNpmTasks('grunt-sass');
  grunt.loadNpmTasks('grunt-contrib-watch');

  grunt.registerTask('build', ['sass', 'modernizr']);
  grunt.registerTask('default', ['build','watch']);
};
