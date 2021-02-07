<?php

namespace App\Models\v1;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * @property int children_id
 * @property int parent_id
 * @property int level
 */
class UserRelation extends Model
{
    public static $withoutAppends = true;
    const USER_RELATION_LEVEL_ONE = 1; //等级：一级
    const USER_RELATION_LEVEL_TWO = 2; //等级：二级
    const USER_RELATION_LEVEL_THREE = 3; //等级：三级

    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param \DateTimeInterface $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 关联分销规则
     */
    public function User()
    {
        return $this->hasMany(User::class);
    }

    //用户关系
    public function UserRelation()
    {
        return $this->hasOne(UserRelation::class, 'children_id', 'parent_id');
    }
}
