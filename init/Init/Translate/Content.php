<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Translates given content, searchs tags and translates
 * 		[##]Text[/##] 			Text to translate (Required)
 * 		[##][%Label%]Text[/##] 	Label to namespace translation (Optional)
 * @method {string} translate() 			translate() 					Searchs for translations tags and perform translation
 * @method {string} translate_callback() 	translate_callback($matches) 	Search translation using Init_Translate
 */
class Init_Translate_Content extends Init_Model{

	public $mdb;
	public $lang;
	protected $translator;
	//
	private $content;
	private $default_label;

	/**
	 * Translate content contruct
	 * @param string $content       Content to translate
	 * @param string $default_label Namespace translations
	 */
	public function __construct($content, $default_label){
		parent::__construct();

		$this->translator = new Init_Translate();
		$this->content =& $content;
		$this->default_label =& $default_label;
	}

	/**
	 * Searchs for translations tags and perform translation
	 * @return string Translated text
	 */
	public function translate() {
		return preg_replace_callback('-\[##\](\[\%(.*?)\%\])?(.+?)\[/##\]-s', array(&$this, 'translate_callback'), $this->content);
	}

	/**
	 * Callback for translate function, search translation using Init_Translate
	 * @param  array $matches  $matches[2]=> Given label, $matches[3] => clean text
	 * @return string          Translated text
	 */
	protected function translate_callback(&$matches) {
		$label 	= !empty($matches[2]) ? $matches[2] : $this->default_label;
		return $this->translator->get_translation($matches[3], $label);
	}

}