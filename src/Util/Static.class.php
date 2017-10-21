<?php
/**
 * 
 * Fl里通用的静态方法
 * @author welefen
 *
 */
class Fl_Static {

	/**
	 * 
	 * 是否是远程地址
	 * @param string $url
	 */
	public static function isRemoteUrl($url) {
		if (strpos ( $url, 'http://' ) === 0 || strpos ( $url, 'https://' ) === 0) {
			return true;
		}
		//没有http或者https的URL
		if (strpos ( $url, '//' ) === 0) {
			return true;
		}
		return false;
	}

	public static function fixPregReplaceQuote($string = '') {
		return str_replace ( '\"', '"', $string );
	}

	/**
	 * 
	 * 获取修复后的url
	 * @param string $url
	 */
	public static function getFixedUrl($url, $parentUrl = '') {
		$url = trim ( $url );
		if (self::isRemoteUrl ( $url )) {
			return $url;
		}
		if (strpos ( strtolower ( $url ), 'javascript' ) === 0) {
			return '';
		}
		if (empty ( $parentUrl )) {
			return $url;
		}
		if (strpos ( $url, '/' ) === 0) {
			return self::getDomain ( $parentUrl ) . $url;
		}
		$result = self::getPath ( $parentUrl ) . $url;
		$pattern = '/\/[\w\-]+\/\.\./i';
		while ( true ) {
			$r = preg_replace ( $pattern, "", $result );
			if ($r == $result) {
				break;
			}
			$result = $r;
		}
		return $result;
	}

	/**
	 * 
	 * 获取域名和路径
	 * @param string $url
	 */
	public static function getPath($url) {
		$pars = parse_url ( $url );
		$return = $pars ['scheme'] . '://' . $pars ['host'];
		if ($pars ['port']) {
			$return .= ':' . $pars ['port'];
		}
		$return .= dirname ( $pars ['path'] );
		$return = rtrim ( $return, '/' ) . '/';
		return $return;
	}

	/**
	 * 
	 * 通过url获取域名
	 * @param string $url
	 */
	public static function getDomain($url) {
		$pars = parse_url ( $url );
		$return = $pars ['scheme'] . '://' . $pars ['host'];
		if ($pars ['port']) {
			$return .= ':' . $pars ['port'];
		}
		return $return;
	}
}