<?php

namespace App\Stores;

use Kily\Tools1C\OData\Client;
use Log;


class Model{
    protected static $_with = [];
    protected $client;
    protected $model;
    protected $id;
    protected $data;
    protected $lastResult = [];
    private static $that=null;
    public function __construct(){
        $this->client = new Client( env('1C_HOST','http://HOSTNAME/BASE/odata/standard.odata/'),[
            'auth' => [
                env('1C_LOGIN','YOUR LOGIN'),
                env('1C_PASSWORD','YOUR PASSWORD')
            ],
        	'timeout' => 300,
        ]);
    }
    public function get($data=[]){
        $query = $this->client->{$this->model};
        if(count($data)){
            foreach($data as $attr => $value){
                $query = $query->filter("{$attr} eq '{$value}'");
            }
        }
        // if(count(self::$_with)){
        //     $query = $query->expand(join(',',self::$_with));
        // }
        $this->lastResult = $query->get()->values();
        return $this->lastResult;
    }
    public function create($data){
        $this->data = $this->client->{$this->model}->create($data);
        $this->id = $this->data->getLastId();
        return $this;
    }
    public function update($data){
        $this->data = $this->client->{$this->model}->update($this->id,$data);
        return $data;
    }
    public function delete($data){
        $data = $this->client->{$this->model}->update($this->id,[
            'DeletionMark'=>true,
        ]);
        return $data;
    }
    public static function with($array){
        self::ifBooted();
        self::$_with = array(self::$_with,$array);
        return self::$that;
    }
    public function toJson(){
        return json_encode($this->lastResult,JSON_UNESCAPED_UNICODE);
    }
    public function __toString(){
        return $this->toJson();
    }
    private static function ifBooted(){

        if(is_null(self::$that)) {
            $class = get_class();
            Log::debug('caller class: '.$class);
            self::$that = new $class;
        }
        return self::$that;
    }
}
