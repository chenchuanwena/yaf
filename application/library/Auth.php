<?php

//���ݿ�
/*
-- ----------------------------
-- think_auth_rule�������
-- id:������name������Ψһ��ʶ, title�������������� status ״̬��Ϊ1������Ϊ0���ã�condition��������ʽ��Ϊ�ձ�ʾ���ھ���֤����Ϊ�ձ�ʾ����������֤
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_rule`;
CREATE TABLE `think_auth_rule` (  
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT,  
    `name` char(80) NOT NULL DEFAULT '',  
    `title` char(20) NOT NULL DEFAULT '',  
    `type` tinyint(1) NOT NULL DEFAULT '1',    
    `status` tinyint(1) NOT NULL DEFAULT '1',  
    `condition` char(100) NOT NULL DEFAULT '',  # ���򸽼�����,���㸽�������Ĺ���,����Ϊ����Ч�Ĺ���
    PRIMARY KEY (`id`),  
    UNIQUE KEY `name` (`name`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group �û���� 
-- id�������� title:�û����������ƣ� rules���û���ӵ�еĹ���id�� �������","������status ״̬��Ϊ1������Ϊ0����
-- ----------------------------
 DROP TABLE IF EXISTS `think_auth_group`;
CREATE TABLE `think_auth_group` ( 
    `id` mediumint(8) unsigned NOT NULL AUTO_INCREMENT, 
    `title` char(100) NOT NULL DEFAULT '', 
    `status` tinyint(1) NOT NULL DEFAULT '1', 
    `rules` char(80) NOT NULL DEFAULT '', 
    PRIMARY KEY (`id`)
) ENGINE=MyISAM  DEFAULT CHARSET=utf8;
-- ----------------------------
-- think_auth_group_access �û�����ϸ��
-- uid:�û�id��group_id���û���id
-- ----------------------------
DROP TABLE IF EXISTS `think_auth_group_access`;
CREATE TABLE `think_auth_group_access` (  
    `uid` mediumint(8) unsigned NOT NULL,  
    `group_id` mediumint(8) unsigned NOT NULL, 
    UNIQUE KEY `uid_group_id` (`uid`,`group_id`),  
    KEY `uid` (`uid`), 
    KEY `group_id` (`group_id`)
) ENGINE=MyISAM DEFAULT CHARSET=utf8;
 */

class Auth{

    //Ĭ������
    protected $config = array(
        'AUTH_ON'           => true,                      // ��֤����
        'AUTH_TYPE'         => 1,                         // ��֤��ʽ��1Ϊʵʱ��֤��2Ϊ��¼��֤��
        'AUTH_GROUP'        => 'auth_group',        // �û������ݱ���
        'AUTH_GROUP_ACCESS' => 'auth_group_access', // �û�-�û����ϵ��
        'AUTH_RULE'         => 'auth_rule',         // Ȩ�޹����
        'AUTH_USER'         => 'member'             // �û���Ϣ��
    );
   public function __construct(){
       $this->_config = Yaf_Registry::get('config');
       $this->_session = Yaf_Session::getInstance();
       $this->_session->start();
       if(!$this->_session->has('username')){
           $this->redirect('/index/');
       }
       if (AUTH_CONFIG) {
           //������������ AUTH_CONFIG, ��������Ϊ���顣
           $this->config['AUTH_CONFIG']=AUTH_CONFIG;
       }
   }

    /**
     * ���Ȩ��
     * @param name string|array  ��Ҫ��֤�Ĺ����б�,֧�ֶ��ŷָ���Ȩ�޹������������
     * @param uid  int           ��֤�û���id
     * @param string mode        ִ��check��ģʽ
     * @param relation string    ���Ϊ 'or' ��ʾ������һ������ͨ����֤;���Ϊ 'and'���ʾ���������й������ͨ����֤
     * @return boolean           ͨ����֤����true;ʧ�ܷ���false
     */
    public function check($name, $uid, $type=1, $mode='url', $relation='or') {
        if (!$this->config['AUTH_ON'])
            return true;
        $authList = $this->getAuthList($uid,$type); //��ȡ�û���Ҫ��֤��������Ч�����б�
        if (is_string($name)) {
            $name = strtolower($name);
            if (strpos($name, ',') !== false) {
                $name = explode(',', $name);
            } else {
                $name = array($name);
            }
        }
        $list = array(); //������֤ͨ���Ĺ�����
        if ($mode=='url') {
            $REQUEST = unserialize( strtolower(serialize($_REQUEST)) );
        }

        foreach ( $authList as $auth ) {
            //��ȡ����Ĳ���
            $query = preg_replace('/^.+\?/U','',$auth);
            if ($mode=='url' && $query!=$auth ) {
                parse_str($query,$param); //���������е�param
                $intersect = array_intersect_assoc($REQUEST,$param);
                $auth = preg_replace('/\?.*$/U','',$auth);
                if ( in_array($auth,$name) && $intersect==$param ) {  //����ڵ������url��������
                    $list[] = $auth ;
                }
            }else if (in_array($auth , $name)){
                $list[] = $auth ;
            }
        }

        if ($relation == 'or' and !empty($list)) {
            return true;
        }
        $diff = array_diff($name, $list);
        if ($relation == 'and' and empty($diff)) {
            return true;
        }
        return false;
    }

