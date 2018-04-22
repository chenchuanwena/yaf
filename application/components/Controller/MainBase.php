<?php
class Controller_MainBase extends Yaf_Controller_Abstract{
    public function is_login() {
        $user_auth= $this->_session->get('user_auth');
        if (empty($user_auth)) {
            return 0;
        } else {
            return  $user_auth['uid'];
        }
    }
    /**
     * ����Ƿ�����Ҫ��̬�жϵ�Ȩ��
     * @return boolean|null
     *      ����true���ʾ��ǰ������Ȩ��
     *      ����false���ʾ��ǰ������Ȩ��
     *      ����null��������checkRule���ݽڵ���Ȩ�ж�Ȩ��
     *
     * @author �´��� 18305954587
     */
    protected function checkDynamic() {
        if (IS_ROOT) {
            return true; //����Ա��������κ�ҳ��
        }
        return null; //����,��checkRule
    }
    /**
     * ��⵱ǰ�û��Ƿ�Ϊ����Ա
     * @return boolean true-����Ա��false-�ǹ���Ա
     * @author �´���
     */
    function is_administrator($uid = null) {
        $uid = is_null($uid) ? $this->is_login() : $uid;
        return $uid && (intval($uid) === USER_ADMINISTRATOR);
    }

    /**
     * Ȩ�޼��
     * @param string  $rule    ���Ĺ���
     * @param string  $mode    checkģʽ
     * @return boolean
     * @author �´���  <wwccw0591@163.com>
     */
    final protected function checkRule($rule, $type =1, $mode = 'url') {
        if (IS_ROOT) {
            return true; //����Ա��������κ�ҳ��
        }
        static $Auth = null;
        if (!$Auth) {
            $Auth=new Auth();
        }
        if (!$Auth->check($rule, UID, $type, $mode)) {
            return false;
        }
        return true;
    }
    public function init(){
        $this->_config = Yaf_Registry::get('config');
        $this->_req = $this->getRequest();
        $this->_session = Yaf_Session::getInstance();
        $this->_session->start();
        define('UID', $this->is_login());
        if (!UID) {
            header('Location:/Index/index/');
        }

         define('IS_ROOT', $this->is_administrator());
        // ������Ȩ��
        $dynamic = $this->checkDynamic(); //��������Ŀ�йصĸ��̬Ȩ��
        if ($dynamic === null) {
            //���Ƕ�̬Ȩ��
            $rule = strtolower($this->_req->module . '/' . $this->_req->controller . '/' . $this->_req->action);
            if (!$this->checkRule($rule, array('in', '1,2'))) {
                $this->error('δ��Ȩ����!');
            }
        } elseif ($dynamic === false) {
            $this->error('δ��Ȩ����!');
        }

        //$this->assign('__MENU__', $this->getMenus($this->_req->controller));
    }

