<?php
/**
 * 
 * PHP原生的模版
 * @license MIT
 * @author welefen
 * @copyright 2011 - 2012
 * @version 1.0 - 2012.02.25
 *
 */
Fl::loadClass ( 'Fl_Tpl_Interface' );
Fl::loadClass ( 'Fl_Static' );
class Fl_Tpl_PHP implements Fl_Tpl_Interface {

	/**
	 * 
	 * xss temp
	 * @var array
	 */
	protected $xssTmp = array ();

	/**
	 * 
	 * default safe vars
	 * @var array
	 */
	public static $defaultSafeVars = array ();

	/**
	 * 
	 * 获取模版语法的Token
	 * @param object $instance
	 */
	public function getToken(Fl_Token &$instance) {
		return $instance->getMatched ( $instance->ld, $instance->rd, true );
	}

	/**
	 * 
	 * 检测当前的tpl是否会输出
	 * @param string $tpl
	 */
	public function checkHasOutput($tpl, Fl_Base &$instance) {
		return true;
	}

	/**
	 * 
	 * 压缩当前的模板Token
	 */
	public function compress($tpl, Fl_Base &$instance) {
		return $tpl;
	}

	public function xss($token, $type, $instance = null) {
		if ($token === true) {
			$value = Fl_Static::fixPregReplaceQuote ( trim ( $instance ) );
			$type = Fl_Static::fixPregReplaceQuote ( $type );
			$tokens = token_get_all ( $type );
			$result = array ();
			$tmp = array ();
			$flag = false;
			foreach ( $tokens as $token ) {
				$tokenName = '';
				if (is_array ( $token )) {
					$tokenValue = $token [1];
				} else {
					$tokenValue = $token;
				}
				if (is_array ( $token )) {
					$tokenName = $token [0];
				}
				if ($tokenValue == ';' || $tokenName == T_CLOSE_TAG) {
					$string = trim ( join ( " ", $tmp ) );
					if (empty ( $string )) {
						$result [] = $tokenValue;
						continue;
					}
					if ($this->isSafeVar ( $string )) {
						return $type;
					}
					$escapeType = $this->getXssType ( $value );
					$escapeLevels = $this->xssTmp ['instance']->escapeLevel;
					$level = $escapeLevels [$escapeType];
					foreach ( $escapeLevels as $name => $l ) {
						if ($l > $level) {
							$typeModifier = $this->xssTmp ['instance']->options [$name];
							if (strpos ( $value, $typeModifier ) !== false) {
								return $type;
							}
						}
					}
					$typeModifier = $this->xssTmp ['instance']->options [$escapeType];
					if (strpos ( $value, $typeModifier ) !== false) {
						return $type;
					}
					$tmpToken = $this->xssTmp ['token'];
					$message = '`' . $type . '` must be use ' . $typeModifier . ' to escape at line:' . $tmpToken ['line'] . ', col:' . $token ['col'];
					$this->xssTmp ['log'] [] = $message;
					$string = $typeModifier . '(' . $string . ')';
					$result [] = $string;
					$result [] = $tokenValue;
					$tmp = array ();
					$flag = false;
					continue;
				}
				if ($tokenName == T_ECHO) {
					$result [] = $tokenValue;
					$flag = true;
					continue;
				}
				if ($flag) {
					$tmp [] = $tokenValue;
				} else {
					$result [] = $tokenValue;
				}
			}
			return join ( " ", $result );
		} else {
			$this->xssTmp = array (
				"token" => $token, 
				"type" => $type, 
				"instance" => $instance, 
				'log' => array () 
			);
			$value = $token ['value'];
			$tplPattern = "/(" . preg_quote ( $instance->ld, "/" ) . "(.*?)" . preg_quote ( $instance->rd, "/" ) . ")/ise";
			$value = preg_replace ( $tplPattern, "self::xss(true, '\\1', '\\2')", $value );
			$log = $this->xssTmp ['log'];
			$this->xssTmp = array ();
			return array (
				"value" => $value, 
				"log" => $log 
			);
		}
	}

	/**
	 * 
	 * is safe var
	 * @param string $value
	 */
	private function isSafeVar($value) {
		$value = trim ( $value );
		foreach ( self::$defaultSafeVars as $str ) {
			if (strpos ( $value, $str ) !== false) {
				return true;
			}
		}
		$noescape = $this->xssTmp ['instance']->options ['noescape'];
		if ($noescape && strpos ( $value, $noescape ) !== false) {
			return true;
		}
		$safeVars = $this->xssTmp ['instance']->safe_vars;
		$noPrefix = substr ( $value, 1 );
		foreach ( $safeVars as $item ) {
			if (empty ( $item )) {
				continue;
			}
			if (substr ( $item, 0, 1 ) === '/' && strpos ( $item, "/", 1 ) !== false) {
				try {
					if (preg_match ( $item, $value ) || preg_match ( $item, $noPrefix )) {
						return true;
					}
				} catch ( Exception $e ) {
				}
			}
			if ($item === $value || $item === $noPrefix) {
				return true;
			}
		}
		return false;
	}

	/**
	 * 
	 * get xss type
	 * @param string $value
	 */
	private function getXssType($value) {
		$defineFn = $this->xssTmp ['instance']->identifyFn;
		if ($defineFn && function_exists ( $defineFn )) {
			$type = call_user_func ( $defineFn, $value );
			if (isset ( $this->xssTmp ['instance']->options [$type] )) {
				return $type;
			}
		}
		return $this->xssTmp ['type'];
	}
}