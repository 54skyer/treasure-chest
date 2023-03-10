<?php

namespace TreasureChest\Algorithm\Sort;

use Exception;

class Poker
{
    /**
     * 洗牌算法，随机打乱一个索引数组成员的顺序
     *
     * @param array $pokers
     * @return array
     * @throws Exception
     * @since  2022/5/16 17:42
     * @author sky.zht 405661806@qq.com
     */
    public static function shuffle(array $pokers)
    {
        $count = count($pokers);
        if ($count <= 1) {
            return $pokers;
        }
        $maxCardsIndex = $count - 1;
        if (array_keys($pokers) !== range(0, $maxCardsIndex)) {
            throw new Exception("参数必须为索引数组");
        }

        # 顺序将牌组里的牌随机与其他牌交换顺序，最后返回新的牌组
        for ($i = 0; $i < $count; $i++) {
            $curCard            = $pokers[$i];
            $randIndex          = rand(0, $maxCardsIndex);
            $randCard           = $pokers[$randIndex];
            $pokers[$i]         = $randCard;
            $pokers[$randIndex] = $curCard;
        }
        return $pokers;
    }

}