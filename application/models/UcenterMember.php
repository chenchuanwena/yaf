<?php


/**
 * ��Աģ��
 */
class UcenterMemberModel extends Db_Base {

    /**
     * ����û����ǲ��Ǳ���ֹע��
     * @param  string $username �û���
     * @return boolean          ture - δ���ã�false - ��ֹע��
     */
    protected function checkDenyMember($username) {
        return true; //TODO: �ݲ����ƣ���һ���汾����
    }

    /**
     * ��������ǲ��Ǳ���ֹע��
     * @param  string $email ����
     * @return boolean       ture - δ���ã�false - ��ֹע��
     */
    protected function checkDenyEmail($email) {
        return true; //TODO: �ݲ����ƣ���һ���汾����
    }

    /**
     * ����ֻ��ǲ��Ǳ���ֹע��
     * @param  string $mobile �ֻ�
     * @return boolean        ture - δ���ã�false - ��ֹע��
     */
    protected function checkDenyMobile($mobile) {
        return true; //TODO: �ݲ����ƣ���һ���汾����
    }

    /**
     * ��������ָ���û�״̬
     * @return integer �û�״̬
     */
    protected function getStatus() {
        return true; //TODO: �ݲ����ƣ���һ���汾����
    }

    /**
     * ע��һ�����û�
     * @param  string $username �û���
     * @param  string $password �û�����
     * @param  string $email    �û�����
     * @param  string $mobile   �û��ֻ�����
     * @return integer          ע��ɹ�-�û���Ϣ��ע��ʧ��-������
     */
    public function register($name, $username, $password, $email, $mobile) {
        $data = array(
            'name' => $name,
            'username' => $username,
            'password' => $password,
            'email' => $email,
            'mobile' => $mobile,
        );
        //��֤�ֻ�
        if (empty($data['mobile']))
            unset($data['mobile']);
        /* ����û� */
        if ($this->create($data)) {
            $uid = $this->add();
            return $uid ? $uid : 0; //0-δ֪���󣬴���0-ע��ɹ�
        } else {
            return $this->getError(); //����������Զ���֤ע��
        }
    }

    /**
     * �û���¼��֤
     * @param  string  $username �û���
     * @param  string  $password �û�����
     * @param  integer $type     �û������� ��1-�û�����2-���䣬3-�ֻ���4-UID��
     * @return integer           ��¼�ɹ�-�û�ID����¼ʧ��-������
     */
    public function login($username, $password, $type = 1) {
        $map = array();
        switch ($type) {
            case 1:
                $map['username'] = $username;
                break;
            case 2:
                $map['email'] = $username;
                break;
            case 3:
                $map['mobile'] = $username;
                break;
            case 4:
                $map['id'] = $username;
                break;
            default:
                return 0; //��������
        }

        /* ��ȡ�û����� */
       // $user = $this->where($map)->find();
        $user=$this->_db->selectFirst($this->_table,$map);
        if (is_array($user) && $user['status']) {
            /* ��֤�û����� */
            if (think_ucenter_md5($password, UC_AUTH_KEY) === $user['password']) {
                $this->updateLogin($user['id']); //�����û���¼��Ϣ
                return $user['id']; //��¼�ɹ��������û�ID
            } else {
                return -2; //�������
            }
        } else {
            return -1; //�û������ڻ򱻽���
        }
    }

    /**
     * ��ȡ�û���Ϣ
     * @param  string  $uid         �û�ID���û���
     * @param  boolean $is_username �Ƿ�ʹ���û�����ѯ
     * @return array                �û���Ϣ
     */
    public function info($uid, $is_username = false) {
        $map = array();
        if ($is_username) { //ͨ���û�����ȡ
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->field('id,username,email,mobile,status,name')->find();
        if (is_array($user) && $user['status'] = 1) {
            return array($user['id'], $user['username'], $user['email'], $user['mobile']);
        } else {
            return -1; //�û������ڻ򱻽���
        }
    }

    public function getInfo($uid, $is_username = false) {
        $map = array();
        if ($is_username) { //ͨ���û�����ȡ
            $map['username'] = $uid;
        } else {
            $map['id'] = $uid;
        }

        $user = $this->where($map)->field('id,username,email,mobile,status,name')->find();
        if (is_array($user) && $user['status'] = 1) {
            return $user;
        } else {
            return -1; //�û������ڻ򱻽���
        }
    }

    /**
     * ����û���Ϣ
     * @param  string  $field  �û���
     * @param  integer $type   �û������� 1-�û�����2-�û����䣬3-�û��绰
     * @return integer         ������
     */
    public function checkField($field, $type = 1) {
        $data = array();
        switch ($type) {
            case 1:
                $data['username'] = $field;
                break;
            case 2:
                $data['email'] = $field;
                break;
            case 3:
                $data['mobile'] = $field;
                break;
            default:
                return 0; //��������
        }

        return $this->create($data) ? 1 : $this->getError();
    }

    /**
     * �����û���¼��Ϣ
     * @param  integer $uid �û�ID
     */
    protected function updateLogin($uid) {
        $data = array(
            'id' => $uid,
            'last_login_time' => NOW_TIME,
            'last_login_ip' => get_client_ip(1),
        );

        $this->save($data);
    }

    /**
     * �����û���Ϣ
     * @param int $uid �û�id
     * @param string $password ���룬������֤
     * @param array $data �޸ĵ��ֶ�����
     * @return true �޸ĳɹ���false �޸�ʧ��
     * @author huajie <banhuajie@163.com>
     */
    public function updateUserFields($uid, $password, $data) {
        if (empty($uid) || empty($password) || empty($data)) {
            $this->error = '��������';
            return false;
        }

        //����ǰ����û�����
        if (!$this->verifyUser($uid, $password)) {
            $this->error = '��֤�������벻��ȷ��';
            return false;
        }
        //�����û���Ϣ
        $data = $this->create($data);
        if ($data) {
            return $this->where(array('id' => $uid))->save($data);
        }
        return false;
    }
    public function resetPassword($uid,$password){
        $password= think_ucenter_md5($password,UC_AUTH_KEY);
        $map['id']=$uid;
        $res=$this->where($map)->setField('password',$password);
        if ($res!==false) {
            return TRUE;
        }else{
            return false;
        }
    }

    /**
     * ��֤�û�����
     * @param int $uid �û�id
     * @param string $password_in ����
     * @return true ��֤�ɹ���false ��֤ʧ��
     * @author huajie <banhuajie@163.com>
     */
    protected function verifyUser($uid, $password_in) {
        $password = $this->getFieldById($uid, 'password');
        if (think_ucenter_md5($password_in, UC_AUTH_KEY) === $password) {
            return true;
        }
        return false;
    }

}
