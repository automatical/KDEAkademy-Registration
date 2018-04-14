<?php
namespace Entity;
class Mailman extends \Spot\Entity
{
    protected static $table = 'mailman';
    public static function fields()
    {
        return [
            'id'                    => ['type' => 'integer', 'primary' => true, 'autoincrement' => true],
            'dn'                    => ['type' => 'string', 'required' => true],
            'conference_id'         => ['type' => 'integer', 'required' => true],
            'date_created'          => ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
