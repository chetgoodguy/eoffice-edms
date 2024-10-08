<?php
/**
 * @filesource modules/car/views/report.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Report;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=car-report
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Car\Tools\View
{
    /**
     * @var array
     */
    private $chauffeur;
    /**
     * @var array
     */
    private $vehicles;
    /**
     * @var \Index\Category\Model
     */
    private $category;

    /**
     * รายงานการจอง (แอดมิน)
     *
     * @param Request $request
     * @param array $params
     *
     * @return string
     */
    public function render(Request $request, $params)
    {
        // หมวดหมู่
        $this->category = \Index\Category\Model::init();
        // รถ
        $this->vehicles = \Car\Vehicles\Model::toSelect(false);
        // พนักงานขับรถ
        $this->chauffeur = [-1 => '{LNG_Self drive}', 0 => '{LNG_Not specified} ({LNG_anyone})']+\Car\Chauffeur\Model::init()->toSelect();
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Car\Report\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('carReport_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => $request->cookie('carReport_sort', 'today,create_date DESC')->toString(),
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'approve', 'closed', 'today', 'remain', 'color', 'begin', 'end', 'phone', 'department'],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['contact', 'phone', 'detail'],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/car/model/report/action',
            'actionCallback' => 'dataTableActionCallback',
            /* ตัวเลือกด้านบนของตาราง ใช้จำกัดผลลัพท์การ query */
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
                ],
                [
                    'name' => 'vehicle_id',
                    'text' => '{LNG_Vehicle}',
                    'options' => [0 => '{LNG_all items}'] + $this->vehicles,
                    'value' => $params['vehicle_id']
                ],
                [
                    'name' => 'department',
                    'text' => $this->category->name('department'),
                    'options' => ['' => '{LNG_all items}'] + $this->category->toSelect('department'),
                    'value' => $params['department']
                ],
                [
                    'name' => 'chauffeur',
                    'text' => '{LNG_Chauffeur}',
                    'options' => [-2 => '{LNG_all items}'] + $this->chauffeur,
                    'value' => $params['chauffeur']
                ],
                [
                    'name' => 'status',
                    'text' => '{LNG_Status}',
                    'options' => [-1 => '{LNG_all items}'] + $params['booking_status'],
                    'value' => $params['status']
                ]
            ],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'detail' => [
                    'text' => '{LNG_Usage details}'
                ],
                'vehicle_id' => [
                    'text' => '{LNG_Vehicle}',
                    'sort' => 'vehicle_id'
                ],
                'contact' => [
                    'text' => '{LNG_Contact name}'
                ],
                'chauffeur' => [
                    'text' => '{LNG_Chauffeur}',
                    'class' => 'center',
                    'sort' => 'chauffeur'
                ],
                'create_date' => [
                    'text' => '{LNG_Created}',
                    'class' => 'center',
                    'sort' => 'create_date'
                ],
                'status' => [
                    'text' => '{LNG_Status}',
                    'class' => 'center'
                ],
                'reason' => [
                    'text' => '{LNG_Reason}'
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'detail' => [
                    'class' => 'top'
                ],
                'contact' => [
                    'class' => 'nowrap small'
                ],
                'chauffeur' => [
                    'class' => 'center small'
                ],
                'create_date' => [
                    'class' => 'center'
                ],
                'status' => [
                    'class' => 'center'
                ]
            ],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'edit' => [
                    'class' => 'icon-valid button green notext',
                    'href' => $uri->createBackUri(['module' => 'car-approve', 'id' => ':id']),
                    'title' => '{LNG_Approve}/{LNG_Edit}'
                ]
            ]
        ]);
        if ($params['approve'] > -2) {
            // สามารถลบได้
            $table->actions = [
                [
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => [
                        'delete' => '{LNG_Delete}'
                    ]
                ]
            ];
        }
        // save cookie
        setcookie('carReport_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
        setcookie('carReport_sort', $table->sort, time() + 2592000, '/', HOST, HTTPS, true);
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
        if ($item['today'] == 1) {
            $prop->class = 'bg3';
        }
        $department = $this->category->get('department', $item['department']);
        if ($department != '') {
            $item['contact'] .= ' ('.$department.')';
        }
        if ($item['phone'] != '') {
            $item['contact'] .= '<br><a class=icon-phone href="tel:'.$item['phone'].'">'.$item['phone'].'</a>';
        }
        $item['create_date'] = '<span class=small>'.Date::format($item['create_date'], 'd M Y').'<br>{LNG_Time} '.Date::format($item['create_date'], 'H:i').'</span>';
        $item['status'] = self::toStatus($item, true);
        $item['detail'] = '<span class=two_lines title="'.$item['detail'].'">'.$item['detail'].'</span>';
        $item['reason'] = '<span class="two_lines small" title="'.$item['reason'].'">'.$item['reason'].'</span>';
        $item['chauffeur'] = isset($this->chauffeur[$item['chauffeur']]) ? $this->chauffeur[$item['chauffeur']] : '';
        $item['vehicle_id'] = isset($this->vehicles[$item['vehicle_id']]) ? '<span class="term" style="background-color:'.$item['color'].'">'.$this->vehicles[$item['vehicle_id']].'</span>' : '';
        $item['vehicle_id'] .= '<div class="small nowrap">'.self::dateRange($item).'</div>';
        return $item;
    }
}
