module.exports = function( grunt ) {

	"use strict";

	// Project configuration
	grunt.initConfig( {

		pkg: grunt.file.readJSON( "package.json" ),

		addtextdomain: {
			options: {
				textdomain: "business-matchup-polls",
			},
			updateAllDomains: {
				options: {
					updateDomains: true
				},
				src: [ "*.php", "**/*.php", "!\.git/**/*", "!bin/**/*", "!node_modules/**/*", "!tests/**/*" ]
			}
		},

		"wp_readme_to_markdown": {
			yourTarget: {
				files: {
					"README.md": "readme.txt"
				}
			},
		},

		makepot: {
			target: {
				options: {
					domainPath: "/languages",
					exclude: [ "\.git/*", "bin/*", "node_modules/*", "tests/*" ],
					mainFile: "business-matchup-polls.php",
					potFilename: "business-matchup-polls.pot",
					potHeaders: {
						poedit: true,
						"x-poedit-keywordslist": true
					},
					type: "wp-plugin",
					updateTimestamp: true
				}
			}
		},
	} );

	grunt.loadNpmTasks( "grunt-wp-i18n" );
	grunt.loadNpmTasks( "grunt-wp-readme-to-markdown" );
	grunt.registerTask( "default", [ "i18n","readme" ] );
	grunt.registerTask( "i18n", ["addtextdomain", "makepot"] );
	grunt.registerTask( "readme", ["wp_readme_to_markdown"] );

	grunt.util.linefeed = "\n";

};