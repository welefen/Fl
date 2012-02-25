<?php
/**
 * 将给定的图片url转换为datauri格式的数据
 * url可以是网络资源，也可以是本地资源
 * 
 * @author zhaoxianlie
 */
class Fl_Datauri_Parse{
	
	/**
	 * 数据缓存
	 * @var unknown_type
	 */
	static $data_cache = array();
	
	/**
	 * 将给定的图片url转换为datauri格式的数据
	 * @param unknown_type $url
	 */
	public function run($url = '') {
		if($url) {
			$data = self::$data_cache[$url];
			if(!$data) {
				$data = $this->getImageDataURI($url);
				self::$data_cache[$url] = $data;
			}
			return $data;
		}else {
			return $url;
		}
	}
	
	/**
	 * 获取图片的二进制数据，图片分两种，一种是网络图片，一种是本地图片
	 * @param unknown_type $url
	 */
	private function getImageBinaryData($url = ''){
		$data = '';
		try {
			//读取网络图片
			if(strpos($url, 'http://') !== false || strpos($url, 'https://') !== false) {
				$ch = curl_init();
				curl_setopt($ch, CURLOPT_URL, $url);
				curl_setopt($ch, CURLOPT_HEADER, 0);
				curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
				$data = curl_exec($ch);
				curl_close($ch);		
			} else {
				//提取绝对路径
				$url = realpath($url);
				if($fp = fopen($url,"rb", 0)){
					//读取图片数据
					$data = fread($fp,filesize($url));
					fclose($fp);
				}
			}
		} catch (Exception $e) {
			echo "\'$url\' does not exist!\n";
			$data = $url;
		}

		return $data;
	}
	
	/**
	 * 根据图片文件的路径，获取图片dataURI格式的数据
	 * @param unknown_type $url 图片路径
	 */
	private function getImageDataURI($url = ''){
		//取得图片的大小，类型等  
		$type=getimagesize($url);
		//base64编码，并拼接为dataURI格式
		$picture = $this->getImageBinaryData($url);
		$dataURI = 'data:' . $type['mime'] . ';base64,' . base64_encode($picture);
		return $dataURI;
	}
	
}
