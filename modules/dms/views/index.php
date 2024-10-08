<?php
/**
 * @filesource modules/dms/views/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Dms\Index;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=dms
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
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Dms\Index\Model::toDataTable($params, $login),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('dmsIndex_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'create_date DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'dms_id', 'url'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['topic', 'document_no', 'file_name'],
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
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/dms/model/index/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'create_date' => [
                    'text' => '{LNG_Date}'
                ],
                'document_no' => [
                    'text' => '{LNG_Document No.}'
                ],
                'topic' => [
                    'text' => '{LNG_Document title}'
                ],
                'file_name' => [
                    'text' => '{LNG_File name}'
                ],
                'ext' => [
                    'text' => ''
                ],
                'downloads' => [
                    'text' => ''
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'create_date' => [
                    'class' => 'nowrap'
                ],
                'document_no' => [
                    'class' => 'nowrap'
                ],
                'topic' => [
                    'class' => 'topic'
                ],
                'file_name' => [
                    'class' => 'topic'
                ],
                'ext' => [
                    'class' => 'center'
                ],
                'downloads' => [
                    'class' => 'center'
                ]
            ],
            /* ฟังก์ชั่นตรวจสอบการแสดงผลปุ่มในแถว */
            'onCreateButton' => [$this, 'onCreateButton'],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'download' => [
                    'class' => 'icon-download button purple',
                    'id' => ':dms_id_:id',
                    'text' => '{LNG_Download}'
                ],
                'detail' => [
                    'class' => 'icon-info button orange',
                    'id' => ':dms_id_:id',
                    'text' => '{LNG_Detail}'
                ]
            ]
        ]);
        foreach ($category->items() as $k => $label) {
            if ($k != 'department') {
                $table->filters[] = [
                    'name' => $k,
                    'text' => $label,
                    'datalist' => $category->toSelect($k),
                    'value' => $params[$k]
                ];
            }
            $table->headers[$k] = [
                'text' => $label,
                'class' => 'center'
            ];
            $table->cols[$k] = [
                'class' => 'center nowrap'
            ];
        }
        // save cookie
        setcookie('dmsIndex_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
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
        $item['topic'] = '<span class=second_lines>'.$item['topic'].'</span>';
        $item['file_name'] = '<span class=second_lines>'.$item['file_name'].'</span>';
        $item['create_date'] = Date::format($item['create_date'], 'd M Y');
        if ($item['url'] != '') {
            $item['ext'] = '';
        } else {
            $item['ext'] = '<img src="'.(is_file(ROOT_PATH.'skin/ext/'.$item['ext'].'.png') ? WEB_URL.'skin/ext/'.$item['ext'].'.png' : WEB_URL.'skin/ext/file.png').'" alt="'.$item['ext'].'">';
        }
        $item['downloads'] = '<span id="downloads_'.$item['id'].'" class="icon-valid color-'.(empty($item['downloads']) ? 'silver' : 'green').' notext"></span>';
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
        if ($btn == 'download') {
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
