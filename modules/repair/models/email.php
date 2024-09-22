<?php
/**
 * @filesource modules/repair/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Email;

use Kotchasan\Database\Sql;
use Kotchasan\Date;
use Kotchasan\Language;

/**
 * ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\KBase
{
    /**
     * ส่งอีเมลและ LINE แจ้งการทำรายการ
     *
     * @param int $repair_id
     *
     * @return string
     */
    public static function send($repair_id)
    {
        // ตรวจสอบรายการที่ต้องการ
        $q1 = \Kotchasan\Model::createQuery()
            ->select('repair_id', Sql::MAX('id', 'max_id'))
            ->from('repair_status')
            ->groupBy('repair_id');
        $order = \Kotchasan\Model::createQuery()
            ->from('repair R')
            ->join([$q1, 'T'], 'LEFT', ['T.repair_id', 'R.id'])
            ->join('repair_status S', 'LEFT', ['S.id', 'T.max_id'])
            ->join('inventory V', 'LEFT', ['V.id', 'R.inventory_id'])
            ->join('inventory_stock T', 'LEFT', ['T.inventory_id', 'V.id'])
            ->join('category C', 'LEFT', [['C.category_id', 'S.status'], ['C.type', 'repairstatus']])
            ->where(['R.id', $repair_id])
            ->first(
                'R.job_id',
                'T.product_no',
                'V.topic',
                'R.job_description',
                'R.create_date',
                'R.customer_id',
                'C.topic status_text',
                'S.comment',
                'S.operator_id',
                'S.status'
            );
        if ($order) {
            $lines = [];
            $emails = [];
            $name = '';
            $mailto = '';
            $line_uid = '';
            // รายชื่อผู้รับ
            if (self::$cfg->demo_mode) {
                // โหมดตัวอย่าง ส่งหาแอดมินเท่านั้น
                $where = [
                    ['id', 1]
                ];
            } elseif ($order->status == self::$cfg->repair_first_status) {
                // ส่งหาผู้ทำรายการ ผู้จัดการ และ ช่างทุกคน
                $where = [
                    ['id', $order->customer_id],
                    ['status', 1],
                    ['permission', 'LIKE', '%,can_manage_repair,%']
                ];
            } else {
                // ส่งหาผู้ทำรายการ ผู้จัดการ และ ช่างผู้รับผิดชอบ
                $where = [
                    ['id', [$order->customer_id, $order->operator_id]],
                    ['permission', 'LIKE', '%,can_manage_repair,%']
                ];
            }
            // ตรวจสอบรายชื่อผู้รับ
            $query = \Kotchasan\Model::createQuery()
                ->select('id', 'username', 'name', 'line_uid')
                ->from('user')
                ->where(['active', 1])
                ->andWhere($where, 'OR')
                ->cacheOn();
            if (self::$cfg->demo_mode) {
                $query->andWhere(['social', 0]);
            }
            foreach ($query->execute() as $item) {
                if ($item->id == $order->customer_id) {
                    // ผู้จอง
                    $name = $item->name;
                    $mailto = $item->username;
                    $line_uid = $item->line_uid;
                } else {
                    // เจ้าหน้าที่
                    $emails[] = $item->name.'<'.$item->username.'>';
                    if ($item->line_uid != '') {
                        $lines[] = $item->line_uid;
                    }
                }
            }
            // ข้อความ
            $msg = [
                '{LNG_Repair jobs} '.$order->job_id,
                '{LNG_Informer} : '.$name,
                '{LNG_Equipment} : '.$order->topic,
                '{LNG_Serial/Registration No.} : '.$order->product_no,
                '{LNG_Date} : '.Date::format($order->create_date),
                '{LNG_Problems and repairs details} : '.$order->job_description
            ];
            if ($order->status != self::$cfg->repair_first_status) {
                $msg[] = '{LNG_Status} : '.$order->status_text;
            }
            // ข้อความของ user
            $msg = Language::trans(implode("\n", $msg));
            // ข้อความของแอดมิน
            $admin_msg = $msg."\nURL : ".WEB_URL.'index.php?module=repair-setup';
            // ส่งข้อความ
            $ret = [];
            if (!empty(self::$cfg->repair_line_id)) {
                // อ่าน token
                $search = \Kotchasan\Model::createQuery()
                    ->from('line')
                    ->where(['id', self::$cfg->repair_line_id])
                    ->cacheOn()
                    ->first('token');
                if ($search) {
                    $err = \Gcms\Line::notify($admin_msg, $search->token);
                    if ($err != '') {
                        $ret[] = $err;
                    }
                }
            }
            if (!empty(self::$cfg->line_channel_access_token)) {
                // LINE ส่วนตัว
                if (!empty($lines)) {
                    $err = \Gcms\Line::sendTo($lines, $admin_msg);
                    if ($err != '') {
                        $ret[] = $err;
                    }
                }
                if (!empty($line_uid)) {
                    $err = \Gcms\Line::sendTo($line_uid, $msg);
                    if ($err != '') {
                        $ret[] = $err;
                    }
                }
            }
            if (self::$cfg->noreply_email != '') {
                // หัวข้ออีเมล
                $subject = '['.self::$cfg->web_title.'] '.Language::get('Repair jobs').' '.$order->status_text;
                // ส่งอีเมลไปยังผู้ทำรายการเสมอ
                $err = \Kotchasan\Email::send($name.'<'.$mailto.'>', self::$cfg->noreply_email, $subject, nl2br($msg));
                if ($err->error()) {
                    // คืนค่า error
                    $ret[] = strip_tags($err->getErrorMessage());
                }
                // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
                if (!empty(self::$cfg->repair_send_mail)) {
                    // รายละเอียดในอีเมล (แอดมิน)
                    $admin_msg = nl2br($admin_msg);
                    foreach ($emails as $item) {
                        // ส่งอีเมล
                        $err = \Kotchasan\Email::send($item, self::$cfg->noreply_email, $subject, $admin_msg);
                        if ($err->error()) {
                            // คืนค่า error
                            $ret[] = strip_tags($err->getErrorMessage());
                        }
                    }
                }
            }
            // คืนค่า
            return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", array_unique($ret));
        }
        // not found
        return Language::get('Sorry, Item not found It&#39;s may be deleted');
    }
}
