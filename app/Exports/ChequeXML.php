<?php
namespace App\Exports;
use Log;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class ChequeXML{
    protected $collection;
    protected $map;
    protected $lastTagName;
    public function __construct(Collection $collection){
        $this->collection = $collection;
    }
    public function store($inn){

        $xml = new \SimpleXMLElement('<cheques/>');
        $this->to_xml($xml,$this->collection);
        $temporaryFile = 'cheques/cheki_INN_'.$inn.'.xml';
        Storage::put($temporaryFile,html_entity_decode($xml->asXML()));
        return $temporaryFile;
    }
    public function download($inn){}
    public function to_xml(\SimpleXMLElement $object, $data){
        foreach ($data as $cheque) {
            $tag = $object->addChild('cheque');
            $tag->addAttribute('id',$cheque->id);
            $tag->addAttribute('created_at',$cheque->created_at);
            $tag->addAttribute('updated_at',$cheque->updated_at);
            $tag->addAttribute('status',$cheque->status);
            $tag->addAttribute('organisation_id',$cheque->organisation_id);
            $organisationTag = $tag->addChild('organisation');
            $organisationTag->addAttribute('id',$cheque->organisation_id);
            $organisationTag->addAttribute('created_at',$cheque->organisation->created_at);
            $organisationTag->addAttribute('updated_at',$cheque->organisation->updated_at);
            $organisationTag->addAttribute('name',preg_replace('/"/im','',$cheque->organisation->name));
            $contractorTag = $tag->addChild('contractor');
            $contractorTag->addAttribute('id',          (isset($cheque->data) && isset($cheque->data['contractor']['id']))?$cheque->data["contractor"]["id"]:"");
            $contractorTag->addAttribute('name',        (isset($cheque->data) && isset($cheque->data['contractor']['name']))?preg_replace('/"/im','',$cheque->data["contractor"]["name"]):"");
            $contractorTag->addAttribute('address',     (isset($cheque->data) && isset($cheque->data['contractor']['address']))?$cheque->data["contractor"]["address"]:"");
            $contractorTag->addAttribute('created_at',  (isset($cheque->data) && isset($cheque->data['contractor']['created_at']))?$cheque->data["contractor"]["created_at"]:"");
            $contractorTag->addAttribute('updated_at',  (isset($cheque->data) && isset($cheque->data['contractor']['updated_at']))?$cheque->data["contractor"]["updated_at"]:"");
            $contractorTag->addAttribute('deleted_at',  (isset($cheque->data) && isset($cheque->data['contractor']['deleted_at']))?$cheque->data["contractor"]["deleted_at"]:"");
            $contractorMetaTag = $contractorTag->addChild('meta');
            $contractorMetaTag->addAttribute('inn', (isset($cheque->data) && isset($cheque->data->contractor) && isset($cheque->data->contractor->inn))?$cheque->data->contractor->inn:'' );
            $contractorMetaTag->addAttribute('kpp', (isset($cheque->data) && isset($cheque->data->contractor) && isset($cheque->data->contractor->kpp))?$cheque->data->contractor->kpp:'' );
            $contractorMetaTag->addAttribute('email', (isset($cheque->user) && isset($cheque->user->email))?$cheque->user->email:'' );
            $positionsTag = $tag->addChild('positions');
            if(isset($cheque->data) && isset($cheque->data["positions"])){
                foreach ($cheque->data['positions'] as $position) {
                    $positionTag = $positionsTag->addChild('position');
                    $positionTag->addAttribute('nds',isset($position['nds'])?$position['nds']:'');
                    $positionTag->addAttribute('name',isset($position['nomenclature'])?preg_replace('/"/im','',$position['nomenclature']):'');
                    $positionTag->addAttribute('amount',isset($position['amount'])?$position['amount']:'');
                    $positionTag->addAttribute('quantity',isset($position['quantity'])?$position['quantity']:'');
                    $positionTag->addAttribute('nomenclature_id',isset($position['nomenclature_id'])?$position['nomenclature_id']:'');
                }
            }
        }
    }
};
