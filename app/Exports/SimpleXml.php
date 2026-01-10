<?php
namespace App\Exports;
use Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class SimpleXml{
    protected $collection;
    protected $map;
    protected $lastTagName;
    public function __construct(Collection $collection,$map){
        $this->collection = $collection;
        $this->map = $map;
    }
    public function download($filename=false){
        // $class = strtolower(get_class($this->collection));
        $class = $filename;
        $this->lastTagName=$class;
        $xml = new \SimpleXMLElement('<'.$class.'s/>');
        $this->to_xml($xml,$this->collection->toArray());
        // dd($class.'.xml');
        // dd($xml->asXML());
        $temporaryFile = $class.'_'.time().'.xml';
        Storage::put($temporaryFile,html_entity_decode($xml->asXML()));
        return Storage::download($temporaryFile,$class.'.xml');
    }
    public function to_xml(\SimpleXMLElement $object, array $data){
        $map = $this->map;
        foreach ($data as $key => $value) {
            $tag = ($key === (int) $key)
                ?preg_replace('/^(.+?)s$/',"$1",$this->lastTagName)
                :(isset($map[$key])?$map[$key]:$key);
            if (is_array($value)) {
                $new_object = $object->addChild($tag);
                $this->lastTagName=$tag;
                $this->to_xml($new_object, $value);

            }
            else {
                $value = preg_replace('/"/im','',$value);
                // if the key is an integer, it needs text with it to actually work.
                if ($key === (int) $key) {
                    $tag = "key_$key";
                }
                $object->addAttribute($tag, $value);
            }

        }
    }
};
