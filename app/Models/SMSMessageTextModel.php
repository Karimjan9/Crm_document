<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class SMSMessageTextModel extends Model
{
    use HasFactory;
    use SoftDeletes;
    protected $table = 's_m_s_message_text';
    protected $fillable = [
        'name',
        'type',
        'message_text1',
        'message_text2',
        'message_text3',
        'description',
    ];


}
