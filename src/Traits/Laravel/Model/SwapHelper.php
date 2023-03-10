<?php

namespace TreasureChest\Traits\Laravel\Model;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 列表成员交换排序处理助手
 * 默认记录创建时令排序字段等于主键id
 *
 * @usage User::swap(1, 3);
 */
trait SwapHelper
{
    /**
     * 根据ID交换成员顺序
     * @param        $id_from
     * @param        $id_to
     * @param string $unique_key_field
     * @param string $sort_field
     * @return bool
     * @throws Exception
     */
    public static function swap($id_from, $id_to, string $unique_key_field = 'id', string $sort_field = 'sort'): bool
    {
        /** @var Model $from */
        $from = static::query()->where($unique_key_field, $id_from)->first();
        if (is_null($from)) {
            ignore_exception("ID={$id_from} 起始记录未找到");
        }

        /** @var Model $to */
        $to = static::query()->where($unique_key_field, $id_to)->first();
        if (is_null($to)) {
            ignore_exception("ID={$id_to} 交换记录未找到");
        }

        static::checkSwap($from, $to);

        DB::beginTransaction();
        try {
            $from->update([$sort_field => $to->{$sort_field}]);
            $to->update([$sort_field => $from->{$sort_field}]);
            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }

        return true;
    }

    /**
     * 满足交换得业务条件判断:比如检测是在同级的成员才可以交换位置。这里暴露一个插槽提供用来校验可以交换的条件
     *
     * @param $from
     * @param $to
     * @return void
     */
    public static function checkSwap($from, $to)
    {
        # business check code
    }
}