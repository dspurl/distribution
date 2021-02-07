<?php

namespace App\Http\Controllers\v1\Plugin\Admin;

use App\Code;
use App\Http\Requests\v1\SubmitDistributionRequest;
use App\Models\v1\Distribution;
use App\Models\v1\DistributionRule;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\DB;

/**
 * distribution
 * 分销管理
 * Class DistributionController
 * @package App\Http\Controllers\v1\Plugin\Admin
 */
class DistributionController extends Controller
{
    /**
     * DistributionList
     * 分销列表
     * @param Request $request
     * @return \Illuminate\Http\Response
     * @queryParam  limit int 每页显示条数
     * @queryParam  sort string 排序
     * @queryParam  page string 页码
     */
    public function list(Request $request)
    {
        DistributionRule::$withoutAppends = false;
        $q = Distribution::query();
        if ($request->has('sort')) {
            $sortFormatConversion = sortFormatConversion($request->sort);
            $q->orderBy($sortFormatConversion[0], $sortFormatConversion[1]);
        }
        $limit = $request->limit;
        $q->with(['DistributionRule']);
        $paginate = $q->paginate($limit);
        return resReturn(1, $paginate);
    }


    /**
     * DistributionCreate
     * 分销添加
     * @param SubmitDistributionRequest $request
     * @return \Illuminate\Http\Response
     * @queryParam  name string 分销名称
     * @queryParam  identification string 分销标识
     * @queryParam  level int 分销级别
     * @queryParam  state int 状态
     */
    public function create(SubmitDistributionRequest $request)
    {
        $return = DB::transaction(function () use ($request) {
            $Distribution = new Distribution();
            $Distribution->name = $request->name;
            $Distribution->identification = $request->identification;
            $Distribution->level = $request->level;
            $Distribution->state = $request->state;
            $Distribution->save();
            foreach ($request->distribution_rule as $distribution_rule) {
                $DistributionRule = new DistributionRule();
                $DistributionRule->distribution_id = $Distribution->id;
                $DistributionRule->name = $distribution_rule['name'];
                $DistributionRule->type = $distribution_rule['type'];
                $DistributionRule->level = $distribution_rule['level'];
                $DistributionRule->price = $distribution_rule['price'];
                $DistributionRule->save();
            }
            return 1;
        }, 5);
        if ($return == 1) {
            return resReturn(1, '添加成功');
        } else {
            return resReturn(0, '添加失败', Code::CODE_PARAMETER_WRONG);
        }
    }

    /**
     * DistributionEdit
     * 分销修改
     * @param int $id
     * @param SubmitDistributionRequest $request
     * @return \Illuminate\Http\Response
     * @queryParam  id int 分销ID
     * @queryParam  name string 分销名称
     * @queryParam  identification string 分销标识
     * @queryParam  level int 分销级别
     * @queryParam  state int 状态
     */
    public function edit($id, SubmitDistributionRequest $request)
    {
        if (!$id) {
            return resReturn(0, '参数有误', Code::CODE_PARAMETER_WRONG);
        }
        $return = DB::transaction(function () use ($request) {
            $Distribution = Distribution::find($request->id);
            $Distribution->name = $request->name;
            $Distribution->identification = $request->identification;
            $Distribution->level = $request->level;
            $Distribution->state = $request->state;
            $Distribution->save();
            $DistributionRuleAll = [];
            foreach ($request->distribution_rule as $distribution_rule) {
                if ($distribution_rule['id']) {
                    $DistributionRule = DistributionRule::find($distribution_rule['id']);
                } else {
                    $DistributionRule = new DistributionRule();
                    $DistributionRule->distribution_id = $Distribution->id;
                }
                $DistributionRule->name = $distribution_rule['name'];
                $DistributionRule->type = $distribution_rule['type'];
                $DistributionRule->level = $distribution_rule['level'];
                $DistributionRule->price = $distribution_rule['price'];
                $DistributionRule->save();
                $DistributionRuleAll[] = $DistributionRule->id;
            }
            //删除去除的规则
            DistributionRule::where('distribution_id', $Distribution->id)->whereNotIn('id', $DistributionRuleAll)->delete();
            return 1;
        }, 5);
        if ($return == 1) {
            return resReturn(1, '更新成功');
        } else {
            return resReturn(0, '更新失败', Code::CODE_PARAMETER_WRONG);
        }
    }

    /**
     * DistributionDestroy
     * 分销删除
     * @param int $id
     * @return \Illuminate\Http\Response
     * @queryParam  id int 分销ID
     */
    public function destroy($id)
    {
        $return = DB::transaction(function () use ($id) {
            Distribution::destroy($id);
            DistributionRule::where('distribution_id', $id)->delete();
            return 1;
        }, 5);
        if ($return == 1) {
            return resReturn(1, '删除成功');
        } else {
            return resReturn(0, '删除失败', Code::CODE_PARAMETER_WRONG);
        }
    }
}
