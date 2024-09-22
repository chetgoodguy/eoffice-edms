<?php
/**
 * @filesource modules/edocument/models/download.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Download;

use Gcms\Login;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * โมเดลสำหรับดาวน์โหลดเอกสาร
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * ดำเนินการดาวน์โหลดเอกสาร
     *
     * @param Request $request
     */
    public function index(Request $request)
    {
        $response = [];

        // ตรวจสอบ session, referer, และการ login
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            // ตรวจสอบรูปแบบของ ID ที่ส่งมา
            if (preg_match('/download_([0-9]+)/', $request->post('id')->toString(), $match)) {
                // ดึงข้อมูลเอกสารจากฐานข้อมูล
                $document = $this->db()->createQuery()
                    ->from('edocument E')
                    ->join('edocument_download D', 'LEFT', [
                        ['D.document_id', 'E.id'],
                        ['D.member_id', (int) $login['id']]
                    ])
                    ->where(['E.id', (int) $match[1]])
                    ->groupBy('E.id')
                    ->first('E.id', 'E.topic', 'D.downloads', 'E.file', 'E.ext', 'E.size');

                // ตรวจสอบว่ามีเอกสารหรือไม่
                if ($document) {
                    $filePath = ROOT_PATH.DATA_FOLDER.'edocument/'.$document->file;

                    // ตรวจสอบว่าไฟล์มีอยู่จริง
                    if (is_file($filePath)) {
                        // อัปเดตจำนวนการดาวน์โหลด
                        $this->db()->update(
                            $this->getTableName('edocument_download'),
                            [
                                ['document_id', $document->id],
                                ['member_id', (int) $login['id']]
                            ],
                            [
                                'downloads' => (int) $document->downloads + 1,
                                'last_update' => time()
                            ]
                        );

                        // สร้าง ID สำหรับการดาวน์โหลดไฟล์
                        $downloadId = uniqid();

                        // บันทึกข้อมูลไฟล์ลงใน session
                        $fileSession = [
                            'file' => $filePath,
                            'size' => $document->size
                        ];

                        // กำหนดชื่อไฟล์ที่ดาวน์โหลด
                        if (!empty(self::$cfg->edocument_download_action) && in_array($document->ext, self::$cfg->know_file_typies)) {
                            $fileSession['name'] = '';
                        } else {
                            $fileSession['name'] = preg_replace('/[,;:_\-\(\)\?\&\+\[\]\s]{1,}/', '_', $document->topic).'.'.$document->ext;
                        }

                        // บันทึกข้อมูลไฟล์ลงใน session
                        $_SESSION[$downloadId] = $fileSession;

                        // คืนค่าการดาวน์โหลด
                        $response['target'] = self::$cfg->edocument_download_action;
                        $response['url'] = WEB_URL.'modules/edocument/filedownload.php?id='.$downloadId;
                    } else {
                        // ไฟล์ไม่พบ
                        $response['alert'] = Language::get('File not found');
                    }
                    $response['modal'] = 'close';
                }
            }
        }

        // ตรวจสอบว่ามีข้อมูลที่ต้องคืนค่าหรือไม่
        if (empty($response)) {
            $response['alert'] = Language::get('Unable to complete the transaction');
        }

        // คืนค่าการตอบกลับเป็น JSON
        echo json_encode($response);
    }

    /**
     * อ่านเอกสารจาก ID
     *
     * @param int $id
     * @param array $login
     * @return object|null
     */
    public static function get($id, $login)
    {
        $search = static::createQuery()
            ->from('edocument A')
            ->join('edocument_download E', 'INNER', [
                ['E.id', 'A.id'],
                ['E.member_id', (int) $login['id']]
            ])
            ->where(['A.id', $id])
            ->first('A.id', 'A.document_no', 'E.downloads', 'A.topic', 'A.ext', 'A.sender_id', 'A.size', 'A.last_update', 'A.department', 'A.detail');

        if ($search) {
            $search->department = explode(',', trim($search->department, ','));
        }

        return $search;
    }
}
