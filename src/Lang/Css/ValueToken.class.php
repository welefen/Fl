<?php
/**
 * 
 * css值的token分析
 * @author welefen
 *
 */
Fl::loadClass ( "Fl_Token" );
Fl::loadClass ( "Fl_Css_Static" );
class Fl_Css_ValueToken extends Fl_Token {

    /**
     * 
     * 当前分析css属性值对应的属性名
     * @var string
     */
    public $property = "";

    /**
     * 执行
     * @see Fl_Token::run()
     */
    public function run ($property = "") {
        $this->property = strtolower ( $property );
        $tokens = array();
        while (true) {
            $token = $this->getNextToken ();
            if (empty ( $token )) {
                break;
            }
            $tokens[] = $token;
        }
        return $this->updateTokenType ( $tokens );
    }

    /**
     * 获取下一个token
     * @see Fl_Token::getNextToken()
     */
    public function getNextToken () {
        $token = parent::getNextToken ();
        if ($token || $token === false) {
            return $token;
        }
        $result = "";
        while ($this->pos < $this->length) {
            //如果是圆括号，则匹配对应的结束圆括号
            if ($this->text{$this->pos} === '(') {
                $match = $this->getMatched ( "(", ")" );
                $result .= $match;
                continue;
            }
            $char = $this->getNextChar ();
            if ($this->isWhiteSpace ( $char ) && ! preg_match ( "/^\s*\(/", substr ( $this->text, $this->pos ) )) {
                break;
            } else {
                $result .= $char;
            }
        }
        if (strlen ( $result )) {
            return $this->getTokenInfo ( FL_TOKEN_CSS_VALUE_TYPE_COMMON, $result );
        }
        return false;
    }

    /**
     * 
     * 更新token的类型
     * @param array $tokens
     */
    public function updateTokenType ($tokens = array()) {
        if (empty ( $this->property )) {
            return $tokens;
        }
        $property = str_replace ( "-", " ", $this->property );
        $property = ucwords ( $property );
        $property = str_replace ( " ", "", $property );
        $method = "update" . $property . "TokenType";
        if (method_exists ( $this, $method )) {
            return $this->$method ( $tokens );
        }
        if (count ( $tokens ) === 1) {
            $tokens[0]['type'] = $this->property;
        }
        return $tokens;
    }

    /**
     * 
     * background
     * @param array $tokens
     */
    public function updateBackgroundTokenType ($tokens) {
        foreach ($tokens as &$token) {
            $value = $token['value'];
            $urlValue = Fl_Css_Static::isUrlValue ( $value );
            if (Fl_Css_Static::isColor ( $value )) { //背景色
                $token['type'] = 'background-color';
            } elseif ($urlValue) { //背景图
                $token['type'] = 'background-image';
                $token['url_value'] = $urlValue;
            } elseif (Fl_Css_Static::isBackgroundRepeat ( $value )) { //平铺方式
                $token['type'] = 'background-repeat';
            } elseif (Fl_Css_Static::isBackgroundAttachment ( $value )) { //背景固定方式
                $token['type'] = 'background-attachment';
            } elseif (Fl_Css_Static::isBackgroundPosition ( $value )) { //背景位置
                $token['type'] = 'background-position';
            }
        }
        return $tokens;
    }
}