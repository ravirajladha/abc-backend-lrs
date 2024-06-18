<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EbookSection extends Model
{
    use HasFactory;

    protected $primaryKey = 'id';

    protected $guarded = ['id'];

    protected $table = 'ebook_sections';

    public static function getTableName()
    {
        return with(new static)->getTable();
    }

    public function elements()
    {
        return $this->hasMany(EbookElement::class);
    }
}
