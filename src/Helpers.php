<?php

use Illuminate\Support\Str;

if (!function_exists('removeNullItems')) {
    /**
     * 移除数组中的值为null的成员
     *
     * @param array $array      目标数组
     * @param bool  $recurrence 是否对数组成员进行递归处理
     * @return void
     */
    function removeNullItems(array &$array = [], bool $recurrence = false)
    {
        if ($recurrence) {
            foreach ($array as $k => $v) {
                if (is_null($v)) {
                    unset($array[$k]);
                } elseif (is_array($v)) {
                    removeNullItems($array[$k]);
                }
            }
        } else {
            foreach ($array as $k => $v) {
                if (is_null($v)) {
                    unset($array[$k]);
                }
            }
        }
    }
}

if (!function_exists('format_time_long')) {
    /**
     * 将秒转换成时长描述
     *
     * @param integer $second
     * @return string
     */
    function format_time_long(int $second): string
    {
        $time_long = [];

        if ($second == 0) {
            return "0天";
        }

        $day_long = floor($second / 86400);
        $left     = $second % 86400;

        if ($day_long != 0) {
            $time_long[] = $day_long . '天';
        }

        if ($left != 0) {
            $hour_long = floor($left / 3600);
            if ($hour_long != 0) {
                $time_long[] = $hour_long . '时';
            }
            $left = $left % 3600;
        }

        if ($left != 0) {
            $minute_long = floor($left / 60);
            if ($minute_long != 0) {
                $time_long[] = $minute_long . '分';
            }
            $second_long = $left % 60;
            if ($second_long != 0) {
                $time_long[] = $second_long . '秒';
            }
        }

        return implode('', $time_long);
    }
}

if (!function_exists('arrayTree')) {
    /**
     * arrayTree
     *
     * @param array  $list     目标数组
     * @param string $id       成员唯一标识
     * @param string $pid      成员父级记录唯一标识
     * @param string $children 构造树形结构的自定义字段
     * @return array
     */
    function arrayTree(array $list, string $id = 'id', string $pid = 'parent_id', string $children = 'children'): array
    {
        $tree = $map = [];
        foreach ($list as $item) {
            $map[$item[$id]] = $item;
        }
        foreach ($list as $item) {
            if (isset($item[$pid]) && isset($map[$item[$pid]])) {
                $map[$item[$pid]][$children][] = &$map[$item[$id]];  //& 指向同一个一起改变
            } else {
                $tree[] = &$map[$item[$id]];
            }
        }
        unset($map);
        return $tree;
    }

    if (!function_exists('transformSnakeArray')) {
        /**
         * 把驼峰风格字段名转化为下划线风格
         *
         * @param array $array
         * 获取转换后的属性
         *
         * @return array
         */
        function transformSnakeArray(array &$array): array
        {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    transformSnakeArray($v);
                }
                unset($array[$k]);
                $array[Str::snake($k)] = $v;
            }

            return $array;
        }
    }

    if (!function_exists('transformSnakeArray')) {
        /**
         * 把下划线风格字段名转化为驼峰风格.
         *
         * @param array $array
         * @return array
         */
        function transformCamelArray(array &$array): array
        {
            foreach ($array as $k => $v) {
                if (is_array($v)) {
                    transformCamelArray($v);
                }
                unset($array[$k]);
                $array[Str::camel($k)] = $v;
            }

            return $array;
        }
    }
}