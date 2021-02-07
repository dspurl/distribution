<?php

namespace App\Models\v1;

use DateTimeInterface;
use Illuminate\Database\Eloquent\Model;
/**
 * @property string name
 * @property int type
 * @property int price
 * @property int level
 * @property int distribution_id
 */
class DistributionRule extends Model
{
    public static $withoutAppends = true;
    const DISTRIBUTION_RULE_TYPE_FIXATION= 0; //返佣方式：按固定值
    const DISTRIBUTION_RULE_TYPE_PERCENT= 1; //返佣方式：按比例
    const DISTRIBUTION_RULE_LEVEL_ONE= 1; //级别：一级
    const DISTRIBUTION_RULE_LEVEL_TWO= 2; //级别：二级
    const DISTRIBUTION_RULE_LEVEL_THREE= 3; //级别：三级
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
     * 返佣方式
     *
     * @return void
     */
    protected function getTypeAttribute()
    {
        if(isset($this->attributes['type'])){
            if(self::$withoutAppends){
                return $this->attributes['type'];
            }else{
                switch ($this->attributes['type']){
                    case self::DISTRIBUTION_RULE_TYPE_FIXATION:
                        return '按固定值';
                        break;
                    case self::DISTRIBUTION_RULE_TYPE_PERCENT:
                        return '按比例';
                        break;
                }
            }
        }
    }

    /**
     * 返佣值
     *
     * @return void
     */
    public function getPriceAttribute()
    {
        if(isset($this->attributes['price'])){
            if(self::$withoutAppends){
                $return= $this->attributes['price'];
            }else{
                $return= $this->attributes['price']/100;
            }
            return $return>0 ? $return : '';
        }
    }

    /**
     * 返佣值
     *
     * @param  string  $value
     * @return void
     */
    public function setPriceAttribute($value)
    {
        $this->attributes['price'] = sprintf("%01.2f",$value)*100;
    }
}
