<?php
/**
 * 
 * XTemplate
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Tpl_Interface' );
Fl::loadClass ( 'Fl_Static' );
class Fl_Tpl_XTemplate implements Fl_Tpl_Interface {

	/**
	 * 
	 * 输出模版语法的正则
	 * @var RegexIterator
	 */
	public static $tplOutputPattern = '/^\s*{LD}\s*\\$[\w\\$\_].*?/';

	/**
	 * 
	 * 关键字
	 * @var array
	 */
	protected $keywords = array (
		'debugger', 
		'each', 
		'extend', 
		'include', 
		'macro', 
		'parse', 
		'range', 
		'set', 
		'with', 
		'if', 
		'elseif', 
		'else', 
		'block' 
	);

	/**
	 * 
	 * xss temp
	 * @var array
	 */
	protected $xssTmp = array ();

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
		$tplText = $instance->getTplText ( $tpl, true );
		$reg = "/^[A-Za-z\.]+/";
		if (! preg_match ( $reg, $tplText )) {
			return false;
		}
		foreach ( $this->keywords as $keyword ) {
			if (strpos ( $tplText, $keyword ) === 0) {
				return false;
			}
		}
		return true;
	}

	/**
	 * 
	 * check xss or auto fixed
	 * @param string $value
	 * @param string $type
	 * @param object $instance
	 */
	public function xss($token, $type, $instance = null) {
		if ($token === true) {
			$value = Fl_Static::fixPregReplaceQuote ( trim ( $instance ) );
			$type = Fl_Static::fixPregReplaceQuote ( $type );
			if (! $this->checkHasOutput ( $type, $this->xssTmp ['instance'] ) || $this->isSafeVar ( $value )) {
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
			if (strpos ( $value, $typeModifier ) === 0) {
				return $type;
			}
			$token = $this->xssTmp ['token'];
			$message = '`' . $type . '` must be use ' . $typeModifier . ' to escape at line:' . $token ['line'] . ', col:' . $token ['col'];
			$this->xssTmp ['log'] [] = $message;
			return $this->xssTmp ['instance']->ld . $typeModifier . '(' . $value . ')' . $this->xssTmp ['instance']->rd;
		} else {
			$this->xssTmp = array (
				"token" => $token, 
				"type" => $type, 
				"instance" => $instance, 
				'log' => array () 
			);
			$value = $token ['value'];
			$tplPattern = "/(" . preg_quote ( $instance->ld, "/" ) . "(.*?)" . preg_quote ( $instance->rd, "/" ) . ")/e";
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
			//正则
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
		if (self::isCallbackType ( $value )) {
			return 'callback';
		}
		return $this->xssTmp ['type'];
	}

	/**
	 * 
	 * is callback type
	 * @param string $value
	 */
	public static function isCallbackType($value) {
		return $value === '$smarty.get.callback' || $value === '$smarty.post.callback';
	}

	/**
	 * 
	 * 压缩当前的模板Token
	 */
	public function compress($tpl, Fl_Base &$instance) {
		return $tpl;
	}
}