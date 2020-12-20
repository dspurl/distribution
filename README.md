#评价
## 说明
- 该插件依赖dsshop项目，而非通用插件
- 支持版本:dshop v1.3及以上
- 已同步版本：dsshop v1.4

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
#### 六、 进入数据库，导入 `distribution/distribution.sql` SQL文件（sql文件需要按照下图进行修改，也可后台自行添加权限，效果一样）
![](/image/1.png)
![](/image/2.png)
#### 七、 进入后台，为管理员分配权限
#### 八、 使用分销插件，如这是第一个插件，可直接按以下步骤替换目标文件即可，如安装有其它插件，可能存在修改同一文件的可能，请进行文件比对进行手动修改
- `comment/example/api/Element/WeChatController.php`->`api/app/Http/Controllers/v1/Element/WeChatController.php`
- `comment/example/api/Models/User.php`->`api/app/Http/Controllers/v1/Models/User.php`
- `comment/example/trade/user/user.vue`->`trade/Dsshop/pages/user/user.vue`
- `comment/example/trade/static/share.jpg`->`trade/Dsshop/static/share.jpg`
- `comment/example/trade/public/login.vue`->`trade/Dsshop/pages/public/login.vue`
- `comment/example/trade/public/register.vue`->`trade/Dsshop/pages/public/register.vue`
#### 九、 测试分销的创建、修改、查看上下级是否能获得返佣，如果功能都能正常使用，则说明你的插件安装成功
## 如何更新插件
- 首先请备份项目，升级可能产生问题（如自行修改了涉及到升级的文件、下载的文件不全等问题）
- 首先查看新版本支持的dshop的版本，如果符合，可通过后台直接升级，升级将会自动覆盖原有文件
- 如果升级涉及到手动修改代码部分，升级说明中会进行讲解
## 如何卸载插件
- 插件安装后不建议卸载，因为涉及到多处手动修改的代码
- 可以按以上安装方式反向操作，即可卸载
