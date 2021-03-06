<?php

use Application\Model\Message;

class MaintenanceProvider extends Zend_Tool_Project_Provider_Abstract
{

    public function dump()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml

        $bootstrapResource = $this->_loadedProfile->search('BootstrapFile'); //get application bootstrap

        /* @var $zendApp Zend_Application */
        $zendApp = $bootstrapResource->getApplicationInstance(); //initialize application instance

        $db = $zendApp->getBootstrap()->bootstrap('db')->getResource('db');

        $config = $db->getConfig();

        $destFile = APPLICATION_PATH . '/data/dump/' . date('Y-m-d_H.i.s') . '.dump.sql';

        print 'Dumping ... ';

        $cmd = sprintf(
            'mysqldump -u%s -p%s -h%s --set-gtid-purged=OFF %s > %s',
            escapeshellarg($config['username']),
            escapeshellarg($config['password']),
            escapeshellarg($config['host']),
            escapeshellarg($config['dbname']),
            escapeshellarg($destFile)
        );

        exec($cmd);

        if (!file_exists($destFile)) {
            throw new Exception('Error creating dump');
        }

        print 'ok' . PHP_EOL;

        print 'Gzipping ... ';

        $cmd = sprintf(
            'gzip %s',
            escapeshellarg($destFile)
        );

        exec($cmd);

        print 'ok' . PHP_EOL;
    }

    public function clearOldSystemPM()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');
        
        $mModel = new Message();
        $count = $mModel->recycleSystem();

        printf("%d messages was deleted\n", $count);
    }

    public function clearDeletedPM()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $mModel = new Message();
        $count = $mModel->recycle();

        printf("%d messages was deleted\n", $count);
    }

    public function clearIpMonitoring()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $ipMon = new Ip_Monitoring();

        $count = $ipMon->delete(array(
            'day_date < CURDATE()'
        ));

        printf("%d ip monitoring rows was deleted\n", $count);
    }

    public function clearRefererMonitoring()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $table = new Referer();

        $count = $table->delete(array(
            'last_date < DATE_SUB(NOW(), INTERVAL 1 DAY)'
        ));

        printf("%d referer monitoring rows was deleted\n", $count);
    }

    public function clearBannedIp()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');


        $bannedIp = new Banned_Ip();
        $count = $bannedIp->delete(
            'up_to < NOW()'
        );

        printf("%d banned ip rows was deleted\n", $count);
    }

    public function clearUserHashes()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $urTable = new User_Remember();
        $count = $urTable->delete(array(
            'date < DATE_SUB(NOW(), INTERVAL 60 DAY)'
        ));

        printf("%d user remember rows was deleted\n", $count);

        $uprTable = new User_Password_Remind();
        $count = $uprTable->delete(array(
            'created < DATE_SUB(NOW(), INTERVAL 10 DAY)'
        ));

        printf("%d password remind rows was deleted\n", $count);
    }

    public function clearUserRenames()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $urTable = new User_Renames();
        $count = $urTable->delete(array(
            'date < DATE_SUB(NOW(), INTERVAL 3 MONTH)'
        ));

        printf("%d user rename rows was deleted\n", $count);
    }

    public function clearSessions()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('session');

        $bootstrap = $zendApp->getBootstrap();

        $options = $zendApp->getOptions();
        $maxlifetime = $options['resources']['session']['gc_maxlifetime'];
        if (!$maxlifetime) {
            throw new Exception('Option session.gc_maxlifetime not found');
        }

        Zend_Session::getSaveHandler()->gc($maxlifetime);

        print "Garabage collected\n";
    }

    public function rebuildCategoryParent()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('session');

        $bootstrap = $zendApp->getBootstrap();

        $cpTable = new Category_Parent();

        $cpTable->rebuild();

        print "Ok\n";
    }

    public function rebuildCarOrderCache()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db')
            ->bootstrap('session');

        $carTable = new Cars();

        $paginator = Zend_Paginator::factory($carTable->select(true)->order('id'))
            ->setItemCountPerPage(100);

        $pagesCount = $paginator->count();
        for ($i=1; $i<=$pagesCount; $i++) {
            $paginator->setCurrentPageNumber($i);
            foreach ($paginator->getCurrentItems() as $carRow) {
                print $carRow->id . "\n";
                $carRow->updateOrderCache();
            }
        }

        print "ok\n";
    }

    public function commentsRepliesCount()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $comments = new Comments();

        $affected = $comments->updateRepliesCount();

        print "ok $affected\n";
    }

    public function updateSpecsVolumes()
    {
        $this->_loadProfile(self::NO_PROFILE_THROW_EXCEPTION); //load .zfproject.xml
        /* @var $zendApp Zend_Application */
        $zendApp = $this->_loadedProfile->search('BootstrapFile')->getApplicationInstance();

        $zendApp
            ->bootstrap('backCompatibility')
            ->bootstrap('phpEnvoriment')
            ->bootstrap('autoloader')
            ->bootstrap('db');

        $userTable = new Users();

        $userTable->updateSpecsVolumes();

        print "ok\n";
    }
}