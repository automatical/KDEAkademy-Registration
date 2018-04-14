<?php
namespace App\Command;

use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Cilex\Provider\Console\Command;

class MailmanCommand extends Command
{
    private $alreadySubscribed = [];

    protected function configure()
    {
        $this
            ->setName('events:mailman')
            ->setDescription('Register Attendess with Mailman');
    }
    
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $output->writeln("Registering Conference Attendees with Mailman");

        // Get conferences with registration
        $database = $this->getApplication()->getService('database');

        $conferenceMapper = $database->mapper('Entity\Conference');
        $registrationMapper = $database->mapper('Entity\Registration');
        $mailmanMapper = $database->mapper('Entity\Mailman');

        $conferences = $conferenceMapper->query("SELECT * FROM conference WHERE mailman is not null");
        
        foreach($conferences as $conference) {
            $existingRegistrations = [];
            $registered = $mailmanMapper->query("SELECT * FROM mailman WHERE conference_id = {$conference->id}");
            foreach($registered as $r) {
                $existingRegistrations[] = $r->dn;
            }

            $toBeRegistered = [];
            $registered = $registrationMapper->query("SELECT * FROM registration WHERE conference_id = {$conference->id}");
            foreach($registered as $r) {
                if(!in_array($r->dn, $existingRegistrations)) {
                    $toBeRegistered[] = $r->dn;
                }
            }

            $output->writeln("{$conference->name}: Pending Registrations: " . count($toBeRegistered));

            foreach($toBeRegistered as $dn) {
                $this->registerToMailman($dn, $conference->mailman, $conference->id, $output);
            }
        }
    }

    private function registerToMailman($dn, $mailmanAddress, $conferenceId, &$output) {
        $database = $this->getApplication()->getService('database');
        $profileMapper = $database->mapper('Entity\Profile');
        $mailmanMapper = $database->mapper('Entity\Mailman');

        $profile = json_decode($profileMapper->where(['dn' => $dn])->first()->data);

        $output->writeln(implode(" ", ["Registering", $profile->Email, "with", $mailmanAddress]));

        // Automated Subscribe: https://www.gnu.org/software/mailman/mailman-member/node41.html#a:commands
        mail($mailmanAddress, 'SUBSCRIBE', "", implode(" ", ["From:", $profile->Email]));

        $mailmanMapper->create([ 'dn' => $dn, 'conference_id' => $conferenceId ]);
    }
}
