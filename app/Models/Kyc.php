<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kyc extends Model
{
   protected $fillable =[
       'user_id',
       'document_type',
       'document_first_part_photo',
       'document_secound_part_photo',
       'new_document_first_part_photo',
       'new_document_secound_part_photo',
       'status',
   ];
}
