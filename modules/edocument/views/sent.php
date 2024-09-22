<?php
/**
 * @filesource modules/edocument/views/sent.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Edocument\Sent;

use Gcms\Login;
use Kotchasan\DataTable;
use Kotchasan\Date;
use Kotchasan\Http\Request;
use Kotchasan\Language;
use Kotchasan\Text;

/**
 * module=edocument-sent
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class View extends \Gcms\View
{
    /**
     * @var object
     */
    private $sender;
    /**
     * @var array
     */
    private $urgencies;

    /**
     * แสดงรายการเอกสารส่ง
     *
     * @param Request $request
     * @param array   $login
     *
     * @return string
     */
    public function render(Request $request, $login)
    {
        // ค่าที่ส่งมา
        $params = [
            'urgency' => $request->request('urgency', -1)->toInt(),
            'sender' => $request->request('sender')->toInt(),
            'member_id' => $login['id']
        ];
        $urgencies = Language::get('URGENCIES');
        $this->urgencies = array_map(['Edocument\View\View', 'urgencyStyle'], array_keys($urgencies), array_values($urgencies));
        // รายชื่อผู้ส่ง
        if (Login::checkPermission($login, 'can_handle_all_edocument')) {
            // ทุกคน
            $this->sender = \Edocument\Sender\Model::init();
            $senders = [0 => '{LNG_all items}'] + $this->sender->toSelect();
        } else {
            // เจ้าตัวเท่านั้น
            $this->sender = \Edocument\Sender\Model::init($login['id']);
            $params['sender'] = $login['id'];
            $senders = $this->sender->toSelect();
        }
        // URL สำหรับส่งให้ตาราง
        $uri = $request->createUriWithGlobals(WEB_URL.'index.php');
        // ตาราง
        $table = new DataTable([
            /* Uri */
            'uri' => $uri,
            /* Model */
            'model' => \Edocument\Sent\Model::toDataTable($params),
            /* รายการต่อหน้า */
            'perPage' => $request->cookie('edocumentSent_perPage', 30)->toInt(),
            /* เรียงลำดับ */
            'sort' => 'last_update DESC',
            /* ฟังก์ชั่นจัดรูปแบบการแสดงผลแถวของตาราง */
            'onRow' => [$this, 'onRow'],
            /* คอลัมน์ที่ไม่ต้องแสดงผล */
            'hideColumns' => ['id', 'downloads'],
            /* ตัวเลือกการแสดงผลที่ส่วนหัว */
            'filters' => [
                [
                    'name' => 'urgency',
                    'text' => '{LNG_Urgency}',
                    'options' => [-1 => '{LNG_all items}'] + $this->urgencies,
                    'value' => $params['urgency']
                ],
                [
                    'name' => 'sender',
                    'text' => '{LNG_Sender}',
                    'options' => $senders,
                    'value' => $params['sender']
                ]
            ],
            /* ตั้งค่าการกระทำของของตัวเลือกต่างๆ ด้านล่างตาราง ซึ่งจะใช้ร่วมกับการขีดถูกเลือกแถว */
            'action' => 'index.php/edocument/model/sent/action',
            'actionCallback' => 'dataTableActionCallback',
            'actions' => [
                [
                    'id' => 'action',
                    'class' => 'ok',
                    'text' => '{LNG_With selected}',
                    'options' => [
                        'delete' => '{LNG_Delete}'
                    ]
                ]
            ],
            /* คอลัมน์ที่สามารถค้นหาได้ */
            'searchColumns' => ['topic', 'document_no'],
            /* ส่วนหัวของตาราง และการเรียงลำดับ (thead) */
            'headers' => [
                'document_no' => [
                    'text' => '{LNG_Document No.}'
                ],
                'urgency' => [
                    'text' => '{LNG_Urgency}',
                    'class' => 'center'
                ],
                'ext' => [
                    'text' => ''
                ],
                'topic' => [
                    'text' => '{LNG_Document title}'
                ],
                'sender_id' => [
                    'text' => '{LNG_Sender}',
                    'class' => 'center'
                ],
                'size' => [
                    'text' => '{LNG_Size of} {LNG_File}',
                    'class' => 'center'
                ],
                'last_update' => [
                    'text' => '{LNG_Date}',
                    'class' => 'center'
                ]
            ],
            /* รูปแบบการแสดงผลของคอลัมน์ (tbody) */
            'cols' => [
                'document_no' => [
                    'class' => 'nowrap'
                ],
                'urgency' => [
                    'class' => 'center'
                ],
                'ext' => [
                    'class' => 'center'
                ],
                'sender_id' => [
                    'class' => 'center nowrap'
                ],
                'size' => [
                    'class' => 'center'
                ],
                'last_update' => [
                    'class' => 'center nowrap'
                ]
            ],
            /* ปุ่มแสดงในแต่ละแถว */
            'buttons' => [
                'download' => [
                    'class' => 'icon-download button purple notext',
                    'id' => ':id',
                    'title' => '{LNG_Download}'
                ],
                'report' => [
                    'class' => 'icon-users button orange',
                    'href' => $uri->createBackUri(['module' => 'edocument-report', 'id' => ':id']),
                    'text' => '(:downloads)',
                    'title' => '{LNG_Recipient}/{LNG_Download history}'
                ],
                'edit' => [
                    'class' => 'icon-edit button green notext',
                    'href' => $uri->createBackUri(['module' => 'edocument-write', 'id' => ':id']),
                    'title' => '{LNG_Edit}'
                ]
            ],
            /* ปุ่มเพิ่ม */
            'addNew' => [
                'class' => 'float_button icon-new',
                'href' => $uri->createBackUri(['module' => 'edocument-write']),
                'title' => '{LNG_Send Document}'
            ]
        ]);
        // save cookie
        setcookie('edocumentSent_perPage', $table->perPage, time() + 2592000, '/', HOST, HTTPS, true);
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
     * @return array คืนค่า $item กลับไป
     */
    public function onRow($item, $o, $prop)
    {
        $item['topic'] = '<span class=one_line title="'.$item['topic'].'">'.$item['topic'].'</span>';
        $item['sender_id'] = $this->sender->get($item['sender_id']);
        $item['downloads'] = '<span id=downloads_'.$item['id'].'>'.(int) $item['downloads'].'</span>';
        $item['size'] = Text::formatFileSize($item['size']);
        $item['last_update'] = Date::format($item['last_update']);
        $item['ext'] = '<img src="'.(is_file(ROOT_PATH.'skin/ext/'.$item['ext'].'.png') ? WEB_URL.'skin/ext/'.$item['ext'].'.png' : WEB_URL.'skin/ext/file.png').'" alt="'.$item['ext'].'">';
        $item['urgency'] = isset($this->urgencies[$item['urgency']]) ? $this->urgencies[$item['urgency']] : '';
        return $item;
    }
}
