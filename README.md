#评价
## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dshop v2.0.0及以上
- 已同步版本：dsshop v2.0.0

## 功能介绍
- 支持上下三级关系自动绑定
- 可根据自定义规则，在不同的业务场景进行分销返佣
- 分销可针对不同级别配置不同的返佣金额和返佣方式
- 支持按固定金额/值返佣和按比例返佣
- 不仅仅支持金额返佣，也可以配合其它业务场景进行返佣，如积分，只需修改业务代码即可
- 分销现只支持一级，可实现返佣后进行站内通知功能
- 暂不支持后台报表展示，后期会补全
- 暂仅支持扫码通过H5唤起

## 使用说明
#### 一、 下载distribution最新版
#### 二、 解压distribution到项目plugin目录下
#### 三、 登录dshop后台，进入插件列表
#### 四、 在线安装（请保持dshop的目录结构，如已部署到线上，请在本地测试环境安装，因涉及admin和uni-app，不建议在线安装）
#### 五、 进入api目录执行数据库迁移使用

```
php artisan migrate
```
#### 六、 进入后台，添加权限

| **权限名称** | **API**             | **分组**   | **菜单图标** | **显示在菜单栏** |
| ------------ | ------------------- | ---------- | ------------ | ---------------- |
| 分销         | Distribution        | 工具       | 否           | 是               |
| 分销列表     | DistributionList    | 工具->分销 | 否           | 是               |
| 添加分销     | DistributionCreate  | 工具->分销 | 否           | 否               |
| 分销编辑     | DistributionEdit    | 工具->分销 | 否           | 否               |
| 分销删除     | DistributionDestroy | 工具->分销 | 否           | 否               |



#### 七、 进入后台，为管理员分配权限

#### 八、 使用说明

##### 管理员南

- 后台可以添加分销，添加时，可以配置分销返佣方式和返佣值
- 添加的分销需要开发人员实现业务代码才有效
- 请不要随意修改分销标识，修改后需要开发人员修改对应的业务代码，不然将会失效

##### 开发指南

###### 添加关系绑定和分销机制

