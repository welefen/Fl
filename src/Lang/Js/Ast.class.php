<?php
/**
 * 
 * js ast class
 * @author welefen
 *
 */
Fl::loadClass ( 'Fl_Base' );
Fl::loadClass ( 'Fl_Js_Static' );
class Fl_Js_Ast extends Fl_Base {

	/**
	 * 
	 * 构建的语法树是否要带上token的相关信息
	 * @var boolean
	 */
	public $embedToken = false;

	/**
	 * 
	 * token类实例
	 * @var object
	 */
	protected $tokenInstance = null;

	/**
	 * 
	 * 上一个token
	 * @var array or false
	 */
	protected $prevToken = false;

	/**
	 * 
	 * peek token
	 * @var array or false
	 */
	protected $peekToken = false;

	/**
	 * 
	 * 当前的token
	 * @var array or false
	 */
	protected $currentToken = false;

	/**
	 * 
	 * 函数深度
	 * @var number
	 */
	protected $funtionDepth = 0;

	/**
	 * 
	 * 循环深度
	 * @var number
	 */
	protected $loopDepth = 0;

	/**
	 * 
	 * in directives
	 * @var boolean
	 */
	protected $inDirectives = true;

	/**
	 * label: for ... 
	 * @var array
	 */
	protected $labels = array ();

	/**
	 * 
	 * 执行
	 * @param string $text
	 * @param boolean $embedToken 语法树是否要带上token的信息，如：tokline, tokcol, tokpos, newlineBefore等信息
	 */
	public function run() {
		$this->tokenInstance = $this->getInstance ( 'Fl_Js_Token' );
		$this->getNextToken ();
		return $this->statementAst ();
	}

	/**
	 * statement处理
	 */
	public function statementAst() {
		if ($this->isToken ( FL_TOKEN_JS_OPERATOR, "/" ) || $this->isToken ( FL_TOKEN_JS_OPERATOR, "/=" )) {
			//$this->peekToken = false;
		//$this->currentToken = $this->getNextToken();
		}
		switch ($this->currentToken ['type']) {
			case FL_TOKEN_JS_STRING :
				$dir = $this->inDirectives;
				$stat = $this->simpleStatement ();
				if ($dir && $stat [1] [0] === FL_TOKEN_JS_STRING && ! $this->isToken ( FL_TOKEN_JS_PUNC, "," )) {
					return array (
						"directive", 
						$stat [1] [1] 
					);
				}
				return $stat;
			case FL_TOKEN_JS_NUMBER :
			case FL_TOKEN_JS_REGEXP :
			case FL_TOKEN_JS_OPERATOR :
			case FL_TOKEN_JS_ATOM :
				return $this->simpleStatement ();
			case FL_TOKEN_JS_NAME :
				if ($this->isToken ( FL_TOKEN_JS_PUNC, ":", $this->getPeekToken () )) {
					$value = $this->currentToken ['value'];
					$this->getNextToken ();
					$this->getNextToken ();
					return $this->labeledStatement ( $value );
				}
				return $this->simpleStatement ();
			case FL_TOKEN_JS_PUNC :
				switch ($this->currentToken ['value']) {
					case "{" :
						return array (
							"block", 
							$this->getBlockAst () 
						);
					case "[" :
					case "(" :
						return $this->simpleStatement ();
					case ";" :
						$this->getNextToken ();
						return array (
							"block" 
						);
					default :
						$this->unexpectTokenError ();
				}
			case FL_TOKEN_JS_KEYWORD :
				$value = $this->currentToken ['value'];
				$this->getNextToken ();
				$keywordMethod = $value . "Statement";
				if (method_exists ( $this, $keywordMethod )) {
					return $this->$keywordMethod ();
				}
				return $this->unexpectTokenError ();
			default :
				$this->unexpectTokenError ();
		}
	}

	/**
	 * 
	 * label statement
	 */
	public function labeledStatement($label) {
		$this->labels [] = $label;
		$start = $this->currentToken;
		$stat = $this->statementAst ();
		if (! Fl_Js_Static::isLabelStatement ( $stat [0] )) {
			$this->unexpectTokenError ( $start );
		}
		array_pop ( $this->labels );
		return array (
			"label", 
			$label, 
			$stat 
		);
	}

