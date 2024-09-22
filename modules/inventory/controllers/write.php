<?php
/**
 * @filesource modules/inventory/controllers/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Inventory\Write;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=inventory-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * เพิ่ม-แก้ไข Inventory
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Equipment');
        // เลือกเมนู
        $this->menu = 'settings';
        // สมาชิก
        $login = Login::isMember();
        // สามารถบริหารจัดการได้
        if (Login::checkPermission($login, 'can_manage_inventory')) {
            // อ่านข้อมูลที่เลือก
            $product = \Inventory\Write\Model::get($request->request('id')->toInt());
            if ($product) {
                // ข้อความ title bar
                if ($product->id == 0) {
                    $title = '{LNG_Add}';
                    $this->title = Language::get('Add').' '.$this->title;
                } else {
                    $title = '{LNG_Details of}';
                    $this->title = Language::get('Details of').' '.$product->topic;
                }
                // แสดงผล
                $section = Html::create('section');
                // breadcrumbs
                $breadcrumbs = $section->add('nav', [
                    'class' => 'breadcrumbs'
                ]);
                $ul = $breadcrumbs->add('ul');
                $ul->appendChild('<li><span class="icon-product">{LNG_Settings}</span></li>');
                $ul->appendChild('<li><a href="{BACKURL?module=inventory-setup&id=0}">{LNG_Inventory}</a></li>');
                $ul->appendChild('<li><span>'.$title.'</span></li>');
                $header = $section->add('header', [
                    'innerHTML' => '<h2 class="icon-list">'.$title.' {LNG_Equipment}</h2>'
                ]);
                // menu
                $section->appendChild(\Index\Tabmenus\View::render($request, 'settings', 'inventory'));
                $div = $section->add('div', [
                    'class' => 'content_bg'
                ]);
                // แสดงฟอร์ม
                $div->appendChild(\Inventory\Write\View::create()->render($request, $product));
                // คืนค่า HTML
                return $section->render();
            }
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
