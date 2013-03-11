<?php

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Javascript processor
 * @method {string} process() 		process($js_files, $version) 	Process given files
 * @method {string} processFile() 	processFile($file, $opt) 		Process a single file
 * @method {bool} 	checkCache() 	checkCache($file, $hash) 		Check If valid cache available
 * @method {string} getHash() 		getHash($files) 				Generates a files hash
 * @method {string} translate() 	translate($content) 			Translate content
 * @method {string} minify() 		minify($content) 				Minify content
 */
class Init_JS {
	/**
	 * For Init_Timer pruposes
	 * @var integer
	 */
	private static $i = 0;

	const API = 'http://closure-compiler.appspot.com/compile';

	/**
	 * Process given files
	 * @param  array  $js_files 	Files to process
	 * @param  string  $js_path 	JS path, relative to APP_PUBLIC (httpdocs/)
	 * @param  boolean $version  	Return query string hash version of file
	 * @return string 				Packed file name
	 */ 
	public static function process($js_files, $js_path = 'js/', $version = TRUE) {
		if (!is_array($js_files)) throw new Init_Exception(Init_Exception::JS_FILES_ARRAY);
		// Get current language
		$init =& get_instance();
		$lang = Init_Language::getCurrent();
		$file_path = APP_PUBLIC . $js_path;
		// Check If current cache is valid
		$hash = self::getHash($js_files, $file_path, $lang);
		$compiled_name = "init.pack.{$lang}_all.js";

		if (self::checkCache($compiled_name, $file_path, $hash)) 
			return $compiled_name . ($version ? '?' . $hash : '' );
		
		if (TIMERS) $timer = new Init_Timer("JS Compile ".self::$i++, array( 'files'=> $js_files, 'path'=> $js_path ));
		// Compile file
		$final_content = '// HASH:' . $hash . "\n";
		foreach ($js_files as $file => $opt) {
			$cmp_name = self::processFile($file, $file_path, $opt);
			$final_content .= file_get_contents($file_path . $cmp_name) . ';' . "\n";
		}
		file_put_contents($file_path . $compiled_name, $final_content);
		if (TIMERS) $timer->end();

		return $compiled_name . ($version ? '?' . $hash : '' );
	}

	/**
	 * Process a single file, available options:
	 * 		$opt['translate'] 	Searchs translate tags [##]Text[/##] // Default label 'javascript'
	 * 		$opt['minify']		Minify file content
	 * @param  string $file File to process
	 * @param  string $path Complete directory path
	 * @param  array $opt  	Valid bool options $opt['translate'], $opt['minify']
	 * @return string 		Processed file name
	 */
	public function processFile($file, $path, $opt) {
		// Get current language
		$init =& get_instance();
		$lang = Init_Language::getCurrent();

		$hash = self::getHash(array($file=> $opt), $path, $lang);
		$info = pathinfo($file);
		$compiled_name = $info['dirname'] . "/init.pack.{$lang}_" . $info['basename'];

		if (self::checkCache($compiled_name, $path, $hash)) return $compiled_name;

		if (TIMERS) $timer = new Init_Timer("JS Compile ".self::$i++, array( 'file'=> $file, 'path'=> $path ));

		$final_content = '// HASH:' . $hash . "\n";
		$content = file_get_contents($path . $file);
		// Translate file content
		if (!empty($opt['translate'])) $content = self::translate($content);
		// Minify file content
		if (!empty($opt['minify'])) $content = self::minify($content);

		$final_content .= $content;
		// Creates file
		file_put_contents($path . $compiled_name, $final_content);
		if (TIMERS) $timer->end();

		return $compiled_name;
	}

	/**
	 * Check if cache is valid
	 * @param  string $file File to check
	 * @param  string $path Complete file path
	 * @param  string $hash Generated hash
	 * @return bool
	 */
	private static function checkCache($file, $path, $hash) {
		if (file_exists($path . $file)) { 
			$f = fopen($path . $file, "r");
			$line = fgets($f);
			fclose($f);
			// Get stored hash
			$line = trim(substr($line, 2));
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
	private static function getHash($files, $path) {
		$hash = '';
		foreach ($files as $file => $opt) {
			if (!file_exists($path . $file)) continue;
			$time = filemtime($path . $file);
			$hash .= $file . '-' . $time . '-' . !empty($opt['minify']) . '-' . !empty($opt['translate']) . ';';
		}
		return md5($hash);
	}

	/**
	 * Translate content
	 * Searchs translate tags [##]Text[/##]. Default tranlation label 'javascript'
	 * @param  string $content 	javascript to tranalste
	 * @return string 			translated javascript
	 */
	private static function translate($content) {
		$TranslateAdmin = new Init_Translate_Content($content, 'javascript');
		return $TranslateAdmin->translate();
	}

	/**
	 * Minify given content using Google Clousure
	 * @param  string $content Text to minify
	 * @return string
	 */
	private static function minify($content) {
		$post = array(
			'output_format' => 'json',
			'output_info' => 'compiled_code',
			'compilation_level' => JS_COMPILE_LEVEL,
			'js_code' => $content,
		);
		$post = http_build_query($post);
		$ch = curl_init();
		curl_setopt($ch, CURLOPT_URL, self::API);
		curl_setopt($ch, CURLOPT_POST, 1);
		curl_setopt($ch, CURLOPT_POSTFIELDS, $post);
		curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
		$response = curl_exec($ch);
		curl_close($ch);
		$response = json_decode($response, TRUE);
		return $response['compiledCode'];
	}
}