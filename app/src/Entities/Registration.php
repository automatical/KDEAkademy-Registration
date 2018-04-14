<?php
namespace Entity;
class Registration extends \Spot\Entity
{
    protected static $table = 'registration';
    public static function fields()
    {
        return [
        	'id'                   => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'dn' 					=> ['type' => 'string', 'required' => true],
            'conference_id'         => ['type' => 'string', 'required' => true],
            'data'           		=> ['type' => 'string', 'required' => true],
            'cancelled'             => ['type' => 'integer', 'required' => true],
            'date_created' 			=> ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
