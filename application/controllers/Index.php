<?php
class IndexController extends Controller_Base {

    private $_layout;

    public function init(){
        parent::init();
        //使用layout页面布局
        $this->_layout = new LayoutPlugin('layout.html', APP_PATH . '/views/layout/');
    }
    /*首页展示*/
    public function IndexAction()
    {
        $ses=$this->_session->get('username');

        if($ses){

            header('Location:/main/index/');
        }else{
            if($this->_req->isXmlHttpRequest()){

                //获取post提交的参数
               // $User = new UserModel();
                $name = $this->_req->getPost('username');

                $pwd = $this->_req->getPost('password');
                $login = new LoginModel();
                $ucenter_member=$login->LoginUsr($name,$this->think_ucenter_md5($pwd, UC_AUTH_KEY));

                if($ucenter_member){

                    /* 登录用户 */
                    $Member = new MemberModel();

                    $user_auth=$Member->login($ucenter_member['id']);

                    if($user_auth){ //登录用户

                        $this->_session->set('username',$ucenter_member['username']);
                        $this->_session->set('user_auth',$user_auth);


                        $this->success('登陆成功','/main/index/');
                    } else {
                        $this->error('登陆失败');
                    }
                }else{
                    $this->error('用户名或密码错');
                }
            }

        }

    }

    /*用户登录*/
    public function LogoutAction()
    {
		$this->_session->__unset('username');
        $this->_session->__unset('user_auth');
        header('Location:/index/');
    }
}
