<?php
/**
 * @filesource modules/edocument/models/email.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Email;

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
     * @param array $receivers  ID ผู้รับ
     * @param array $departments แผนกผู้รับ
     * @param array $document  ข้อมูล
     *
     * @return string
     */
    public static function send($receivers, $departments, $document)
    {
        $ret = [];
        // ข้อความ
        $msg = [
            '{LNG_E-Document}',
            '{LNG_Document No.}: '.$document['document_no'],
            '{LNG_Document title}: '.$document['topic'],
            '{LNG_Date}: '.Date::format($document['last_update']),
            '',
            '{LNG_A new document has been sent to you. Please check back.}',
            'URL: '.WEB_URL.'index.php?module=edocument'
        ];
        $msg = Language::trans(implode("\n", $msg));
        // ส่งอีเมลไปยังผู้ที่เกี่ยวข้อง
        $emails = [];
        $lines = [];
        if (!empty(self::$cfg->edocument_send_mail) && (!empty($departments) || !empty($receivers))) {
            // query อีเมลผู้รับ
            $query = \Kotchasan\Model::createQuery()
                ->select('U.name', 'U.username', 'U.line_uid')
                ->from('user U')
                ->join('user_meta D', 'LEFT', [['D.member_id', 'U.id'], ['D.name', 'department']]);
            if (!empty($departments)) {
                // ผู้รับในแผนกที่เลือก
                $query->where([
                    ['U.username', '!=', ''],
                    ['U.active', 1],
                    ['D.value', $departments]
                ]);
            } elseif (!empty($receivers)) {
                // ผู้รับที่เลือก
                $query->where(['id', $receivers]);
            }
            foreach ($query->execute() as $item) {
                if ($item->username != '') {
                    $emails[$item->username] = $item->name.'<'.$item->username.'>';
                }
                if ($item->line_uid != '') {
                    $lines[] = $item->line_uid;
                }
            }
            if (!empty($emails)) {
                // ส่งอีเมล
                $subject = '['.self::$cfg->web_title.'] '.Language::get('There are new documents sent to you.');
                $err = \Kotchasan\Email::send(implode(',', $emails), self::$cfg->noreply_email, $subject, nl2br($msg));
                if ($err->error()) {
                    // คืนค่า error
                    $ret[] = strip_tags($err->getErrorMessage());
                }
            }
            // LINE ส่วนตัว
            if (!empty($lines) && !empty(self::$cfg->line_channel_access_token)) {
                $err = \Gcms\Line::sendTo($lines, $msg);
                if ($err != '') {
                    $ret[] = $err;
                }
            }
        }
        // ส่งใลน์
        if (!empty(self::$cfg->edocument_line_id)) {
            // บัญชีไลน์ที่ต้องส่ง
            $lines = [];
            foreach ($departments as $s) {
                if (!empty(self::$cfg->edocument_line_id[$s])) {
                    $lines[] = self::$cfg->edocument_line_id[$s];
                }
            }
            if (!empty($lines)) {
                // อ่าน token
                $query = \Kotchasan\Model::createQuery()
                    ->select('token')
                    ->from('line')
                    ->where(['id', $lines])
                    ->groupBy('token')
                    ->cacheOn();
                foreach ($query->execute() as $item) {
                    $err = \Gcms\Line::notify($msg, $item->token);
                    if ($err != '') {
                        $ret['alert'] = $err;
                    }
                }
            }
        }
        // คืนค่า
        return empty($ret) ? Language::get('Your message was sent successfully') : implode("\n", array_unique($ret));
    }
}
