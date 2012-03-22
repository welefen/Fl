<?php
/**
 * 
 * 支持模版语法的HTML/JS/CSS分析、检测、美化、压缩工具
 * @author welefen
 *
 */
/**
 * 
 * 基础类
 * @author welefen
 *
 */
class Fl {
	/**
	 * 
	 * 单例模式对应的实例
	 * @var object
	 */
	private static $_instance = null;
	/**
	 * 
	 * 单例模式
	 */
	public static function getInstance() {
		if (self::$_instance === null) {
			self::$_instance = new self ();
		}
		return self::$_instance;
	}
	/**
	 * 
	 * 加载类文件
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
			
			$classPath = array ('Lang', 'Util' );
			$exist = false;
			foreach ( $classPath as $dir ) {
				$repath = str_replace ( array ('fl', 'Fl' ), $dir, $path );
				$file = dirname ( __FILE__ ) . '/' . $repath . '.class.php';
				if (file_exists ( $file )) {
					require_once $file;
					$exist = true;
					break;
				}
			}
			if (! $exist) {
				self::loadClass ( 'Fl_Exception' );
				throw new Fl_Exception ( $sourceClass . ' is not exist', $code );
			}
		}
		if ($new) {
			return new $class ();
		}
		return $class;
	}
	/**
	 * 
	 * 魔术方法
	 * @param string $method
	 * @param array $args
	 */
	public function __call($method, $args) {
		self::loadClass ( $method );
		return new $method ( $args );
	}
}