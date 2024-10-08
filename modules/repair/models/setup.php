<?php
/**
 * @filesource modules/repair/models/setup.php
 *
 * @copyright 2016 Goragod.com
 * @license https://www.kotchasan.com/license/
 *
 * @see https://www.kotchasan.com/
 */

namespace Repair\Setup;

use Gcms\Login;
use Kotchasan\Database\Sql;
use Kotchasan\File;
use Kotchasan\Http\Request;
use Kotchasan\Language;

/**
 * module=repair-setup
 *
 * @author Goragod Wiriya <admin@goragod.com>
 *
 * @since 1.0
 */
class Model extends \Kotchasan\Model
{
    /**
     * Query ข้อมูลสำหรับส่งให้กับ DataTable
     *
     * @param array $params
     *
     * @return \Kotchasan\Database\QueryBuilder
     */
    public static function toDataTable($params)
    {
        $where = [];
        if (!empty($params['operator_id'])) {
            $where[] = ['S.operator_id', $params['operator_id']];
        }
        if ($params['status'] > 0) {
            $where[] = ['S.status', $params['status']];
        }
        if (!empty($params['from'])) {
            $where[] = [Sql::DATE('R.create_date'), '>=', $params['from']];
        }
        if (!empty($params['to'])) {
            $where[] = [Sql::DATE('R.create_date'), '<=', $params['to']];
        }
        $q1 = static::createQuery()
            ->select('repair_id', Sql::MAX('id', 'max_id'))
            ->from('repair_status')
            ->groupBy('repair_id');
        return static::createQuery()
            ->select('R.id', 'R.job_id', 'U.name', 'U.phone', 'V.topic', 'R.create_date', 'S.operator_id', 'S.status')
            ->from('repair R')
            ->join([$q1, 'T'], 'LEFT', ['T.repair_id', 'R.id'])
            ->join('repair_status S', 'LEFT', ['S.id', 'T.max_id'])
            ->join('inventory V', 'LEFT', ['V.id', 'R.inventory_id'])
            ->join('user U', 'LEFT', ['U.id', 'R.customer_id'])
            ->where($where);
    }

    /**
     * รับค่าจาก action (setup.php)
     *
     * @param Request $request
     */
    public function action(Request $request)
    {
        $ret = [];
        // session, referer, member, ไม่ใช่สมาชิกตัวอย่าง
        if ($request->initSession() && $request->isReferer() && $login = Login::isMember()) {
            if (Login::notDemoMode($login)) {
                // รับค่าจากการ POST
                $action = $request->post('action')->toString();
                // id ที่ส่งมา
                if (preg_match_all('/,?([0-9]+),?/', $request->post('id')->filter('0-9,'), $match)) {
                    if ($action === 'delete' && Login::checkPermission($login, 'can_manage_repair')) {
                        // ลบรายการสั่งซ่อม
                        $this->db()->delete($this->getTableName('repair'), ['id', $match[1]], 0);
                        $this->db()->delete($this->getTableName('repair_status'), ['repair_id', $match[1]], 0);
                        foreach ($match[1] as $id) {
                            File::removeDirectory(ROOT_PATH.DATA_FOLDER.'repair/'.$id.'/');
                        }
                        // log
                        \Index\Log\Model::add(0, 'repair', 'Delete', '{LNG_Delete} {LNG_Repair} ID : '.implode(', ', $match[1]), $login['id']);
                        // reload
                        $ret['location'] = 'reload';
                    } elseif ($action === 'status' && Login::checkPermission($login, ['can_manage_repair', 'can_repair'])) {
                        // อ่านข้อมูลรายการที่ต้องการ
                        $index = \Repair\Detail\Model::get($request->post('id')->toInt());
                        if ($index) {
                            $ret['modal'] = Language::trans(\Repair\Action\View::create()->render($index, $login));
                        }
                    }
                }
            }
        }
        if (empty($ret)) {
            $ret['alert'] = Language::get('Unable to complete the transaction');
        }
        // คืนค่า JSON
        echo json_encode($ret);
    }
}
