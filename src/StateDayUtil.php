<?php
/**
 * 获取统计时间段
 */
namespace statistics;

class StateDayUtil
{
    private $startDay;                      //统计开始日期

    private $endDay;                        //统计截至日期,不包括此天

    private $stateTimeField;                //获取时间戳范围

    private $stateDayField;                 //获取日期范围

    public function __construct($startDay,$endDay='')
    {
        $this->__setTimeZone('Asia/Shanghai');

        $this->startDay = $startDay;
        if(empty($endDay)){
            $this->endDay = date('Ymd',time());
        }else {
            $this->endDay = $endDay;
        }

        $this->__getStateTime();
    }

    //获取时间戳统计值
    public function getStateTime(){
        return $this->stateTimeField;
    }

    //获取日期统计值
    public function getStateDay(){
        return $this->stateDayField;
    }

    //获取统计时间段
    private function __getStateTime()
    {
        $s = strtotime($this->startDay);

        $e = strtotime($this->endDay);

        $days = ($e - $s) / 86400;

        while ($days > 0) {
            $days--;

            $start_time =  $s + ($days) * 86400;

            $end_time = $start_time + 86400;

            $this->stateTimeField[] = array('start_time'=>$start_time,'end_time'=>$end_time);

            $this->stateDayField[] = array('start_day'=>date('Ymd',$start_time),'end_day'=>date('Ymd',$end_time));
        }
    }

    //设置时区
    private function __setTimeZone($zone){
        date_default_timezone_set($zone);
    }

}
