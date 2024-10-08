<?php
/**
 * @filesource modules/car/views/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Setup;

use Kotchasan\DataTable;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=car-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var array
     */
    private $publisheds;
    /**
     * @var mixed
     */
    private $car_select;
    /**
     * @var object
     */
    private $category;

    /**
     * ตารางยานพาหนะ
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        $this->category = \Car\Category\Model::init();
        $this->publisheds = Language::get('PUBLISHEDS');
        $this->car_select = Language::get('CAR_SELECT', []);
        $headers = [
            'number' => [
                'text' => '{LNG_Vehicle}',
                'sort' => 'number'
            ],
            'id' => [
                'text' => '{LNG_Image}',
                'class' => 'center'
            ]
        ];
        $cols = [
            'id' => [
                'class' => 'center'
            ]
        ];
        $filters = [];
        foreach ($this->car_select as $type => $text) {
            $filters[$type] = [
                'name' => $type,
                'default' => 0,
                'text' => $text,
                'options' => [0 => '{LNG_all items}'] + $this->category->toSelect($type),
                'value' => $request->request($type)->toInt()
            ];
            $headers[$type] = [
                'text' => $text,
                'class' => 'center'
            ];
            $cols[$type] = ['class' => 'center'];
        }
        $headers['seats'] = [
            'text' => '{LNG_Number of seats}',
            'class' => 'center'
        ];
        $cols['seats'] = ['class' => 'center'];
        $headers['published'] = ['text' => ''];
        $cols['published'] = ['class' => 'center'];
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Car\Setup\Model::toDataTable(),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('carSetup_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('carSetup_sort', 'id desc')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['color'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['number'],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/car/model/setup/action',
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
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
            'filters' => $filters,
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => $headers,
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => $cols,
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                [
                    'class' => 'icon-edit button green',
                    'href' => $uri->createBackUri(['module' => 'car-write', 'id' => ':id']),
                    'text' => '{LNG_Edit}'
                ]
            ],
            /* ปุ่มเพิ่ม */
            'addNew' => [
                'class' => 'float_button icon-new',
                'href' => 'index.php?module=car-write',
                'title' => '{LNG_Add} {LNG_Vehicle}'
            ]
        ]);
        // save cookie
        setcookie('carSetup_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('carSetup_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
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
     * @return array
     */
    public function onRow($item, $o, $prop)
    {
        $item['number'] = '<span class="term" style="background-color:'.$item['color'].';color:#fff;">'.$item['number'].'</span>';
        $item['published'] = '<a id=published_'.$item['id'].' class="icon-published'.$item['published'].'" title="'.$this->publisheds[$item['published']].'"></a>';
        $thumb = is_file(ROOT_PATH.DATA_FOLDER.'car/'.$item['id'].self::$cfg->stored_img_type) ? WEB_URL.DATA_FOLDER.'car/'.$item['id'].self::$cfg->stored_img_type : WEB_URL.'modules/car/img/noimage.png';
        $item['id'] = '<img src="'.$thumb.'" style="max-height:50px;max-width:50px" alt=thumbnail>';
        foreach ($this->car_select as $type => $text) {
            $item[$type] = $this->category->get($type, $item[$type]);
        }
        return $item;
    }
}
