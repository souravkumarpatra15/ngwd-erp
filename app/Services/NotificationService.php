<?php
namespace App\Services;
use App\Models\NotificationModel;

class NotificationService
{
    public function create(int $userId, string $type, string $title, string $message, ?int $refId=null, ?string $refType=null): int {
        return (new NotificationModel())->insert(['user_id'=>$userId,'type'=>$type,'title'=>$title,'message'=>$message,'reference_id'=>$refId,'reference_type'=>$refType]);
    }
}
