<?php

/**
 * 酒店开门控制器类
 *
 */
class DoorController extends \BaseController
{

    /**
     *
     * @var DoorModel
     */
    private $model;


    public function init()
    {
        parent::init();
        $this->model = new DoorModel();
    }

    /**
     * 开门
     * http://devservice.easyiservice.com/Door/openDoor?ROOMCODE=101&CUSCODE=BAC37B30&IDCODE=2345
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 101
     *            CUSCODE   账号或卡号                       BAC37B30
     *            IDCODE    信息登记时证件号码                2345
     * @return Json
     */
    public function openDoorAction()
    {
        $param = array();
        $param['ROOMCODE'] = intval($this->getParamList('ROOMCODE'));
        $param['CUSCODE'] = intval($this->getParamList('CUSCODE'));
        $param['IDCODE'] = intval($this->getParamList('IDCODE'));

        $data = $this->model->openDoor($param);
        
        print_r($data);
    }
}
