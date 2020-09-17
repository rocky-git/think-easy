<?php
/**
 * Created by PhpStorm.
 * User: rocky
 * Date: 2020-08-09
 * Time: 15:54
 */

namespace thinkEasy;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Migration\AbstractMigration;
use Phinx\Migration\MigrationInterface;
use Phinx\Seed\AbstractSeed;
use Phinx\Util\Util;
use Symfony\Component\Finder\Finder;

/**
 * 插件
 * Class Plug
 * @package thinkEasy
 */
abstract class Plug
{
    protected $info = [
        'name'=>'插件名称',
        'description'=>'插件描述',
        'logo'=>'',
        'version'=>'1.0.0',
        'author'=>'作者',
    ];
    //获取插件信息
    final function getInfo(){
        return $this->info;
    }
    /**
     * 获取目录下的所有填充文件
     * @param $path 目录
     * @return Seeder[]
     */
    protected function getSeeds($path)
    {
        $phpFiles = glob($path . DIRECTORY_SEPARATOR . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);

        // filter the files to only get the ones that match our naming scheme
        $fileNames = [];
        /** @var Seeder[] $seeds */
        $seeds = [];

        foreach ($phpFiles as $filePath) {
            if (Util::isValidSeedFileName(basename($filePath))) {
                // convert the filename to a class name
                $class = pathinfo($filePath, PATHINFO_FILENAME);
                $fileNames[$class] = basename($filePath);

                // load the seed file
                /** @noinspection PhpIncludeInspection */
                require_once $filePath;
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(sprintf('Could not find class "%s" in file "%s"', $class, $filePath));
                }

                // instantiate it
                $seed = new $class();

                if (!($seed instanceof AbstractSeed)) {
                    throw new \InvalidArgumentException(sprintf('The class "%s" in file "%s" must extend \Phinx\Seed\AbstractSeed', $class, $filePath));
                }

                $seeds[$class] = $seed;
            }
        }
        ksort($seeds);
        return $seeds;
    }
    /**
     * 获取目录下的所有迁移文件
     * @param $path 目录
     * @return Migrator[]
     */
    protected function getMigrations($path)
    {
        $phpFiles = glob($path . DIRECTORY_SEPARATOR . '*.php', defined('GLOB_BRACE') ? GLOB_BRACE : 0);

        // filter the files to only get the ones that match our naming scheme
        $fileNames = [];
        /** @var Migrator[] $versions */
        $versions = [];

        foreach ($phpFiles as $filePath) {
            if (Util::isValidMigrationFileName(basename($filePath))) {
                $version = Util::getVersionFromFileName(basename($filePath));

                if (isset($versions[$version])) {
                    throw new \InvalidArgumentException(sprintf('Duplicate migration - "%s" has the same version as "%s"', $filePath, $versions[$version]->getVersion()));
                }

                // convert the filename to a class name
                $class = Util::mapFileNameToClassName(basename($filePath));

                if (isset($fileNames[$class])) {
                    throw new \InvalidArgumentException(sprintf('Migration "%s" has the same name as "%s"', basename($filePath), $fileNames[$class]));
                }

                $fileNames[$class] = basename($filePath);

                // load the migration file
                /** @noinspection PhpIncludeInspection */
                require_once $filePath;
                if (!class_exists($class)) {
                    throw new \InvalidArgumentException(sprintf('Could not find class "%s" in file "%s"', $class, $filePath));
                }

                // instantiate it
                $migration = new $class($version);

                if (!($migration instanceof AbstractMigration)) {
                    throw new \InvalidArgumentException(sprintf('The class "%s" in file "%s" must extend \Phinx\Migration\AbstractMigration', $class, $filePath));
                }

                $versions[$version] = $migration;
            }
        }

        ksort($versions);

        return $versions;
    }
    protected function execSeeds($seeds)
    {
        $default = config('database.default');
        $config = config("database.connections.{$default}");
        $dbConfig = [
            'host'         => $config['hostname'],
            'name'         => $config['database'],
            'user'         => $config['username'],
            'pass'         => $config['password'],
            'port'         => $config['hostport'],
            'charset'      => $config['charset'],
            'table_prefix' => $config['prefix'],
        ];
        $adapter = AdapterFactory::instance()->getAdapter($config['type'],$dbConfig);
        $seeds = $this->getSeeds($seeds);

        foreach ($seeds as $seeder) {
            $seeder->setAdapter($adapter);
            $seeder->run();
        }
    }
    protected function execMigrations($migrations, $direction = MigrationInterface::UP)
    {

        $migrations = $this->getMigrations($migrations);
        ksort($migrations);
        $default = config('database.default');
        $config = config("database.connections.{$default}");
        $dbConfig = [
            'host'         => $config['hostname'],
            'name'         => $config['database'],
            'user'         => $config['username'],
            'pass'         => $config['password'],
            'port'         => $config['hostport'],
            'charset'      => $config['charset'],
            'table_prefix' => $config['prefix'],
        ];
        $adapter = AdapterFactory::instance()->getAdapter($config['type'],$dbConfig);
        foreach ($migrations as $migration) {
            if (MigrationInterface::DOWN === $direction) {
                $proxyAdapter = AdapterFactory::instance()->getWrapper('proxy',$adapter);
                $migration->setAdapter($proxyAdapter);
                $migration->change();
                $proxyAdapter->executeInvertedCommands();
            } else {

                $migration->setAdapter($adapter);
                $migration->change();
            }
        }
    }

    /**
     * 递归复制目录文件
     * @param $dir 源目录
     * @param $src 目标目录
     */
    protected function copyDir($dir,$src){
        if(!is_dir($src)){
            mkdir($src,0755);
        }
        $finder = new Finder();
        foreach ($finder->in($dir)  as $file){
            $path = $file->getRealPath();
            $makePath = $src.DIRECTORY_SEPARATOR.$file->getRelativePath().DIRECTORY_SEPARATOR.$file->getFilename();
            if(is_dir($path)){
                mkdir($makePath,0755);
            }else{
                copy($path,$makePath);
            }
        }
    }
    /**
     * 递归删除目录文件
     * @param $dir 源目录
     */
    protected function delDir($dirName){
        if(!is_dir($dirName))
        {
            return false;
        }
        $handle = @opendir($dirName);
        while(($file = @readdir($handle)) !== false)
        {
            if($file != '.' && $file != '..')
            {
                $dir = $dirName . '/' . $file;
                is_dir($dir) ? $this->delDir($dir) : @unlink($dir);
            }
        }
        closedir($handle);
        return rmdir($dirName) ;
    }
    //获取插件描述
    final function getDescription(){
        return $this->getDescription();
    }
    //安装
    abstract function install();
    //卸载
    abstract function uninstall();
}
