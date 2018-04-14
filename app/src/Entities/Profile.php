<?php
namespace Entity;
class Profile extends \Spot\Entity
{
    protected static $table = 'profile';
    public static function fields()
    {
        return [
        	'dn' 					=> ['type' => 'string', 'primary' => true, 'required' => true],
            'data'           		=> ['type' => 'string', 'required' => true],
            'date_created' 			=> ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
