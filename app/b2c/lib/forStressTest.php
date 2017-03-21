<?php
class b2c_forStressTest{
    static $sqlAmountWarn = 200;
    static $sqlShowWarn = 1;
    static $sqlAmount = 0;
    static private $sqlAmountFile = 'ecstore-sql-amount';
    static private $slowSqlFIle = 'ecstore-slow-sqls';
    static private $slowSqlStartTime;

    static function logSqlAmount(){
        if(self::$sqlAmount >= self::$sqlAmountWarn){
            $of = fopen(ROOT_DIR.'/'.self::$sqlAmountFile, 'ab+');
            fwrite($of, kernel::this_url() . "\t" . self::$sqlAmount . "\n");
            fclose($of);
        }
    }

    static function slowSqlStart(){
        self::$slowSqlStartTime = microtime(1);
    }

    static function slowSqlEnd($sql){
        if(!preg_match('/(?:^|\()SELECT\s+/is', $sql))
            return;
        $sqlExecTime = microtime(1) - self::$slowSqlStartTime;
        if($sqlExecTime > self::$sqlShowWarn){
            $of = fopen(ROOT_DIR.'/'.self::$slowSqlFIle, 'ab+');
            $sqlExecTime = $sqlExecTime * 1000;
            fwrite($of, kernel::this_url() . "\t" . $sql . "\t" . $sqlExecTime . "\n");
            fclose($of);
        }
    }
}
