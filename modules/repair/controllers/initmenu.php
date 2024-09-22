<?php
/**
 * @filesource modules/repair/controllers/initmenu.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Initmenu;

use Gcms\Login;
use Kotchasan\Http\Request;

/**
 * Init Menu
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Kotchasan\KBase
{
    /**
     * ฟังก์ชั่นเริ่มต้นการทำงานของโมดูลที่ติดตั้ง
     * และจัดการเมนูของโมดูล
     *
     * @param Request                $request
     * @param \Index\Menu\Controller $menu
     * @param array                  $login
     */
    public static function execute(Request $request, $menu, $login)
    {
        if ($login) {
            // สมาชิก
            $submenus = [
                [
                    'text' => '{LNG_My device}',
                    'url' => 'index.php?module=inventory'
                ],
                [
                    'text' => '{LNG_Get a repair}',
                    'url' => 'index.php?module=repair-receive'
                ],
                [
                    'text' => '{LNG_History}',
                    'url' => 'index.php?module=repair-history'
                ]
            ];
            // สามารถจัดการรายการซ่อมได้, ช่างซ่อม
            if (Login::checkPermission($login, ['can_manage_repair', 'can_repair'])) {
                $submenus[] = [
                    'text' => '{LNG_Repair list}',
                    'url' => 'index.php?module=repair-setup'
                ];
            }
            // เมนูแจ้งซ่อม
            $menu->addTopLvlMenu('repair', '{LNG_Repair jobs}', null, $submenus, 'member');
            // สามารถตั้งค่าระบบได้
            if (Login::checkPermission($login, 'can_config')) {
                $menu->add('settings', '{LNG_Repair jobs}', null, [
                    [
                        'text' => '{LNG_Settings}',
                        'url' => 'index.php?module=repair-settings'
                    ],
                    [
                        'text' => '{LNG_Repair status}',
                        'url' => 'index.php?module=repair-repairstatus'
                    ]
                ], 'repair');
            }
        }
    }
}
