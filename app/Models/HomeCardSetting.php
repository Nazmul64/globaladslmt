<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class HomeCardSetting extends Model
{
    use HasFactory;

    protected $table = 'home_card_settings';

    protected $fillable = [
        'key',
        'title',
        'icon_type',
        'icon',
        'image',
        'bg_color',
        'icon_color',
        'text_color',
        'is_active',
        'sort_order',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'sort_order' => 'integer',
    ];

    protected $appends = ['image_url', 'formatted_icon_html', 'title_color'];

    /**
     * Get title color accessor (alias for text_color)
     */
    public function getTitleColorAttribute()
    {
        return $this->text_color ?? '#FFFFFF';
    }

    /**
     * Get full image URL accessor
     */
    public function getImageUrlAttribute()
    {
        if ($this->image) {
            if (str_starts_with($this->image, 'http://') || str_starts_with($this->image, 'https://')) {
                return $this->image;
            }
            return asset($this->image);
        }
        return null;
    }

    /**
     * Get HTML string for rendering icon font in admin panel
     */
    public function getFormattedIconHtmlAttribute()
    {
        $icon = trim($this->icon ?? '');
        if (empty($icon)) {
            return '<i class="material-icons">help</i>';
        }

        // If user entered full HTML tag <i class="..."></i>
        if (str_contains($icon, '<i') || str_contains($icon, '<span')) {
            return $icon;
        }

        // If user entered FontAwesome class like "fa fa-tasks" or "fa-solid fa-user"
        if (str_contains($icon, 'fa-') || str_contains($icon, 'fa ') || str_contains($icon, 'bi-') || str_contains($icon, 'ri-')) {
            return '<i class="' . e($icon) . '"></i>';
        }

        // Default to Material Icons font name e.g. "task_alt"
        return '<i class="material-icons">' . e($icon) . '</i>';
    }
}
