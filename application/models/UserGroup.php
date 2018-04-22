<?php
class UserGroupModel extends Db_Base{
    protected $_table = "auth_group_access";

    /**
     * 自动登录用户
     * @param  integer $user 用户信息数组
     */
    private function autoLogin($user,$server_station=array(),$custemer=array()){
        /* 更新登录信息 */
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
        /* 记录登录SESSION和COOKIES */

        // session('user_auth_sign', data_auth_sign($auth));

    }
    /**
     * 登录指定用户
     * @param  integer $uid 用户ID
     * @return boolean      ture-登录成功，false-登录失败
     */
    public function login($uid){
        /* 检测是否在当前应用注册 */
        $user = $this->_db->selectFirst($this->_table,array('uid'=>$uid));
        if(!$user || 1 != $user['status']) {
            $this->error = '用户不存在或已被禁用！'; //应用级别禁用
            return false;
        }
        //记录行为
        //  action_log('user_login', 'member', $uid, $uid);

        /* 登录用户 */
        return  $this->autoLogin($user);
    }
    /**
     * 用户登录判断
     */
    public function LoginUsr($usr, $passwd)
    {
        $usrinfo = $this->_db->selectFirst($this->_table,array('username'=>$usr,'password'=>$passwd));
        //$usrinfo = $this->_db->select($this->_table,array('username'=>$usr,'password'=>$passwd),1);
        //可以用query方法
        //$sql = "SELECT * FROM $this->_table WHERE username='{$usr}' AND password='{$passwd}' AND is_del='0'";
        //$usrinfo = $this->_db->query($sql);
        if($usrinfo){
            return $usrinfo;
        }else{
            return false;
        }
    }
    /**
     * 显示所有用户信息
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
     * 添加用户
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
     *根据id获取用户信息
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
     * 编辑member
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
     * 编辑用户
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
     * 删除用户
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
