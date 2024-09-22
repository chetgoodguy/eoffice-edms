<?php
/**
 * @filesource modules/edocument/views/write.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Write;

use Kotchasan\Html;
use Kotchasan\Language;
use Kotchasan\Text;

/**
 * module=edocument-write
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * ฟอร์มสร้าง/แก้ไข เอกสาร
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
            'action' => 'index.php/edocument/model/write/submit',
            'onsubmit' => 'doFormSubmit',
            'ajax' => true,
            'token' => true
        ]);
        $fieldset = $form->add('fieldset', [
            'titleClass' => 'icon-write',
            'title' => '{LNG_Details of} {LNG_Document}'
        ]);
        // document_no
        $fieldset->add('text', [
            'id' => 'document_no',
            'labelClass' => 'g-input icon-number',
            'itemClass' => 'item',
            'label' => '{LNG_Document No.}',
            'placeholder' => '{LNG_Leave empty for generate auto}',
            'comment' => '{LNG_Registration number of the document}',
            'maxlength' => 20,
            'value' => $index->document_no
        ]);
        // urgency
        $urgencies = Language::get('URGENCIES');
        $fieldset->add('radiogroups', [
            'id' => 'urgency',
            'labelClass' => 'g-input icon-rocket',
            'itemClass' => 'item',
            'label' => '{LNG_Urgency}',
            'options' => array_map(['Edocument\View\View', 'urgencyStyle'], array_keys($urgencies), array_values($urgencies)),
            'value' => $index->urgency
        ]);
        // file
        if (!empty($index->file) && is_file(ROOT_PATH.DATA_FOLDER.'edocument/'.$index->file)) {
            $file = WEB_URL.DATA_FOLDER.'edocument/'.$index->file;
            $placeholder = $index->topic.'.'.$index->ext;
        } else {
            $file = '';
            $placeholder = '{LNG_Browse file}';
        }
        $fieldset->add('file', [
            'id' => 'file',
            'labelClass' => 'g-input icon-upload',
            'itemClass' => 'item',
            'label' => '{LNG_Browse file}',
            'comment' => '{LNG_Upload :type files} {LNG_no larger than :size}',
            'accept' => self::$cfg->edocument_file_typies,
            'onchange' => 'edocumentFileChanged',
            'dataPreview' => 'filePreview',
            'previewSrc' => $file,
            'placeholder' => $placeholder
        ]);
        // topic
        $fieldset->add('text', [
            'id' => 'topic',
            'labelClass' => 'g-input icon-edit',
            'itemClass' => 'item',
            'label' => '{LNG_Document title}',
            'comment' => '{LNG_The name of the document when downloaded. If empty, use the name of the uploaded file. (Thai language available)}',
            'maxlength' => 255,
            'value' => $index->topic
        ]);
        // detail
        $fieldset->add('textarea', [
            'id' => 'detail',
            'labelClass' => 'g-input icon-file',
            'itemClass' => 'item',
            'label' => '{LNG_Description}',
            'comment' => '{LNG_Note or additional notes}',
            'rows' => 5,
            'value' => $index->detail
        ]);
        $fieldset = $form->add('fieldset', [
            'title' => '{LNG_Recipient}'
        ]);
        $category = \Index\Category\Model::init();
        if (!$category->isEmpty('department')) {
            // department
            $fieldset->add('checkboxgroups', [
                'id' => 'department',
                'name' => 'department[]',
                'labelClass' => 'g-input icon-group',
                'itemClass' => 'item',
                'label' => $category->name('department'),
                'comment' => '{LNG_The recipient is listed in the selected group can be downloaded.} ({LNG_You can select multiple groups})',
                'options' => $category->toSelect('department'),
                'value' => $index->department
            ]);
        }
        // receiver
        $autocomplete = [
            'url' => 'index.php/edocument/model/autocomplete/findUser'
        ];
        $fieldset->add('inputgroups', [
            'id' => 'receiver',
            'labelClass' => 'g-input icon-users',
            'itemClass' => 'item',
            'label' => '{LNG_Member list}',
            'comment' => '{LNG_Choose from a list of members (can choose multiple people)}',
            'autocomplete' => $autocomplete,
            'value' => $index->receiver
        ]);
        $fieldset = $form->add('fieldset', [
            'class' => 'submit'
        ]);
        // submit
        $fieldset->add('submit', [
            'class' => 'button ok large icon-save',
            'value' => '{LNG_Save}'
        ]);
        $fieldset->add('checkbox', [
            'id' => 'send_mail',
            'labelClass' => 'inline',
            'label' => '&nbsp;{LNG_Send an email notification to the recipient}',
            'checked' => self::$cfg->edocument_send_mail && $index->id == 0,
            'value' => 1
        ]);
        // id
        $fieldset->add('hidden', [
            'id' => 'id',
            'value' => $index->id
        ]);
        \Gcms\Controller::$view->setContentsAfter([
            '/:type/' => implode(', ', self::$cfg->edocument_file_typies),
            '/:size/' => Text::formatFileSize(self::$cfg->edocument_upload_size)
        ]);
        // Javascript
        $form->script('initEdocumentWrite();');
        // คืนค่า HTML
        return $form->render();
    }
}
