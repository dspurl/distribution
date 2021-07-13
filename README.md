# 分销

## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dshop v2.0.0及以上
- 已同步版本：dsshop v2.0.1

## 功能介绍
- 支持上下三级关系自动绑定
- 可根据自定义规则，在不同的业务场景进行分销返佣
- 分销可针对不同级别配置不同的返佣金额和返佣方式
- 支持按固定金额/值返佣和按比例返佣
- 不仅仅支持金额返佣，也可以配合其它业务场景进行返佣，如积分，只需修改业务代码即可
- 分销现只支持一级，可实现返佣后进行站内通知功能
- 暂不支持后台报表展示，后期会补全
- 暂仅支持扫码通过H5唤起

#### 八、 使用说明

##### 管理员南

- 后台可以添加分销，添加时，可以配置分销返佣方式和返佣值
- 添加的分销需要开发人员实现业务代码才有效
- 请不要随意修改分销标识，修改后需要开发人员修改对应的业务代码，不然将会失效

##### 开发指南

- 插件并不整合到业务代码中，所以当你安装插件后，需要根据以下步骤进行个性化的开发

#### 移动端

###### pages.json添加路由

```js
#client\uni-app\mix-mall\pages.json
    ,{
        "path": "pages/distribution/share",
            "style": {
                "enablePullDownRefresh": true,
                    "navigationBarTitleText": "邀请好友"
            }
    }
```

###### 添加按钮

```vue
#client\uni-app\mix-mall\pages\user\user.vue
<template>
	<list-cell icon="icon-share" iconColor="#9789f7" @eventClick="navTo('/pages/distribution/share')" title="分享" tips="邀请好友赢10元奖励"></list-cell>
</template>
<script>

</script>
```

###### 授权登录添加关联代码

```vue
#client\uni-app\mix-mall\pages\public\login.vue
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

#### 网站

###### 添加关系绑定和分销机制

```php
#api\app\Models\v1\User.php
	/**
     * 用户关系
     * @return \Illuminate\Database\Eloquent\Relations\HasOne
     */
    public function UserRelation()
    {
        return $this->hasOne(UserRelation::class, 'children_id', 'id');
    }
```

配置模板通知

```php
#api\config\notification.php
'wechat'=>[ //微信公众号
    ...
    'recommend_success'=>env('WECHAT_SUBSCRIPTION_INFORMATION_RECOMMEND_SUCCESS',''),  //	推荐会员成功提醒
    ],
```

```shell
#.env
WECHAT_SUBSCRIPTION_INFORMATION_RECOMMEND_SUCCESS=
```



## 如何更新插件
- 将最新版的插件下载，并替换老的插件，后台可一键升级
## 如何卸载插件
- 后台可一键卸载