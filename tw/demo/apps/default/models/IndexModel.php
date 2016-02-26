<?php
class IndexModel extends Tw_Model
{
    protected $_table = 'mobile_info';
    protected $_db = 'DB_db_tip';
    protected $_mc = 'MC_test';
    public function get()
    {
        try {
	 $data = $this->sql("select * from tw_test limit 15;");
            return $data;
        } catch (Exception $e) {
            echo $e;
        }
    }
    public function add($uin) {
	try {
		$data = $this->sql("INSERT INTO tw_test  values ($uin);");
		return $data;
	} catch (Exception $e) {
		echo $e;
	}
    }

    public function del($uin)
    {
        try {
            
	    $data = $this->sql("delete from tw_test where uin=$uin");
            return $data;
        } catch (Exception $e) {
            echo $e;
        }
    }

    public function setCache($key,$value){
	$a = $this->mc(array(array('172.16.84.7','11211')));
	return $a->set($key,$value,60);		
    }


    public function getCache($key){
	return $this->mc->get($key);
    }
}
