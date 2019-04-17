<?php

/**
 * 酒店开门数据层
 */
class Dao_Door extends Dao_Base
{

    private $access_key = "BWRCUHIS";


    public function __construct()
    {
        parent::__construct();

        // TODO set access key

    }



    /**
     * 查询房间并获取门锁信息
     *
     * @param
     *            array 入参
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 
     *            CUSCODE   账号或卡号 
     *            IDCODE    信息登记时证件号码 
     * @return string
     */

    public function openLock(array $param)
    {


        $value = 'Door has been open succ';


        /*
        *
        *
        *GET ROOM INFO
        *
        *
        */
        // get xml request
        $room_info_request_xml = $this->getRoomInfoRequestXML($param);
        // get room info
        $room_info = $this->sendRequest($room_info_request_xml);


        // get lock code from room info
        // TODO: add validation for xml
        $room_info_xml = simplexml_load_string($room_info);
        $lock_code = $room_info_xml->SVCHEAD->ROOMLIST;

        // print_r($lock_code);


        //if (!isset($lock_code)) {
        //print_r('<br />');

        $lock_code = '010101';
        // }


        $lock_code = '010101';

        $param['LOCKCODE'] = $lock_code;

        /*
        *
        *
        * OPEN LOCK
        *
        *
        */
        // get open lock request
        $lock_open_request_xml = $this->getOpenLockRequestXML($param);

        // send request 
        $lock_info = $this->sendRequest($lock_open_request_xml);

        // print_r('open lock response data <br />');
        // var_dump($lock_info);
        // print_r('<br />');

        // get lock status
        // TODO: add validation for xml
        $lock_info_xml = simplexml_load_string($lock_info);
        $svrbtime = $lock_info_xml->SVCHEAD->PROCESSTIME;

        // print_r('get svrbtime <br />');
        // var_dump($svrbtime . '');
        // print_r('<br />');

        $param['SVRBTIME'] = $svrbtime;


        /*
        *
        *
        * GET LOCK STATUS
        *
        *
        */
        // get lock status request
        $lock_status_request_xml = $this->getLockStatusRequestXML($param);
        // print_r('lock status request data <br />');
        // var_dump($lock_status_request_xml);
        // print_r('<br />');

        // send request 
        $lock_status = $this->sendRequest($lock_status_request_xml);
        // print_r('lock status response data <br />');
        // var_dump($lock_status);
        // print_r('<br />');

        $lock_status_xml = simplexml_load_string($lock_status);
        $lock_status_code = $lock_status_xml->SVCCONT->RESPCODE;


        return $value . '. Lock (' . $lock_code . ') status code = ' . $lock_status_code;
    }


    /**
     * 生成房间请求XML
     *
     * @param
     *            array 入参
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 
     *            CUSCODE   账号或卡号 
     *            IDCODE    信息登记时证件号码 
     * @return string
     */
    private function getRoomInfoRequestXML(array $param): string
    {
        $room_code = $param['ROOMCODE'];

        // // YY MM DD HH MI SS ZZZ 
        $sys_process_time = date("ymdHisv");

        $sign_code = md5("101" . "2" . $sys_process_time . $this->access_key);

        $request_data = '
            <SVCINTER>
                <SVCHEAD>
                    <BIPCODE>101</BIPCODE>
                    <PROCID>2</PROCID>
                    <PROCESSTIME>' . $sys_process_time . '</PROCESSTIME>
                    <sign>' . $sign_code . '</sign>
                </SVCHEAD>
                <SVCCONT>
                    <ROOMCODE>' . $room_code . '</ROOMCODE>
                </SVCCONT>
            </SVCINTER>
        ';



        return  $request_data;
    }


    /**
     * 生成开门请求XML
     *
     * @param
     *            array 入参
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 
     *            CUSCODE   账号或卡号 
     *            IDCODE    信息登记时证件号码 
     * @return string
     */
    private function getOpenLockRequestXML(array $param): string
    {

        // get required data
        $cus_code = $param['CUSCODE'];
        $id_code = $param['IDCODE'];
        $lock_code = $param['LOCKCODE'];

        $sys_process_time = date("ymdHisv");

        $sign_code = md5("103" . "2" . $sys_process_time . $this->access_key);

        $request_data = '
            <SVCINTER>
                <SVCHEAD>
                    <BIPCODE>103</BIPCODE>
                    <PROCID>2</PROCID>
                    <PROCESSTIME>' . $sys_process_time . '</PROCESSTIME>
                    <sign>' . $sign_code . '</sign>
                </SVCHEAD>
                <SVCCONT>
                    <CUSCODE>' . $cus_code . '</CUSCODE>
                    <IDCODE>' . $id_code . '</IDCODE>
                    <LOCKCODE>' . $lock_code . '</LOCKCODE>
                </SVCCONT>
            </SVCINTER>
        ';



        return  $request_data;
    }


    /**
     * 获取门锁状态XML
     *
     * @param
     *            array 入参
     *            CUSCODE  账号或卡号 
     *            LOCKCODE   门锁编号
     *            SVRBTIME    远程开门申请后服务端回复的处理时间
     * @return string
     */
    private function getLockStatusRequestXML(array $param): string
    {

        // get required data
        $cus_code = $param['CUSCODE'];
        $lock_code = $param['LOCKCODE'];
        $svrb_time = $param['SVRBTIME'];

        $sys_process_time = date("ymdHisv");

        $sign_code = md5("104" . "2" . $sys_process_time . $this->access_key);

        $request_data = '
            <SVCINTER>
                <SVCHEAD>
                    <BIPCODE>104</BIPCODE>
                    <PROCID>2</PROCID>
                    <PROCESSTIME>' . $sys_process_time . '</PROCESSTIME>
                    <sign>' . $sign_code . '</sign>
                </SVCHEAD>
                <SVCCONT>
                    <CUSCODE>' . $cus_code . '</CUSCODE>
                    <LOCKCODE>' . $lock_code . '</LOCKCODE>
                    <SVRBTIME>' . $svrb_time . '</SVRBTIME>
                </SVCCONT>
            </SVCINTER>
        ';



        return  $request_data;
    }



    /**
     * 发送数据到门锁管理系统
     *
     * @param
     *            array 入参
     *            ROOMCODE  房间名称（门锁管理软件中房间名称） 
     *            CUSCODE   账号或卡号 
     *            IDCODE    信息登记时证件号码 
     * @return string
     */
    private function sendRequest($xml): string
    {

        // send request to server
        // TODO: set this url to setting
        $client = new SoapClient("http://183.239.170.26:6007/wsdl/IBWHISIFSERVER");


        $result = $client->__soapCall("BWHISOPIF", array(
            "QuestXml" => $xml
        ));



        return $result;
    }
}
