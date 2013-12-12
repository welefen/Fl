<?php
/**
 * 
 * css值的token分析
 * @author welefen
 *
 */
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
    public function run () {
        
    }

    /**
     * 获取下一个token
     * @see Fl_Token::getNextToken()
     */
    public function getNextToken () {
        //do something
    }
}