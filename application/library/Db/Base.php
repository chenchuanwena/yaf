<?php
class Db_Base
{
	public function __construct() {
		$this->_config = Yaf_Registry::get("config");
		$this->_db = new Db_Mysql ($this->_config->database->config->toArray());
		//$this->_redis = new Redis();
		//$this->_redis->connect($this->_config->redis->host);
	}
	/**
	 * ��ȡ�ͻ���IP��ַ
	 * @param integer $type �������� 0 ����IP��ַ 1 ����IPV4��ַ����
	 * @return mixed
	 */
	function get_client_ip($type = 0) {
		$type       =  $type ? 1 : 0;
		static $ip  =   NULL;
		if ($ip !== NULL) return $ip[$type];
		if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
			$arr    =   explode(',', $_SERVER['HTTP_X_FORWARDED_FOR']);
			$pos    =   array_search('unknown',$arr);
			if(false !== $pos) unset($arr[$pos]);
			$ip     =   trim($arr[0]);
		}elseif (isset($_SERVER['HTTP_CLIENT_IP'])) {
			$ip     =   $_SERVER['HTTP_CLIENT_IP'];
		}elseif (isset($_SERVER['REMOTE_ADDR'])) {
			$ip     =   $_SERVER['REMOTE_ADDR'];
		}
		// IP��ַ�Ϸ���֤
		$long = sprintf("%u",ip2long($ip));
		$ip   = $long ? array($ip, $long) : array('0.0.0.0', 0);
		return $ip[$type];
	}
}