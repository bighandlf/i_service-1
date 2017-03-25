<?php

class UserController extends \BaseController {

    private $model;

    private $convertor;

    public function init() {
        parent::init();
        $this->model = new UserModel();
        $this->convertor = new Convertor_User();
    }

    /**
     * 获取User列表
     *
     * @return Json
     */
    public function getUserListAction() {
        $param = array();
        $param['name'] = trim($this->getParamList('name'));
        $data = $this->model->getUserList($param);
        $data = $this->convertor->getUserListConvertor($data);
        $this->echoJson($data);
    }

    /**
     * 根据id获取User详情
     *
     * @param
     *            int id 获取详情信息的id
     * @return Json
     */
    public function getUserDetailAction() {
        $id = intval($this->getParamList('id'));
        if ($id) {
            $data = $this->model->getUserDetail($id);
            $data = $this->convertor->getUserDetail($data);
        } else {
            $this->throwException(1, '查询条件错误，id不能为空');
        }
        $this->echoJson($data);
    }

    /**
     * 根据id修改User信息
     *
     * @param
     *            int id 获取详情信息的id
     * @param
     *            array param 需要更新的字段
     * @return Json
     */
    public function updateUserByIdAction() {
        $id = intval($this->getParamList('id'));
        if ($id) {
            $param = array();
            $param['name'] = trim($this->getParamList('name'));
            $data = $this->model->updateUserById($param, $id);
            $data = $this->convertor->commonConvertor($data);
        } else {
            $this->throwException(1, 'id不能为空');
        }
        $this->echoJson($data);
    }

    /**
     * 添加User信息
     *
     * @param
     *            array param 需要新增的信息
     * @return Json
     */
    public function addUserAction() {
        $param = array();
        $param['name'] = trim($this->getParamList('name'));
        $data = $this->model->addUser($param);
        $data = $this->convertor->commonConvertor($data);
        $this->echoJson($data);
    }

    /**
     * 登录
     *
     * @param
     *            string room_no 房间号
     * @param
     *            string fullname 登录姓名
     * @param
     *            int hotelid 物业ID
     * @param
     *            int groupid 集团ID
     * @param
     *            int platform 平台ID
     * @param
     *            string identity 平台标识
     * @param
     *            string lang 语言
     * @return Json
     */
    public function loginAction() {
        $param = array();
        $param['room_no'] = trim($this->getParamList('room_no'));
        $param['fullname'] = trim($this->getParamList('fullname'));
        $param['hotelid'] = intval($this->getParamList('hotelid'));
        $param['groupid'] = intval($this->getParamList('groupid'));
        $param['platform'] = intval($this->getParamList('platform'));
        $param['identity'] = trim($this->getParamList('identity'));
        $param['lang'] = trim($this->getParamList('lang'));
        
        $result = $this->model->loginAction($param);
        $result = $this->convertor->userInfoConvertor($result);
        $this->echoSuccessData($result);
    }

    /**
     * 根据Token获取用户信息
     *
     * @param
     *            string token
     * @return Json
     */
    public function getUserInfoByTokenAction() {
        $token = trim($this->getParamList('token'));
        $userId = Auth_Login::getToken($token);
        if (empty($userId)) {
            $this->throwException(2, 'token验证失败');
        }
        $userInfo = $this->model->getUserDetail($userId);
        $userInfo['token'] = $token;
        $result = $this->convertor->userInfoConvertor($userInfo);
        $this->echoSuccessData($result);
    }

    /**
     * 更新用户语言，切换语言的时候调用
     * 
     * @param
     *            string token
     * @param
     *            int platform 平台ID
     * @param
     *            string identity 平台标识
     * @param
     *            string lang 语言
     * @return Json
     */
    public function updateUserLangAction() {
        $token = trim($this->getParamList('token'));
        $userId = Auth_Login::getToken($token);
        if (empty($userId)) {
            $this->throwException(2, 'token验证失败');
        }
        $param = array();
        $param['platform'] = intval($this->getParamList('platform'));
        $param['identity'] = trim($this->getParamList('identity'));
        $param['language'] = trim($this->getParamList('lang'));
        
        if (empty($param['platform']) || empty($param['identity']) || empty($param['language'])) {
            $this->throwException(3, '入参错误');
        }
        $langNameList = Enum_Lang::getLangNameList();
        if (! $langNameList[$param['language']]) {
            $this->throwException(5, '暂不支持该语言');
        }
        
        $result = $this->model->updateUserById($param, $userId);
        if (! $result) {
            $this->throwException(4, '更新失败');
        }
        $userInfo = $this->model->getUserDetail($userId);
        $userInfo['token'] = $token;
        $result = $this->convertor->userInfoConvertor($userInfo);
        $this->echoSuccessData($result);
    }
}