<?php

require_once 'Zend/Tool/Project/Provider/Abstract.php';
require_once 'Zend/Tool/Project/Provider/Exception.php';

class PicturesProvider extends Zend_Tool_Project_Provider_Abstract
{

    public function clearQueue()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml

        $bootstrapResource = $this->_loadedProfile->search('BootstrapFile'); //get application bootstrap

        /* @var $zendApp Zend_Application */
        $zendApp = $bootstrapResource->getApplicationInstance(); //initialize application instance

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('imageStorage');

        $imageStorage = $zendApp->getBootstrap()->getResource('imageStorage');


        $table = new Picture();
        $pictures = $table->fetchAll(
            $table->select(true)
                ->where('status = ?', Picture::STATUS_REMOVING)
                ->where('removing_date is null OR (removing_date < DATE_SUB(CURDATE(), INTERVAL 7 DAY) )')
                ->limit(1000)
        );

        $count = count($pictures);

        if ($count) {
            printf("Removing %d pictures\n", $count);

            $adapter = new Zend_ProgressBar_Adapter_Console(array(
                'textWidth' => 80,
                'elements'  => array(
                    Zend_ProgressBar_Adapter_Console::ELEMENT_PERCENT,
                    Zend_ProgressBar_Adapter_Console::ELEMENT_BAR,
                    Zend_ProgressBar_Adapter_Console::ELEMENT_ETA,
                    Zend_ProgressBar_Adapter_Console::ELEMENT_TEXT
                )
            ));
            $progressBar = new Zend_ProgressBar($adapter, 0, count($pictures));

            foreach ($pictures as $idx => $picture) {
                $imageId = $picture->image_id;
                if ($imageId) {
                    $picture->delete();
                    $imageStorage->removeImage($imageId);
                } else {
                    print "Brokern image `{$picture->id}`. Skip\n";
                }

                $progressBar->update($idx + 1, $picture->id);
            }

            $progressBar->finish();
        }
    }
}