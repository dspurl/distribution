<?php


namespace App\Observers\User;


use App\Models\v1\Distribution;
use App\Models\v1\DistributionLog;
use App\Models\v1\MoneyLog;
use App\Models\v1\User;
use App\Models\v1\UserRelation;
use App\Notifications\InvoicePaid;
use Illuminate\Http\Request;

/**
 * registration incentives
 * 注册奖励
 * Class UserLogObserver
 * @package App\Observers\User
 */
class RegistrationIncentivesObserver
{
    protected $request;
    protected $route = [
        'app/register',
        'app/authorization'
    ];
    protected $execute = false;

    public function __construct(Request $request)
    {
        if (!app()->runningInConsole()) {
            $this->request = $request;
            $path = explode("admin", $request->path());
            if (count($path) == 2) {
                $name = 'admin' . $path[1];
            } else {
                $path = explode("app", $request->path());
                $name = 'app' . $path[1];
            }
            if (collect($this->route)->contains($name)) {
                $this->execute = true;
            }
        }
    }

    public function created(User $user)
    {
        if ($this->execute || app()->runningInConsole()) {
            // 用户关系绑定
            if ($this->request->has('uuid')) {
                // 注册奖励规则获取
                $Distribution = Distribution::where('state', Distribution::DISTRIBUTION_STATE_OPEN)->where('identification', Distribution::DISTRIBUTION_IDENTIFICATION_REGISTRATION__CASH)
                    ->with(['DistributionRule'])->first();
                try {    // 防止未按后台录入格式入库的脏数据产生的异常
                    if ($Distribution->DistributionRule[0]->type == Distribution::DISTRIBUTION_TYPE_FIXED_AMOUNT) {
                        $price = $Distribution->DistributionRule[0]->price;
                    } else {
                        $price = 0;   //注册奖励没有参考金额，所以无法按比例奖励，如需按比例，请写死一个固定值
                    }
                } catch (\EXception $e) {
                    return 1;
                }
                $UserParent = User::where('uuid', $this->request->uuid)->with([ //一级
                    'UserRelation' => function ($q) {   //二级
                        $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE)->with(['UserRelation' => function ($q) {   //三级
                            $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE);
                        }]);
                    }
                ])->first();
                // 注册奖励处理
                if ($Distribution) {
                    User::where('id', $UserParent->id)->increment('money', $price);
                    $DistributionLog = new DistributionLog();
                    $DistributionLog->user_id = $UserParent->id;
                    $DistributionLog->children_id = $user->id;
                    $DistributionLog->name = $Distribution->name;
                    $DistributionLog->type = $Distribution->DistributionRule[0]->type;
                    $DistributionLog->level = DistributionLog::DISTRIBUTION_LOG_LEVEL_ONE;
                    $DistributionLog->price = $price;
                    $DistributionLog->save();
                    $Money = new MoneyLog();
                    $Money->user_id = $UserParent->id;
                    $Money->type = MoneyLog::MONEY_LOG_TYPE_INCOME;
                    $Money->money = $price;
                    $Money->remark = '邀请奖励，获得' . ($price / 100) . '元';
                    $Money->save();
                    $parameter = [
                        'money_id' => $Money->id,  //资金记录ID
                        'total' => $price,    //奖励金额
                        'user_id' => $UserParent->id   //用户ID
                    ];
                    $invoice = [
                        'type' => InvoicePaid::NOTIFICATION_TYPE_DEAL,
                        'title' => '邀请奖励',
                        'list' => [
                            [
                                'keyword' => '支付方式',
                                'data' => '余额支付'
                            ]
                        ],
                        'price' => $parameter['total'],
                        'url' => '/pages/finance/bill_show?id=' . $parameter['money_id'],
                        'remark' => '邀请奖励，获得' . ($parameter['total'] / 100) . '元',
                        'prefers' => ['database']
                    ];
                    $notifyUser = User::find($parameter['user_id']);
                    $notifyUser->notify(new InvoicePaid($invoice));
                }
                // 一级关系绑定
                $UserRelation = new UserRelation();
                $UserRelation->children_id = $user->id;    //注册用户ID
                $UserRelation->parent_id = $UserParent->id;    //一级ID
                $UserRelation->level = UserRelation::USER_RELATION_LEVEL_ONE;
                $UserRelation->save();
                //二级关系绑定
                if ($UserParent->UserRelation) {
                    $UserRelation = new UserRelation();
                    $UserRelation->children_id = $user->id;  //注册用户ID
                    $UserRelation->parent_id = $UserParent->UserRelation->parent_id;  //二级ID
                    $UserRelation->level = UserRelation::USER_RELATION_LEVEL_TWO;
                    $UserRelation->save();
                    //三级关系绑定
                    if ($UserParent->UserRelation->UserRelation) {
                        $UserRelation = new UserRelation();
                        $UserRelation->children_id = $user->id;  //注册用户ID
                        $UserRelation->parent_id = $UserParent->UserRelation->UserRelation->parent_id;  //三级ID
                        $UserRelation->level = UserRelation::USER_RELATION_LEVEL_THREE;
                        $UserRelation->save();
                    }
                }
            }
        }
    }
}
