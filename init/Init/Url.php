<?php 

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * URL operations
 * @method {bool} 	isAjax() 			isAjax() 								Check If is an ajax request
 * @method {string} getDomain() 		getDomain() 							Get current domain name
 * @method {string} languageByFolder() 	languageByFolder() Ensure language in folder present
 * @method {void} 	unify() 			unify($url) 							Unify URL request, remove trailing slash
 * @method {string} getUnifiedUrl() 	getUnifiedUrl($parsed) 				Get unified URL
 * @method {void} 	doRedirect() 		doRedirect($parsed, $lang) 			Make URL and do redirect
 * @method {string} cleanLang() 		cleanLang($url_path) 					Clean language from URL
 */
class Init_Url {

	/**
     * Check If is an ajax request
     * @return boolean
     */
    public static function isAjax() {
    	return (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
    		strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest');
    }

    /**
	 * Gets domain name
	 * @return string
	 * @throws Init_Exception If domain name missconfigured
	 */
	public static function getDomain() {
		if (!empty($_SERVER['HTTP_HOST'])) return $_SERVER['HTTP_HOST'];
		if (!empty($_SERVER['SERVER_NAME'])) return $_SERVER['SERVER_NAME'];
		throw new Init_Exception(Init_Exception::URL_DOMAIN_MISSCONFIGURED);
	}

	/**
	 * Ensure language in folder in requests (optional for ajax)
	 * @return string  Clean URL for Init_Router
	 */
	public static function languageByFolder() {
		$current_lang = Init_Language::getCurrent();

		$parsed = parse_url($_SERVER['REQUEST_URI']);
		$url_to_route = $parsed['path'];
		// Search language
		preg_match('#^/([a-z]{2})(/|\?|$)#i', $parsed['path'], $matches);
		// If is ajax request and found language variable, just remove it
		if (self::isAjax()) {
			if (!empty($matches[1])) $parsed['path'] = self::cleanLang($parsed['path']);;
			return $parsed['path'];
		}
		// Check correct language
		if (!empty($matches[1])) {
		 	// Invalid language
		 	if (!in_array($matches[1], Init_Language::getAvailable())) 
		 		throw new Init_Exception(Init_Exception::URL_NOT_FOUND_404);
		 	// Check current language
		 	if ($matches[1]!=$current_lang) {
		 		// Clean invalid language
		 		$parsed['path'] = self::cleanLang($parsed['path']); 
		 		// Do redirect
		 		self::doRedirect($parsed, $current_lang);
		 	}
		}
		// If empty language, redirect to default language stored in session
		if (empty($matches[1])) {
			// Do URL redirect
			self::doRedirect($parsed, $current_lang);
		}
		// Clean language from url
		$url_clean = self::cleanLang($url_to_route);
		return $url_clean;
	}
	
	/**
	 * Unify URL request, remove trailing slash and check domain
	 * @param  string $url URL to unify
	 */
	public static function unify($url) {
		$parsed = parse_url($url);
		$prefered = parse_url(WEB_HOST);
		// Check host
		if (self::getDomain() != $prefered['host']) {
			$redirect_url = self::getUnifiedUrl($parsed);
			header("HTTP/1.1 301 Moved Permanently");
			die(header("Location: {$redirect_url}"));
		}
		// Trailing slash at URL?
		if ($parsed['path']=='/' OR substr($parsed['path'],	-1)!='/') return;
		//
		$redirect_url = self::getUnifiedUrl($parsed);
		header("HTTP/1.1 301 Moved Permanently");
		die(header("Location: {$redirect_url}"));
	}

	/**
	 * Redirect to URL
	 * @param  string $redirectUrl 		Target URL
	 * @param  boolean $prependLanguage If TRUE, prepend language to URL
	 */
	public static function redirect($redirectUrl, $params = array()) {
		
		if (LANGUAGE_BY_FOLDER && strpos($redirectUrl, 'http://')===FALSE
			&& strpos($redirectUrl, 'https://')===FALSE) 
			$redirectUrl = '/' . Init_Language::getCurrent() . '/' . $redirectUrl;
		$redirectUrl = str_replace('//', '/', $redirectUrl);
		if (!empty($params)) {
			$params = http_build_query($params);
			if (strpos($redirectUrl, '?')!==FALSE) $redirectUrl .= '&' . $params;
			else $redirectUrl .= '?' . $params;
		}
		header("Location: {$redirectUrl}");
		die();
	}

	/**
	 * Make public URL
	 * @param  string $url URL path (without language)
	 * @return string
	 */
	public static function makeUrl($url, $params = array()) {
		if (LANGUAGE_BY_FOLDER && strpos($redirectUrl, 'http://')===FALSE
			&& strpos($redirectUrl, 'https://')===FALSE) 
			$finalUrl = '/' . Init_Language::getCurrent() . '/' . $url;
		if (!empty($params)) {
			$params = http_build_query($params);
			if (strpos($finalUrl, '?')!==FALSE) $finalUrl .= urlencode('&') . $params;
			else $finalUrl .= '?' . $params;
		}
		return WEB_HOST . str_replace('//', '/', $finalUrl);
	}

	/**
	 * Get unified url (WEB_HOST . $path . $query_strings)
	 * @param  array $parsed parse_url result
	 * @return string
	 */
	private static function getUnifiedUrl($parsed) {
		if ($parsed['path']!='/' && substr($parsed['path'],	-1)=='/') 
			$parsed['path'] = substr($parsed['path'], 0, -1);
		// Remove trailing slash
		$redirect_url = WEB_HOST . $parsed['path'];
		if (!empty($parsed['query'])) $redirect_url .= '?'. $parsed['query']; 
		return $redirect_url;
	}

	/**
	 * Makes URL and perform redirect
	 * @param  array 	$parsed 	parse_url array result 
	 * @param  string 	$lang   	Language to redirect
	 */
	private static function doRedirect($parsed, $lang) {
		$redirect_url = WEB_HOST . '/' . $lang . $parsed['path'];
		// If URL has a trailing slash, removes it
		if (substr($redirect_url, -1)=='/') $redirect_url = substr($redirect_url, 0, -1);
		// If found query params
		if (!empty($parsed['query'])) $redirect_url .= '?'. $parsed['query'];
		// Do redirect
		header("HTTP/1.1 301 Moved Permanently");
		die(header("Location: {$redirect_url}"));
	}

	/**
	 * Clean language from URL
	 * @param  string $url_path URL to clean
	 * @return string           Clean URL
	 */
	private static function cleanLang($url_path) {
		$url_clean = substr($url_path, 3);
		if (empty($url_clean)) $url_clean = '/';
		return $url_clean;
	}
}