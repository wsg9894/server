<?php

class BaseRedis
{
    const IS_PREDIS = true;
    
    const REGION_REDIS = 'redis';
    const GLOBAL_REDIS = 'globalredis';
    
    private static $redisGroup = array(
        self::REGION_REDIS => 1, // multi redis instances, instance $id is need in getInstance
        self::GLOBAL_REDIS => 0,
    );
    
    private $client = null;
    
    private static $sigInstance = null;
    
    public static function getInstance($type)
    {
        if (empty(self::$sigInstance))
        {
            self::$sigInstance = new BaseRedis($type);
        }
        
        return self::$sigInstance;
    }

    public function __destruct()
    {
        if($this->client){
            if(method_exists($this->client, 'quit')){
                $this->client->quit();
            }else if(method_exists($this->client, 'close')){
                $this->client->close();
            }
        }
        $this->client = null;
    }
    public function __construct($type, $id = 1)
    {
        try{
            //global $arGameConfig;
            $arRedisConf = Yii::app()->params['redis'];
            
            if(!isset($arRedisConf[$type])){
                return false;
            }
            $redisConfig = array_values($arRedisConf[$type]);
            $instance = 0;
            if(isset(self::$redisGroup[$type]) && self::$redisGroup[$type]>0){
                if($id - 1 < 0){
                 throw new Exception;
                }
                $instance = $id - 1;
            }
            
            $config = $redisConfig[$instance];
            
            $client = self::createRedisClient($config);
        }  catch (Exception $e){
            var_dump($e->getTraceAsString());
            return false;
        }
        $this->client = $client;
        return $client;
    }
    
    private static function createRedisClient($config)
    {
        if(!self::IS_PREDIS){
            $client = new Redis();
            $client->connect($config[0], $config[1]);
        }else{
            //require_once INCPATH.'Predis/Autoloader.php';
        	Yii::import('application.models.process.lib.Predis.Autoloader.php');
            Predis\Autoloader::register();
            
            $client = new Predis\Client(array(
                'scheme' => 'tcp',
                'host'   => $config[0],
                'port'   => $config[1],
            ));
            
        }
        if(isset($config[2])){
            $client->select($config[2]);
        }
        return $client;
    }
    
    private function zrevrangebyscore()
    {
        $args = func_get_args();
        $key = $args[0];
        $max = $args[1];
        $min = $args[2];
        $param = array($key, $max, $min);
        $num = count($args);
        if(self::IS_PREDIS){
            for($i = 3; $i < $num; ++$i){
                if(is_array($args[$i])){
                    if(isset($args[$i]['withscores'])){
                        $param[] = 'withscores';
                    }
                    if(isset($args[$i]['limit'])){
                        $param[] = 'limit';
                        $param[] = $args[$i]['limit'][0];
                        $param[] = $args[$i]['limit'][1];
                    }
                }else{
                    $param[] = $args[$i];
                }
            }
            
        }else{
            if(isset($args[3]) && !is_array($args[3])){
                $num = count($args);
                $tmp = array();
                for($i = 3; $i < $num; ++$i){
                    if($args[$i] == 'withscores'){
                        $tmp['withscores'] = true;
                    }
                    if($args[$i] == 'limit'){
                        $tmp['limit'] = array($args[$i + 1], $args[$i + 2]);
                        $i += 2;
                    }
                }
                if(!empty($tmp)){
                    $param[] = $tmp;
                }
            }
        }
        $ret = call_user_func_array(array($this->client, 'zrevrangebyscore'), $param);
        if(self::IS_PREDIS){
            return $ret;
        }
        $data = array();
        foreach($ret as $k => $v){
            $data[] = array($k, $v);
        }
        return $data;
    }
    
    public function pipeline($func){
        if(self::IS_PREDIS){
            return $this->client->pipeline($func);
        }
        $pipe = $this->client->multi(Redis::PIPELINE);
        $func($pipe);
        return $pipe->exec();
    }
    
    public function __call($name, $arguments)
    {
        if(empty($this->client)){
            return array();
        }
        return call_user_func_array(array($this->client, $name), $arguments);
    }
}