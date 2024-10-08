<?php
/**
 * @filesource modules/edocument/controllers/index.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Index;

use Gcms\Login;
use Kotchasan\Html;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=edocument
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Controller extends \Gcms\Controller
{
    /**
     * แสดงรายการเอกสาร
     *
     * @param Request $request
     *
     * @return string
     */
    public function render(Request $request)
    {
        // ข้อความ title bar
        $this->title = Language::get('Received document');
        // เลือกเมนู
        $this->menu = 'edocument';
        // สมาชิก
        if ($login = Login::isMember()) {
            // ค่าที่ส่งมา
            $params = [
                'urgency' => $request->request('urgency', -1)->toInt(),
                'sender' => $request->request('sender')->toInt(),
                'member_id' => $login['id']
            ];
            // แสดงผล
            $section = Html::create('section');
            // breadcrumbs
            $breadcrumbs = $section->add('nav', [
                'class' => 'breadcrumbs'
            ]);
            $ul = $breadcrumbs->add('ul');
            $ul->appendChild('<li><span class="icon-edocument">{LNG_E-Document}</span></li>');
            $ul->appendChild('<li><span>{LNG_Received document}</span></li>');
            $section->add('header', [
                'innerHTML' => '<h2 class="icon-documents">'.$this->title.'</h2>'
            ]);
            $div = $section->add('div', [
                'class' => 'content_bg'
            ]);
            // ตารางรายการเอกสาร
            $div->appendChild(\Edocument\Index\View::create()->render($request, $params));
            // คืนค่า HTML
            return $section->render();
        }
        // 404
        return \Index\Error\Controller::execute($this, $request->getUri());
    }
}
