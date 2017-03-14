module.exports = function(grunt) {

	// Project configuration.
	grunt.initConfig({
		pkg: grunt.file.readJSON('package.json'),
		clean: [
			"<%= pkg.dirPlugins %>/components-font-awesome",
			"<%= pkg.dirPlugins %>/font-awesome",
			"<%= pkg.dirPlugins %>/handlebars.js",
			"<%= pkg.dirPlugins %>/jqueryui",
			"<%= pkg.dirPlugins %>/sass-bootstrap",
			"<%= pkg.dirPlugins %>/**/bootstrap",
			"<%= pkg.dirPlugins %>/**/src",
			"<%= pkg.dirPlugins %>/**/grunt",
			"<%= pkg.dirPlugins %>/**/test",
			"<%= pkg.dirPlugins %>/**/tests",
			"<%= pkg.dirPlugins %>/**/doc",
			"<%= pkg.dirPlugins %>/**/docs",
			"<%= pkg.dirPlugins %>/**/example",
			"<%= pkg.dirPlugins %>/**/examples",
			"<%= pkg.dirPlugins %>/**/sample",
			"<%= pkg.dirPlugins %>/**/samples",
			"<%= pkg.dirPlugins %>/**/demo",
			"<%= pkg.dirPlugins %>/**/demos",
			"<%= pkg.dirPlugins %>/**/bower_components",
			"<%= pkg.dirPlugins %>/**/node_modules",
			"<%= pkg.dirPlugins %>/**/bower.json",
			"<%= pkg.dirPlugins %>/**/component.json",
			"<%= pkg.dirPlugins %>/**/components.json",
			"<%= pkg.dirPlugins %>/**/composer.json",
			"<%= pkg.dirPlugins %>/**/Gruntfile.js",
			"<%= pkg.dirPlugins %>/**/package.json",
			"<%= pkg.dirPlugins %>/**/CONTRIBUTING.md",
			"<%= pkg.dirPlugins %>/**/README.md",
			"<%= pkg.dirPlugins %>/**/CHANGELOG.md"
		],
		modernizr: {
			dist: {
				// [REQUIRED] Path to the build you're using for development.
				"devFile" : "remote",

				// Path to save out the built file.
				"outputFile" : "<%= pkg.dirPlugins %>/modernizr/modernizr.min.js",

				// Based on default settings on http://modernizr.com/download/
				"extra" : {
					"shiv" : true,
					"printshiv" : false,
					"load" : true,
					"mq" : false,
					"cssclasses" : true
				},

				// Based on default settings on http://modernizr.com/download/
				"extensibility" : {
					"addtest" : false,
					"prefixed" : false,
					"teststyles" : false,
					"testprops" : false,
					"testallprops" : false,
					"hasevents" : false,
					"prefixes" : true,
					"domprefixes" : false,
					"cssclassprefix": ""
				},

				// By default, source is uglified before saving
				"uglify" : true,

				// Define any tests you want to implicitly include.
				"tests" : [],

				// By default, this task will crawl your project for references to Modernizr tests.
				// Set to false to disable.
				"parseFiles" : true,

				// When parseFiles = true, this task will crawl all *.js, *.css, *.scss and *.sass files,
				// except files that are in node_modules/.
				// You can override this by defining a "files" array below.
				// "files" : {
						// "src": []
				// },

				// This handler will be passed an array of all the test names passed to the Modernizr API, and will run after the API call has returned
				// "handler": function (tests) {},

				// When parseFiles = true, matchCommunityTests = true will attempt to
				// match user-contributed tests.
				"matchCommunityTests" : false,

				// Have custom Modernizr tests? Add paths to their location here.
				"customTests" : []
			}
		}
	});

	// Grunt Clean
	grunt.loadNpmTasks('grunt-contrib-clean');

	// Modernizr
	grunt.loadNpmTasks("grunt-modernizr");

};