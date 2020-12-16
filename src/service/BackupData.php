<?php


namespace thinkEasy\service;


use thinkEasy\Service;
use Ifsnop\Mysqldump as IMysqldump;
class BackupData extends Service
{
    /**
     * 备份数据库
     * @auth true
     * @login true
     */
    public function backup()
    {
        $dumpSettings = array(
            'compress' => IMysqldump\Mysqldump::NONE,
            'no-data' => false,
            'reset-auto-increment' => false,
            'add-drop-table' => true,
            'single-transaction' => true,
            'lock-tables' => true,
            'add-locks' => true,
            'extended-insert' => true,
            'disable-foreign-keys-check' => true,
            'skip-triggers' => false,
            'add-drop-trigger' => true,
            'databases' => true,
            'add-drop-database' => true,
            'hex-blob' => true
        );
        if (!is_dir($this->backupPath())) {
            mkdir($this->backupPath(), 0755);
        }
        try {
            $dump = new IMysqldump\Mysqldump('mysql:host=' . config('database.connections.mysql.hostname') . ';dbname=' . config('database.connections.mysql.database'), config('database.connections.mysql.username'), config('database.connections.mysql.password'), $dumpSettings);
            $dump->start($this->backupPath() . date('YmdHis') . '.sql');
        } catch (\Exception $e) {
            return $e->getMessage();
        }
        return true;
    }
    //删除备份文件
    public function delete($id)
    {
        $delFile = $this->backupPath() . $id . '.sql';
        if (is_file($delFile) && unlink($delFile)) {
            return true;
        } else {
            return false;
        }
    }
    //备份目录
    protected function backupPath()
    {
        $backupPath = app()->getRootPath() . 'backup/';
        return $backupPath;
    }
    /**
     * 还原数据库
     * @auth true
     * @login true
     */
    public function reduction()
    {
        $name = $this->app->request->put('name');
        $file = $this->backupPath() . $name . '.sql';
        set_time_limit(0);
        $mysqli = mysqli_connect(config('database.connections.mysql.hostname'), config('database.connections.mysql.username'), config('database.connections.mysql.password'), config('database.connections.mysql.database'), config('database.connections.mysql.hostport'));
        $mysqli->set_charset(config('database.connections.mysql.charset'));
        $res = $mysqli->multi_query(file_get_contents($file));
        $mysqli->close();
        if ($res) {
            return true;
        } else {
            return false;
        }
    }
    /**
     * 获取备份数据列表
     * @return array
     */
    public function getBackUpList()
    {
        $controllerFiles = [];
        foreach (glob(app()->getRootPath() . 'backup/*.sql') as $key => $file) {
            if (is_file($file)) {
                $controllerFiles[] = [
                    'id' => str_replace('.sql', '', basename($file)),
                    'name' => str_replace('.sql', '', basename($file)),
                    'size' => $this->getSize(filesize($file)),
                    'path' => $file,
                    'create_time' => date('Y-m-d H:i:s', fileatime($file)),
                ];
            }
        }
        arsort($controllerFiles);
        $controllerFiles = array_values($controllerFiles);
        return $controllerFiles;
    }
    protected function getSize($filesize)
    {
        if ($filesize >= 1073741824) {

            $filesize = round($filesize / 1073741824 * 100) / 100 . ' GB';

        } elseif ($filesize >= 1048576) {

            $filesize = round($filesize / 1048576 * 100) / 100 . ' MB';

        } elseif ($filesize >= 1024) {

            $filesize = round($filesize / 1024 * 100) / 100 . ' KB';

        } else {
            $filesize = $filesize . ' 字节';

        }
        return $filesize;
    }
}
