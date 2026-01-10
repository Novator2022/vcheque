<?php

namespace App;

use Log;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class Category extends Model
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
            $category = null;
            $name = $position["category"] ?? $position["name"];
            $nds = $position["nds"] ?? 22;
            $nds = in_array($nds, [0,10,20,22]) ? $nds : 22;
            $id = $position["category_id"] ?? null;

            if (!empty($id)) {
                $category = Category::find($id);
            }

            if (empty($category)) {
                $category = Category::where('name', $name)->first();
            }
            if (!empty($category)) {
                continue;
            }

            try {
                Category::create([
                    "name"=>$name,
                    "nds"=>$nds
                ]);
            } catch (\Exception $e) {
                Log::error($e->getMessage(), $position);
            }
        }
    }
}
