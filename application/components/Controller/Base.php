<?php
class Controller_Base extends Yaf_Controller_Abstract{
   public function is_login() {
       $user_auth= $this->_session->get('user_auth');
        if (empty($user_auth)) {
            return 0;
        } else {
            return  $user_auth['uid'];
        }
    }
    public function init(){
        $this->_config = Yaf_Registry::get('config');
        $this->_req = $this->getRequest();
        $this->_session = Yaf_Session::getInstance();
        $this->_session->start();
        // 获取当前用户ID

//        /* 读取数据库中的配置 */
//        $config = S('DB_CONFIG_DATA');
//        if (!$config) {
//            $config = api('Config/lists');
//            S('DB_CONFIG_DATA', $config);
//        }
//        C($config); //添加配置
//        // 是否是超级管理员
//        define('IS_ROOT', is_administrator());
//        if (!IS_ROOT && C('ADMIN_ALLOW_IP')) {
//            // 检查IP地址访问
//            if (!in_array(get_client_ip(), explode(',', C('ADMIN_ALLOW_IP')))) {
//                $this->error('403:禁止访问');
//            }
//        }
//        // 检测访问权限
//        $access = $this->accessControl();
//        if ($access === false) {
//            $this->error('403:禁止访问');
//        } elseif ($access === null) {
//            $dynamic = $this->checkDynamic(); //检测分类栏目有关的各项动态权限
//            if ($dynamic === null) {
//                //检测非动态权限
//                $rule = strtolower(MODULE_NAME . '/' . CONTROLLER_NAME . '/' . ACTION_NAME);
//                if (!$this->checkRule($rule, array('in', '1,2'))) {
//                    $this->error('未授权访问!');
//                }
//            } elseif ($dynamic === false) {
//                $this->error('未授权访问!');
//            }
//        }
//        $auth_user = session("user_auth");
//        $this->server_station_id = (int) $auth_user["server_station_id"];
//        $this->assign('__MENU__', $this->getMenus());
    }
    /**
     * 系统非常规MD5加密方法
     * @param  string $str 要加密的字符串
     * @return string
     */
    function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
    /**
     * reffer验证
     * @return boolean
     */
    public function checkReffer($whiteList = array()) {
        $check = new checkReffer();
        if (!$check->check_referer($whiteList)) {
            $result = array("errno" => 5000, "errmsg" => "Error: unaccepted request! Wrong Referer!");
            $callback = isset($_GET['callback']) ? trim($_GET['callback']) : false;
            $this->show_json($result, $callback);
        }
    }    
    /**
     * 输出json结果
     * @param array $data
     * @param string $callback
     */
    public function show_json($data, $callback = false) {
        header("Content-type: application/json;charset=utf-8");
        $callback = isset($_GET['callback']) ? trim($_GET['callback']) : false;
        if ($callback) {
            echo $callback . "(" . json_encode($data) . ")";
        } else {
            echo json_encode($data);
        }
        exit;
    }
    /**
     * Ajax方式返回数据到客户端
     * @access protected
     * @param mixed $data 要返回的数据
     * @param String $type AJAX返回数据格式
     * @return void
     */
    protected function ajaxReturn($data,$type='') {
        if(empty($type)) $type  =   DEFAULT_AJAX_RETURN;
        switch (strtoupper($type)){
            case 'JSON' :
                // 返回JSON数据格式到客户端 包含状态信息
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // 返回xml格式数据
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'EVAL' :
                // 返回可执行的js脚本
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // 用于扩展其他返回格式数据
                Hook::listen('ajax_return',$data);
        }
    }
    /**
     * 默认跳转操作 支持错误导向和正确跳转
     * 调用模板显示 默认为public目录下面的success页面
     * 提示页面为可配置 支持模板标签
     * @param string $message 提示信息
     * @param Boolean $status 状态
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=true) {
        if(true === $ajax || IS_AJAX) {// AJAX提交
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
    }
    /**
     * 操作成功跳转的快捷方法
     * @access protected
     * @param string $message 提示信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }
    /**
     * 操作错误跳转的快捷方法
     * @access protected
     * @param string $message 错误信息
     * @param string $jumpUrl 页面跳转地址
     * @param mixed $ajax 是否为Ajax方式 当数字时指定跳转时间
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }
    /**
     * 输出 json 格式的数据 
     */
    public function renderJson($data, $encoding = 'utf-8') {
        header("Content-type: application/json;charset=$encoding");
        echo json_encode($data);
        exit;
    }

    public function renderSuccessJson($data = null, $encoding = 'utf-8') {
        $out = array('errno' => 0, 'errmsg' => 'ok');
        if ($data) {
            $out = array_merge($out, $data);
        }
        return $this->renderJson($out, $encoding);
    }
}
