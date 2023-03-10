<?php

namespace TreasureChest\Traits\Laravel\Model;

use Illuminate\Support\Str;

/**
 * 枚举类型字段处理助手
 * 利用 laravel 的 model 类调用不存在的属性时，会触 get{Str::studly(enum_field)}Attribute(enum_field_value),然后 当程序调用一个不存在或不可见的成员方法时，自动触发执行__call()
 * 相当于把枚举类型字段的翻译的，通过约定特定数据格式的方式统一解析，并用模式方法来实现，减少手动写一堆的 get{Str::studly(enum_field)}Attribute(enum_field_value)
 * 具体实现如下：
 * 1、格式约定：枚举字段定义常量枚举声明和翻译声明 固定格式 枚举：strtoupper("{$enum_field}_ENUM")   枚举释义：strtoupper("MAP_{$enum_field}_CN")
 * 2、调用：可以在 model 的 appends属性 设置 protected $appends = [$enum_field1_text, $enum_field2_text, ...];
 * 或者在模 Model::query()->first(); Model::query()->get() 后循环里显示使用 $model->append([$enum_field1_text, $enum_field2_text, ...])
 *
 * @usage $user->append(['status_text']);
 */
trait EnumHelper
{
//    # 状态字段  status 枚举
//    const STATUS_ENUM = [
//        'IN_USE'   => 1,
//        'STOP_USE' => 0,
//    ];
//
//    # 状态枚举翻译
//    const MAP_STATUS_CN = [
//        self::STATUS_ENUM['IN_USE']   => '使用中',
//        self::STATUS_ENUM['STOP_USE'] => '已停用',
//    ];

    /**
     * @param $name
     * @param $arguments
     * @return mixed|string
     */
    public function __call($name, $arguments)
    {
        // 判断这个东西是不是获取器 TextAttribute
        if (Str::startsWith($name, 'get') && Str::endsWith($name, 'TextAttribute')) {
            $name     = Str::replaceFirst('get', '', $name);
            $name     = Str::replaceLast('TextAttribute', '', $name);
            $name     = Str::snake($name);
            $constant = strtoupper("MAP_{$name}_CN");
            return constant("static::$constant")[$this->$name] ?? '-';
        }
        return parent::__call($name, $arguments);
    }

    /**
     * 获取枚举类属性的选择器，方便前端渲染和后端维护
     * @usage Model::getAttributeEnumSelector(['status']);
     *
     * @param array $attributes
     * @return array
     */
    public static function getAttributeEnumSelector(array $attributes = []): array
    {
        $selectors = [];
        foreach ($attributes as $attribute) {
            $map_attribute_cn_const_key = "static::MAP_" . strtoupper($attribute) . "_CN";
            $map_attribute_cn_const     = constant($map_attribute_cn_const_key);
            if (empty($map_attribute_cn_const)) {
                $selectors[$attribute] = [];
            } else {
                foreach ($map_attribute_cn_const as $enum_value => $enum_label) {
                    $selectors[$attribute][] = [
                        'label' => $enum_label,
                        'value' => $enum_value,
                    ];
                }
            }
        }
        return $selectors;
    }
}