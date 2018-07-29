<?php

namespace App\Console\Commands;

use App\Domain;
use App\Mailbox;
use Illuminate\Console\Command;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Facades\Hash;

class MailboxesCreate extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'mailboxes:create
                            {local_part : The local part of the email address}
                            {domain : An existing domain in MUM}
                            {--password= : You are asked for a password if you don\'t supply this option. Be aware that the value of the password option may be recorded in your shell history.}
                            {--name= : The name of the person that uses the mailbox}
                            {--alternative_email= : An alternative address that may be used to reset the password}
                            {--quota= : Maximum disk space for emails in GB}
                            {--send_only : Whether the mailbox should be able to receive emails, defaults to false}
                            {--inactive : Whether the mailbox should be inactive, defaults to false}
                            {--super_admin : Whether the mailbox should be a super admin, defaults to false}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new mailbox. ATTENTION: This command currently does not perform ANY validation of supplied data.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        try {
            $this->executeCommand();
            return 0;
        } catch (ModelNotFoundException $exception) {
            $this->error('Domain was not found');
            return 1;
        }
    }

    /**
     * Wraps the actual handling of the command to allow for easier
     * error handling.
     *
     * @throws ModelNotFoundException
     */
    protected function executeCommand()
    {
        $data = [
            'local_part'        => $this->argument('local_part'),
            'domain_id'         => $this->findDomainId(),
            'name'              => $this->option('name'),
            'alternative_email' => $this->option('alternative_email'),
            'quota'             => $this->option('quota'),
            'send_only'         => $this->option('send_only'),
            'active'            => !$this->option('inactive'),
            'is_super_admin'    => $this->option('super_admin')
        ];
        $mailbox = new Mailbox($data);
        $mailbox->password = $this->getHashedPassword();
        $mailbox->save();
        $this->info($mailbox->address() . ' was created successfully.', 1);
    }

    /**
     * Find a domain based on the supplied command line option.
     *
     * @return int
     * @throws ModelNotFoundException
     */
    protected function findDomainId()
    {
        return Domain::query()
            ->where('domain', $this->argument('domain'))
            ->orWhere('id', $this->argument('domain'))
            ->firstOrFail()->id;
    }

    /**
     * Returns the hashed password of either the command line option or
     * the user input when asked for a password.
     *
     * @return string
     */
    protected function getHashedPassword()
    {
        if ($this->option('password')) {
            return Hash::make($this->option('password'));
        }
        $passwordInput = $this->secret('Please choose a password');
        return Hash::make($passwordInput);
    }
}
