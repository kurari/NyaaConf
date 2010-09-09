<?php
/**
 * Conf
 */
require_once 'object/object.class.php';
require_once 'store/store.class.php';

/**
 * Conf
 */
class NyaaConf extends NyaaObject{

	public static function load( $file, $default = array() ){
		if(file_exists($file) ){
			return self::loadText( file_get_contents( $file ), $default );
		}else{
			return self::loadText(  $file, $default );
		}
	}

	/**
	 * Load Text 
	 *
	 * @var $str
	 * @return parser
	 */
	public static function loadText( $text, $default = array() ){
		if(is_string( $default ) ){
			$c = self::loadText( $default );
			$default = $c->get( );
		}
		$store = new NyaaStore( );
		$store->set( $default );
		$subkey = $mainkey  = "";
		$state  = array( );

		foreach(preg_split('/\n/', $text) as $line){
			$line = trim($line);
			if( empty($line) ) continue;
			if( $line[0] == '-' ) continue;
			if( $line[0] == '[' ){
				$section = substr($line, 1, strpos($line, ']') - 1);

				if(substr($section, strlen($section)-2) == '{}'){
					$real = substr($section, 0, strlen($section)-2);
					$tmp = $store->getRef($real, true);
					if(!is_array($tmp)) $tmp = array();
					$c = count($tmp);
					$section = $real.'.'.$c;
				}

				if($section[0] == '.'){
					$state['sub'][0] = substr($section, 1);
				}else{
					$state['sub'] = array( );
					$state['section'] = $section;
				}
				if(is_array($state['sub']) && count($state['sub']) > 0){
					$state['key']	= $state['section'].".".implode($state['sub']);
				}else{
					$state['key']	= $state['section'];
				}
				continue;
			}
			if( !empty($line) && false !== ($pos = strpos($line, '='))){
				$key = trim(substr($line,0, $pos));
				$val = $store->format(str_replace('%', '%%', trim(substr($line, $pos+1))));

				if(isset($state['key'])) $store->set( sprintf('%s.%s', $state['key'], $key), $val);
				else $store->set( $key, $val);
			}
			continue;
		}
		return $store;
	}
}
?>
