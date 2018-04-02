<?php
/**
 * 分块全表扫描的方法
 */
namespace statistics;

class ScanTableById
{
	const  pageNum;

	/**
	 * @param  $table string 要全表搜索的表
	 * @param  $field string 要查找的表字段
	 * @param  $pageSize int 一次查找的数量
	 * @param  $function string 数据用来处理业务的回调函数
	 * @return void
	 * 
	 */
    function startJobBySlice($table, $field, $pageSize, $function)
    {
    	$page = 0;

        list($minId, $maxId) = $this->getIdRange($table, $this->curStatDay);
        
        $start_id = $minId;
        $end_id = $minId + $pageSize;

        if ($end_id > $maxId) {
            $end_id = $maxId + 1;
        }

        while ($start_id < $maxId + 1) {
            $sql = sprintf('select %s from %s where id >= %d and id < %d  and use_zhaopin = 1 order by id asc', $field, $table, $start_id, $end_id);
            $handle = $this->getHandle();
            $data = \DBMysqlNamespace::getAll($handle, $sql);
            $this->$function($data);
            $page++;
            $start_id = $end_id;
            $end_id = $end_id + $pageSize;
            if ($end_id > $maxId) {
                $end_id = $maxId + 1;
            }
        }
    }


    //获取主键ID的最大最小值
    function getIdRange($table, $date)
    {
        $sql = sprintf('SELECT MIN(id) as min_id, MAX(id) as max_id FROM %s WHERE dt = %s',
            $table, $date
        );

        $handler = $this->getHandle();

        $result = \DBMysqlNamespace::getRow($handler, $sql);

        return array($result['min_id'], $result['max_id']);
    }
}
