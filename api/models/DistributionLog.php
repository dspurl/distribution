<?php

namespace App\Models\v1;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int user_id
 * @property int children_id
 * @property string name
 * @property int type
 * @property int price
 * @property int level
 */
class DistributionLog extends Model
{
    public static $withoutAppends = true;
    const DISTRIBUTION_LOG_TYPE_FIXATION = 0; //返佣方式：按固定值
    const DISTRIBUTION_LOG_TYPE_PERCENT = 1; //返佣方式：按比例
    const DISTRIBUTION_LOG_LEVEL_ONE = 1; //级别：一级
    const DISTRIBUTION_LOG_LEVEL_TWO = 2; //级别：二级
    const DISTRIBUTION_LOG_LEVEL_THREE = 3; //级别：三级

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
     * 返佣金额
     *
     * @return void
     */
    public function getPriceAttribute()
    {
        if (isset($this->attributes['price'])) {
            if (self::$withoutAppends) {
                $return = $this->attributes['price'];
            } else {
                $return = $this->attributes['price'] / 100;
            }
            return $return > 0 ? $return : '';
        }
    }
}
