<?php
/**
 * @filesource modules/dms/views/report.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Dms\Report;

use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;

/**
 * module=dms-report
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * รายงานการดาวน์โหลด
     *
     * @param Request $request
     * @param object  $index
     *
     * @return object
     */
    public function render(Request $request, $index)
    {
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Dms\Report\Model::toDataTable($index->id),
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id'],
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('dmsReport_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'last_update DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'status' => [
                    'text' => '{LNG_Recipient}'
                ],
                'name' => [
                    'text' => '{LNG_Name}'
                ],
                'last_update' => [
                    'text' => '{LNG_Date}',
                    'class' => 'center'
                ],
                'downloads' => [
                    'text' => '{LNG_Download}',
                    'class' => 'center'
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'last_update' => [
                    'class' => 'center'
                ],
                'downloads' => [
                    'class' => 'center'
                ]
            ]
        ]);
        // save cookie
        setcookie('dmsReport_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
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
        $item['last_update'] = $item['last_update'] == 0 ? '' : Date::format($item['last_update']);
        $item['status'] = isset(self::$cfg->member_status[$item['status']]) ? '<span class=status'.$item['status'].'>'.self::$cfg->member_status[$item['status']].'</span>' : '';
        return $item;
    }
}
