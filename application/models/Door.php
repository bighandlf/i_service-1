<?php

/**
 * Class DoorModel
 * 门信息管理
 */
class DoorModel extends \BaseModel
{

    private $dao;

    public function __construct()
    {
        parent::__construct();
        $this->dao = new Dao_Door();
    }

    /**
     * 开门
     *
     * @param
     *            array param 
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 
     *            CUSCODE   账号或卡号 
     *            IDCODE    信息登记时证件号码 
     * @return array
     */
    public function openDoor(array $param)
    {
        // TODO: check param
        return $this->dao->openLock($param);
    }
}
