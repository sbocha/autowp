<?php

use Application\Db\Table;

class Attrs_User_Values_Abstract extends Table
{
    protected $_referenceMap = [
        'Attribute' => [
            'columns'       => ['attribut_id'],
            'refTableClass' => 'Attrs_Attributes',
            'refColumns'    => ['id']
        ],
        'User' => [
            'columns'       => ['user_id'],
            'refTableClass' => \Application\Model\DbTable\User::class,
            'refColumns'    => ['id']
        ],
        'ItemType' => [
            'columns'       => ['item_type_id'],
            'refTableClass' => 'Attrs_Item_Types',
            'refColumns'    => ['id']
        ],
    ];
}