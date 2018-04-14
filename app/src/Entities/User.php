<?php
namespace Entity;
class User extends \Spot\Entity
{
    protected static $table = 'users';
    public static function fields()
    {
        return [
        	'dn' 					=> ['type' => 'string', 'primary' => true, 'required' => true],
            'cn'           			=> ['type' => 'string', 'required' => true],
            'email'        			=> ['type' => 'string'],
            'gender'       			=> ['type' => 'string'],
            'ircnick'      			=> ['type' => 'string'],
            'homepostaladdress' 	=> ['type' => 'string'],
            'date_created' 			=> ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
