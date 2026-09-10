<?php

namespace BehinLogging\Models;

use Illuminate\Database\Eloquent\Model;

class UserActionLog extends Model
{
    protected $table = 'user_action_logs';
    protected $fillable = ['user_id', 'user_name', 'method', 'path', 'action', 'params', 'status_code', 'ip_address', 'user_agent'];
    protected $casts = ['params' => 'array'];
}
