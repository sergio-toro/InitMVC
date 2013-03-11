<?php

/**
 * @author Sergio Toro <yiti007@gmail.com>
 * @method {string} getCurrent()        getCurrent()    Get current country
 * @method {string} negotiateCountry()  negotiateCountry() Get user country
 */
class Init_Country {

    private static $country;

    /**
     * Language contructor, use session to store language
     */
    public static function init() {
        // Try to get lang from session
        self::$country = !empty($_SESSION['country']) ? $_SESSION['country'] : FALSE;
        // If not language in session
        if (!self::$country OR _gf('set_country')) 
            self::$country = self::negotiateCountry();
        if (SESSION) $_SESSION['country'] = self::$country;
    }

    /**
     * Get current language
     * @return string
     */
    public static function getCurrent() {
        return self::$country;
    }


    /**
     * Get user compatible language
     * @return string
     */
    private static function negotiateCountry() {
        if (!empty($_SERVER['HTTP_CF_IPCOUNTRY'])) return $_SERVER['HTTP_CF_IPCOUNTRY'];
        $country = _gf('set_country');
        if ($country && strlen($country)==2) return strtoupper($country);
        return COUNTRY_DEFAULT;
    }
}