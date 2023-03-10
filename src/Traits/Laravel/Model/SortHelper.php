<?php

namespace TreasureChest\Traits\Laravel\Model;

use Exception;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * 重排起始记录和目标记录以及之间成员的排序处理助手
 * 默认记录创建时令排序字段等于主键id
 *
 * @usage Category::sort(1, 3， ['parent_id']);
 */
trait SortHelper
{
    /**
     * 列表记录排序，resort的升级版。只对操作的两个记录间的记录进行重排
     *
     * @param        $id_from          起始记录ID
     * @param        $id_to            目标记录ID
     * @param string $to_position      起始记录移动后相对目标记录的位置  前-before 后-after
     * @param array  $limit_fields     起始记录和目标记录的重排字段限制：通常是要求具有相同的某些字段值，比如在相同的分类下
     * @param string $unique_key_field 起始记录和目标记录的唯一ID所在的字段名称，默认主键ID
     * @param string $sort_field       排序字段 默认sort
     * @param string $sort_direction   列表记录本来按sort要求的排序规则，asc-升序 降序-desc；比如默认记录按sort字段升序
     * @return bool
     * @throws Exception
     */
    public static function sort($id_from, $id_to, string $to_position = 'before', array $limit_fields = [], string $unique_key_field = 'id', string $sort_field = 'sort', string $sort_direction = 'asc')
    {
        if ($id_from == $id_to) {
            return true;
        }

        # 先确定调整顺序的两个成员，按业务要求的排序，确定其先后顺序；
        $items = static::query()
            ->whereIn($unique_key_field, [$id_from, $id_to])
            ->orderBy($sort_field, $sort_direction)
            ->get()
            ->keyBy($unique_key_field);
        if (empty($items[$id_from])) {
            ignore_exception("起始记录已被删除");
        }
        if (empty($items[$id_to])) {
            ignore_exception("目标记录已被删除");
        }

        $from = $items[$id_from];
        $to   = $items[$id_to];

        $items = array_values($items->toArray());

        # 排序规则为升序排列
        if ($items[0][$unique_key_field] == $id_from) {
            # 重排前，起始记录排序在前,向下移动
            $sort_start   = $from->{$sort_field};
            $sort_end     = $to->{$sort_field};
            $to_direction = "down";
        } else {
            # 重排前，起始记录排序在后,向上移动
            $sort_start   = $to->{$sort_field};
            $sort_end     = $from->{$sort_field};
            $to_direction = "up";
        }

        # 起始结束节点间公有的条件
        $extra_condition = static::checkSort($from, $to, $limit_fields);

        $list = static::query()
            ->select([$unique_key_field, $sort_field])
            ->where($sort_field, '>=', $sort_start)
            ->where($sort_field, '<=', $sort_end)
            ->when(!empty($extra_condition), function ($query) use ($extra_condition) {
                $query->where($extra_condition);
            })
            ->orderBy($sort_field, $sort_direction)
            ->get()
            ->toArray();

        # 找到重排前，需要重排的记录的排序值列表，先取出起始结束间的顺序
        $wait_to_resort = array_column($list, $sort_field);

        # 移动后起始节点和结束节点的关系
        $start_end_relation = [];

        # 向下移动
        if ($to_direction == 'down') {
            if ($to_position == 'before') {
                $start_end_relation[] = array_shift($list);
                $start_end_relation[] = array_pop($list);
            } else {
                $start_end_relation[] = array_pop($list);
                $start_end_relation[] = array_shift($list);
            }
            $sort_list = array_merge($list, $start_end_relation);
        } else {
            if ($to_position == 'before') {
                $start_end_relation[] = array_pop($list);
                $start_end_relation[] = array_shift($list);
            } else {
                $start_end_relation[] = array_shift($list);
                $start_end_relation[] = array_pop($list);
            }
            $sort_list = array_merge($start_end_relation, $list);
        }

        $unique_key_field_list = array_column($sort_list, $unique_key_field);

        self::sortExecute($unique_key_field_list, $unique_key_field, $sort_field, $wait_to_resort);

        return true;
    }

    /**
     * 执行重排写入数据库
     *
     * @param array  $unique_key_field_list 待重排记录唯一键列表
     * @param string $unique_key_field      待重排记录唯一键字段名
     * @param string $sort_field            排序字段名
     * @param array  $wait_to_resort        待重排记录排序值列表
     * @return void
     * @throws Exception
     */
    public static function sortExecute(array $unique_key_field_list, string $unique_key_field, string $sort_field, array $wait_to_resort): void
    {
        DB::beginTransaction();
        try {
            $index = 0;
            foreach ($unique_key_field_list as $unique_key_field_transport) {
                /** @var Model $item */
                $item = static::query()->where($unique_key_field, $unique_key_field_transport)->first();
                # 重排期间指定列表里被删掉的记录直接忽略
                if (is_null($item)) {
                    continue;
                }
                $item->update([
                    $sort_field => $wait_to_resort[$index],
                ]);
                $index++;
            }

            DB::commit();
        } catch (Exception $exception) {
            DB::rollBack();
            throw $exception;
        }
    }

    /**
     * 前端传来的排序起始与结束节点间的业务判断：比如检测是在同级的成员才可以交换位置。这里暴露一个插槽提供用来校验可以交换的条件，并最后返回这个限制，否则无法控制两个成员间的其他成员范围
     *
     * @param $from
     * @param $to
     * @param $limit_fields
     * @return array
     */
    public static function checkSort($from, $to, $limit_fields)
    {
        # business check code
        return [];
    }
}