<?php

Class compiler {

	// google url to compile the code
	const url = 'http://closure-compiler.appspot.com/compile';

	static function it($path, $options = array()) {
		// options
		$quiet = in_array('quiet', $options);
		$pretend = in_array('pretend', $options);
		$recursive = in_array('recursive', $options);
		$join = in_array('join', $options);
		$supress_warnings = in_array('supress_warnings', $options);
		$compilation_level = in_array('compilation_level', $options) ? $options['compilation_level'] : 'SIMPLE_OPTIMIZATIONS';

		// create the curl object
		$curl = curl_init(self::url);

		// set default options
		curl_setopt_array($curl, array(
			CURLOPT_RETURNTRANSFER => true,
			CURLOPT_POST => true
		));

		// is the path is a folder, we get all javascript files on these folder
		if (is_dir($path))
			self::folder($curl, $path, $quiet, $pretend, $recursive, $join, $supress_warnings, $compilation_level);
		else
			self::file($curl, $path, $quiet, $pretend, $supress_warnings, $compilation_level);

		// close curl to free memory
		curl_close($curl);
	}

	private static function folder($curl, $folder_path, $quiet, $pretend, $recursive, $join, $supress_warnings, $compilation_level) {
		// loop through all files on the folder to get javascript files
		$it = new DirectoryIterator($folder_path);

		foreach ($it as $file)
			// ignore the dot file
			if (!$file->isDot()) {
				$path = $file->getPathname();

				// if it's a folder, scan it too
				if ($file->isDir()) {
					if ($recursive)
						self::folder($curl, $path, $quiet, $pretend, $recursive, $join, $supress_warnings, $compilation_level);
				}
				elseif (preg_match('/(?<!\.min)\.js$/i', $path))
					$files[] = $path;
			}

		if (count($files) > 0)
			// combine the files before sending the code to google
			if ($join) {
				foreach ($files as $path)
					$code[] = file_get_contents($path);

				$code = implode("\n", $code);

				$path = $folder_path . DIRECTORY_SEPARATOR . 'all.js';

				return self::code($curl, $path, $code, $quiet, $pretend, $supress_warnings, $compilation_level);
			}
			// send every file code separately
			else
				foreach ($files as $path) {
					$code = file_get_contents($path);

					self::code($curl, $path, $code, $quiet, $pretend, $supress_warnings, $compilation_level);

					if (!$quiet)
						echo "\n";
				}
	}

	private static function code($curl, $path, $code, $quiet, $pretend, $supress_warnings, $compilation_level) {
		$post_data = 'js_code=' . urlencode($code) . '&compilation_level=' . $compilation_level . '&output_format=json&output_info=compiled_code&output_info=warnings&output_info=errors&output_info=statistics&warning_level=verbose';

		curl_setopt($curl, CURLOPT_POSTFIELDS, $post_data);

		if (!$quiet)
			echo "  compiling " . $path . "\n";

		// call the server app
		$response = curl_exec($curl);

		// if no response from the server
		if ($response === false) {
			if (!$quiet)
				echo "  error: the server has gone\n";
		}
		// server respond
		else {
			// remove conflict chars on json_decode
			$response = preg_replace('/\t/', ' ', $response);

			// decode the json response
			$data = json_decode($response);


			// if there is some error
			if (!empty($data->serverErrors)) {
				if (!$quiet)
					echo "  error: " . strtolower($data->serverErrors[0]->error) . "\n";
			}
			// look for errors on the file
			elseif (!empty($data->errors)) {
				if (!$quiet) {
					echo "  error: syntax error\n";

					foreach ($data->errors as $error) {
						echo "    " . strtolower($error->error) . ", line " . $error->lineno . ", char " . $error->charno . "\n";

						echo "    " . trim($error->line) . "\n\n";
					}
				}
			}
			// look for errors on the file
			elseif (!$supress_warnings && !empty($data->warnings)) {
				if (!$quiet) {
					echo "  error: syntax warning\n";

					foreach ($data->warnings as $warning) {
						echo "    " . strtolower($warning->warning) . ", line " . $warning->lineno . ", char " . $warning->charno . "\n";

						echo "    " . trim($warning->line) . "\n\n";
					}
				}
			}
			// if optimized size is larget than the original
			elseif ($data->statistics->originalSize < $data->statistics->compressedSize) {
				if (!$quiet)
					echo "  error: got larger\n";
			}
			// if size are equal
			elseif ($data->statistics->originalSize == $data->statistics->compressedSize) {
				if (!$quiet)
					echo "  cannot be optimized further";
			}
			else {
				if (!$quiet)
					echo str_pad("  " . $data->statistics->originalSize . " -> " . $data->statistics->compressedSize . " (" . $data->statistics->compressedGzipSize . " gzip)", 26, " ") . " = " . round($data->statistics->compressedSize * 100 / $data->statistics->originalSize) . "% (" . round($data->statistics->compressedGzipSize * 100 / $data->statistics->originalSize) . "% gzip)\n";

				if ($pretend)
					return true;

				$content = $data->compiledCode;

				$path = substr($path, 0, -2) . 'min.js';

				return file_put_contents($path, $content);
			}
		}
	}

}

?>