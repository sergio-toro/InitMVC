<?php

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * @method {string} getCurrent() 		getCurrent() 		Get current language
 * @method {array} 	getAvailable() 	getAvailable() 	Get available languages
 * @method {string} negotiateLang() 	negotiateLang()	Get user compatible language
 */
class Init_Language {
    private static $instance;
	private static $available_langs = array();
	private static $lang = FALSE;

	/**
	 * Init languange and save it to $_SESSION
	 */
	public static function init() {
		// Try to get lang from session
		self::$lang = !empty($_SESSION['lang']) ? $_SESSION['lang'] : FALSE;
		// If not language in session
		if (!self::$lang OR _gf('set_lang')) self::$lang = self::negotiateLang();
		if (SESSION) $_SESSION['lang'] = self::$lang;
		if (!headers_sent()) {
			header('Content-Language: ' . self::$lang);
            header('Content-type: text/html; charset=utf-8');
		}
	}

	/**
	 * Get current language
	 * @return string
	 */
	public static function getCurrent() {
		return self::$lang;
	}

	/**
	 * Get available languages defined in config
	 * @return array Available languages
	 */
    public static function getAvailable() {
    	if (!empty(self::$available_langs)) return self::$available_langs;
    	$langs = explode(',', LANGUAGE_AVAILABLE);
    	$langs[] = LANGUAGE_DEFAULT;
		$langs = array_unique($langs);
		
		self::$available_langs = array();
		foreach ($langs as $lang) {
			if (empty($lang)) continue;
			self::$available_langs[] = $lang;
		}
		return self::$available_langs;
    }

    /**
     * Get user compatible language
     * @return string
     */
	private static function negotiateLang() {
        $available = self::getAvailable();
        if (_gf('set_lang') && in_array(_gf('set_lang'), $available))
            return _gf('set_lang');
        $wanted = array();
        if (!empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) 
            $wanted = explode(';', $_SERVER["HTTP_ACCEPT_LANGUAGE"]);
        foreach ($wanted as $req_lang) {
            foreach ($available as $iso) {
                if (strpos($req_lang, $iso) !== FALSE) return $iso;
            }
        }
        return LANGUAGE_DEFAULT;
    }
}