    /**
     * �����û�id��ȡ�û���,����ֵΪ����
     * @param  uid int     �û�id
     * @return array       �û��������û��� array(
     *                                         array('uid'=>'�û�id','group_id'=>'�û���id','title'=>'�û�������','rules'=>'�û���ӵ�еĹ���id,���,�Ÿ���'),
     *                                         ...)
     */
    public function getGroups($uid) {
        static $groups = array();
        if (isset($groups[$uid]))
            return $groups[$uid];
        $user_group= new UserGroupModel();
       $sql="SELECT `rules` FROM auth_group_access a INNER JOIN auth_group g on a.group_id=g.id WHERE ( a.uid={$uid} and g.status='1' )";
       $user_groups=$user_group->SelectDb($sql);
        $groups[$uid]=$user_groups?$user_groups:array();
        return $groups[$uid];
    }

    /**
     * ���Ȩ���б�
     * @param integer $uid  �û�id
     * @param integer $type
     */
    protected function getAuthList($uid,$type) {
        static $_authList = array(); //�����û���֤ͨ����Ȩ���б�
        $t = implode(',',(array)$type);
        if (isset($_authList[$uid.$t])) {
            return $_authList[$uid.$t];
        }
        if( $this->_config['AUTH_TYPE']==2 && isset($_SESSION['_AUTH_LIST_'.$uid.$t])){
            return $_SESSION['_AUTH_LIST_'.$uid.$t];
        }

        //��ȡ�û������û���
        $groups = $this->getGroups($uid);
        $ids = array();//�����û������û������õ�����Ȩ�޹���id
        foreach ($groups as $g) {
            $ids = array_merge($ids, explode(',', trim($g['rules'], ',')));
        }
        $ids = array_unique($ids);
        if (empty($ids)) {
            $_authList[$uid.$t] = array();
            return array();
        }
       if($ids){
            $ids=implode(',',$ids);
       }
//        $map=array(
//            'id'=>array('in',$ids),
//            'type'=>$type,
//            'status'=>1,
//        );
        //��ȡ�û�������Ȩ�޹���
        $sql="select `condition`,`name` from auth_rule where id in({$ids}) and `type` in(1,2) and `status`=1";
        $user_group= new UserGroupModel();
        $rules=$user_group->SelectDb($sql);

        //ѭ�������жϽ����
        $authList = array();   //
        foreach ($rules as $rule) {
            if (!empty($rule['condition'])) { //����condition������֤
                $user = $this->getUserInfo($uid);//��ȡ�û���Ϣ,һά����

                $command = preg_replace('/\{(\w*?)\}/', '$user[\'\\1\']', $rule['condition']);
                //dump($command);//debug
                @(eval('$condition=(' . $command . ');'));
                if ($condition) {
                    $authList[] = strtolower($rule['name']);
                }
            } else {
                //ֻҪ���ھͼ�¼
                $authList[] = strtolower($rule['name']);
            }
        }
        $_authList[$uid.$t] = $authList;
        if($this->config['AUTH_TYPE']==2){
            //�����б������浽session
            $_SESSION['_AUTH_LIST_'.$uid.$t]=$authList;
        }
        return array_unique($authList);
    }

    /**
     * ����û�����,�����Լ��������ȡ���ݿ�
     */
    protected function getUserInfo($uid) {
        static $userinfo=array();
        if(!isset($userinfo[$uid])){
            $userinfo[$uid]=M()->where(array('uid'=>$uid))->table($this->_config['AUTH_USER'])->find();
        }
        return $userinfo[$uid];
    }

}
