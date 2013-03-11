<?php
/**
 * @author Sergio Toro <yiti007@gmail.com>
 * Control translations
 * @method {mixed} 	translate() 				translate($text, $from_lang, $to_lang) 	Translate given text using google translate
 * @method {array} 	get_by_id() 				get_by_id($id) 							Gets translation by id
 * @method {string} get_translation() 			get_translation($text, $label) 			Translate given text and save it to MongoDB
 * @method {string} create_id() 				create_id($text, $label) 				Creates hash wich identifies text
 * @method {array} 	update_text_translation() 	update_text_translation($text, $label) 	Try to translate text to available languages
 * @method {array} 	new_text_translation() 		new_text_translation($text, $label) 	Creates new translation, and try to translate to available languages
 */
class Init_Translate extends Init_Model{
	const COLLECTION = 'init.Translate';
	const API = 'https://www.googleapis.com/language/translate/v2';
	const KEY = TRANSLATE_GOOGLE_SERVER_KEY;


	private $lang;

	public function __construct() {
		parent::__construct();
		$this->lang = Init_Language::getCurrent();
	}

	/**
	 * Translate given text using google translate
	 * @param  string $text      Text to translate
	 * @param  string $from_lang ISO2 source language
	 * @param  string $to_lang   ISO2 target language
	 * @return mixed             If FALSE, error doing tranlation, else returns translated text
	 */	
	public function translate($text, $from_lang, $to_lang) {
		if ($from_lang == $to_lang) return $text;
		$params = array(
			'key' => self::KEY,
			'q' => $text,
			'source' => $from_lang,
			'target' => $to_lang
		);
		$query = self::API . '?' . http_build_query($params);
		$response = file_get_contents($query);
		$data = json_decode($response, TRUE);
		if (!empty($data['data']['translations'][0]['translatedText']))
			return $data['data']['translations'][0]['translatedText'];
		return FALSE;
	}

	/**
	 * Gets translation by id
	 * @param  string $id Text hash identifier
	 * @return array
	 */
	public function get_by_id($id) {
		$where = array( '_id'=> (string) $id);
		return $this->mdb->findOne(self::COLLECTION, $where);

	}

	/**
	 * Translate given text and save it to MongoDB, text is identified by md5($label-md5($text))
	 * @param  string $text  Text to translate
	 * @param  string $label Text group label
	 * @return string
	 */
	public function get_translation($text, $label) {
		$data = $this->get_by_id($this->create_id($text, $label));

		// Most common case, translation found, then return
		if (!empty($data['translation'][$this->lang]['text'])) 
			return $data['translation'][$this->lang]['text'];

		// If translation doesn't exists, creates translation
		if (empty($data)) $data = $this->new_text_translation($text, $label, $this->lang);
		// Updates current translation, maybe new language
		if (empty($data['translation'][$this->lang]['text']) && TRANSLATE_GOOGLE) 
			$data = $this->update_text_translation($text, $label, $this->lang);
		// Translation found after create/update?
		if (!empty($data['translation'][$this->lang]['text'])) 
			return $data['translation'][$this->lang]['text'];			
		// Translation not found, maybe TRANSLATE_GOOGLE = FALSE
		 
		// If fallback to default text...
		if (empty($data['translation'][$this->lang]['text']) && TRANSLATE_FALLBACK_DEFAULT) 
			return $data['translation']['original']['text'];

		return ''; // No translation, no fallback
	}

	/**
	 * Creates text identifier using md5
	 * @param  string $text  Text
	 * @param  string $label Label, used to namespace text
	 * @return string        Hash identifier
	 */
	public function create_id($text, $label) {
		return md5($label . '-' . md5($text)); 
	}

	/**
	 * Updates existing translation, If TRANSLATE_GOOGLE, Try to translate text to available languages
	 * @param  string $text  Source text
	 * @param  string $label Text group label
	 * @return array
	 */
	public function update_text_translation($text, $label) {
		$_id = $this->create_id($text, $label);
		$data = $this->get_by_id($_id);
		if (TRANSLATE_GOOGLE) {
			$count = count($data['translation']);
			foreach (Init_Language::getAvailable() as $lang) {
				if (!empty($data['translation'][$lang])) continue;
				$translation = $this->translate($text, LANGUAGE_DEFAULT, $lang);
				if ($translation === FALSE) continue;
				$data['translation'][$lang] = array(
					'text' => $translation,
					'auto' => TRUE
				);
			}
			if ($count<count($data['translation'])) {
				$where = array( '_id'=> $_id );
				$update = array(
					'$set' => array(
						'translation' => $data['translation'],
						'date_modified' => new MongoDate()
					)
				);
				$this->mdb->update(self::COLLECTION, $where, $update);
			}
		}
		return $data;
	}

	/**
	 * Create new translation, If TRANSLATE_GOOGLE, try to translate text to AVAILABLE_LANGUAGES
	 * @param  string $text  Source text
	 * @param  string $label Text group label
	 * @return array
	 */
	public function new_text_translation($text, $label) {

		$data = array(
			'_id' => $this->create_id($text, $label),
			'label' => (string) $label,
			'text_hash' => md5($text),
			'date_created' => new MongoDate(),
			'date_modified' => new MongoDate(),
			'translation' => array(
				'original' => array( 'text'=> $text, 'auto'=> FALSE)
			)
		);
		if (TRANSLATE_GOOGLE) { // Translates content to all available languages
			foreach (Init_Language::getAvailable() as $lang) {
				$translation = $this->translate($text, LANGUAGE_DEFAULT, $lang);
				if ($translation === FALSE) continue;
				$data['translation'][$lang] = array(
					'text' => $translation,
					'auto' => TRUE
				);
			}
		}
		$this->mdb->insert(self::COLLECTION, $data);
		return $data;
	}
}