```php
#api\app\Http\Controllers\v1\Client\LoginController.php
use App\Models\v1\Distribution;
use App\Models\v1\DistributionLog;
use App\Models\v1\MoneyLog;
use App\Models\v1\UserRelation;
public function authorization(Request $request)
{
	...
    $user->save();
    // 用户关系绑定
    if ($request->has('uuid')) {
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
        $User = User::where('uuid', $request->uuid)->with([ //一级
            'UserRelation' => function ($q) {   //二级
                $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE)->with(['UserRelation' => function ($q) {   //三级
                    $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE);
                }]);
            }
        ])->first();
        // 注册奖励处理
        if ($Distribution) {
            User::where('id', $User->id)->increment('money', $price);
            $DistributionLog = new DistributionLog();
            $DistributionLog->user_id = $User->id;
            $DistributionLog->children_id = $user->id;
            $DistributionLog->name = $Distribution->name;
            $DistributionLog->type = $Distribution->DistributionRule[0]->type;
            $DistributionLog->level = DistributionLog::DISTRIBUTION_LOG_LEVEL_ONE;
            $DistributionLog->price = $price;
            $DistributionLog->save();
            $Money = new MoneyLog();
            $Money->user_id = $User->id;
            $Money->type = MoneyLog::MONEY_LOG_TYPE_INCOME;
            $Money->money = $price;
            $Money->remark = '邀请奖励，获得' . ($price / 100) . '元';
            $Money->save();
            $Common = (new Common)->inviteReward([
                'money_id' => $Money->id,  //资金记录ID
                'total' => $price,    //奖励金额
                'user_id' => $User->id   //用户ID
            ]);
            if ($Common['result'] == 'error') {
                return $Common;
            }
        }
        // 一级关系绑定
        $UserRelation = new UserRelation();
        $UserRelation->children_id = $user->id;    //注册用户ID
        $UserRelation->parent_id = $User->id;    //一级ID
        $UserRelation->level = UserRelation::USER_RELATION_LEVEL_ONE;
        $UserRelation->save();
        //二级关系绑定
        if ($User->UserRelation) {
            $UserRelation = new UserRelation();
            $UserRelation->children_id = $user->id;  //注册用户ID
            $UserRelation->parent_id = $User->UserRelation->parent_id;  //二级ID
            $UserRelation->level = UserRelation::USER_RELATION_LEVEL_TWO;
            $UserRelation->save();
            //三级关系绑定
            if ($User->UserRelation->UserRelation) {
                $UserRelation = new UserRelation();
                $UserRelation->children_id = $user->id;  //注册用户ID
                $UserRelation->parent_id = $User->UserRelation->UserRelation->parent_id;  //三级ID
                $UserRelation->level = UserRelation::USER_RELATION_LEVEL_THREE;
                $UserRelation->save();
            }
        }
    }
    ...
}
public function register(Request $request)
{
    ...
    $addUser->save();
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

    // 用户关系绑定
    if ($request->has('uuid')) {
        $User = User::where('uuid', $request->uuid)->with([ //一级
            'UserRelation' => function ($q) {   //二级
                $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE)->with(['UserRelation' => function ($q) {   //三级
                    $q->where('level', UserRelation::USER_RELATION_LEVEL_ONE);
                }]);
            }
        ])->first();
        // 注册奖励处理
        if ($Distribution) {
            User::where('id', $User->id)->increment('money', $price);
            $DistributionLog = new DistributionLog();
            $DistributionLog->user_id = $User->id;
            $DistributionLog->children_id = $addUser->id;
            $DistributionLog->name = $Distribution->name;
            $DistributionLog->type = $Distribution->DistributionRule[0]->type;
            $DistributionLog->level = DistributionLog::DISTRIBUTION_LOG_LEVEL_ONE;
            $DistributionLog->price = $price;
            $DistributionLog->save();
            $Money = new MoneyLog();
            $Money->user_id = $User->id;
            $Money->type = MoneyLog::MONEY_LOG_TYPE_INCOME;
            $Money->money = $price;
            $Money->remark = '邀请奖励，获得' . ($price / 100) . '元';
            $Money->save();
            $Common = (new Common)->inviteReward([
                'money_id' => $Money->id,  //资金记录ID
                'total' => $price,    //奖励金额
                'user_id' => $User->id   //用户ID
            ]);
            if ($Common['result'] == 'error') {
                return $Common;
            }
        }
        // 一级关系绑定
        $UserRelation = new UserRelation();
        $UserRelation->children_id = $addUser->id;    //注册用户ID
        $UserRelation->parent_id = $User->id;    //一级ID
        $UserRelation->level = UserRelation::USER_RELATION_LEVEL_ONE;
        $UserRelation->save();
        //二级关系绑定
        if ($User->UserRelation) {
            $UserRelation = new UserRelation();
            $UserRelation->children_id = $addUser->id;  //注册用户ID
            $UserRelation->parent_id = $User->UserRelation->parent_id;  //二级ID
            $UserRelation->level = UserRelation::USER_RELATION_LEVEL_TWO;
            $UserRelation->save();
            //三级关系绑定
            if ($User->UserRelation->UserRelation) {
                $UserRelation = new UserRelation();
                $UserRelation->children_id = $addUser->id;  //注册用户ID
                $UserRelation->parent_id = $User->UserRelation->UserRelation->parent_id;  //三级ID
                $UserRelation->level = UserRelation::USER_RELATION_LEVEL_THREE;
                $UserRelation->save();
            }
        }
    }
}   
```

```php
#api\app\Models\v1\User.php
//用户关系
public function UserRelation()
{
    return $this->hasOne(UserRelation::class, 'children_id', 'id');
}
```

###### 添加按钮

```vue
#trade\Dsshop\pages\user\user.vue
<template>
	<list-cell icon="icon-share" iconColor="#9789f7" @eventClick="navTo('/pages/user/share')" title="分享" tips="邀请好友赢10元奖励"></list-cell>
</template>
<script>

</script>
```

######授权登录添加关联代码

```vue
#trade\Dsshop\pages\public\login.vue
<script>
export default{
		data(){
			return {
               ruleForm: {
                   ...
                   uuid: ''
               } 
            }
        },
   	 	onLoad(){
			if(options.uuid){
				this.ruleForm.uuid = options.uuid
			}
		},
}
</script>
```



## 如何更新插件
- 将最新版的插件下载，并替换老的插件，后台可一键升级
## 如何卸载插件
- 后台可一键卸载
