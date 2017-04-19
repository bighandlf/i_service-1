<?php

class Dao_AppstartPic extends Dao_Base {

    public function __construct() {
        parent::__construct();
    }

    /**
     * 查询iservice_appstart_pic列表
     *
     * @param
     *            array 入参
     * @return array
     */
    public function getAppstartPicList(array $param): array {
        $limit = $param['limit'] ? intval($param['limit']) : 0;
        $page = $this->getStart($param['page'], $limit);

        $paramSql = $this->handlerListParams($param);
        $sql = "select * from iservice_appstart_pic {$paramSql['sql']}";
        if ($limit) {
            $sql .= " limit {$page},{$limit}";
        }
        $result = $this->db->fetchAll($sql, $paramSql['case']);
        return is_array($result) ? $result : array();
    }

    /**
     * 查询hotel_list数量
     *
     * @param
     *            array 入参
     * @return array
     */
    public function getAppstartPicCount(array $param): int {
        $paramSql = $this->handlerListParams($param);
        $sql = "select count(1) as count from iservice_appstart_pic {$paramSql['sql']}";
        $result = $this->db->fetchAssoc($sql, $paramSql['case']);
        return intval($result['count']);
    }

    private function handlerListParams($param) {
        $whereSql = array();
        $whereCase = array();
        if (isset($param['id'])) {
            if (is_array($param['id'])) {
                $whereSql[] = 'id in (' . implode(',', $param['id']) . ')';
            } else {
                $whereSql[] = 'id = ?';
                $whereCase[] = $param['id'];
            }
        }
        if (isset($param['status'])) {
            $whereSql[] = 'status = ?';
            $whereCase[] = $param['status'];
        }
        $whereSql = $whereSql ? ' where ' . implode(' and ', $whereSql) : '';
        return array(
            'sql' => $whereSql,
            'case' => $whereCase
        );
    }

    /**
     * 根据id查询iservice_appstart_pic详情
     *
     * @param
     *            int id
     * @return array
     */
    public function getAppstartPicDetail(int $id): array {
        $result = array();

        if ($id) {
            $sql = "select * from iservice_appstart_pic where id=?";
            $result = $this->db->fetchAssoc($sql, array(
                $id
            ));
        }

        return $result;
    }

    /**
     * 根据id更新iservice_appstart_pic
     *
     * @param
     *            array 需要更新的数据
     * @param
     *            int id
     * @return array
     */
    public function updateAppstartPicById(array $info, int $id) {
        $result = false;

        if ($id) {
            $result = $this->db->update('iservice_appstart_pic', $info, array('id' => $id));
        }

        return $result;
    }

    /**
     * 单条增加iservice_appstart_pic数据
     *
     * @param
     *            array
     * @return int id
     */
    public function addAppstartPic(array $info) {
        $this->db->insert('iservice_appstart_pic', $info);
        return $this->db->lastInsertId();
    }
}
