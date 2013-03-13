<?php
/**
 * 
 * 支持模版语法的HTML/JS/CSS分析、检测、美化、压缩工具
 * @author welefen
 *
 */
class Fl {

	/**
	 * 
	 * class find path
	 * @var array
	 */
	public static $classPath = array (
		'Lang', 
		'Util' 
	);

	/**
	 * 
	 * 加载类文件,所有的类都在Fl_下
	 * @param string $class
	 */
	public static function loadClass($class = "", $new = false) {
		if (! class_exists ( $class )) {
			$prefix = 'Fl_';
			$sourceClass = $class;
			if (stripos ( $class, $prefix ) !== 0) {
				$class = $prefix . $class;
			}
			$class = ucwords ( str_replace ( "_", " ", $class ) );
			$path = str_replace ( ' ', '/', $class );
			$exist = false;
			foreach ( self::$classPath as $dir ) {
				$repath = str_ireplace ( 'fl', $dir, $path );
				$file = dirname ( __FILE__ ) . '/' . $repath . '.class.php';
				if (file_exists ( $file )) {
					require_once $file;
					$exist = true;
					break;
				}
			}
			if (! $exist) {
				self::loadClass ( 'Fl_Exception' );
				throw new Fl_Exception ( $sourceClass . ' is not exist', - 1 );
			}
		}
		if ($new) {
			return new $class ();
		}
		return $class;
	}
}