<?php

require_once 'Zend/Tool/Project/Provider/Abstract.php';
require_once 'Zend/Tool/Project/Provider/Exception.php';

class ImageStorageProvider extends Zend_Tool_Project_Provider_Abstract
{
    /*public function migrate()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('imageStorage');

        $imageStorage = $zendApp->getBootstrap()->getResource('imageStorage');


        $pictureTable = new Picture();

        $select = $pictureTable->select(true)
            ->join('formated_image', 'pictures.image_id = formated_image.image_id', null)
            ->where('formated_image.format = ?', 'picture-thumb')
            ->join('image', 'formated_image.formated_image_id = image.id', null)
            ->where('image.width > 155')
            ->where('image.date_add < ?', "2014-09-24 00:00:00")
            ->where('pictures.id')
            ->order(array('id asc'));

        $paginator = Zend_Paginator::factory($select)
            ->setItemCountPerPage(150);

        $pages = $paginator->count();

        for ($i=1; $i<=$pages; $i++) {

            print 'PAGE ' . $i . ' of ' . $pages . PHP_EOL;

            $paginator->setCurrentPageNumber($i);

            foreach ($paginator->getCurrentItems() as $row) {

                print $row->id . PHP_EOL;

                $imageStorage->flush(array(
                    'format' => 'picture-thumb',
                    'image'  => $row->image_id,
                ));

                $imageStorage->flush(array(
                    'format' => 'picture-medium',
                    'image'  => $row->image_id,
                ));

                $imageStorage->getFormatedImage($row->getFormatRequest(), 'picture-thumb');
                $imageStorage->getFormatedImage($row->getFormatRequest(), 'picture-medium');

                usleep(200000);

            }
        }
    }*/

    public function clearEmptyDirs($dirname)
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('imageStorage');

        $imageStorage = $zendApp->getBootstrap()->getResource('imageStorage');

        $dir = $imageStorage->getDir($dirname);
        if (!$dir) {
            throw new Exception("Dir '$dirname' not found");
        }

        $this->_recursiveDirectory(realpath($dir->getPath()));
    }

    private function _recursiveDirectory($dir) {
        $stack[] = $dir;

        while ($stack) {
            $currentDir = array_pop($stack);
            //print realpath($currentDir) . PHP_EOL;
            if ($dh = opendir($currentDir)){
                $count = 0;
                while (($file = readdir($dh)) !== false) {
                    if ($file !== '.' AND $file !== '..') {
                        $count++;
                        $currentFile = $currentDir . DIRECTORY_SEPARATOR . $file;
                        if (is_dir($currentFile)) {
                            $stack[] = $currentFile;
                        }
                    }
                }

                if ($count <= 0) {
                    print $currentDir . ' - empty' . PHP_EOL;
                    rmdir($currentDir);
                    //return;
                }
            }
        }
    }
}