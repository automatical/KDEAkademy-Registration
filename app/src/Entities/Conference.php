<?php
namespace Entity;
class Conference extends \Spot\Entity
{
    protected static $table = 'conference';
    public static function fields()
    {
        return [
            'id'                    => ['type' => 'integer', 'primary' => true, 'required' => true],
            'name'                  => ['type' => 'string', 'primary' => true, 'required' => true],
            'description'           => ['type' => 'string', 'primary' => true, 'required' => false],
            'notes'                 => ['type' => 'string', 'primary' => true, 'required' => false],
            'venue'                 => ['type' => 'string', 'primary' => true, 'required' => true],
            'website'               => ['type' => 'string', 'primary' => true, 'required' => true],
            'start_date'            => ['type' => 'string', 'required' => true],
            'end_date'              => ['type' => 'string', 'required' => true],
            'enable_donation'       => ['type' => 'integer', 'required' => true],
            'mailman'               => ['type' => 'string', 'required' => true],
            'enabled'               => ['type' => 'integer', 'required' => true],
            'date_created' 			=> ['type' => 'datetime', 'value' => new \DateTime()]
        ];
    }
}
