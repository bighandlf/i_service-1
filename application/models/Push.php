<?php

class PushModel extends \BaseModel {

    private $dao;

    public function __construct() {
        parent::__construct();
        $this->dao = new Dao_Push();
    }

    /**
     * 获取Push列表信息
     *
     * @param
     *            array param 查询条件
     * @return array
     */
    public function getPushList(array $param) {
        $param['id'] ? $paramList['id'] = $param['id'] : false;
        $param['type'] ? $paramList['type'] = $param['type'] : false;
        $param['dataid'] ? $paramList['dataid'] = $param['dataid'] : false;
        isset($param['result']) ? $paramList['result'] = intval($param['result']) : false;
        isset($param['platform']) ? $paramList['platform'] = intval($param['platform']) : false;
        $paramList['limit'] = $param['limit'];
        $paramList['page'] = $param['page'];
        return $this->dao->getPushList($paramList);
    }

    /**
     * 获取Push数量
     *
     * @param
     *            array param 查询条件
     * @return array
     */
    public function getPushCount(array $param) {
        $paramList = array();
        $param['id'] ? $paramList['id'] = $param['id'] : false;
        $param['type'] ? $paramList['type'] = $param['type'] : false;
        $param['dataid'] ? $paramList['dataid'] = $param['dataid'] : false;
        isset($param['result']) ? $paramList['result'] = intval($param['result']) : false;
        isset($param['platform']) ? $paramList['platform'] = intval($param['platform']) : false;
        return $this->dao->getPushCount($paramList);
    }

    /**
     * 根据id查询Push信息
     *
     * @param
     *            int id 查询的主键
     * @return array
     */
    public function getPushDetail($id) {
        $result = array();
        if ($id) {
            $result = $this->dao->getPushDetail($id);
        }
        return $result;
    }

    /**
     * 根据id更新Push信息
     *
     * @param
     *            array param 需要更新的信息
     * @param
     *            int id 主键
     * @return array
     */
    public function updatePushById($param, $id) {
        $result = false;
        // 自行添加要更新的字段,以下是age字段是样例
        if ($id) {
            $info['age'] = intval($param['age']);
            $result = $this->dao->updatePushById($info, $id);
        }
        return $result;
    }

