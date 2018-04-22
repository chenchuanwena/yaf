<?php
class LoginModel extends Db_Base{
    protected $_table = "ucenter_member";
    /**
     * �û���¼�ж�
     */
    public function LoginUsr($usr, $passwd)
    {

        $usrinfo = $this->_db->selectFirst($this->_table,array('username'=>$usr,'password'=>$passwd));
        //$usrinfo = $this->_db->select($this->_table,array('username'=>$usr,'password'=>$passwd),1);
        //������query����
        //$sql = "SELECT * FROM $this->_table WHERE username='{$usr}' AND password='{$passwd}' AND is_del='0'";
        //$usrinfo = $this->_db->query($sql);
        if($usrinfo){
            return $usrinfo;
        }else{
            return false;
        }
    }
    /**
     * ��ʾ�����û���Ϣ
     */
    public function ShowUsers()
    {
        //$sql = "SELECT * FROM $this->_table WHERE is_del='0'";
        //return $this->_db->query($sql);
        $usrinfo = $this->_db->select($this->_table);
        return $usrinfo;
    }
    /**
     *
     * ����û�
     * @param [type] $info [description]
     */
    public function AddUsr($info)
    {
        $new_id=$this->_db->insert($this->_table,$info);
        if($new_id){
            return $new_id;
        }else{
            return false;
        }
    }
    /**
     *����id��ȡ�û���Ϣ
     * @param [type] $id [description]
     */
    public function GetUsrInfo($id)
    {
        $usrinfo = $this->_db->selectFirst($this->_table,array('uid'=>$id));
        return $usrinfo;
    }
    /**
     * �༭�û�
     * @param [type] $id     [description]
     * @param [type] $params [description]
     */
    public function EditUsr($id,$params)
    {
        $wheres = array('id'=>$id);
        try{
            $ret = $this->_db->update($this->_table, $params, $wheres);
            if($ret===false){
                return false;
            }
            return true;
        }catch(Exception $e){
            return false;
        }
    }
    /**
     * ɾ���û�
     * @param [type] $id [description]
     */
    public function Del($id)
    {
        $sql = "DELECT FROM $this->_table WHERE id='{$id}' ";
        $params = array('is_del'=>1);
        $wheres = array('id'=>$id);
        $this->_db = new Db_Mysql ($this->_config->database->config->toArray());
        if($this->_db->update($this->_table, $params, $wheres)){
            return true;
        }else{
            return false;
        }
    }
}