	/**
	 * 
	 * simple statement
	 */
	public function simpleStatement() {
		$expression = $this->expressionAst ();
		$this->isSemicolon ();
		return array (
			"stat", 
			$expression 
		);
	}

	/**
	 * 
	 * break的处理
	 */
	public function breakStatement() {
		return $this->breakCont ( "break" );
	}

	/**
	 * 
	 * continue的处理
	 */
	public function continueStatement() {
		return $this->breakCont ( "continue" );
	}

	/**
	 * 
	 * debugger的处理
	 */
	public function debuggerStatement() {
		$this->isSemicolon ();
		return array (
			"debugger" 
		);
	}

	/**
	 * 
	 * do while循环的处理
	 */
	public function doStatement() {
		$body = $this->inLoop ( 'statementAst' );
		$this->expectToken ( FL_TOKEN_JS_KEYWORD, "while" );
		$condition = $this->getParenthesisedAst ();
		$this->isSemicolon ();
		return array (
			'do', 
			$condition, 
			$body 
		);
	}

	/**
	 * 
	 * for循环的处理
	 */
	public function forStatement() {
		$this->expectToken ( FL_TOKEN_JS_PUNC, "(" );
		$init = null;
		if (! $this->isToken ( FL_TOKEN_JS_PUNC, ";" )) {
			if ($this->isToken ( FL_TOKEN_JS_KEYWORD, 'var' )) {
				$this->getNextToken ();
				$init = $this->varStatement ( true );
			} else {
				$init = $this->expressionAst ( true, true );
			}
			//for(var a in xxx)
			if ($this->isToken ( FL_TOKEN_JS_KEYWORD, 'in' )) {
				if ($init [0] === 'var' && count ( $init [1] ) > 1) {
					$this->throwException ( "Only one variable declaration allowed in for..in loop" );
				}
				$lhs = $init [0] === 'var' ? array (
					"name", 
					$init [1] [0] 
				) : $init;
				$this->getNextToken ();
				$obj = $this->expressionAst ();
				$this->expectToken ( FL_TOKEN_JS_PUNC, ")" );
				return array (
					"for-in", 
					$init, 
					$lhs, 
					$obj, 
					$this->inLoop ( 'statementAst' ) 
				);
			}
		}
		//处理 for(;xxx) 这种
		$this->expectToken ( FL_TOKEN_JS_PUNC, ";" );
		$test = $this->isToken ( FL_TOKEN_JS_PUNC, ";" ) ? null : $this->expressionAst ();
		$this->expectToken ( FL_TOKEN_JS_PUNC, ";" );
		$step = $this->isToken ( FL_TOKEN_JS_PUNC, ")" ) ? null : $this->expressionAst ();
		$this->expectToken ( FL_TOKEN_JS_PUNC, ")" );
		return array (
			"for", 
			$init, 
			$test, 
			$step, 
			$this->statementAst () 
		);
	}

	/**
	 * 
	 * 处理函数
	 */
	public function functionStatement($inStatement = true) {
		$name = null;
		if ($this->isToken ( FL_TOKEN_JS_NAME )) {
			$name = $this->currentToken ['value'];
			$this->getNextToken ();
		}
		if ($inStatement && ! $name) {
			$this->unexpectTokenError ();
		}
		$this->expectToken ( FL_TOKEN_JS_PUNC, "(" );
		$type = $inStatement ? "defun" : "function";
		//获取函数参数
		$first = true;
		$arguments = array ();
		while ( ! $this->isToken ( FL_TOKEN_JS_PUNC, ")" ) ) {
			if ($first) {
				$first = false;
			} else {
				$this->expectToken ( FL_TOKEN_JS_PUNC, "," );
			}
			if (! $this->isToken ( FL_TOKEN_JS_NAME )) {
				$this->unexpectTokenError ();
			}
			$arguments [] = $this->currentToken ['value'];
			$this->getNextToken ();
		}
		$this->getNextToken ();
		//获取函数body
		++ $this->funtionDepth;
		$loop = $this->loopDepth;
		$this->loopDepth = 0;
		$body = $this->getBlockAst ();
		-- $this->funtionDepth;
		$this->loopDepth = $loop;
		return array (
			$type, 
			$name, 
			$arguments, 
			$body 
		);
	}

