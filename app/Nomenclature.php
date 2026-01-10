<?php

namespace App;

use Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Nomenclature extends Model
{
    use SoftDeletes;
    protected $fillable = [
        'metadata','name','description','nds'
    ];
    protected $casts = [
        'metadata' => 'array',
    ];
    public static function updateFromCheque($data)
    {
        if (!isset($data["positions"])) {
            return;
        }

        foreach ($data["positions"] as $position) {
            $nomenclature = null;
            $name = $position["nomenclature"] ?? $position["name"];
            $nds = $position["nds"] ?? 22;
            $nds = in_array($nds, [0,10,20,22]) ? $nds : 22;
            $id = $position["nomenclature_id"] ?? null;

            if (!empty($id)) {
                $nomenclature = Nomenclature::find($id);
            }

            if (empty($nomenclature)) {
                $nomenclature = Nomenclature::where('name', $name)->first();
            }
            if (!empty($nomenclature)) {
                continue;
            }

            try {
                Nomenclature::create([
                    "name"=>$name,
                    "nds"=>$nds
                ]);
            } catch (\Exception $e) {
                Log::error($e->getMessage(), $position);
            }
        }
    }
}
