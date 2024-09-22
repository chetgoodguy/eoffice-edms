<?php
/**
 * @filesource modules/index/views/lines.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Index\Lines;

use Kotchasan\DataTable;
use Kotchasan\Http\Request;

/**
 * module=lines
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var Object
     */
    private $category;
    /**
     * ตารางราย ไลน์กลุ่ม
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // แผนก
        $this->category = \Index\Category\Model::init();
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Index\Lines\Model::toDataTable(),
            /* เรียงลำดับ */
            'sort' => 'id desc',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'department'],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/index/model/lines/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => [
                [
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => [
                        'delete' => '{LNG_Delete}'
                    ]
                ]
            ],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'name' => [
                    'text' => '{LNG_LINE group name}'
                ],
                'token' => [
                    'text' => '{LNG_Token}'
                ]
            ],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'send' => [
                    'class' => 'icon-line button orange',
                    'id' => ':id',
                    'text' => '{LNG_Test}'
                ],
                'edit' => [
                    'class' => 'icon-edit button green',
                    'id' => ':id',
                    'text' => '{LNG_Edit}'
                ]
            ],
            /* ปุ่มเพิ่ม */
            'addNew' => [
                'class' => 'float_button icon-new',
                'id' => 'edit_0',
                'title' => '{LNG_Add} {LNG_LINE group account}'
            ]
        ]);
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
        $item['name'] = $item['department'] > 0 ? $this->category->get('department', $item['department']) : $item['name'];
        return $item;
    }
}
