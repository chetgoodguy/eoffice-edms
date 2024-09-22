<?php
/**
 * @filesource modules/dms/views/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Dms\Setup;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=dms-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * แสดงรายการเอกสารส่ง
     *
     * @param Request $request
     * @param array $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        // ค่าที่ส่งมา
        $params = [
            'from' => $request->request('from')->date(),
            'to' => $request->request('to')->date()
        ];
        // หมวดหมู่
        $category = \Dms\Category\Model::init();
        foreach ($category->items() as $k => $label) {
            $params[$k] = $request->request($k)->topic();
        }
        if (!($login['status'] == 1 || empty($login['department']) || empty(self::$cfg->dms_upload_options))) {
            $params['department'] = $login['department'];
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Dms\Setup\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('dmsSetup_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('dmsSetup_sort', 'create_date DESC')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'url'],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/dms/model/setup/action',
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
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['topic', 'document_no'],
            /* ตัวเลือกการแสดงผลที่ส่วนหัว */
            'filters' => [
                [
                    'name' => 'from',
                    'type' => 'date',
                    'text' => '{LNG_from}',
                    'value' => $params['from']
                ],
                [
                    'name' => 'to',
                    'type' => 'date',
                    'text' => '{LNG_to}',
                    'value' => $params['to']
                ]
            ],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'create_date' => [
                    'text' => '{LNG_Date}',
                    'sort' => 'create_date'
                ],
                'document_no' => [
                    'text' => '{LNG_Document No.}',
                    'sort' => 'document_no'
                ],
                'topic' => [
                    'text' => '{LNG_Document title}',
                    'sort' => 'topic'
                ]
            ],
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => [$this, 'onCreateButton'],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'files' => [
                    'class' => 'icon-documents button brown',
                    'href' => $uri->createBackUri(['module' => 'dms-files', 'id' => ':id']),
                    'text' => '{LNG_File}'
                ],
                'edit' => [
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(['module' => 'dms-write', 'id' => ':id']),
                    'text' => '{LNG_Edit}'
                ]
            ],
            /* ปุ่มเพิ่ม */
            'addNew' => [
                'class' => 'float_button icon-new',
                'href' => $uri->createBackUri(['module' => 'dms-write']),
                'title' => '{LNG_Upload} {LNG_Document}'
            ]
        ]);
        foreach ($category->items() as $k => $label) {
            if ($k == 'department' && !($login['status'] == 1 || empty($login['department']) || empty(self::$cfg->dms_upload_options))) {
                $categories = [];
                foreach ($login['department'] as $department) {
                    $categories[$department] = $category->get('department', $department);
                }
            } else {
                $categories = $category->toSelect($k);
            }
            $table->filters[] = [
                'name' => $k,
                'text' => $label,
                'datalist' => $categories,
                'value' => $params[$k]
            ];
            $table->headers[$k] = [
                'text' => $label,
                'class' => 'center'
            ];
            $table->cols[$k] = [
                'class' => 'center'
            ];
        }
        // save cookie
        setcookie('dmsSetup_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('dmsSetup_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
        // คืนค่า HTML
        return $table->render();
    }

    /**
     * จัดรูปแบบการแสดงผลในแต่ละแถว
     *
     * @param array $item
     *
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        $item['cabinet'] = '<span class="two_lines" title="'.$item['cabinet'].'">'.$item['cabinet'].'</span>';
        if (isset($item['department'])) {
            $item['department'] = '<span class="two_lines" title="'.$item['department'].'">'.$item['department'].'</span>';
        }
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        return $item;
    }

    /**
     * ฟังกชั่นตรวจสอบว่าสามารถสร้างปุ่มได้หรือไม่
     *
     * @param array $item
     *
     * @return array
     */
    public function onCreateButton($btn, $attributes, $item)
    {
        if ($btn == 'files') {
            if ($item['url'] != '') {
                $attributes['href'] = $item['url'];
                $attributes['class'] = 'button blue icon-world';
                $attributes['text'] = '{LNG_URL}';
                $attributes['target'] = '_blank';
            }
        }
        return $attributes;
    }
}
