<?php
/**
 * 主业务处理任务脚本
 */
class getGuiDangWantedPost
{
    private $table;
    private $limitNum = 10000;
    private $startTable;
    private $endTable;

    public function __construct()
    {
        global $argv;
        $this->startTable = $argv[1];
        $this->endTable = $argv[2];

        if(!is_numeric($this->startTable) || !is_numeric($this->endTable)){
            die('表传参异常');
        }
    }

    //生成csv文件
    public function writeCSV($value){
        file_put_contents('/tmp/wanted_post/gd/wanted_post_gd_'.$this->startTable.'_'.$this->endTable.'.csv',$value,FILE_APPEND);
    }

    //开始
    function run()
    {
        for ($i = $this->startTable; $i <= $this->endTable; $i++) {
            $table = 'table_' . $i;
            $this->getOneTableData($table);
        }
    }


    function getOneTableData($table){
        list($minId, $maxId) = $this->getIdRange($table);

        $page = 0;

        $start_id = $minId;

        $end_id = $minId + $this->limitNum;

        if ($end_id > $maxId) {
            $end_id = $maxId + 1;
        }

        while ($start_id < $maxId + 1) {
            echo '开始处理表'.$table.'中id:'.$start_id.'-id:'.$end_id.'的数据!'.PHP_EOL;
            $sql = sprintf('select id,url from %s where id >= %d and id < %d ',$table,$start_id,$end_id);
            $handle = $this->getHandle();
            $data = DBMysqlNamespace::getAll($handle,$sql);
            $page++;
            $start_id = $end_id;
            $end_id = $end_id + $this->limitNum;
            if ($end_id > $maxId) {
                $end_id = $maxId + 1;
            }

            if(is_array($data) && count($data) > 0){
                foreach($data as $item){
                    if($this->isZhaoPin($item['url'])){
                        $postInfoRow = $this->getPostData($table,$item['id'],$handle);
                        if(is_array($postInfoRow) && array_key_exists('data',$postInfoRow)){
                            $postInfo = $this->_formatPostData($postInfoRow['data']);
                            if(is_array($postInfo) && $postInfo['post_at'] >= strtotime(20150101) && $postInfo['post_at'] < strtotime(20180101)){
                                $value = $postInfo['puid']."\t".$postInfo['major_category']."\t".$postInfo['tag']."\t".$postInfo['listing_status']."\t".$postInfo['phone']."\t".$postInfo['phone2']."\n";
                                $this->writeCSV($value);
                            }
                        }
                    }
                }
            }
        }
    }



    //获取单条帖子数据
    function getPostData($table,$id,$handle){
        $sql = sprintf("select id,data from %s where id = %d",$table,$id);
        $row = DBMysqlNamespace::getRow($handle,$sql);
        return $row;
    }

    //获取主键ID的最大最小值
    function getIdRange($table)
    {
        $sql = sprintf('SELECT MIN(id) as min_id, MAX(id) as max_id FROM %s',$table);
        $handler = $this->getHandle();
        $result = DBMysqlNamespace::getRow($handler, $sql);
        if (!is_array($result)) {
            echo "从表 $table 未获取到最大ID和最小ID值".PHP_EOL;
        }
        return array($result['min_id'], $result['max_id']);
    }


    //获取mysql连接
    function getHandle()
    {
        $handle = DBMysqlNamespace::createDBHandle(DBConfig::$SERVER_ARCHIVE_SLAVE, 'archive');
        return $handle;
    }

    /**
     * @brief 格式化data数据，返回json
     * @param string $data
     * @return json
     */
    private function _formatPostData($data) {
        if (!$data) return null;

        preg_match("/^(\[.+\])\{([\s\S]+)\}/i", $data, $match);
        // 不合规范的json
        if (!preg_match("/^{[\s\S]+}$/", $match[2])) {
            $lines  = explode(',', $match[2]);
            foreach ($lines as $key => $line) {
                $ret    = preg_match("/([^:]+):([\s\S]+)?/", $line, $item);
                if ($ret) {
                    $json[$item[1]] = $item[2];
                    $last_key   = $item[1];
                }
                else {
                    $json[$last_key] .= $line;
                }
            }
            return $json;
        }
        else {
            $match[2]   = str_replace("\r\n" , '\n' , $match[2]);
            return json_decode($match[2], true);
        }
        return false;
    }
}

$work = new getGuiDangWantedPost();

$s = microtime(true);

$work->run();

echo '耗时:' . (microtime(true) - $s) . '秒'.PHP_EOL;

