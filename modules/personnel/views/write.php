<?php
/**
 * @filesource modules/personnel/views/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Personnel\Write;

use Kotchasan\Html;
use Kotchasan\Language;

/**
 * module=personnel-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มสร้าง/แก้ไข บุคลากร
     *
     * @param object $index
     *
     * @return string
     */
    public function render($index)
    {
        $form = Html::create('form', [
            'id' => 'setup_frm',
            'class' => 'setup_frm',
            'autocomplete' => 'off',
            'action' => 'index.php/personnel/model/write/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-write',
            'title' => '{LNG_Details of} {LNG_Personnel}'
        ]);
        $groups = $fieldset->add('groups');
        // name
        $groups->add('text', [
            'id' => 'personnel_name',
            'labelClass' => 'g-input icon-customer',
            'itemClass' => 'width50',
            'label' => '{LNG_Name}',
            'maxlength' => 100,
            'value' => $index->name
        ]);
        // phone
        $groups->add('text', [
            'id' => 'personnel_phone',
            'labelClass' => 'g-input icon-phone',
            'itemClass' => 'width50',
            'label' => '{LNG_Phone}',
            'maxlength' => 32,
            'value' => $index->phone
        ]);
        // หมวดหมู่
        $category = \Index\Category\Model::init(true);
        $groups = $fieldset->add('groups');
        $groups->add('select', [
            'id' => 'personnel_department',
            'labelClass' => 'g-input icon-category',
            'label' => '{LNG_Department}',
            'itemClass' => 'width50',
            'options' => [0 => '{LNG_Please select}'] + $category->toSelect('department'),
            'value' => isset($index->department) ? $index->department : 0
        ]);
        $groups->add('select', [
            'id' => 'personnel_position',
            'labelClass' => 'g-input icon-category',
            'label' => '{LNG_Position}',
            'itemClass' => 'width50',
            'options' => [0 => '{LNG_Please select}'] + $category->toSelect('position'),
            'value' => isset($index->position) ? $index->position : 0
        ]);
        // custom item
        $n = 0;
        foreach (Language::get('PERSONNEL_DETAILS', []) as $type => $label) {
            if ($n % 2 == 0) {
                $groups = $fieldset->add('groups');
            }
            $groups->add('text', [
                'id' => 'personnel_'.$type,
                'labelClass' => 'g-input icon-edit',
                'itemClass' => 'width50',
                'label' => $label,
                'value' => isset($index->{$type}) ? $index->{$type} : ''
            ]);
            ++$n;
        }
        // personnel picture
        if (is_file(ROOT_PATH.DATA_FOLDER.'personnel/'.$index->id.self::$cfg->stored_img_type)) {
            $img = WEB_URL.DATA_FOLDER.'personnel/'.$index->id.self::$cfg->stored_img_type;
        } else {
            $img = WEB_URL.'modules/personnel/img/noimage.jpg';
        }
        $fieldset->add('file', [
            'id' => 'personnel_picture',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Image}',
            'comment' => '{LNG_Browse image uploaded, type :type} {LNG_size :width*:height pixel} ({LNG_resized automatically})',
            'dataPreview' => 'imgPicture',
            'previewSrc' => $img,
            'accept' => self::$cfg->img_typies
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
            'id' => 'personnel_id',
            'value' => $index->id
        ]);
        \Gcms\Controller::$view->setContentsAfter([
            '/:type/' => implode(', ', self::$cfg->img_typies),
            '/:width/' => self::$cfg->personnel_w,
            '/:height/' => self::$cfg->personnel_h
        ]);
        // คืนค่า HTML
        return $form->render();
    }
}