	/**
	 * 
	 * if处理
	 */
	public function ifStatement() {
		$condition = $this->getParenthesisedAst ();
		$body = $this->statementAst ();
		$else = false;
		if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "else" )) {
			$this->getNextToken ();
			$else = $this->statementAst ();
		}
		return array (
			"if", 
			$condition, 
			$body, 
			$else 
		);
	}

	/**
	 * 
	 * return处理
	 */
	public function returnStatement() {
		if ($this->funtionDepth === 0) {
			$this->throwException ( "'return' outside of function" );
		}
		$return = '';
		if ($this->isToken ( FL_TOKEN_JS_PUNC, ";" )) {
			$this->getNextToken ();
		} else {
			if ($this->canInsertSemicolon ()) {
			} else {
				$return = $this->expressionAst ();
				$this->isSemicolon ();
			}
		}
		return array (
			"return", 
			$return 
		);
	}

	/**
	 * 
	 * switch处理
	 */
	public function switchStatement($getResult = true) {
		if ($getResult) {
			$condition = $this->getParenthesisedAst ();
			return array (
				"switch", 
				$condition, 
				$this->inLoop ( 'switchStatement', false ) 
			);
		}
		$this->expectToken ( FL_TOKEN_JS_PUNC, "{" );
		$result = array ();
		$cur = null;
		while ( ! $this->isToken ( FL_TOKEN_JS_PUNC, "}" ) ) {
			if ($this->currentToken === false) {
				$this->unexpectTokenError ();
			}
			if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "case" )) {
				$this->getNextToken ();
				$cur = array ();
				$result [] = array (
					$this->expressionAst (), 
					$cur 
				);
				$this->expectToken ( FL_TOKEN_JS_PUNC, ":" );
			} else if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "default" )) {
				$this->getNextToken ();
				$this->expectToken ( FL_TOKEN_JS_PUNC, ":" );
				$cur = array ();
				$result [] = array (
					null, 
					$cur 
				);
			} else {
				if (! $cur) {
					$this->unexpectTokenError ();
				}
				$cur [] = $this->statementAst ();
			}
		}
		$this->getNextToken ();
		return $result;
	}

	/**
	 * 
	 * throw处理
	 */
	public function throwStatement() {
		if ($this->currentToken ['newlineBefore']) {
			$this->throwException ( "Illegal newline after 'throw'" );
		}
		$value = $this->expressionAst ();
		$this->isSemicolon ();
		return array (
			'throw', 
			$value 
		);
	}

	/**
	 * 
	 * try处理
	 */
	public function tryStatement() {
		$body = $this->getBlockAst ();
		$bcatch = '';
		$bfinally = '';
		if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "catch" )) {
			$this->getNextToken ();
			$this->expectToken ( FL_TOKEN_JS_PUNC, "(" );
			if (! $this->isToken ( FL_TOKEN_JS_NAME )) {
				$this->throwException ( "Name expected" );
			}
			$name = $this->currentToken ['value'];
			$this->getNextToken ();
			$this->expectToken ( FL_TOKEN_JS_PUNC, ")" );
			$bcatch = array (
				$name, 
				$this->getBlockAst () 
			);
		}
		if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "finally" )) {
			$this->getNextToken ();
			$bfinally = $this->getBlockAst ();
		}
		if (! $bcatch && ! $bfinally) {
			$this->throwException ( "Missing catch/finally blocks" );
		}
		return array (
			"try", 
			$body, 
			$bcatch, 
			$bfinally 
		);
	}

	/**
	 * 
	 * var处理
	 */
	public function varStatement($notIn = false) {
		$result = array (
			"var", 
			$this->getVarDefs ( $notIn ) 
		);
		$this->isSemicolon ();
		return $result;
	}

	/**
	 * 
	 * const处理
	 */
	public function constStatement() {
		$result = array (
			"const", 
			$this->getVarDefs () 
		);
		$this->isSemicolon ();
		return $result;
	}

	/**
	 * 
	 * while处理
	 */
	public function whileStatement() {
		return array (
			'while', 
			$this->getParenthesisedAst (), 
			$this->inLoop ( 'statementAst' ) 
		);
	}

	/**
	 * 
	 * with处理
	 */
	public function withStatement() {
		return array (
			'with', 
			$this->getParenthesisedAst (), 
			$this->statementAst () 
		);
	}

	/**
	 * 
	 * var的定义
	 */
	public function getVarDefs($notIn = false) {
		$result = array ();
		while ( true ) {
			if (! $this->isToken ( FL_TOKEN_JS_NAME )) {
				$this->unexpectTokenError ();
			}
			$name = $this->currentToken ['value'];
			$this->getNextToken ();
			if ($this->isToken ( FL_TOKEN_JS_OPERATOR, "=" )) {
				$this->getNextToken ();
				$result [] = array (
					$name, 
					$this->expressionAst ( false, $notIn ) 
				);
			} else {
				$result [] = array (
					$name 
				);
			}
			if (! $this->isToken ( FL_TOKEN_JS_PUNC, "," )) {
				break;
			}
			$this->getNextToken ();
		}
		return $result;
	}

	/**
	 * 
	 * 获取{}内值
	 */
	public function getBlockAst() {
		$this->expectToken ( FL_TOKEN_JS_PUNC, "{" );
		$result = array ();
		while ( ! $this->isToken ( FL_TOKEN_JS_PUNC, "}" ) ) {
			if ($this->currentToken === false) {
				$this->unexpectTokenError ();
			}
			$result [] = $this->statementAst ();
		}
		$this->getNextToken ();
		return $result;
	}

	/**
	 * 
	 * 获取()内值
	 */
	public function getParenthesisedAst() {
		$this->expectToken ( FL_TOKEN_JS_PUNC, "(" );
		$ex = $this->expressionAst ();
		$this->expectToken ( FL_TOKEN_JS_PUNC, ")" );
		return $ex;
	}

	/**
	 * 
	 * break or continue
	 * @param string $type
	 */
	public function breakCont($type = '') {
		$name = '';
		if (! $this->canInsertSemicolon ()) {
			$name = $this->isToken ( FL_TOKEN_JS_NAME ) ? $this->currentToken ['value'] : null;
		}
		if ($name !== null) {
			$this->getNextToken ();
			if (! in_array ( $name, $this->labels )) {
				$this->throwException ( "Label " . $name . " without matching loop or statement" );
			}
		} else if ($this->loopDepth == 0) {
			$this->throwException ( $type . " not inside a loop or switch" );
		}
		$this->isSemicolon ();
		return array (
			$type, 
			$name 
		);
	}

	/**
	 * 
	 * 操作符
	 * @param string $left
	 * @param number $minPrec
	 * @param boolean $notIn
	 */
	public function exprOperator($left, $minPrec, $notIn = false) {
		$op = $this->isToken ( FL_TOKEN_JS_OPERATOR ) ? $this->currentToken ['value'] : null;
		if ($op && $op === 'in' && $notIn) {
			$op = null;
		}
		$prec = ($op != null ? Fl_Js_Static::getPrecedenceValue ( $op ) : null);
		if ($prec != null && $prec > $minPrec) {
			$this->getNextToken ();
			$right = $this->exprOperator ( $this->maybeUnary ( true ), $prec, $notIn );
			return $this->exprOperator ( array (
				"binary", 
				$op, 
				$left, 
				$right 
			), $minPrec, $notIn );
		}
		return $left;
	}

	/**
	 * 
	 * 操作符
	 * @param boolean $notIn
	 */
	public function exprOperators($notIn) {
		return $this->exprOperator ( $this->maybeUnary ( true ), 0, $notIn );
	}

	/**
	 * 
	 * 可能是一元操作符
	 * @param boolean $allowCalls
	 */
	public function maybeUnary($allowCalls) {
		if ($this->isToken ( FL_TOKEN_JS_OPERATOR ) && Fl_Js_Static::isUnaryPrefix ( $this->currentToken ['value'] )) {
			return $this->makeUnary ( "unary-prefix", $this->execEach ( $this->currentToken ['value'], 'getNextToken' ), $this->maybeUnary ( $allowCalls ) );
		}
		//$val = $this->maybeEmbedTokens ( 'exprAtomAst', $allowCalls );
		$val = $this->exprAtomAst ( $allowCalls );
		while ( $this->isToken ( FL_TOKEN_JS_OPERATOR ) && Fl_Js_Static::isUnarySuffix ( $this->currentToken ['value'] ) && ! $this->currentToken ['newlineBefore'] ) {
			$val = $this->makeUnary ( "unary-postfix", $this->currentToken ['value'], $val );
			$this->getNextToken ();
		}
		return $val;
	}

	/**
	 * 
	 * 一元操作符
	 * @param string $tag
	 * @param string $op
	 * @param array $expr
	 */
	public function makeUnary($tag, $op, $expr) {
		if (($op === "++" || $op === "--") && ! $this->isAssignable ( $expr )) {
			$this->throwException ( "Invalid use of " . $op . " operator" );
		}
		return array (
			$tag, 
			$op, 
			$expr 
		);
	}

	/**
	 * 
	 * new处理
	 */
	public function newStatement() {
		$newExp = $this->exprAtomAst ( false );
		$args = array ();
		if ($this->isToken ( FL_TOKEN_JS_PUNC, "(" )) {
			$this->getNextToken ();
			$args = $this->exprList ( ")" );
		}
		return $this->subScripts ( array (
			"new", 
			$newExp, 
			$args 
		), true );
	}

	/**
	 * 
	 * 获取表达式内部列表
	 * @param string $closing
	 * @param boolean $allowTrailingComma
	 * @param boolean $allowEmpty
	 */
	public function exprList($closing = "", $allowTrailingComma = false, $allowEmpty = false) {
		$first = true;
		$result = array ();
		while ( ! $this->isToken ( FL_TOKEN_JS_PUNC, $closing ) ) {
			if ($first) {
				$first = false;
			} else {
				$this->expectToken ( FL_TOKEN_JS_PUNC, "," );
			}
			if ($allowTrailingComma && $this->isToken ( FL_TOKEN_JS_PUNC, $closing )) {
				break;
			}
			if ($this->isToken ( FL_TOKEN_JS_PUNC, "," ) && $allowEmpty) {
				$result [] = array (
					"atom", 
					"undefined" 
				);
			} else {
				$result [] = $this->expressionAst ( false );
			}
		}
		$this->getNextToken ();
		return $result;
	}

	public function subScripts($expr, $allowCalls = false) {
		if ($this->isToken ( FL_TOKEN_JS_PUNC, "." )) {
			$this->getNextToken ();
			return $this->subScripts ( array (
				"dot", 
				$expr, 
				$this->asName () 
			), $allowCalls );
		} elseif ($this->isToken ( FL_TOKEN_JS_PUNC, "[" )) {
			$this->getNextToken ();
			$value = $this->expressionAst ();
			$this->expectToken ( FL_TOKEN_JS_PUNC, "]" );
			return $this->subScripts ( array (
				"sub", 
				$expr, 
				$value 
			), $allowCalls );
		} elseif ($allowCalls && $this->isToken ( FL_TOKEN_JS_PUNC, "{" )) {
			$this->getNextToken ();
			return $this->subScripts ( array (
				"call", 
				$expr, 
				$this->exprList ( ")" ) 
			), true );
		}
		return $expr;
	}

	/**
	 * 
	 * 单一关键字，如：null, false, true, undefined, new
	 * @param boolean $allowCalls
	 */
	public function exprAtomAst($allowCalls = false) {
		if ($this->isToken ( FL_TOKEN_JS_OPERATOR, "new" )) {
			$this->getNextToken ();
			$this->newStatement ();
		}
		if ($this->isToken ( FL_TOKEN_JS_PUNC )) {
			switch ($this->currentToken ['value']) {
				case "(" :
					$this->getNextToken ();
					$value = $this->expressionAst ();
					$this->expectToken ( FL_TOKEN_JS_PUNC, ")" );
					return $this->subScripts ( $value, $allowCalls );
				case "[" :
					$this->getNextToken ();
					return $this->subScripts ( $this->arrayStatement (), $allowCalls );
				case "{" :
					$this->getNextToken ();
					return $this->subScripts ( $this->objectStatement (), $allowCalls );
			}
			$this->unexpectTokenError ();
		}
		if ($this->isToken ( FL_TOKEN_JS_KEYWORD, "function" )) {
			$this->getNextToken ();
			return $this->subScripts ( $this->functionStatement ( false ), $allowCalls );
		}
		if (Fl_Js_Static::isAtomStartType ( $this->currentToken ['type'] )) {
			if ($this->currentToken ['type'] === FL_TOKEN_JS_REGEXP) {
				$atom = array (
					"regexp", 
					$this->currentToken ['value'] [0], 
					$this->currentToken ['value'] [1] 
				);
			} else {
				$atom = array (
					$this->currentToken ['type'], 
					$this->currentToken ['value'] 
				);
			}
			$this->getNextToken ();
			return $this->subScripts ( $atom, $allowCalls );
		}
		$this->unexpectTokenError ();
	}

	/**
	 * 
	 * 数组的处理
	 */
	public function arrayStatement() {
		return array (
			"array", 
			$this->exprList ( "]", true, true ) 
		);
	}

	/**
	 * 
	 * 对象的处理
	 */
	public function objectStatement() {
		$first = true;
		$result = array ();
		while ( ! $this->isToken ( FL_TOKEN_JS_PUNC, "}" ) ) {
			if ($first) {
				$first = false;
			} else {
				$this->expectToken ( FL_TOKEN_JS_PUNC, "," );
			}
			if ($this->isToken ( FL_TOKEN_JS_PUNC, "}" )) {
				break;
			}
			$type = $this->currentToken ['type'];
			if ($type === FL_TOKEN_JS_STRING || $type === FL_TOKEN_JS_NUMBER) {
				$name = $this->currentToken ['value'];
				$this->getNextToken ();
			} else {
				$name = $this->asName ();
			}
			if ($type === FL_TOKEN_JS_NAME && ($name === "set" || $name === "get") && ! $this->isToken ( FL_TOKEN_JS_PUNC, ":" )) {
				$result [] = array (
					$this->asName (), 
					$this->functionStatement ( false ), 
					$name 
				);
			} else {
				$this->expectToken ( FL_TOKEN_JS_PUNC, ":" );
				$result [] = array (
					$name, 
					$this->expressionAst ( false ) 
				);
			}
		}
		$this->getNextToken ();
		return array (
			"object", 
			$result 
		);
	}

	/**
	 * 
	 * 可能是个判断条件
	 */
	public function maybeConditional($notIn = false) {
		$expr = $this->exprOperators ( $notIn );
		if ($this->isToken ( FL_TOKEN_JS_OPERATOR, "?" )) {
			$this->getNextToken ();
			$yes = $this->expressionAst ( false );
			$this->expectToken ( FL_TOKEN_JS_PUNC, ":" );
			return array (
				"conditional", 
				$expr, 
				$yes, 
				$this->expressionAst ( false, $notIn ) 
			);
		}
		return $expr;
	}

	/**
	 * 
	 * 可能是赋值
	 * @param boolean $notIn
	 */
	public function maybeAssign($notIn = false) {
		$left = $this->maybeConditional ( $notIn );
		$value = $this->currentToken ['value'];
		if ($this->isToken ( FL_TOKEN_JS_OPERATOR ) && Fl_Js_Static::isAssignment ( $value )) {
			if ($this->isAssignable ( $left )) {
				$this->getNextToken ();
				return array (
					"assign", 
					Fl_Js_Static::getAssignmentValue ( $value ), 
					$left, 
					$this->maybeAssign ( $notIn ) 
				);
			}
			$this->throwException ( "Invalid assignment" );
		}
		return $left;
	}

	/**
	 * 
	 * 表达式
	 * @param boolean $commas
	 * @param boolean $notIn
	 */
	public function expressionAst($commas = true, $notIn = false) {
		$expr = $this->maybeAssign ( $notIn );
		if ($commas && $this->isToken ( FL_TOKEN_JS_PUNC, "," )) {
			$this->getNextToken ();
			return array (
				'seq', 
				$expr, 
				$this->expressionAst ( true, $notIn ) 
			);
		}
		return $expr;
	}

	/**
	 * 
	 * 检测是否是某个token，检测类型和值
	 * @param string $type
	 * @param string $value
	 */
	public function isToken($type, $value = false, $token = false) {
		if ($token === false) {
			$token = $this->currentToken;
		}
		return $token ['type'] === $type && ($token ['value'] === $value || $value === false);
	}

	/**
	 * 
	 * 判断当前能否插入分号
	 */
	public function canInsertSemicolon() {
		return $this->currentToken ['newlineBefore'] || $this->isToken ( FL_TOKEN_JS_PUNC, "}" || $this->isToken ( FL_TOKEN_LAST, "" ) ) || $this->currentToken === false;
	}

	/**
	 * 
	 * 判断当前是否是分号token,或者是能否插入分号
	 */
	public function isSemicolon() {
		if ($this->isToken ( FL_TOKEN_JS_PUNC, ";" )) {
			$this->getNextToken ();
		} else if (! $this->canInsertSemicolon ()) {
			$this->unexpectTokenError ();
		}
	}

	/**
	 * 
	 * 获取token
	 */
	public function getNextToken() {
		$this->prevToken = $this->currentToken;
		if ($this->peekToken) {
			$this->currentToken = $this->peekToken;
			$this->peekToken = false;
		} else {
			$this->currentToken = $this->tokenInstance->getNextToken ();
		}
		if ($this->inDirectives) {
			$this->inDirectives = $this->currentToken ['type'] === FL_TOKEN_JS_STRING || $this->isToken ( FL_TOKEN_JS_PUNC, ";" );
		}
		return $this->currentToken;
	}

	/**
	 * 
	 * 获取下一个token并作为一个临时token存起来
	 */
	public function getPeekToken() {
		if ($this->peekToken) {
			return $this->peekToken;
		}
		return $this->peekToken = $this->tokenInstance->getNextToken ();
	}

	/**
	 * 
	 * 
	 * @param function $fn
	 */
	public function maybeEmbedTokens($fn) {
		$args = func_get_args ();
		array_shift ( $args );
		if ($this->embedToken) {
			$start = $this->currentToken;
			$end = $this->prevToken;
			$a = call_user_func_array ( array (
				$this, 
				$fn 
			), $args );
			return array (
				$a [0], 
				$start, 
				$end 
			);
		} else {
			return call_user_func_array ( array (
				$this, 
				$fn 
			), $args );
		}
	}

	/**
	 * 
	 * 执行循环
	 * @param string $fn
	 */
	public function inLoop($fn = '') {
		try {
			$args = func_get_args ();
			array_shift ( $args );
			++ $this->loopDepth;
			$this->$fn ( $args );
		} catch ( Fl_Exception $e ) {
			//do nothing
		}
		-- $this->loopDepth;
	}

	/**
	 * 
	 * 是否是赋值
	 * @param array $expr
	 */
	public function isAssignable($expr) {
		switch (strval ( $expr [0] )) {
			case "dot" :
			case "sub" :
			case "new" :
			case "call" :
				return true;
			case "name" :
				return $expr [1] != "true";
		}
		return false;
	}

	public function asPropName() {
		switch ($this->currentToken ['type']) {
			case FL_TOKEN_JS_NUMBER :
			case FL_TOKEN_JS_STRING :
				$value = $this->currentToken ['value'];
				$this->getNextToken ();
				return $value;
		}
		return $this->asName ();
	}

	public function asName() {
		switch ($this->currentToken ['type']) {
			case FL_TOKEN_JS_NAME :
			case FL_TOKEN_JS_OPERATOR :
			case FL_TOKEN_JS_KEYWORD :
			case FL_TOKEN_JS_ATOM :
				$value = $this->currentToken ['value'];
				$this->getNextToken ();
				return $value;
			default :
				$this->unexpectTokenError ();
		}
	}

	/**
	 * 
	 * 执行每个方法
	 * @param string $fn
	 */
	public function execEach($fn = '') {
		if (method_exists ( $this, $fn )) {
			$fn = $this->$fn ();
		}
		$args = func_get_args ();
		for($i = 1, $count = count ( $args ); $i < $count; $i ++) {
			$this->$args [$i] ();
		}
		return $fn;
	}

	/**
	 * 
	 * 如果类型正确则获取下一个token,不对则抛出异常
	 */
	public function expectToken($type, $value) {
		if ($this->isToken ( $type, $value )) {
			return $this->getNextToken ();
		}
		$this->throwException ( "Unexpected token " . $this->currentToken ['type'] . ", expected " . $type . ', value:' . $value );
	}

	/**
	 * 
	 * 抛出token的相关异常
	 */
	public function throwException($msg = '', $token = false) {
		if ($token === false) {
			$token = $this->currentToken;
		}
		$ext = ' at line:' . strval ( $token ['tokline'] ) . ', col:' . ($token ['tokcol']) . ', pos:' . $token ['tokpos'];
		parent::throwException ( $msg . $ext );
	}

	/**
	 * 
	 * token类型不正确抛出异常
	 * @param array or false $token
	 */
	public function unexpectTokenError($token = false) {
		if ($token === false) {
			$token = $this->currentToken;
		}
		$this->throwException ( "Unexpected token: " . $token ['type'] . " (" + $token ['value'] . ")" );
	}
}