    /**
     * Push新增信息
     *
     * @param
     *            array param 需要增加的信息
     * @return array
     */
    public function addPushOne($param) {
        // 判断参数错误
        $info['type'] = intval($param['type']);
        if (empty($info['type'])) {
            $this->throwException('推送类型错误', 3);
        }
        $info['dataid'] = intval($param['dataid']);
        $info['platform'] = $param['platform'];
        $info['cn_title'] = $param['cn_title'];
        $info['cn_value'] = $param['cn_value'];
        $info['en_title'] = $param['en_title'];
        $info['en_value'] = $param['en_value'];
        if (empty($info['cn_value']) && empty($info['en_value'])) {
            $this->throwException('推送内容错误', 4);
        }

        $info['url'] = $param['url'];
        if (empty($info['url'])) {
            $this->throwException('推送URL错误', 5);
        }

        $pushParams = array();
        $pushParams['url'] = $info['url'];
        switch ($param['type']) {
            case Enum_Push::PUSH_TYPE_ALL :
                $pushParams['phoneType'] = $info['platform'];
                $pushParams['type'] = Enum_Push::PUSH_TYPE_ALL;
                $pushParams['cnTitle'] = $info['cn_title'];
                $pushParams['cnValue'] = $info['cn_value'];
                $pushParams['enTitle'] = $info['en_title'];
                $pushParams['enValue'] = $info['en_value'];
                break;
            case Enum_Push::PUSH_TYPE_USER :
                if (empty($info['dataid'])) {
                    $this->throwException('推送ID错误', 4);
                }
                $pushParams['type'] = Enum_Push::PUSH_TYPE_ALIAS;
                $userModel = new UserModel();
                $userInfo = $userModel->getUserDetail($info['dataid']);
                if (empty($userInfo['platform'])) {
                    break;
                }
                $info['platform'] = $pushParams['phoneType'] = $userInfo['platform'];
                $pushParams['dataid'] = Enum_Push::PUSH_ALIAS_USER_PREFIX . $userInfo['id'];
                if ($userInfo['language'] == 'zh') {
                    $pushParams['title'] = $info['cn_title'];
                    $pushParams['value'] = $info['cn_value'];
                } else {
                    $pushParams['title'] = $info['en_title'];
                    $pushParams['value'] = $info['en_value'];
                }
                break;
            case Enum_Push::PUSH_TYPE_STAFF :
                if (empty($info['dataid'])) {
                    $this->throwException('推送ID错误', 4);
                }
                $pushParams['type'] = Enum_Push::PUSH_TYPE_ALIAS;
                $staffModel = new StaffModel();
                $staffInfo = $staffModel->getStaffDetail($info['dataid']);
                if (empty($staffInfo['platform'])) {
                    break;
                }
                $info['platform'] = $pushParams['phoneType'] = $staffInfo['platform'];
                $pushParams['dataid'] = Enum_Push::PUSH_ALIAS_STAFF_PREFIX . $staffInfo['id'];
                $pushParams['title'] = $info['cn_title'];
                $pushParams['value'] = $info['cn_value'];
                break;
            case Enum_Push::PUSH_TYPE_HOTEL :
                if (empty($info['dataid'])) {
                    $this->throwException('推送ID错误', 4);
                }
                $pushParams['phoneType'] = $info['platform'];
                $pushParams['cnTitle'] = $info['cn_title'];
                $pushParams['cnValue'] = $info['cn_value'];
                $pushParams['enTitle'] = $info['en_title'];
                $pushParams['enValue'] = $info['en_value'];
                $pushParams['hotelId'] = $info['dataid'];
                $pushParams['type'] = Enum_Push::PUSH_TYPE_TAG;
                break;
            case Enum_Push::PUSH_TYPE_GROUP :
                if (empty($info['dataid'])) {
                    $this->throwException('推送ID错误', 4);
                }
                $pushParams['phoneType'] = $info['platform'];
                $pushParams['cnTitle'] = $info['cn_title'];
                $pushParams['cnValue'] = $info['cn_value'];
                $pushParams['enTitle'] = $info['en_title'];
                $pushParams['enValue'] = $info['en_value'];
                $pushParams['groupId'] = $info['dataid'];
                $pushParams['type'] = Enum_Push::PUSH_TYPE_TAG;
                break;
        }

        $pushResult = $this->pushMsg($pushParams);
        $info['createtime'] = time();
        $info['result'] = $pushResult ? 1 : 0;
        $this->dao->addPush($info);
        return $pushResult;
    }

    /**
     * 推送消息
     *
     * @param
     *            array param 推送消息信息
     *            $return boolean
     */
    public function pushMsg($param) {
        return true;
        $config = Enum_Push::getConfig('umeng');
        $push = new Push_Push(
            array(
                'android' => $config['android']['appKey'],
                'ios' => $config['ios']['appKey'],
            ),
            array(
                'android' => $config['android']['secretKey'],
                'ios' => $config['ios']['secretKey'],
            )
        );

        $info = array();
        $pushResult = false;
        switch ($param['type']) {
            case Enum_Push::PUSH_TYPE_ALL :
                $info['tag'] == Enum_Push::PUSH_TAG_LANG_CN;
                $info['title'] = $param['cnTitle'];
                $info['value'] = $param['cnValue'];
                $info['phoneType'] = $param['phoneType'];
                $info['url'] = $param['url'];
                $pushResult = $push->pushAll($info); //推送中文标签全量
                $info['tag'] == Enum_Push::PUSH_TAG_LANG_EN;
                $info['title'] = $param['enTitle'];
                $info['value'] = $param['enValue'];
                $pushResult = $push->pushAll($info);//推送英文标签全量
                break;
            case Enum_Push::PUSH_TYPE_ALIAS :
                $dataId = array_unique(array_filter(explode(",", $param['dataid'])));
                if (!count($dataId)) {
                    $this->throwException('推送数据ID错误', 2);
                }
                $info['alias'] = implode(',', $dataId);
                $info['title'] = $param['title'];
                $info['url'] = $param['url'];
                $info['value'] = $param['value'];
                $info['phoneType'] = $param['phoneType'];
                $pushResult = $push->pushAlias($info);
                break;
            case Enum_Push::PUSH_TYPE_TAG : //tag推送
                $dataId = array_unique(array_filter(explode(",", $param['dataid'])));
                if (count($dataId) > 0) {
                    $info['alias'] = implode(',', $dataId);
                    $info['alias_type'] = 'customizedcast';
                }
                $param['hotelId'] ? $info['tag'][] = Enum_Push::PUSH_TAG_HOTEL_PREFIX . $param['hotelId'] : false;
                $param['groupId'] ? $info['tag'][] = Enum_Push::PUSH_TAG_GROUP_PREFIX . $param['groupId'] : false;
                $info['title'] = $param['cnTitle'];
                $info['value'] = $param['cnValue'];
                $info['url'] = $param['url'];
                $info['phoneType'] = $param['phoneType'];
                $info['tag'][] = Enum_Push::PUSH_TAG_LANG_CN;
                $pushResult = $push->pushTag($info);
                $info['title'] = $param['enTitle'];
                $info['value'] = $param['enValue'];
                array_pop($info['tag']);
                $info['tag'][] = Enum_Push::PUSH_TAG_LANG_EN;
                $pushResult = $push->pushTag($info);
                break;
        }
        return $pushResult;
    }

