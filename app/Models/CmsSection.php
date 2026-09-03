<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsSection extends Model
{
    protected $table = 'cms_sections';

    protected $fillable = ['cms_page_id', 'key', 'type', 'value', 'metadata'];

    protected $casts = [
        'metadata' => 'array'
    ];

    public function page()
    {
        return $this->belongsTo(CmsPage::class, 'cms_page_id');
    }
}
