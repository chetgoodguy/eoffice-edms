<?php
/**
 * @filesource modules/repair/views/history.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\History;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=repair-history
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var obj
     */
    private $statuses;
    /**
     * @var obj
     */
    private $operators;

    /**
     * ประวัติการแจ้งซ่อม
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        $params = [
            'customer_id' => $login['id'],
            'status' => $request->request('status', -1)->toInt()
        ];
        // สถานะการซ่อม
        $this->statuses = \Repair\Status\Model::create();
        // รายชื่อช่างซ่อม
        $this->operators = \Repair\Operator\Model::create();
        // URL สำหรับส่งให้ตาราง
        $uri = self::$request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Repair\History\Model::toDataTable($params),
            /* แบ่งหน้า */
            'perPage' => $request->cookie('repairHistory_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('repairHistory_sort', 'create_date desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['topic'],
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => [
                [
                    'name' => 'status',
                    'text' => '{LNG_Repair status}',
                    'options' => [-1 => '{LNG_all items}'] + $this->statuses->toSelect(),
                    'value' => $params['status']
                ]
            ],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'job_id' => [
                    'text' => '{LNG_Job No.}'
                ],
                'topic' => [
                    'text' => '{LNG_Equipment}'
                ],
                'create_date' => [
                    'text' => '{LNG_Received date}',
                    'class' => 'center',
                    'sort' => 'create_date'
                ],
                'operator_id' => [
                    'text' => '{LNG_Operator}',
                    'class' => 'center'
                ],
                'status' => [
                    'text' => '{LNG_Repair status}',
                    'class' => 'center',
                    'sort' => 'status'
                ],
                'comment' => [
                    'text' => '{LNG_Comment}'
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'topic' => [
                    'class' => 'nowrap'
                ],
                'create_date' => [
                    'class' => 'center nowrap'
                ],
                'operator_id' => [
                    'class' => 'center nowrap'
                ],
                'status' => [
                    'class' => 'center'
                ],
                'comment' => [
                    'class' => 'topic'
                ]
            ],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'print' => [
                    'class' => 'icon-print button print',
                    'href' => WEB_URL.'modules/repair/print.php?id=:id',
                    'target' => 'print',
                    'title' => '{LNG_Print} {LNG_Repair receipt}'
                ],
                'description' => [
                    'class' => 'icon-report button purple',
                    'href' => $uri->createBackUri(['module' => 'repair-detail', 'id' => ':id']),
                    'title' => '{LNG_Repair job description}'
                ]
            ]
        ]);
        // save cookie
        setcookie('repairHistory_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('repairHistory_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML
        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array  $item ข้อมูลแถว
     * @param int    $o    ID ของข้อมูล
     * @param object $prop กำหนด properties ของ TR
     *
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        $item['status'] = '<mark class=term style="background-color:'.$this->statuses->getColor($item['status']).'">'.$this->statuses->get($item['status']).'</mark>';
        $item['operator_id'] = $this->operators->get($item['operator_id']);
        $item['comment'] = '<span class=two_lines title="'.$item['comment'].'">'.$item['comment'].'</span>';
        return $item;
    }
}
