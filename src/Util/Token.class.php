<?php
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( 'Fl_Exception' );
Fl::loadClass ( 'Fl_Define' );
Fl::loadClass ( 'Fl_Tpl' );
/**
 * 
 * 词法分析的基础类
 * @author welefen
 *
 */
abstract class Fl_Token extends Fl_Base {

	/**
	 * 
	 * 分析到的位置
	 * @var int
	 */
	protected $pos = 0;

	/**
	 * 
	 * token所在的位置
	 * @var int
	 */
	protected $tokpos = 0;

	/**
	 * 
	 * 分析到的行
	 * @var int
	 */
	protected $line = 0;

	/**
	 * 
	 * token所在的行
	 * @var int
	 */
	protected $tokline = 0;

	/**
	 * 
	 * 分析到的列
	 * @var int
	 */
	protected $col = 0;

	/**
	 * 
	 * token所在的列
	 * @var int
	 */
	protected $tokcol = 0;

	/**
	 * 
	 * 当前token之前换行个数
	 * @var boolean
	 */
	protected $newlineBefore = 0;

	/**
	 * 
	 * 当前token之前注释列表
	 * @var array
	 */
	protected $commentBefore = array ();

	/**
	 * 
	 * 评论类型及前后定界符
	 * @var array
	 */
	protected $commentType = array ();

	/**
	 * 空白字符
	 */
	protected $whiteSpace = array (
		" " => 1, 
		"\n" => 1, 
		"\t" => 1, 
		"\f" => 1, 
		"\b" => 1, 
		"\x{180e}" => 1 
	);

	/**
	 * 
	 * 是否暂停下一个字符的读取，在readWhile方法里使用
	 * @var boolean
	 */
	protected $pendingNextChar = false;

	/**
	 * 
	 * 最后一个token的类型
	 * @var string
	 */
	protected $lastTokenType = FL_TOKEN_LAST;

	/**
	 * 
	 * 设置评论类型以及左右定界符
	 * @param string $type
	 * @param array $value
	 */
	public function init() {
		$this->commentType ['multi'] = array (
			'prefix' => '/*', 
			'suffix' => '*/' 
		);
		$this->commentType ['inline'] = array (
			'prefix' => '//', 
			'suffix' => "\n" 
		);
		$this->commentType ['html'] = array (
			'prefix' => '<!--', 
			'suffix' => '-->' 
		);
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
		return isset ( $this->whiteSpace [$char] );
	}

	/**
	 * 
	 * 跳过空白字符
	 */
	public function skipWhiteSpace() {
		$flag = false;
		while ( $this->isWhiteSpace ( $this->text {$this->pos} ) ) {
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
			if ($this->text {$pos - 1} !== '\\') {
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
	public function startWith($what, $sensitive = true, $len = 0) {
		$sub = substr ( $this->text, $this->pos, $len ? $len : strlen ( $what ) );
		if ($sensitive) {
			return $what == $sub;
		}
		return strtolower ( $sub ) === strtolower ( $what );
	}

	/**
	 * 
	 * 获取注释内容
	 * @param string $type
	 * @param boolean $skipWhitespace 是否跳过空白
	 */
	public function getComment($type = 'multi', $skipWhitespace = true, $returnArray = false) {
		/*if (! isset ( $this->commentType [$type] )) {
			$this->throwException ( 'comment type ' . $type . ' not found.' );
		}*/
		if ($returnArray) {
			$pos = $this->pos;
			$line = $this->line;
			$col = $this->col;
		}
		$value = $this->commentType [$type];
		$result = $this->getMatched ( $value ['prefix'], $value ['suffix'] );
		if ($result) {
			$skipWhitespace && $this->skipWhiteSpace ();
			if ($returnArray) {
				return array (
					'text' => $result, 
					'pos' => $pos, 
					'line' => $line, 
					'col' => $col 
				);
			}
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
		$startLen = strlen ( $start );
		if (! $this->startWith ( $start, $sensitive, $startLen )) {
			return false;
		}
		$startPos = $this->pos;
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
		$result = $this->getSubText ( $strlen );
		return $result;
	}

	/**
	 * 
	 * 获取token的相关信息
	 * @param string or int $type
	 * @param string $value
	 * @param boolean $isComment
	 */
	public function getTokenInfo($type = '', $value = '', $isComment = false) {
		$return = array (
			'type' => $type 
		);
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
		$this->skipWhiteSpace ();
		$this->skipComment ();
		$this->startToken ();
		$tplToken = $this->getTplToken ();
		if ($tplToken !== false) {
			return $this->getTokenInfo ( FL_TOKEN_TPL, $tplToken );
		}
		if ($this->pos >= $this->length) {
			return $this->getLastToken ();
		}
	}

	/**
	 * 
	 * 获取所有的token
	 */
	public function run() {
		$this->checkHasTplToken ();
		$result = array ();
		while ( $token = $this->getNextToken () ) {
			$result [] = $token;
		}
		return $result;
	}

	/**
	 * 
	 * 获取模版语法的Token
	 * @param object $obj
	 */
	public function getTplToken() {
		if ($this->tplTokenHasChecked && ! $this->hasTplToken) {
			return false;
		}
		if (! $this->checkHasTplToken () || ! $this->startWith ( $this->ld )) {
			return false;
		}
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
		while ( $this->pos < $this->length ) {
			$tpl = $this->getTplToken ();
			if ($tpl) {
				$return .= $tpl;
			}
			$currentChar = $this->getNextChar ();
			if ($useEscape && $currentChar === "\\") {
				$escape = ! $escape;
			} else if ($currentChar === $char && $this->text {$this->pos} !== $char) {
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
			$this->throwException ( __FUNCTION__ . " uncaught end exception with " . $char . " char" );
		}
		$this->pendingNextChar = true;
		return $return;
	}
}