<?php
/**
 * @filesource modules/dms/models/home.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Dms\Home;

use Kotchasan\Database\Sql;

/**
 * โมเดลสำหรับอ่านข้อมูลแสดงในหน้า  Home
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * เอกสารใหม่ ระหว่างวันที่ระบุ
     *
     * @param array $login
     * @param string $from
     * @param string $to
     *
     * @return int
     */
    public static function getNew($login, $from, $to)
    {
        $where = [];
        if (!empty($login['department'])) {
            $where[] = ['D.value', $login['department']];
        }
        $where[] = Sql::BETWEEN('A.create_date', $from, $to);
        $search = static::createQuery()
            ->from('dms A')
            ->join('dms_meta D', 'INNER', [['D.dms_id', 'A.id'], ['D.type', 'department']])
            ->join('dms_files F', 'LEFT', ['F.dms_id', 'A.id'])
            ->where($where)
            ->notExists('dms_download', [
                ['dms_id', 'A.id'],
                ['member_id', $login['id']],
                ['file_id', Sql::create('CASE WHEN A.`url`="" THEN F.`id` ELSE 0 END')]
            ])
            ->first(Sql::COUNT('A.id', 'count'));
        if ($search) {
            return $search->count;
        }
        return 0;
    }
}
