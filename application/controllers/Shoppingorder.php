<?php

/**
 * 体验购物订单控制器类
 *
 */
class ShoppingOrderController extends \BaseController {

    /**
     *
     * @var ShoppingOrderModel
     */
    private $model;

    /**
     *
     * @var Convertor_ShoppingOrder
     */
    private $convertor;

    public function init() {
        parent::init();
        $this->model = new ShoppingOrderModel ();
        $this->convertor = new Convertor_ShoppingOrder ();
    }

    /**
     * 获取体验购物订单列表
     *
     * @return Json
     */
    public function getShoppingOrderListAction() {
        $param = array();
        $param ['hotelid'] = intval($this->getParamList('hotelid'));
        $adminId = Auth_Login::getToken($this->getParamList('token'), 2);
        if (empty ($adminId)) {
            $this->throwException(3, 'token验证失败');
        }
        if (empty ($param ['hotelid'])) {
            $this->throwException(2, '物业Id错误');
        }
        $param['status'] = [Enum_ShoppingOrder::ORDER_STATUS_WAIT, Enum_ShoppingOrder::ORDER_STATUS_SERVICE];

        $this->getPageParam($param);
        $list = $this->model->getShoppingOrderList($param);
        $count = $this->model->getShoppingOrderCount($param);
        $data = $this->convertor->getShoppingOrderListConvertor($list, $count, $param);
        $this->echoSuccessData($data);
    }

    /**
     * 后台体验购物订单列表查询
     *
     * @return json
     */
    public function getOrderListAction() {
        $param = array();
        $param ['page'] = intval($this->getParamList('page'));
        $param ['limit'] = intval($this->getParamList('limit', 5));
        $param ['id'] = intval($this->getParamList('id'));
        $param ['hotelid'] = intval($this->getParamList('hotelid'));
        $param ['shoppingid'] = intval($this->getParamList('shoppingid'));
        $param ['userid'] = intval($this->getParamList('userid'));
        $param['status'] = $this->getParamList('status');
        $data = $this->model->getShoppingOrderList($param);
        $count = $this->model->getShoppingOrderCount($param);
        $data = $this->convertor->getOrderListConvertor($data, $count, $param);
        $this->echoSuccessData($data);
    }

    /**
     * Output json for filter of the order list
     */
    public function getOrderFilterListAction(){
        $param = array();
        $param ['hotelid'] = intval($this->getParamList('hotelid'));
        $usersList = $this->model->getShoppingOrderFilterList($param);
        $usersList = array_column($usersList, 'room_no','userid');
        $statusList = Enum_ShoppingOrder::getStatusNameList();

        $data['userlist'] = $usersList;
        $data['statuslist'] = $statusList;
        $this->echoSuccessData($data);
    }


    /**
     * 根据id获取体验购物订单详情
     *
     * @param
     *            int id 获取详情信息的id
     * @return Json
     */
    public function getShoppingOrderDetailAction() {
        $id = intval($this->getParamList('id'));
        if ($id) {
            $data = $this->model->getShoppingOrderDetail($id);
            $data = $this->convertor->getShoppingOrderDetail($data);
        } else {
            $this->throwException(1, '查询条件错误，id不能为空');
        }
        $this->echoSuccessData($data);
    }

    /**
     * 根据id修改体验购物订单信息
     *
     * @param
     *            int id 获取详情信息的id
     * @param
     *            array param 需要更新的字段
     * @return Json
     */
    public function updateShoppingOrderByIdAction() {
        $id = intval($this->getParamList('id'));
        if ($id) {
            $param = array();
            $param ['name'] = trim($this->getParamList('name'));
            $data = $this->model->updateShoppingOrderById($param, $id);
            $data = $this->convertor->commonConvertor($data);
        } else {
            $this->throwException(1, 'id不能为空');
        }
        $this->echoJson($data);
    }

    /**
     * 添加体验购物订单信息
     *
     * @param
     *            array param 需要新增的信息
     * @return Json
     */
    public function addShoppingOrderAction() {
        $param = array();
        $param ['count'] = trim($this->getParamList('count'));
        $param ['shoppingid'] = intval($this->getParamList('shoppingid'));
        $param ['hotelid'] = intval($this->getParamList('hotelid'));
        if (empty ($param ['count']) || empty ($param ['shoppingid']) || empty ($param ['hotelid'])) {
            $this->throwException(2, '入参错误');
        }
        $token = trim($this->getParamList('token'));
        $param ['userid'] = Auth_Login::getToken($token);
        if (empty ($param ['userid'])) {
            $this->throwException(3, '登录验证失败');
        }
        //        $checkOrder = $this->model->getShoppingOrderList(array('shoppingid' => $param ['shoppingid'], 'hotelid' => $param ['hotelid'], 'userid' => $param ['userid'], 'status' => array(Enum_ShoppingOrder::ORDER_STATUS_WAIT, Enum_ShoppingOrder::ORDER_STATUS_SERVICE)));
        //        if (count($checkOrder) > 0) {
        //            $this->throwException(4, '已经存在有效订单，请不要重复提交');
        //        }
        $data = $this->model->addShoppingOrder($param);
        if (!$data) {
            $this->throwException(5, '提交失败');
        }
        if ($data) {
            $pushParams['cn_title'] = '您有一笔新体验购物订单';
            $pushParams['cn_value'] = '点击查看订单详情';
            $pushParams['en_title'] = 'You have a new shopping order';
            $pushParams['en_value'] = 'Click to check the order';
            $pushParams['type'] = Enum_Push::PUSH_TYPE_STAFF;
            $pushParams['contentType'] = Enum_Push::PUSH_CONTENT_TYPE_SHOPPING_ORDER;
            $pushParams['contentValue'] = $data;
            $pushModel = new PushModel();

            $pushStaffIds = Enum_ShoppingOrder::getOrderPushStaffIdList($param['hotelid']);
            if ($pushStaffIds) {
                $staffModel = new StaffModel();
                $staffInfoList = $staffModel->getStaffList(array('staffid' => $pushStaffIds));
                foreach ($staffInfoList as $staffInfo) {
                    $pushParams['dataid'] = $staffInfo['id'];
                    $pushModel->addPushOne($pushParams);
                }
            }
        }
        $this->echoSuccessData(array('orderId' => $data));
    }

    /**
     * 修改订单状态
     */
    public function changeOrderStatusAction() {
        $param = array();
        $param ['id'] = intval($this->getParamList('orderid'));
        $param ['status'] = intval($this->getParamList('status'));
        $param ['userid'] = Auth_Login::getToken($this->getParamList('token'), 2);
        if (empty ($param ['id'])) {
            $this->throwException(2, '订单ID错误');
        }
        if (empty ($param ['userid'])) {
            $this->throwException(3, 'token验证失败');
        }
        if (!Enum_ShoppingOrder::getStatusNameList()[$param ['status']]) {
            $this->throwException(2, '订单状态错误');
        }
        // 验证订单信息
        $orderInfo = $this->model->getShoppingOrderDetail($param ['id']);
        if (empty ($orderInfo ['id'])) {
            $this->throwException(4, '订单信息错误');
        }
        if ($orderInfo ['status'] >= $param ['status']) {
            $this->throwException(6, '订单状态不可改变');
        }
        $result = $this->model->updateShoppingOrderById(array('status' => $param ['status'], 'adminid' => $param ['userid']), $param ['id']);
        if (!$result) {
            $this->throwException(5, '修改失败');
        }
        $orderInfo ['status'] = $param ['status'];
        $orderInfo ['adminid'] = $param ['userid'];
        $this->echoSuccessData($orderInfo);
    }
}
