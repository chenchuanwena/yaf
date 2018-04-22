<?php


class UserController extends Controller_Base {

    /**
     * ���췽����ʵ��������ģ��
     */
    public function init() {
        $this->model = new UcenterMemberModel();
    }

    /**
     * ע��һ�����û�
     * @param  string $username �û���
     * @param  string $password �û�����
     * @param  string $email    �û�����
     * @param  string $mobile   �û��ֻ�����
     * @return integer          ע��ɹ�-�û���Ϣ��ע��ʧ��-������
     */
    public function register($name,$username, $password, $email, $mobile = '') {
        return $this->model->register($name,$username, $password, $email, $mobile);
    }
    public function resetPassword($id,$password){
        return $this->model->resetPassword($id, $password);
    }

    /**
     * �û���¼��֤
     * @param  string  $username �û���
     * @param  string  $password �û�����
     * @param  integer $type     �û������� ��1-�û�����2-���䣬3-�ֻ���4-UID��
     * @return integer           ��¼�ɹ�-�û�ID����¼ʧ��-������
     */
    public function login($username, $password, $type = 1) {
        echo 'asdfsadf';exit;
        return $this->model->login($username, $password, $type);
    }

    /**
     * ��ȡ�û���Ϣ
     * @param  string  $uid         �û�ID���û���
     * @param  boolean $is_username �Ƿ�ʹ���û�����ѯ
     * @return array                �û���Ϣ
     */
    public function info($uid, $is_username = false) {
        return $this->model->info($uid, $is_username);
    }

    public function getInfo($uid, $is_username = false) {
        return $this->model->getInfo($uid, $is_username);
    }

    /**
     * ����û���
     * @param  string  $field  �û���
     * @return integer         ������
     */
    public function checkUsername($username) {
        return $this->model->checkField($username, 1);
    }

    /**
     * �������
     * @param  string  $email  ����
     * @return integer         ������
     */
    public function checkEmail($email) {
        return $this->model->checkField($email, 2);
    }

    /**
     * ����ֻ�
     * @param  string  $mobile  �ֻ�
     * @return integer         ������
     */
    public function checkMobile($mobile) {
        return $this->model->checkField($mobile, 3);
    }

    /**
     * �����û���Ϣ
     * @param int $uid �û�id
     * @param string $password ���룬������֤
     * @param array $data �޸ĵ��ֶ�����
     * @return true �޸ĳɹ���false �޸�ʧ��
     * @author huajie <banhuajie@163.com>
     */
    public function updateInfo($uid, $password, $data) {
        if ($this->model->updateUserFields($uid, $password, $data) !== false) {
            $return['status'] = true;
        } else {
            $return['status'] = false;
            $return['info'] = $this->model->getError();
        }
        return $return;
    }

}
