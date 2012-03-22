<?php
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( 'Fl_Exception' );
Fl::loadClass ( 'Fl_Define' );
/**
 * 
 * 词法分析的基础类
 * @author welefen
 *
 */
abstract class Fl_Token extends Fl_Base {
	/**
	 * 
	 * 要分析的文本
	 * @var string
	 */
	public $text = '';
	/**
	 * 
	 * 分析到的位置
	 * @var int
	 */
	public $pos = 0;
	/**
	 * 
	 * token所在的位置
	 * @var int
	 */
	public $tokpos = 0;
	/**
	 * 
	 * 分析到的行
	 * @var int
	 */
	public $line = 0;
	/**
	 * 
	 * token所在的行
	 * @var int
	 */
	public $tokline = 0;
	/**
	 * 
	 * 分析到的列
	 * @var int
	 */
	public $col = 0;
	/**
	 * 
	 * token所在的列
	 * @var int
	 */
	public $tokcol = 0;
	/**
	 * 
	 * 当前token之前换行个数
	 * @var boolean
	 */
	public $newlineBefore = 0;
	/**
	 * 
	 * 当前token之前注释列表
	 * @var array
	 */
	public $commentBefore = array ();
	/**
	 * 
	 * 评论类型及前后定界符
	 * @var array
	 */
	public $commentType = array ();
	/**
	 * 空白字符
	 */
	protected $_whiteSpace = array (" ", "\n", "\t", "\f", "\b", "\x{180e}" );
	/**
	 * 
	 * 是否有模板语法的TOKEN
	 * @var boolean
	 */
	public $hasTplToken = false;
	/**
	 * 是否进行模版语法的检测
	 */
	private $_hasTplTokenCheck = false;
	/**
	 * 
	 * 是否暂停下一个字符的读取，在readWhile方法里使用
	 * @var boolean
	 */
	public $pendingNextChar = false;
	/**
	 * 
	 * 最后一个token的类型
	 * @var string
	 */
	public $lastTokenType = FL_TOKEN_LAST;
	/**
	 * 
	 * 构造函数
	 * @param string $text
	 */
	public function __construct($text = '') {
		parent::__construct ( $text );
		$this->setCommentType ();
	}
	/**
	 * 
	 * 设置评论类型以及左右定界符
	 * @param string $type
	 * @param array $value
	 */
	public function setCommentType($type = '', $value = array()) {
		if ($type) {
			$this->commentType [$type] = $value;
		} else {
			$this->commentType ['multi'] = array ('prefix' => '/*', 'suffix' => '*/' );
			$this->commentType ['inline'] = array ('prefix' => '//', 'suffix' => "\n" );
			$this->commentType ['html'] = array ('prefix' => '<!--', 'suffix' => '-->' );
		}
	}
	/**
	 * 
	 * 检测是否含有模板语法的TOKEN
	 */
	public function checkHasTplToken() {
		if ($this->_hasTplTokenCheck) {
			return $this->hasTplToken;
		}
		$this->_hasTplTokenCheck = true;
		if (! $this->tpl || ! $this->ld || ! $this->rd) {
			return false;
		}
		if ($this->find ( $this->ld ) === false || $this->find ( $this->rd ) === false) {
			return false;
		}
		return $this->hasTplToken = true;
	}
	/**
	 * 
	 * 获取要分析文本的长度,一个中文2个字节
	 */
	public function size() {
		return strlen ( $this->text );
	}
	/**
	 * 
	 * 获取某个位置的字符
	 */
	public function getPosChar($pos) {
		if ($pos >= $this->length) {
			return false;
		}
		return $this->text {$pos};
	}
	/**
	 * 
	 * 获取当前的字符
	 */
	public function getCurrentChar() {
		if ($this->pos >= $this->length) {
			return false;
		}
		return $this->text {$this->pos};
	}
	/**
	 * 
	 * 获取下一个字符.
	 */
	public function getNextChar() {
		if ($this->pos >= $this->length) {
			return false;
		}
		$char = $this->text {$this->pos ++};
		if ($char === FL_NEWLINE) {
			$this->newlineBefore ++;
			$this->line ++;
			$this->col = 0;
		} else {
			$this->col ++;
		}
		return $char;
	}
	/**
	 * 
	 * 循环读取字符
	 * @param function $fn
	 */
	public function readWhile($fn = '') {
		$return = $char = $result = '';
		while ( $this->pos < $this->length ) {
			//是否需要通过getCurrentChar来获取当前的字符需要性能测试
			//$char = $this->getCurrentChar ();
			$char = $this->text {$this->pos};
			$result = $this->$fn ( $char );
			if (! $this->pendingNextChar) {
				$this->getNextChar ();
			} else {
				$this->pendingNextChar = false;
			}
			if ($result === false) {
				return $return . $char;
			} else if ($result === true) {
				return $return;
			}
			$return .= isset ( $result ) ? $result : $char;
		}
		return $return;
	}
	/**
	 * 
	 * 检测是否是空白字符
	 * @param string $char
	 */
	public function isWhiteSpace($char = '') {
		return in_array ( $char, $this->_whiteSpace );
	}
	/**
	 * 
	 * 是否已经结束
	 */
	public function isEof() {
		return $this->pos >= $this->length;
	}
	/**
	 * 
	 * 跳过空白字符
	 */
	public function skipWhiteSpace() {
		$flag = false;
		while ( $this->isWhiteSpace ( $this->getCurrentChar () ) ) {
			$flag = true;
			if ($this->getNextChar () === false) {
				break;
			}
		}
		return $flag;
	}
	/**
	 * 
	 * 开始一个token
	 */
	public function startToken() {
		$this->tokline = $this->line;
		$this->tokcol = $this->col;
		$this->tokpos = $this->pos;
	}
	/**
	 * 
	 * 查找某个字符串
	 * @param string $what
	 * @param boolean $escape 是否支持escape模式
	 * @param boolean sensitive 是否区分大小写
	 */
	public function find($what, $escape = true, $sensitive = true) {
		if ($this->pos >= $this->length) {
			return false;
		}
		if ($sensitive) {
			$pos = strpos ( $this->text, $what, $this->pos );
		} else {
			$pos = stripos ( $this->text, $what, $this->pos );
		}
		if (! $escape || ! $pos) {
			return $pos;
		}
		$len = strlen ( $what );
		while ( $pos ) {
			if ($this->getPosChar ( $pos - 1 ) !== '\\') {
				break;
			}
			if ($sensitive) {
				$pos = strpos ( $this->text, $what, $pos + $len );
			} else {
				$pos = stripos ( $this->text, $what, $pos + $len );
			}
		}
		return $pos;
	}
	/**
	 * 
	 * 以某些字符开头
	 * @param string $what
	 * @param boolean $sensitive
	 * @param boolean $checkFirst 是否检测首个字符
	 */
	public function startWith($what, $sensitive = true, $checkFirst = false) {
		$sub = substr ( $this->text, $this->pos, strlen ( $what ) );
		if ($sensitive) {
			return $what === $sub;
		} else {
			return strtolower ( $sub ) === strtolower ( $what );
		}
	}
	/**
	 * 
	 * 获取注释内容
	 * @param string $type
	 * @param boolean $skipWhitespace 是否跳过空白
	 */
	public function getComment($type = 'multi', $skipWhitespace = true) {
		if (! array_key_exists ( $type, $this->commentType )) {
			$this->throwException ( 'comment type ' . $type . ' not found.' );
		}
		$value = $this->commentType [$type];
		$result = $this->getMatched ( $value ['prefix'], $value ['suffix'] );
		if ($result && $skipWhitespace) {
			$this->skipWhiteSpace ();
		}
		return $result;
	}
	/**
	 * 
	 * 跳过注释，每个类型的语言里跳过注释的实现方式不一样
	 */
	public function skipComment() {
	
	}
	/** 
	 * 获取最后一个token，最后可能含有注释
	 */
	public function getLastToken() {
		if (($this->newlineBefore || count ( $this->commentBefore )) && $this->lastTokenType) {
			$result = $this->getTokenInfo ( $this->lastTokenType );
			$this->commentBefore = array ();
			return $result;
		}
		return false;
	}
	/**
	 * 
	 * 获取向后匹配的字符
	 */
	public function getMatchedChar($char = '"') {
	
	}
	/**
	 * 
	 * 获取当前特定长度的字符串，并更新pos, col, line等信息
	 * @param length 获取指定字符串的长度
	 */
	public function getSubText($length = 0) {
		if ($length === 0 || $this->pos >= $this->length) {
			return false;
		}
		$sub = substr ( $this->text, $this->pos, $length );
		if (strpos ( $sub, FL_NEWLINE ) !== false) {
			$lines = explode ( FL_NEWLINE, $sub );
			$countLine = count ( $lines );
			$this->line += $countLine - 1;
			$this->col = strlen ( $lines [$countLine - 1] );
		} else {
			$this->col += strlen ( $sub );
		}
		$this->pos += strlen ( $sub );
		return $sub;
	}
	/**
	 * 
	 * 获取匹配的字符，当前状态下必须是以start字符串开始
	 * @param string $start 前缀字符
	 * @param string $end 后缀字符
	 * @param boolean $nested 是否支持嵌套
	 * @param boolean $fixed 是否支持容错
	 * @param boolean $sensitive 是否区分大小写
	 */
	public function getMatched($start, $end, $nested = true, $fixed = false, $sensitive = true) {
		if (! $this->startWith ( $start, $sensitive, true )) {
			return false;
		}
		$startPos = $this->pos;
		$startLen = strlen ( $start );
		if ($startLen !== 1) {
			$fixed = false;
		}
		$escape = $startLen === 1 ? true : false;
		//如果start字符串里包含end字符串或者相等，如：匹配2个双引号之间的内容
		if (strpos ( $start, $end ) !== false) {
			$this->getSubText ( $startLen );
		}
		$endPos = $this->find ( $end, $escape, $sensitive );
		if ($endPos === false) {
			return false;
		}
		$sp = $startPos + $startLen;
		$endLen = strlen ( $end );
		if ($nested) {
			$substr = substr ( $this->text, $sp, $endPos - $sp );
			//@TODO 使用explode可能不太安全，因为有可能使用escape的情况
			$nests = explode ( $start, $substr );
			$count = count ( $nests );
			if ($count > 1) {
				while ( $count -- > 1 ) {
					$this->pos = $endPos + $endLen;
					$endPos = $this->find ( $end, $escape, $sensitive );
				}
			}
		}
		$this->pos = $endPos + $endLen;
		//是否自动修复,如：<input value=""" />，这里需要对双引号自动修复
		if ($fixed) {
			while ( $this->find ( $end ) === $this->pos ) {
				$this->pos += $endLen;
				$endPos += $endLen;
			}
		}
		$strlen = $endPos - $startPos + $endLen;
		$this->pos -= $strlen;
		return $this->getSubText ( $strlen );
	}
	/**
	 * 
	 * 获取token的相关信息
	 * @param string or int $type
	 * @param string $value
	 * @param boolean $isComment
	 */
	public function getTokenInfo($type = '', $value = '', $isComment = false) {
		$return = array ('type' => $type );
		$return ['value'] = $value;
		$return ['line'] = $this->tokline;
		$return ['col'] = $this->tokcol;
		$return ['pos'] = $this->tokpos;
		$return ['newlineBefore'] = $this->newlineBefore;
		if (! $isComment) {
			$return ['commentBefore'] = $this->commentBefore;
			$this->commentBefore = array ();
		}
		$this->newlineBefore = 0;
		return $return;
	}
	/**
	 * 
	 * 检测是不是同一个token
	 * @param boolean or array $token
	 * @param string $type
	 * @param string $value
	 */
	public function isToken($token, $type = '', $value = '') {
		if (is_array ( $token )) {
			return $token ['type'] === $type && $token ['value'] === $value;
		}
		return false;
	}
	/**
	 * 
	 * 获取下一个token
	 */
	public function getNextToken() {
		$char = $this->getCurrentChar ();
		if ($char === false) {
			return $this->getLastToken ();
		}
		$this->skipWhiteSpace ();
		$this->skipComment ();
		$this->startToken ();
		$tplToken = $this->getTplToken ();
		if ($tplToken !== false) {
			$this->hasTplToken = true;
			return $this->getTokenInfo ( FL_TOKEN_TPL, $tplToken );
		}
	}
	/**
	 * 
	 * 获取所有的token
	 */
	public function getAllTokens($text = '') {
		if ($text) {
			$this->setText ( $text );
		}
		$result = array ();
		while ( $token = $this->getNextToken () ) {
			$result [] = $token;
		}
		return $result;
	}
	/**
	 * 
	 * @getAllTokens
	 * @param string $text
	 */
	public function run($text = '') {
		return $this->getAllTokens ( $text );
	}
	/**
	 * 
	 * 获取模版语法的Token
	 * @param object $obj
	 */
	public function getTplToken() {
		if (! $this->checkHasTplToken () || ! $this->startWith ( $this->ld )) {
			return false;
		}
		Fl::loadClass ( 'Fl_Tpl' );
		return Fl_Tpl::factory ( $this )->getToken ( $this );
	}
	/**
	 * 
	 * 异常处理
	 */
	public function throwException($msg = '') {
		$ext = ' at line:' . ($this->line + 1) . ', col:' . ($this->col + 1) . ', pos:' . $this->pos;
		parent::throwException ( $msg . $ext, $code );
	}
	/**
	 * 
	 * 获取引号内的内容，支持模版语法
	 * @param string $char
	 * @param boolean $next
	 * @param boolean $useEscape 是否支持转义
	 */
	public function getQuoteText($char = '"', $next = true, $useEscape = false) {
		if ($char !== '"' && $char !== "'") {
			return false;
		}
		$return = $char;
		$find = false;
		$pending = true;
		if ($next) {
			$this->getNextChar ();
		}
		$escape = false;
		while ( ! $this->isEof () ) {
			$tpl = $this->getTplToken ();
			if ($tpl) {
				$return .= $tpl;
			}
			$currentChar = $this->getNextChar ();
			if ($useEscape && $currentChar === "\\") {
				$escape = ! $escape;
			} else if ($currentChar === $char && $this->getCurrentChar () !== $char) {
				if ($escape) {
					$escape = false;
				} else {
					$find = true;
					$return .= $currentChar;
					break;
				}
			} else {
				$escape = false;
			}
			$return .= $currentChar;
		}
		if (! $find) {
			$this->throwException ( __FUNCTION__ . "uncaught end exception with " . $char . " char" );
		}
		$this->pendingNextChar = true;
		return $return;
	}
}