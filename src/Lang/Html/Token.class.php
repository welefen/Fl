<?php
/**
 * 继承自Fl_Token类
 */
Fl::loadClass ( 'Fl_Token' );
Fl::loadClass ( 'Fl_Html_Static' );
/**
 * 
 * HTML词法分析类
 * 支持XML
 * @author welefen
 * @version 1.0 - 2011.11.05
 * @license 
 *
 */
class Fl_Html_Token extends Fl_Token {
	/**
	 * 
	 * 是否检测Tag标签合法，如： <div class="welefen" 这种没有>结束标签就是非法的
	 * @var boolean
	 */
	public $tagChecked = true;
	/**
	 * 
	 * 当前检测的是否是XML
	 * @var boolean
	 */
	public $isXML = false;
	/**
	 * 
	 * html里不能过滤空格字符
	 * @var array
	 */
	protected $_whiteSpace = array ("\n", "\t", "\f" );
	/**
	 * 获取下一个TOKEN
	 * (non-PHPdoc)
	 * @see Fl_Token::getNextToken()
	 */
	public function getNextToken() {
		$token = parent::getNextToken ();
		if ($token || $token === false) {
			return $token;
		}
		$char = $this->getCurrentChar ();
		if ($char === false) {
			return $this->getLastToken ();
		}
		//现在特殊token的配置第一个字符都是<，所有可以优先判断<进行提速
		if ($char === Fl_Html_Static::LEFT) {
			$token = $this->getSpecialToken ();
			if ($token) {
				return $token;
			}
		}
		$next = $this->getPosChar ( $this->pos + 1 );
		//当前字符为<，且下个字符不为<，且下个字符是个合法的tag首字符
		if ($char === Fl_Html_Static::LEFT && $next !== Fl_Html_Static::RIGHT && Fl_Html_Static::isTagFirstChar ( $next )) {
			$token = $this->readWhile ( 'getTagToken' );
			if ($this->tagChecked) {
				$lastChar = $token {strlen ( $token ) - 1};
				//检测tag最有一个字符是否是>
				if ($lastChar !== Fl_Html_Static::RIGHT) {
					$this->throwException ( 'uncaught tag token ' . $token );
				}
			}
			$type = FL_TOKEN_HTML_TAG_START;
			if (strpos ( $token, '</' ) === 0) {
				$type = FL_TOKEN_HTML_TAG_END;
			} elseif (! $this->isXML && strpos ( $token, Fl_Html_Static::XML_PREFIX ) === 0) {
				$this->isXML = true;
				$type = FL_TOKEN_XML_HEAD;
				array_push ( Fl_Html_Static::$specialTokens, array (Fl_Html_Static::CDATA_PREFIX, Fl_Html_Static::CDATA_SUFFIX, FL_TOKEN_XML_CDATA ) );
			}
			return $this->getTokenInfo ( $type, $token );
		}
		$token = $this->readWhile ( 'getTextToken' );
		if (isset ( $token )) {
			$this->newlineBefore -= count ( explode ( FL_NEWLINE, $token ) ) - 1;
			return $this->getTokenInfo ( FL_TOKEN_HTML_TEXT, $token );
		}
		$this->throwException ( 'uncaught char ' . $char );
	}
	/**
	 * 
	 * 获取文本节点
	 */
	public function getTextToken($char) {
		$next = $this->getPosChar ( $this->pos + 1 );
		$renext = $this->getPosChar ( $this->pos + 2 );
		if ($next === false) {
			return false;
		}
		/*
		 * 如果下一个是模板语法的token，则返回
		 * 这里用find有严重的性能问题，故直接使用substr比较
		 */
		if ($this->ld && $this->ld === substr ( $this->text, $this->pos + 1, strlen ( $this->ld ) )) {
			return false;
		}
		/*
		 * 如果下一个字符是“<”, 并且下下个字符不是<
		 * 需要兼容<div>welefen<<</div>这样的情况
		 * Chrome下： 对于<div>welefen< 会被解析成<div>welefen, 最后的<字符会被忽略
		 */
		if ($next === Fl_Html_Static::LEFT && $renext !== Fl_Html_Static::LEFT && Fl_Html_Static::isTagFirstChar ( $renext )) {
			return false;
		}
	}
	/**
	 * 
	 * 获取标签token
	 */
	public function getTagToken($char) {
		if ($return = $this->getQuoteText ( $char, true )) {
			return $return;
		}
		$tpl = $this->getTplToken ();
		if ($tpl) {
			$this->pendingNextChar = true;
			return $tpl;
		}
		if ($char === Fl_Html_Static::RIGHT) {
			return false;
		}
	}
	/**
	 * 跳过注释
	 * (non-PHPdoc)
	 * @see Fl_Token::skipComment()
	 */
	public function skipComment() {
		while ( true ) {
			$flag = false;
			foreach ( Fl_Html_Static::$specialCommentPrefix as $item ) {
				if ($this->startWith ( $item, false )) {
					$flag = true;
					break;
				}
			}
			if ($flag) {
				break;
			}
			$comment = $this->getComment ( 'html' );
			if (! $comment) {
				break;
			}
			$this->commentBefore [] = $comment;
		}
	}
	/**
	 * 
	 * 获取特殊的token, 如: style, script, IE hack等等
	 */
	public function getSpecialToken() {
		foreach ( Fl_Html_Static::$specialTokens as $item ) {
			//对]>和]>-->的特殊处理, IE Hack的时候
			if (count ( $item ) === 4) {
				$pos = $this->find ( $item [1] );
				if ($pos !== false && $pos === $this->find ( $item [3] )) {
					$item [1] = $item [3];
				}
			}
			$result = $this->getMatched ( $item [0], $item [1], false, false, false );
			if ($result) {
				return $this->getTokenInfo ( $item [2], $result );
			}
		}
		return false;
	}
}