<?php

class UserModel extends \BaseModel {

    private $dao;

    public function __construct() {
        parent::__construct();
        $this->dao = new Dao_User();
    }

    /**
     * 获取User列表信息
     *
     * @param
     *            array param 查询条件
     * @return array
     */
    public function getUserList(array $param) {
        isset($param['oid']) ? $paramList['oid'] = trim($param['oid']) : false;
        $paramList['limit'] = $param['limit'];
        $paramList['page'] = $param['page'];
        return $this->dao->getUserList($paramList);
    }

    /**
     * 根据id查询User信息
     *
     * @param
     *            int id 查询的主键
     * @return array
     */
    public function getUserDetail($id) {
        $result = array();
        if ($id) {
            $result = $this->dao->getUserDetail($id);
        }
        return $result;
    }

    /**
     * 根据oid查询User信息
     *
     * @param
     *            int id 查询的主键
     * @return array
     */
    public function getUserDetailByOId($oid) {
        $result = array();
        if ($oid) {
            $result = $this->dao->getUserDetailByOId($oid);
        }
        return $result;
    }

    /**
     * 根据id更新User信息
     *
     * @param
     *            array param 需要更新的信息
     * @param
     *            int id 主键
     * @return array
     */
    public function updateUserById($param, $id) {
        $result = false;
        // 自行添加要更新的字段,以下是age字段是样例
        if ($id) {
            $info['room_no'] = $param['room_no'];
            $info['hotelid'] = intval($param['hotelid']);
            $info['groupid'] = intval($param['groupid']);
            $info['fullname'] = strval($param['fullname']);
            $info['lastlogintime'] = time();
            $info['lastloginip'] = Util_Tools::ipton(Util_Http::getIP());
            $info['platform'] = intval($param['platform']);
            $info['identity'] = $param['identity'];
            $info['language'] = $param['language'];
            $result = $this->dao->updateUserById($info, $id);
        }
        return $result;
    }

    /**
     * User新增信息
     *
     * @param
     *            array param 需要增加的信息
     * @return array
     */
    public function addUser($param) {
        $info['room_no'] = $param['room_no'];
        $info['hotelid'] = intval($param['hotelid']);
        $info['groupid'] = intval($param['groupid']);
        $info['oid'] = strval($param['oid']);
        $info['fullname'] = strval($param['fullname']);
        $info['createtime'] = time();
        $info['lastlogintime'] = time();
        $info['lastloginip'] = Util_Tools::ipton(Util_Http::getIP());
        $info['platform'] = intval($param['platform']);
        $info['identity'] = $param['identity'];
        $info['language'] = $param['language'];
        return $this->dao->addUser($info);
    }

    /**
     * 获取用Oid信息，跟gsm接口交互
     *
     * @param
     *            array param 需要增加的信息
     * @return array
     */
    public function getOIdInfo($param) {
        return array(
            'oId' => md5(1)
        );
    }

    /**
     * 登录
     *
     * @param array $param            
     * @return array
     */
    public function loginAction($param) {
        if (empty($param['room_no']) || empty($param['fullname'])) {
            $this->throwException('登录信息不正确', 2);
        }
        if (empty($param['hotelid']) || empty($param['groupid'])) {
            $this->throwException('酒店集团信息不正确', 3);
        }
        
        if ($param['lang']) {
            $langNameList = Enum_Lang::getLangNameList();
            if (! $langNameList[$param['lang']]) {
                $this->throwException('暂不支持该语言', 6);
            }
        }
        
        // 获取Oid
        $oIdInfo = $this->getOIdInfo($param);
        if (empty($oIdInfo['oId'])) {
            $this->throwException('房间号和名称错误，登录失败', 4);
        }
        
        // 获取用户信息
        $getUserInfo = $this->getUserDetailByOId($oIdInfo['oId']);
        $userId = $getUserInfo['id'];
        
        $newUserInfo = array(
            'hotelid' => $param['hotelid'],
            'groupid' => $param['groupid'],
            'room_no' => $param['room_no'],
            'fullname' => $param['fullname'],
            'platform' => intval($param['platform']),
            'identity' => trim($param['identity']),
            'language' => trim($param['lang'])
        );
        
        // 入住记录数据
        $userHistoryModel = new UserHistoryModel();
        $historyInfo = array(
            'hotelid' => $param['hotelid'],
            'groupid' => $param['groupid']
        );
        if ($userId) {
            // 更新用户数据
            if (! $this->updateUserById($newUserInfo, $userId)) {
                $this->throwException('登录失败，请重试', 5);
            }
            if ($param['hotelid'] != $getUserInfo['hotelid']) {
                $historyInfo['userid'] = $userId;
                $userHistoryModel->addUserHistory($historyInfo);
            }
        } else {
            // 新建用户
            $newUserInfo['oid'] = $oIdInfo['oId'];
            $userId = $this->addUser($newUserInfo);
            if (! $userId) {
                $this->throwException('登录失败，请重试', 5);
            }
            $historyInfo['userid'] = $userId;
            $userHistoryModel->addUserHistory($historyInfo);
        }
        $userInfo = $this->getUserDetail($userId);
        $userInfo['token'] = Auth_Login::makeToken($userId);
        return $userInfo;
    }
}