<?php
namespace Entity;
class Admin extends \Spot\Entity
{
    protected static $table = 'admins';
    public static function fields()
    {
        return [
        	'id'                    => ['type' => 'integer', 'primary' => true, 'required' => true],
        	'dn' 					=> ['type' => 'string', 'required' => true],
            'conference_id'			=> ['type' => 'integer', 'required' => true]
        ];
    }
}
