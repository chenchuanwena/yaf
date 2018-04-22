<?php
class UserGroupModel extends Db_Base{
    protected $_table = "auth_group_access";

    /**
     * �Զ���¼�û�
     * @param  integer $user �û���Ϣ����
     */
    private function autoLogin($user,$server_station=array(),$custemer=array()){
        /* ���µ�¼��Ϣ */
        $data = array(
            'login'           => '`login`+1',
            'last_login_time' => NOW_TIME,
            'last_login_ip'   => $this->get_client_ip(1),
        );
        if($this->EditMemUsr($user['uid'],$data)){
            return $user;
        }else{
            return false;
        }
        /* ��¼��¼SESSION��COOKIES */

        // session('user_auth_sign', data_auth_sign($auth));

    }
    /**
     * ��¼ָ���û�
     * @param  integer $uid �û�ID
     * @return boolean      ture-��¼�ɹ���false-��¼ʧ��
     */
    public function login($uid){
        /* ����Ƿ��ڵ�ǰӦ��ע�� */
        $user = $this->_db->selectFirst($this->_table,array('uid'=>$uid));
        if(!$user || 1 != $user['status']) {
            $this->error = '�û������ڻ��ѱ����ã�'; //Ӧ�ü������
            return false;
        }
        //��¼��Ϊ
        //  action_log('user_login', 'member', $uid, $uid);

        /* ��¼�û� */
        return  $this->autoLogin($user);
    }
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
    public function ShowUsers($condition)
    {
        //$sql = "SELECT * FROM $this->_table WHERE is_del='0'";
        //return $this->_db->query($sql);
        $usrinfo = $this->_db->select($this->_table,$condition);
        return $usrinfo;
    }
    /**
     *
     * ����û�
     * @param [type] $info [description]
     */
    public function AddUsr($info)
    {
        if($this->_db->insert($this->_table,$info)){
            return true;
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
        $usrinfo = $this->_db->selectFirst($this->_table,array('id'=>$id));
        return $usrinfo;
    }

    public function SelectDb($sql)
    {
        return $this->_db->query($sql);
    }
    /**
     * �༭member
     * @param [type] $id     [description]
     * @param [type] $params [description]
     */
    public function EditMemUsr($id,$params)
    {
        $wheres = array('uid'=>$id);
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
