<?php
/**
 * @filesource modules/inventory/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Inventory\Index;

use Kotchasan\Language;

/**
 * module=inventory
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param int $member_id
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($member_id)
    {
        $select = [
            'R.*',
            'S.product_no',
            'U.create_date'
        ];
        $query = static::createQuery()
            ->from('inventory R')
            ->join('inventory_stock S', 'INNER', ['S.inventory_id', 'R.id'])
            ->join('inventory_user U', 'INNER', ['U.stock_id', 'S.id'])
            ->where(['U.member_id', $member_id]);
        $n = 1;
        foreach (Language::get('INVENTORY_CATEGORIES') as $type => $text) {
            $query->join('inventory_meta M'.$n, 'LEFT', [['M'.$n.'.inventory_id', 'R.id'], ['M'.$n.'.name', $type]]);
            $select[] = 'M'.$n.'.value '.$type;
            ++$n;
        }
        return $query->select($select);
    }
}
