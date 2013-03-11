<?php 

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Css processor
 * @method {string} process() 		process($js_files, $version) 	Process given files
 * @method {string} process_file() 	process_file($file, $opt) 		Process a single file
 * @method {bool} 	check_cache() 	check_cache($file, $hash) 		Check If valid cache available
 * @method {string} get_hash() 		get_hash($files) 				Generates a files hash
 */
class Init_CSS {
	/**
	 * For Init_Timer count proposes
	 * @var integer
	 */
	private static $i = 0;

	/**
	 * Process given files
	 * @param  array  $css_files 	Files to process
	 * @param  string  $css_path 	Css path, relative to APP_PUBLIC (httpdocs/)
	 * @param  boolean $version  	Return query string hash version of file
	 * @return string 				Packed file name
	 */
	public static function process($css_files, $css_path = 'css/', $version = TRUE) {
		if (!is_array($css_files)) throw new Init_Exception(Init_Exception::CSS_FILES_ARRAY);


		// Define file_path
		$file_path = APP_PUBLIC . $css_path;

		// Check If current cache is valid
		$hash = self::get_hash($css_files, $file_path);
		$compiled_name = "init.pack.all.css";
		if (self::check_cache($compiled_name, $file_path,  $hash)) return $compiled_name . ($version ? '?' . $hash : '' );
		
		if (TIMERS) $timer = new Init_Timer("CSS Compile ".self::$i++, array( 'files'=> $css_files, 'path'=> $css_path ));
		// Compile file
		$final_content = '/* HASH:' . $hash . ' */' . "\n";
		foreach ($css_files as $file => $opt) {
			$cmp_name = self::process_file($file, $file_path, $opt);
			$final_content .= file_get_contents($file_path . $cmp_name) .  "\n";
		}
		file_put_contents($file_path . $compiled_name, $final_content);
		if (TIMERS) $timer->end();

		return $compiled_name . ($version ? '?' . $hash : '' );
	}

	/**
	 * Process a single file, available options:
	 * 		$opt['minify']		Minify file content
	 * @param  string $file File to process
	 * @param  string $path Complete directory path
	 * @param  array $opt  	Valid bool option $opt['minify']
	 * @return string 		Processed file name
	 */
	public function process_file($file, $path, $opt) {
		if (TIMERS) $timer = new Init_Timer("CSS Compile ".self::$i++, array( 'file'=> $file, 'path'=> $path ));
		// Load CssCrush library
		require_once INIT_ROOT . 'external/CssCrush/CssCrush.php';
		$options = array(
			'versioning' => FALSE,
			'minify' => !empty($opt['minify']),
		);

		$min_file = CssCrush::file($path . $file, $options);
		$css_path = str_replace(substr(APP_PUBLIC, 0, -1), '', $path);

		$pos = strpos($min_file, $css_path);
		if ($pos!==FALSE) $min_file = substr_replace($min_file, '', $pos, strlen($css_path));
		if (TIMERS) $timer->end();
		
		return $min_file;
	}

	/**
	 * Check if cache is valid
	 * @param  string $file File to check
	 * @param  string $path Complete file path
	 * @param  string $hash Generated hash
	 * @return bool
	 */
	private static function check_cache($file, $path, $hash) {
		if (file_exists($path . $file)) { 
			$f = fopen($path . $file, "r");
			$line = fgets($f);
			fclose($f);
			// Get stored hash
			$line = trim(substr(substr(trim($line), 2), 0, -2));
			$line = explode(':', $line);
			// Check stored hash
			if (!empty($line[1]) && $line[1] == $hash) return TRUE;
		}
		return FALSE;
	}

	/**
	 * Generates a files hash 
	 * @param  array $files 	Files to hash
	 * @param  string $path 	Complete directory path
	 * @return string
	 */
	private static function get_hash($files, $path) {
		$hash = '';
		foreach ($files as $file => $opt) {
			$time = filemtime($path . $file);
			$hash .= $file . '-' . $time . '-' . !empty($opt['minify']) . ';';
		}
		return md5($hash);
	}

}