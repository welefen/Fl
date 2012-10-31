<?php
/**
 * 
 * 模版类
 * @author welefen
 *
 */
Fl::loadClass ( "Fl_Define" );
class Fl_Tpl {

	/**
	 * 
	 * 对象管理器
	 * @var array
	 */
	public static $register = array ();

	/**
	 * 
	 * 模版语言的工厂
	 * @param Fl_Token $instance
	 */
	public static function factory(Fl_Base $instance, $new = false) {
		$tpl = strtolower ( $instance->tpl );
		$list = array (
			'php' => "PHP", 
			'smarty' => "Smarty" 
		);
		if (isset ( $list [$tpl] )) {
			$tpl = $list [$tpl];
		}
		$class = 'Fl_Tpl_' . $tpl;
		if (! $new && array_key_exists ( $class, self::$register )) {
			return self::$register [$class];
		}
		Fl::loadClass ( $class );
		$new = new $class ();
		if (! $new) {
			self::$register [$class] = $new;
		}
		return $new;
	}
}