    /**
     * GSM推送
     * @param $paramList
     * @return array
     */
    public function pushGsmMsg($paramList) {
        $pushParams = array();
        $pushParams['cn_title'] = $paramList['cn_title'];
        $pushParams['cn_value'] = $paramList['cn_value'];
        $pushParams['en_title'] = $paramList['en_title'];
        $pushParams['en_value'] = $paramList['en_value'];
        $urlCode = $paramList['url_code'];

        $dataIdList = explode(",", $paramList['dataid']);
        if (empty($dataIdList)) {
            $this->throwException('用户ID无效', 3);
        }

        $sendSuccessIdList = array();
        switch ($paramList['type']) {
            case 1://用户ID推送
                $hotelModel = new HotelListModel();
                $userModel = new UserModel();
                $userInfoList = $userModel->getUserList(array('oid' => $dataIdList));
                //获取对应物业的propertyinterfid
                $hotelIdList = array_column($userInfoList, 'hotelid');
                $hotelInfoList = $hotelModel->getHotelListList(array('id' => $hotelIdList));
                $propertyinterfIdList = array_column($hotelInfoList, 'propertyinterfid', 'id');
                foreach ($userInfoList as $userOne) {
                    if ($userOne['platform']) {
                        $redirectParams = array(
                            'OrderID' => $urlCode,
                            'PropertyInterfID' => $propertyinterfIdList[$userOne['hotelid']],
                            'CustomerID' => $userOne['oid'],
                            'Room' => $userOne['room_no'],
                            'LastName' => $userOne['fullname']
                        );
                        $pushParams['type'] = Enum_Push::PUSH_TYPE_USER;
                        $pushParams['url'] = $userModel->getGsmRedirect($redirectParams);
                        $pushParams['dataid'] = $userOne['id'];
                        if ($this->addPushOne($pushParams)) {
                            $sendSuccessIdList[] = $userOne['oid'];
                        }
                    }
                }
                break;
            case 2://员工ID推送
                $staffModel = new StaffModel();
                $staffInfoList = $staffModel->getStaffList(array('staffid' => $dataIdList));
                foreach ($staffInfoList as $staffOne) {
                    if ($staffOne['platform']) {
                        $redirectParams = array(
                            'OrderID' => $urlCode,
                            'StaffID' => $staffOne['staffid']
                        );
                        $pushParams['type'] = Enum_Push::PUSH_TYPE_STAFF;
                        $pushParams['url'] = $staffModel->getGsmRedirect($redirectParams);
                        $pushParams['dataid'] = $staffOne['id'];
                        if ($this->addPushOne($pushParams)) {
                            $sendSuccessIdList[] = $staffOne['staffid'];
                        }
                    }
                }
                break;

        }
        return $sendSuccessIdList;
    }
}
