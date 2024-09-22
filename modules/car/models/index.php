<?php
/**
 * @filesource modules/car/models/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Index;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=car
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
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = [
            ['V.member_id', $params['member_id']]
        ];
        if ($params['vehicle_id'] > 0) {
            $where[] = ['V.vehicle_id', $params['vehicle_id']];
        }
        if ($params['status'] > -1) {
            $where[] = ['V.status', $params['status']];
        }
        if ($params['from'] != '') {
            $where[] = [Sql::DATE('V.begin'), '>=', $params['from']];
        }
        if ($params['to'] != '') {
            $where[] = [Sql::DATE('V.begin'), '<=', $params['to']];
        }
        $sql = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');
        return static::createQuery()
            ->select('V.detail', 'V.id', 'V.vehicle_id', 'V.begin', 'V.end', 'V.status', 'V.approve', 'V.closed', 'V.reason', $sql, 'R.color')
            ->from('car_reservation V')
            ->join('vehicles R', 'INNER', ['R.id', 'V.vehicle_id'])
            ->where($where);
    }

    /**
     * รับค่าจาก action (index.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = [];
        // session, referer
        if ($request->initSession() && $request->isReferer()) {
            // สมาชิก
            $login = Login::isMember();
            // ค่าที่ส่งมา
            $action = $request->post('action')->toString();
            // Database
            $db = $this->db();
            // Table
            $reservation_table = $this->getTableName('car_reservation');
            if ($action === 'cancel' && $login) {
                // ยกเลิกการจอง
                $q1 = Sql::create('(CASE WHEN NOW() BETWEEN V.`begin` AND V.`end` THEN 1 WHEN NOW() > V.`end` THEN 2 ELSE 0 END) AS `today`');
                $search = static::createQuery()
                    ->from('car_reservation V')
                    ->where(['V.id', $request->post('id')->toInt()])
                    ->toArray()
                    ->first('V.*', $q1);
                if ($search && $login['id'] == $search['member_id'] && self::canCancle($search)) {
                    // ยกเลิกการจองโดยผู้จอง
                    $search['status'] = self::$cfg->car_cancled_status;
                    // อัปเดต
                    $db->update($reservation_table, $search['id'], [
                        'status' => $search['status']
                    ]);
                    // Log
                    \Index\Log\Model::add(0, 'car', 'Status', Language::get('BOOKING_STATUS', '', $search['status']).' ID : '.$search['id'], $login['id']);
                    // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
                    $ret['alert'] = \Car\Email\Model::send($search);
                    // reload
                    $ret['location'] = 'reload';
                }
            } elseif ($action === 'delete' && $login && !empty(self::$cfg->car_delete)) {
                // ลบรายการจอง
                $search = $db->first($reservation_table, $request->post('id')->toInt());
                if ($search && $login['id'] == $search->member_id && in_array($search->status, self::$cfg->car_delete)) {
                    // ลบ
                    $db->delete($reservation_table, $search->id);
                    // Log
                    \Index\Log\Model::add(0, 'car', 'Delete', '{LNG_Delete} ID : '.$search->id, $login['id']);
                    // ลบเรียบร้อย
                    $ret['alert'] = Language::get('Successfully deleted');
                    // reload
                    $ret['location'] = 'reload';
                }
            } elseif ($action === 'detail') {
                // แสดงรายละเอียดการจอง
                $search = $this->bookDetail($request->post('id')->toInt());
                if ($search) {
                    $ret['modal'] = \Car\Detail\View::create()->booking($search);
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }

    /**
     * อ่านข้อมูลรายการที่เลือก
     * คืนค่าข้อมูล object ไม่พบคืนค่า null
     *
     * @param int $id
     *
     * @return object|null
     */
    public function bookDetail($id)
    {
        $query = $this->db()->createQuery()
            ->from('car_reservation V')
            ->join('vehicles R', 'LEFT', ['R.id', 'V.vehicle_id'])
            ->join('user U', 'LEFT', ['U.id', 'V.member_id'])
            ->join('user U2', 'LEFT', ['U2.id', 'V.chauffeur'])
            ->where(['V.id', $id]);
        $select = ['V.*', 'R.number', 'U2.name chauffeur_name', 'U2.phone chauffeur_phone',
            'U.name contact', 'U.phone', 'R.color', 'R.seats'];
        $n = 1;
        foreach (Language::get('CAR_SELECT', []) as $key => $label) {
            $query->join('vehicles_meta M'.$n, 'LEFT', [['M'.$n.'.vehicle_id', 'R.id'], ['M'.$n.'.name', $key]]);
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }
        foreach (Language::get('CAR_OPTIONS', []) as $key => $label) {
            $query->join('car_reservation_data M'.$n, 'LEFT', [['M'.$n.'.reservation_id', 'V.id'], ['M'.$n.'.name', $key]]);
            $select[] = 'M'.$n.'.value '.$key;
            ++$n;
        }
        return $query->first($select);
    }

    /**
     * ฟังก์ชั่นตรวจสอบว่าสามารถยกเลิกได้หรือไม่
     *
     * @param array $item
     *
     * @return bool
     */
    public static function canCancle($item)
    {
        if (self::$cfg->car_cancellation == 2) {
            // ก่อนหมดเวลาจอง
            return in_array($item['today'], [0, 1]) && in_array($item['status'], [0, 1]) ? true : false;
        } elseif (self::$cfg->car_cancellation == 1) {
            // ก่อนถึงเวลาจอง
            return $item['today'] == 0 && in_array($item['status'], [0, 1]) ? true : false;
        } else {
            // สถานะรอตรวจสอบ
            return $item['status'] == 0 && $item['approve'] == 1 && $item['approve'] != $item['closed'] ? true : false;
        }
    }
}
