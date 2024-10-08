<?php
/**
 * @filesource modules/car/views/approved.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Approved;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=car-leave
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Car\Tools\View
{
    /**
     * ฟอร์ม ปรับสถานะ
     *
     * @param array  $index
     * @param int  $status สถานะใหม่ที่ต้องการ
     * @param array  $login
     *
     * @return string
     */
    public function render($index, $status, $login)
    {
        // สถานะอนุมัติ
        $statuses = [];
        foreach (Language::get('BOOKING_STATUS') as $value => $label) {
            if ($login['status'] == 1 || in_array($value, [1, 2, $status, $index['status']])) {
                $statuses[$value] = $label;
            }
        }
        // สามารถอนุมัติได้
        $canApprove = \Car\Base\Controller::canApprove($login, (object) $index);
        // form
        $form = Html::create('form', [
            'id' => 'car_approved_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/car/model/approved/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $form->add('header', [
            'innerHTML' => '<h3 class=icon-valid>{LNG_Status update} ['.self::toStatus($index).']</h3>'
        ]);
        $fieldset = $form->add('fieldset');
        // รายการที่อนุมัติ
        $approver = [];
        foreach (self::$cfg->car_approve_department as $approve => $department) {
            if ($canApprove == -1 || $canApprove == $approve) {
                $approver[$approve] = Language::get('Approver');
            }
        }
        // approve
        $fieldset->add('select', [
            'id' => 'approved_approve',
            'labelClass' => 'g-input icon-valid',
            'label' => '{LNG_Approver}',
            'itemClass' => 'item',
            'options' => $approver,
            'value' => $index['approve']
        ]);
        // status
        $fieldset->add('select', [
            'id' => 'approved_status',
            'labelClass' => 'g-input icon-star0',
            'label' => '{LNG_Status}',
            'itemClass' => 'item',
            'options' => $statuses,
            'value' => $status
        ]);
        // chauffeur
        $fieldset->add('select', [
            'id' => 'approved_chauffeur',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'item',
            'label' => '{LNG_Chauffeur}',
            'options' => [-1 => '{LNG_Do not want}', 0 => '{LNG_Not specified} ({LNG_anyone})']+\Car\Chauffeur\Model::init($index['chauffeur'])->toSelect(),
            'value' => $index['chauffeur']
        ]);
        // reason
        $fieldset->add('text', [
            'id' => 'approved_reason',
            'labelClass' => 'g-input icon-file',
            'label' => '{LNG_Reason}',
            'itemClass' => 'item',
            'value' => $index['reason']
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit right'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ]);
        // id
        $fieldset->add('hidden', [
            'id' => 'approved_id',
            'value' => $index['id']
        ]);
        // Javascript
        $form->script('initCarApproved();');
        // คืนค่า HTML
        return Language::trans($form->render());
    }
}
