<?php
/**
 * @filesource modules/car/views/settings.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Settings;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=car-settings
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ตั้งค่าโมดูล
     *
     * @param object $config
     * @param array $login
     *
     * @return string
     */
    public function render($config, $login)
    {
        $booleans = Language::get('BOOLEANS');
        // form
        $form = Html::create('form', [
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/car/model/settings/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-config',
            'title' => '{LNG_Module settings}'
        ]);
        // car_login_type
        $fieldset->add('select', [
            'id' => 'car_login_type',
            'labelClass' => 'g-input icon-visited',
            'itemClass' => 'item',
            'label' => '{LNG_Book a vehicle}/{LNG_Vehicle}',
            'options' => Language::get('LOGIN_TYPIES'),
            'value' => isset($config->car_login_type) ? $config->car_login_type : 0
        ]);
        // car_w
        $fieldset->add('text', [
            'id' => 'car_w',
            'labelClass' => 'g-input icon-width',
            'itemClass' => 'item',
            'label' => '{LNG_Size of} {LNG_Image} ({LNG_Width})',
            'comment' => '{LNG_Image size is in pixels} ({LNG_resized automatically})',
            'value' => isset($config->car_w) ? $config->car_w : 600
        ]);
        // chauffeur_status
        $fieldset->add('select', [
            'id' => 'chauffeur_status',
            'labelClass' => 'g-input icon-star0',
            'itemClass' => 'item',
            'label' => '{LNG_Chauffeur} ({LNG_Member status})',
            'comment' => '{LNG_Status of members who are drivers}',
            'options' => $config->member_status,
            'value' => isset($config->chauffeur_status) ? $config->chauffeur_status : 2
        ]);
        // car_approving
        $fieldset->add('select', [
            'id' => 'car_approving',
            'labelClass' => 'g-input icon-write',
            'itemClass' => 'item',
            'label' => '{LNG_Approving/editing reservations}',
            'options' => Language::get('APPROVING_RESERVATIONS'),
            'value' => isset($config->car_approving) ? $config->car_approving : 0
        ]);
        // car_cancellation
        $fieldset->add('select', [
            'id' => 'car_cancellation',
            'labelClass' => 'g-input icon-warning',
            'itemClass' => 'item',
            'label' => '{LNG_Cancellation}',
            'options' => Language::get('CANCEL_RESERVATIONS'),
            'value' => isset($config->car_cancellation) ? $config->car_cancellation : 0
        ]);
        // car_delete
        $fieldset->add('checkboxgroups', [
            'id' => 'car_delete',
            'labelClass' => 'g-input icon-delete',
            'itemClass' => 'item',
            'label' => '{LNG_Deleting a reservation (booker)}',
            'comment' => '{LNG_Select the status in which the booker can delete his or her entry. Or not selected at all, it can only be deleted by the approver.}',
            'options' => Language::get('BOOKING_STATUS'),
            'value' => isset($config->car_delete) && is_array($config->car_delete) ? $config->car_delete : [3]
        ]);
        $fieldset = $form->add('fieldset', [
            'id' => 'verfied',
            'titleClass' => 'icon-verfied',
            'title' => '{LNG_Approval}'
        ]);
        // car_approve_level
        $fieldset->add('select', [
            'id' => 'car_approve_level',
            'labelClass' => 'g-input icon-menus',
            'itemClass' => 'item',
            'label' => '{LNG_Approval}',
            'options' => $booleans,
            'value' => count($config->car_approve_status)
        ]);
        // หมวดหมู่
        $category = \Index\Category\Model::init();
        $groups = $fieldset->add('groups');
        // car_approve_status
        $groups->add('select', [
            'id' => 'car_approve_status1',
            'name' => 'car_approve_status[1]',
            'labelClass' => 'g-input icon-star0',
            'itemClass' => 'width50',
            'label' => '{LNG_Approval} ({LNG_Member status})',
            'options' => $config->member_status,
            'value' => empty($config->car_approve_status[1]) ? 0 : $config->car_approve_status[1]
        ]);
        // car_approve_department
        $groups->add('select', [
            'id' => 'car_approve_department1',
            'name' => 'car_approve_department[1]',
            'labelClass' => 'g-input icon-group',
            'itemClass' => 'width50',
            'label' => $category->name('department'),
            'options' => $category->toSelect('department'),
            'value' => empty($config->car_approve_department[1]) ? '' : $config->car_approve_department[1]
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-comments',
            'title' => '{LNG_Notification}'
        ]);
        // car_email
        $fieldset->add('select', [
            'id' => 'car_email',
            'labelClass' => 'g-input icon-email',
            'label' => '{LNG_Send notification messages When making a transaction}',
            'itemClass' => 'item',
            'options' => $booleans,
            'value' => isset($config->car_email) ? $config->car_email : 1
        ]);
        // car_notifications
        $fieldset->add('select', [
            'id' => 'car_notifications',
            'labelClass' => 'g-input icon-email',
            'itemClass' => 'item',
            'label' => '{LNG_Notify relevant parties when booking details are modified by customers}',
            'options' => $booleans,
            'value' => isset($config->car_notifications) ? $config->car_notifications : 0
        ]);
        // car_line_id
        $fieldset->add('select', [
            'id' => 'car_line_id',
            'itemClass' => 'item',
            'label' => '{LNG_Send notification to LINE group when making a transaction}',
            'labelClass' => 'g-input icon-line',
            'options' => [0 => $booleans[0]]+\Index\Linegroup\Model::create()->toSelect(),
            'value' => isset($config->car_line_id) ? $config->car_line_id : 0
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button save large icon-save',
            'value' => '{LNG_Save}'
        ]);
        // Javascript
        $form->script('initCarSettings();');
        // คืนค่า HTML
        return $form->render();
    }
}
