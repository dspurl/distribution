<?php

namespace App\Models\v1;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
/**
 * @property string name
 * @property string identification
 * @property int level
 * @property int state
 */
class Distribution extends Model
{
    public static $withoutAppends = true;
    const DISTRIBUTION_LEVEL_ONE= 1; //分销级别：一级
    const DISTRIBUTION_LEVEL_TWO= 2; //分销级别：二级
    const DISTRIBUTION_LEVEL_THREE= 3; //分销级别：三级
    const DISTRIBUTION_IDENTIFICATION_REGISTRATION__CASH = 'sys_registration_cash';    //奖励事件：注册奖励现金
    const DISTRIBUTION_TYPE_FIXED_AMOUNT=0;   //返佣方式:固定金额
    const DISTRIBUTION_TYPE_RATIO=1;   //返佣方式:按比例
    const DISTRIBUTION_STATE_OPEN=0;   //状态:开启
    const DISTRIBUTION_STATE_CLOSE=1;   //状态:关闭
    /**
     * Prepare a date for array / JSON serialization.
     *
     * @param  \DateTimeInterface  $date
     * @return string
     */
    protected function serializeDate(DateTimeInterface $date)
    {
        return $date->format('Y-m-d H:i:s');
    }

    /**
     * 关联分销规则
     */
    public function DistributionRule(){
        return $this->hasMany(DistributionRule::class);
    }
}
