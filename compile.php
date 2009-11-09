<?php

function help() {
	echo "Compiles a single javascript file or a whole folder in the cloud.\n";
	echo "\n";
	echo "Usage:\n";
	echo "  php compile.php /javascripts [options]\n";
	echo "  php compile.php /javascripts/myfile.js [options]\n";
	echo "\n";
	echo "Options are:\n";
	echo str_pad("  -q, --quiet", 26, " ") . "no output\n";
	echo str_pad("  -p, --pretend", 26, " ") . "no changes are made\n";
	echo str_pad("  -r, --recursive", 26, " ") . "execute the action on all subdirectories\n";
	echo str_pad("  -w, --supress_warnings", 26, " ") . "don't show compilation warnings\n";
	echo str_pad("  -l, --compilation_level", 26, " ") . "compilation level\n";
	echo str_repeat(" ", 26) . "WHITESPACE_ONLY\n";
	echo str_repeat(" ", 26) . "SIMPLE_OPTIMIZATIONS (default)\n";
	echo str_repeat(" ", 26) . "ADVANCED_OPTIMIZATIONS\n";
	echo str_pad("  -c, --combine", 26, " ") . "if folder is given js files are combined in all.min.js\n";
	echo str_pad("  -h, --help", 26, " ") . "show this\n";

	exit;
}

if ($_SERVER['argc'] == 1)
	help();

require_once 'compiler.php';

$options = array();
$path = false;

$arguments = array_splice($_SERVER['argv'], 1);

foreach ($arguments as $arg) {
	$is_option = preg_match('/^-/', $arg);

	if ($is_option && !$path)
		help();

	switch ($arg) {
		case '--supress_warnings':
		case '-w':
			$options[] = 'supress_warnings';
			break;

		case '--combine':
		case '-c':
			$options[] = 'combine';
			break;

		case '--quiet':
		case '-q':
			$options[] = 'quiet';
			break;

		case '--pretend':
		case '-p':
			$options[] = 'pretend';
			break;

		case '--recursive':
		case '-r':
			$options[] = 'recursive';
			break;

		default:
			$exploded = explode('=', $arg);

			if (count($exploded) == 2)
				switch ($exploded[0]) {
					case '--compilation_level':
					case '-l':
						$option['compilation_level'] = $exploded[1];
						break;

					default:
						help();
				}
			else {
				if ($is_option || $path)
					help();

				$path = $arg;
			}
	}
}

compiler::it($path, $options);

?>