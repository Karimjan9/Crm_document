<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Carbon\Carbon;

class Holiday extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'date',
        'type',
        'color',
        'description',
        'is_recurring',
        'is_active',
        'created_by'
    ];

    protected $casts = [
        'date' => 'date',
        'is_recurring' => 'boolean',
        'is_active' => 'boolean'
    ];

    /**
     * Scope для активных праздников
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    /**
     * Scope для поиска по году
     */
    public function scopeByYear($query, $year)
    {
        return $query->whereYear('date', $year);
    }

    /**
     * Scope для поиска по месяцу
     */
    public function scopeByMonth($query, $month)
    {
        return $query->whereMonth('date', $month);
    }

    /**
     * Scope для поиска по типу
     */
    public function scopeByType($query, $type)
    {
        return $query->where('type', $type);
    }

    /**
     * Проверяет, является ли дата праздничной
     */
    public static function isHoliday($date)
    {
        return self::active()->whereDate('date', $date)->exists();
    }

    /**
     * Получает праздники на определенную дату
     */
    public static function getHolidaysByDate($date)
    {
        return self::active()->whereDate('date', $date)->get();
    }

    /**
     * Конвертирует в событие для FullCalendar
     */
    public function toCalendarEvent()
    {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'start' => $this->date->format('Y-m-d'),
            'end' => $this->date->format('Y-m-d'),
            'color' => $this->getColor(),
            'textColor' => $this->getTextColor(),
            'allDay' => true,
            'display' => 'background',
            'editable' => false,
            'extendedProps' => [
                'type' => 'holiday',
                'holiday_type' => $this->type,
                'description' => $this->description,
                'is_recurring' => $this->is_recurring,
                'created_by' => $this->created_by
            ]
        ];
    }

    /**
     * Получает цвет для календаря
     */
    public function getColor()
    {
        if ($this->color) {
            return $this->color;
        }

        return $this->getDefaultColor();
    }

    /**
     * Получает цвет текста для календаря
     */
    public function getTextColor()
    {
        // Для темных цветов - белый текст, для светлых - черный
        $color = $this->getColor();
        if (!$color || $color === 'transparent') {
            return '#333333';
        }

        $hex = str_replace('#', '', $color);
        $r = hexdec(substr($hex, 0, 2));
        $g = hexdec(substr($hex, 2, 2));
        $b = hexdec(substr($hex, 4, 2));

        // Формула для определения яркости
        $brightness = (($r * 299) + ($g * 587) + ($b * 114)) / 1000;

        return $brightness > 128 ? '#333333' : '#ffffff';
    }

    /**
     * Цвет по умолчанию в зависимости от типа
     */
    private function getDefaultColor()
    {
        $colors = [
            'national' => '#ff6b6b', // Красный - государственные
            'company'  => '#4ecdc4',  // Бирюзовый - корпоративные
            'regional' => '#ffd166', // Желтый - региональные
            'religious' => '#9b5de5', // Фиолетовый - религиозные
            'other'    => '#00bbf9'   // Голубой - другие
        ];

        return $colors[$this->type] ?? '#cccccc';
    }

    /**
     * Получает название типа на русском
     */
    public function getTypeName()
    {
        $types = [
            'national' => 'Государственный',
            'company'  => 'Корпоративный',
            'regional' => 'Региональный',
            'religious' => 'Религиозный',
            'other'    => 'Другой'
        ];

        return $types[$this->type] ?? $this->type;
    }

    /**
     * Проверяет, является ли праздник повторяющимся
     */
    public function isRecurring()
    {
        return $this->is_recurring;
    }

    /**
     * Создает копию праздника для следующего года
     */
    public function createCopyForNextYear()
    {
        if (!$this->is_recurring) {
            return null;
        }

        $copy = $this->replicate();
        $copy->date = Carbon::parse($this->date)->addYear();
        $copy->save();

        return $copy;
    }
}
