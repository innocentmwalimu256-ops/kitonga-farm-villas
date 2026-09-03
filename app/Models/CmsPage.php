<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CmsPage extends Model
{
    protected $table = 'cms_pages';

    protected $fillable = ['title', 'slug', 'seo_title', 'seo_description', 'active'];

    protected $casts = [
        'active' => 'boolean'
    ];

    public function sections()
    {
        return $this->hasMany(CmsSection::class, 'cms_page_id');
    }
}
