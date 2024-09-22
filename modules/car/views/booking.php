<?php
/**
 * @filesource modules/car/views/booking.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Car\Booking;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=car-booking
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Car\Tools\View
{
    /**
     * ฟอร์มสร้าง/แก้ไข การจอง (user)
     *
     * @param object $index
     * @param array $login
     *
     * @return string
     */
    public function render($index, $login)
    {
        $form = Html::create('form', [
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/car/model/booking/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'title' => '{LNG_Details of} {LNG_Booking} '.($index->id > 0 ? self::toStatus((array) $index, true) : '')
        ]);
        $groups = $fieldset->add('groups');
        // vehicle_id
        $vehicles = \Car\Vehicles\Model::toSelect(true, $index->vehicle_id);
        if (count($vehicles) < 10) {
            $groups->add('select', [
                'id' => 'vehicle_id',
                'labelClass' => 'g-input icon-shipping',
                'itemClass' => 'width50',
                'label' => '{LNG_Vehicle}',
                'options' => $vehicles,
                'value' => $index->vehicle_id
            ]);
        } else {
            $groups->add('text', [
                'id' => 'vehicle_id',
                'labelClass' => 'g-input icon-shipping',
                'itemClass' => 'width50',
                'label' => '{LNG_Vehicle}',
                'placeholder' => Language::replace('Search :name and select from the list', [':name' => 'Vehicle']),
                'datalist' => $vehicles,
                'value' => $index->vehicle_id
            ]);
        }
        // travelers
        $groups->add('number', [
            'id' => 'travelers',
            'labelClass' => 'g-input icon-group',
            'itemClass' => 'width50',
            'label' => '{LNG_Number of travelers}',
            'unit' => '{LNG_persons}',
            'value' => isset($index->travelers) ? $index->travelers : 1
        ]);
        // detail
        $fieldset->add('textarea', [
            'id' => 'detail',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Usage details}',
            'rows' => 3,
            'value' => isset($index->detail) ? $index->detail : ''
        ]);
        $groups = $fieldset->add('groups');
        // name
        $groups->add('text', [
            'id' => 'name',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Contact name}',
            'disabled' => true,
            'value' => $index->name
        ]);
        // member_id
        $groups->add('hidden', [
            'id' => 'member_id',
            'value' => $index->member_id
        ]);
        // phone
        $groups->add('text', [
            'id' => 'phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 32,
            'value' => $index->phone
        ]);
        $groups = $fieldset->add('groups');
        // begin_date
        $begin = empty($index->begin) ? time() : strtotime($index->begin);
        $groups->add('date', [
            'id' => 'begin_date',
            'label' => '{LNG_Begin date}',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'width50',
            'value' => date('Y-m-d', $begin)
        ]);
        // begin_time
        $groups->add('time', [
            'id' => 'begin_time',
            'label' => '{LNG_Begin time}',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'width50',
            'value' => date('H:i', $begin)
        ]);
        $groups = $fieldset->add('groups');
        // end_date
        $end = empty($index->end) ? time() : strtotime($index->end);
        $groups->add('date', [
            'id' => 'end_date',
            'label' => '{LNG_End date}',
            'labelClass' => 'g-input icon-calendar',
            'itemClass' => 'width50',
            'value' => date('Y-m-d', $end)
        ]);
        // end_time
        $groups->add('time', [
            'id' => 'end_time',
            'label' => '{LNG_End time}',
            'labelClass' => 'g-input icon-clock',
            'itemClass' => 'width50',
            'value' => date('H:i', $end)
        ]);
        // ตัวเลือก checkbox
        $category = \Car\Category\Model::init();
        foreach (Language::get('CAR_OPTIONS', []) as $key => $label) {
            if (!$category->isEmpty($key)) {
                $fieldset->add('checkboxgroups', [
                    'id' => $key,
                    'labelClass' => 'g-input icon-list',
                    'itemClass' => 'item',
                    'label' => $label,
                    'options' => $category->toSelect($key),
                    'value' => isset($index->{$key}) ? explode(',', $index->{$key}) : []
                ]);
            }
        }
        // comment
        $fieldset->add('textarea', [
            'id' => 'comment',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Other}',
            'rows' => 3,
            'value' => isset($index->comment) ? $index->comment : ''
        ]);
        // chauffeur
        $fieldset->add('select', [
            'id' => 'chauffeur',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'item',
            'label' => '{LNG_Chauffeur}',
            'options' => [-1 => '{LNG_Do not want} ({LNG_Self drive})', 0 => '{LNG_Not specified} ({LNG_anyone})']+\Car\Chauffeur\Model::init()->toSelect(),
            'value' => isset($index->chauffeur) ? $index->chauffeur : 0
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button ok large icon-save',
            'value' => '{LNG_Save}'
        ]);
        // id
        $fieldset->add('hidden', [
            'id' => 'id',
            'value' => $index->id
        ]);
        // คืนค่า HTML
        return $form->render();
    }
}
