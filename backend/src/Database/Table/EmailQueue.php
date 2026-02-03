<?php

namespace Karolina\Database\Table;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;

class EmailQueue extends Model
{
    protected $table = 'kf_email_queue';

    protected $primaryKey = 'email_queue_id';
    protected $guarded = [];


}