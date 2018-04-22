<?php
class MainController extends Controller_MainBase {
    private $_layout;
    private $_Main;
    public function init(){

		parent::init();
        $this->_layout = new LayoutPlugin('layout.html');

        $this->dispatcher = Yaf_Registry::get("dispatcher");

        $this->dispatcher->registerPlugin($this->_layout);
        $this->_Main = new MemberModel();

        $this->_layout->menus=$this->getMenus($this->_req->controller);
        $this->_layout->username=$this->_session->get('username');
        $this->_view->user_auth=$this->_session->get('user_auth');

    }

    public function indexAction()
    {

          //  $this->getView()->display('main/index.html');
    }

    public function getJsonAction() {
        if($this->_req->isXmlHttpRequest()) {
            $user = $this->_session->get('user_auth');
            $page_size = $this->_req->getPost('rows');
            $page_index = $this->_req->getPost('page');
            $start=($page_index-1)*$page_size;
            $mysql = new Db_Mysql($this->_config->database->config->toArray());
            $retrun_data = $mysql->select('member',null,$page_size,$start);
            $returnObj = array("total" => 0, "rows" => null);
            $returnObj["total"] = $mysql->getCount('member');
            $returnObj["rows"] = $retrun_data;
            echo json_encode($returnObj);
            exit;
        }
    }

    /**
     * �û����
     */
    public function AddAction() {
        if ($this->_req->isXmlHttpRequest()) {

            $Posts = $this->_req->getPost();
            $Posts['password'] =$this->think_ucenter_md5($Posts['password'], UC_AUTH_KEY);
            $Posts['repassword'] = $this->think_ucenter_md5($Posts['repassword'], UC_AUTH_KEY);

            foreach ($Posts as $v) {
                if (empty($v)) {
                    $this->show_json(array('status'=>0,'message'=>'input empty'));
                }
            }
            if ($Posts['password'] != $Posts['repassword']) {
                $this->show_json(array('status'=>0,'message'=>'twice password is no match'));
            }
            unset($Posts['repassword']);
            $Posts['status'] = 1;
            if (preg_match("/^(13[0-9]|15[0|3|6|7|8|9]|18[0-9])\d{8}$/", $Posts['username'])) {
                   try{
                       $User = new LoginModel();
                       $uid = $User->AddUsr($Posts);

                       if (0 < $uid) { //ע��ɹ�

                           $mem = array('uid' => $uid, 'nickname' => $Posts['username'], 'name' =>  $Posts['name'], 'status' => 1);
                           if ($this->_Main->AddUsr($mem)) {
                               $this->success('add success','/Main/index');
                           } else {

                           }
                       } else { //ע��ʧ�ܣ���ʾ������Ϣ
                           $this->error('register fail');
                       }
                   }
                   catch(Exception $e){
                       $this->error($e->getMessage());
                   }

            } else {
                $this->error('please input correct telphone number');
            }
        } else {
            $this->_layout->add = true;
        }
    }
    /**
     * �༭�û���Ϣ
     */
    public function EditAction() {
        if ($this->_req->isXmlHttpRequest()) {

            $Posts = $this->_req->getPost();
            $id = $Posts['uid'];
            unset($Posts['uid']);

            foreach ($Posts as $k => $v) {
                if ($k == 'password' || $k == 'repassword') {
                    unset($Posts[$k]);
                } else {
                    if ($v=='') {
                        $this->show_json(array('status'=>0,'message'=>'input error'));
                    }
                }
            }
            if ($this->_Main->EditUsr($id, $Posts)) {
               $this->success('update success','/Main/index/');
            } else {
                $this->error('update fail');
            }
        }
        //��ȡ�û���Ϣ
        $id = $this->_req->getQuery('id');
        //��ȡ�û���Ϣ
        $userinfo = $this->_Main->GetUsrInfo($id);
        $this->_view->assign('info',$userinfo);

    }
    /**
     * ɾ���û�
     */
    public function DelAction() {
        $id = $this->_req->getPost('id');
        if ($this->_Admin->Del($id)) {
            $this->show_json(array('errno'=>0,'errmsg'=>'ɾ���ɹ�'));
        } else {
            $this->show_json(array('errno'=>101,'errmsg'=>'ɾ��ʧ��'));
        }
    }
    /**
     * �û��޸�����
     */
    public function PasswdAction() {
        $this->_layout->meta_title = '�޸�����';
    }

    /**
     * ��Ա״̬�޸�
     * @author �´��� 18305954587
     */
    public function changeStatusAction() {
        $method= $this->_req->getQuery('method');
        $id = $this->_req->getQuery('id');
        if ( $id==1) {
            $this->error("������Գ�������Աִ�иò���!");
        }
        if (empty($id)) {
            $this->error('��ѡ��Ҫ����������!');
        }
        switch (strtolower($method)) {
            case 'forbiduser':
                $this->update(0, $id);
                break;
            case 'resumeuser':
                $this->update(1,  $id);
                break;
            case 'deleteuser':
                $this->update(-1, $id);
                break;
            default:
                $this->update('�����Ƿ�');
        }
    }
    public function update($status,$id) {
        $Posts['status']=$status;
        if ($this->_Main->EditUsr($id, $Posts)) {
            $this->success('update success');
        } else {
            $this->error('update fail');
        }
    }


}

