<?php
/**
 * @filesource modules/repair/views/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Setup;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=repair-setup
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
     * รายการซ่อม (ช่างซ่อม)
     *
     * @param Request $request
     * @param array   $params
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $params, $login)
    {
        // สามารถจัดการรายการซ่อมได้
        $isAdmin = Login::checkPermission($login, 'can_manage_repair');
        // สถานะการซ่อม
        $this->statuses = \Repair\Status\Model::create();
        // รายชื่อช่างซ่อม
        $this->operators = \Repair\Operator\Model::create();
        $operators = [];
        if ($isAdmin) {
            $operators[0] = '{LNG_all items}';
        }
        foreach ($this->operators->toSelect() as $k => $v) {
            if ($isAdmin || $k == $login['id']) {
                $operators[$k] = $v;
            }
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Repair\Setup\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('repairSetup_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('repairSetup_sort', 'create_date desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['name', 'phone', 'job_id', 'topic'],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/repair/model/setup/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => [
                [
                    'type' => 'date',
                    'name' => 'from',
                    'text' => '{LNG_from}',
                    'value' => $params['from']
                ],
                [
                    'type' => 'date',
                    'name' => 'to',
                    'text' => '{LNG_to}',
                    'value' => $params['to']
                ],
                [
                    'name' => 'operator_id',
                    'text' => '{LNG_Operator}',
                    'options' => $operators,
                    'value' => $params['operator_id']
                ],
                [
                    'name' => 'status',
                    'text' => '{LNG_Repair status}',
                    'options' => [0 => '{LNG_all items}'] + $this->statuses->toSelect(),
                    'value' => $params['status']
                ]
            ],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'job_id' => [
                    'text' => '{LNG_Job No.}'
                ],
                'name' => [
                    'text' => '{LNG_Informer}',
                    'sort' => 'name'
                ],
                'phone' => [
                    'text' => '{LNG_Phone}',
                    'class' => 'center'
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
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'phone' => [
                    'class' => 'center'
                ],
                'create_date' => [
                    'class' => 'center'
                ],
                'operator_id' => [
                    'class' => 'center'
                ],
                'status' => [
                    'class' => 'center'
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
                'status' => [
                    'class' => 'icon-list button orange',
                    'id' => ':id',
                    'title' => '{LNG_Repair status}'
                ],
                'description' => [
                    'class' => 'icon-report button purple',
                    'href' => $uri->createBackUri(['module' => 'repair-detail', 'id' => ':id']),
                    'title' => '{LNG_Repair job description}'
                ]
            ]
        ]);
        // สามารถแก้ไขใบรับซ่อมได้
        if ($isAdmin) {
            $table->actions[] = [
                'id' => 'action',
                'class' => 'ok',
                'text' => '{LNG_With selected}',
                'options' => [
                    'delete' => '{LNG_Delete}'
                ]
            ];
            $table->buttons['edit'] = [
                'class' => 'icon-edit button green',
                'href' => $uri->createBackUri(['module' => 'repair-receive', 'id' => ':id']),
                'title' => '{LNG_Edit} {LNG_Repair details}'
            ];
        }
        // save cookie
        setcookie('repairSetup_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('repairSetup_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
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
        $item['phone'] = self::showPhone($item['phone']);
        $item['status'] = '<mark class=term style="background-color:'.$this->statuses->getColor($item['status']).'">'.$this->statuses->get($item['status']).'</mark>';
        $item['operator_id'] = $this->operators->get($item['operator_id']);
        return $item;
    }
}