    final public function getMenus($controller = 'main') {
        // $menus  =   session('ADMIN_MENU_LIST'.$controller);

        $mysql = new Db_Mysql ($this->_config->database->config->toArray());
        if (empty($menus)) {
            // ��ȡ���˵�
            $where['pid'] = 0;
            $where['hide'] = 0;

            $menus['main'] =$mysql->select('menu',$where);
            $menus['child'] = array(); //���
            //������
            $like_where=$this->_req->controller.'/'.$this->_req->action;
            $sql="select id from menu where url like '%".$like_where."%'";
            $current = $mysql->queryFirst($sql);
            if ($current) {
                $sql="select id,pid,title from menu where id={$current['id']}";
                $nav = $mysql->queryFirst($sql);
                $nav_first_title = $nav['title'];
                foreach ($menus['main'] as $key => $item) {

                    if (!is_array($item) || empty($item['title']) || empty($item['url'])) {
                        $this->error('����������$menus����Ԫ����������');
                    }

                    if (stripos($item['url'], $this->_req->module) !== 0) {
                        $item['url'] = $this->_req->module . '/' . $item['url'];
                    }


                    if (!IS_ROOT && !$this->checkRule($item['url'], 2, null)) {
                        unset($menus['main'][$key]);
                        continue; //����ѭ��
                    }

                    // ��ȡ��ǰ���˵����Ӳ˵���
                    if ($item['title'] == $nav_first_title) {
                        $menus['main'][$key]['class'] = 'current';
                        //����child��
                        $sql="select distinct `group` from menu where pid={$item['id']}";

                        $groups = $mysql->query($sql);

                        //$groups = M('Menu')->where("pid = {$item['id']}")->distinct(true)->field("`group`")->select();
                        if ($groups) {
                            $groups = array_column($groups, 'group');
                        } else {
                            $groups = array();
                        }

                        //��ȡ��������ĺϷ�url
                        $where = array();
                        $where['pid'] = $item['id'];
                        $where['hide'] = 0;
                        $sql="select id,url from menu where pid={$item['id']} and hide=0" ;
                        $second_us = $mysql->query($sql);
                        $second_urls=array();
                        foreach ($second_us as $key => $to_url) {
                                $second_urls[$to_url['id']]=$to_url['url'];
                        }
                        if (!IS_ROOT) {
                            // ���˵�Ȩ��
                            $to_check_urls = array();
                            foreach ($second_urls as $key => $to_check_url) {
                                if (stripos($to_check_url,$this->_req->module) !== 0) {
                                    $rule = $this->_req->module . '/' . $to_check_url;
                                } else {
                                    $rule = $to_check_url;
                                }
                                if ($this->checkRule($rule, 2, null))
                                    $to_check_urls[] = $to_check_url;
                            }
                        }
                        unset($map);
                        // ���շ��������Ӳ˵���
                        $admin = new LoginModel();
                        $se=$admin->ShowUsers();
                        foreach ($groups as $g) {
                            $map = array('group' => $g);
                            if (isset($to_check_urls)) {
                                if (empty($to_check_urls)) {

                                    continue;
                                } else {
                                    $map['url']="'";
                                    $map['url'] .=implode("','",$to_check_urls);
                                    $map['url'].="'";
                                }
                            }
                            $map['pid'] = $item['id'];
                            $map['hide'] = 0;
                            if(!empty($map['url'])){
                                $sql="SELECT `id`,`pid`,`title`,`url`,`tip` FROM `menu` WHERE ( `group` = '{$g}' ) AND ( `url` IN ({$map['url']}) ) AND ( `pid` = {$map['pid']}  ) AND ( `hide` = 0 ) ORDER BY sort asc";
                            }else{
                                $sql="SELECT `id`,`pid`,`title`,`url`,`tip` FROM `menu` WHERE ( `group` = '{$g}' ) AND ( `pid` = {$map['pid']}  ) AND ( `hide` = 0 ) ORDER BY sort asc";
                            }

                            $menuList = $mysql->query($sql);
                            if($menuList){
                                $menus['child'][$g] = $this->list_to_tree($menuList, 'id', 'pid', 'operater', $item['id']);
                            }
                        }
                        if ($menus['child'] === array()) {

                        }
                    }
                }
            }

        }
        return $menus;
    }
    /**
     * �ѷ��ص����ݼ�ת����Tree
     * @param array $list Ҫת�������ݼ�
     * @param string $pid parent����ֶ�
     * @param string $level level����ֶ�
     * @return array
     * @author �´���
     */
    function list_to_tree($list, $pk = 'id', $pid = 'pid', $child = '_child', $root = 0) {
        // ����Tree
        $tree = array();
        if (is_array($list)) {
            // ����������������������
            $refer = array();
            foreach ($list as $key => $data) {
                $refer[$data[$pk]] = & $list[$key];
            }
            foreach ($list as $key => $data) {
                // �ж��Ƿ����parent
                $parentId = $data[$pid];
                if ($root == $parentId) {
                    $tree[] = & $list[$key];
                } else {
                    if (isset($refer[$parentId])) {
                        $parent = & $refer[$parentId];
                        $parent[$child][] = & $list[$key];
                    }
                }
            }
        }
        return $tree;
    }
    /**
     * ϵͳ�ǳ���MD5���ܷ���
     * @param  string $str Ҫ���ܵ��ַ���
     * @return string
     */
    function think_ucenter_md5($str, $key = 'ThinkUCenter'){
        return '' === $str ? '' : md5(sha1($str) . $key);
    }
    /**
     * reffer��֤
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
     * ���json���
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
     * Ajax��ʽ�������ݵ��ͻ���
     * @access protected
     * @param mixed $data Ҫ���ص�����
     * @param String $type AJAX�������ݸ�ʽ
     * @return void
     */
    protected function ajaxReturn($data,$type='') {

        if(empty($type)) $type  =   DEFAULT_AJAX_RETURN;
        switch (strtoupper($type)){
            case 'JSON' :
                // ����JSON���ݸ�ʽ���ͻ��� ����״̬��Ϣ
                header('Content-Type:application/json; charset=utf-8');
                exit(json_encode($data));
            case 'XML'  :
                // ����xml��ʽ����
                header('Content-Type:text/xml; charset=utf-8');
                exit(xml_encode($data));
            case 'EVAL' :
                // ���ؿ�ִ�е�js�ű�
                header('Content-Type:text/html; charset=utf-8');
                exit($data);
            default     :
                // ������չ�������ظ�ʽ����
                Hook::listen('ajax_return',$data);
        }
    }
    /**
     * Ĭ����ת���� ֧�ִ��������ȷ��ת
     * ����ģ����ʾ Ĭ��ΪpublicĿ¼�����successҳ��
     * ��ʾҳ��Ϊ������ ֧��ģ���ǩ
     * @param string $message ��ʾ��Ϣ
     * @param Boolean $status ״̬
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @access private
     * @return void
     */
    private function dispatchJump($message,$status=1,$jumpUrl='',$ajax=true) {
        if(true === $ajax || IS_AJAX) {// AJAX�ύ
            $data           =   is_array($ajax)?$ajax:array();
            $data['info']   =   $message;
            $data['status'] =   $status;
            $data['url']    =   $jumpUrl;
            $this->ajaxReturn($data);
        }
    }
    /**
     * �����ɹ���ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ��ʾ��Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @return void
     */
    protected function success($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,1,$jumpUrl,$ajax);
    }
    /**
     * ����������ת�Ŀ�ݷ���
     * @access protected
     * @param string $message ������Ϣ
     * @param string $jumpUrl ҳ����ת��ַ
     * @param mixed $ajax �Ƿ�ΪAjax��ʽ ������ʱָ����תʱ��
     * @return void
     */
    protected function error($message='',$jumpUrl='',$ajax=false) {
        $this->dispatchJump($message,0,$jumpUrl,$ajax);
    }
    /**
     * ��� json ��ʽ������
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
