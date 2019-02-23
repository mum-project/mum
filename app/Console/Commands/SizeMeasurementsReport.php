<?php

namespace App\Console\Commands;

use App\Domain;
use App\Mailbox;
use App\SizeMeasurement;
use function array_filter;
use function array_intersect;
use function array_keys;
use function config;
use const FILTER_VALIDATE_INT;
use function filter_var;
use Illuminate\Console\Command;
use InvalidArgumentException;
use function sizeof;

class SizeMeasurementsReport extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'size-measurements:report
                            {directory : Home directory of a mailbox or a domain}
                            {size : Size of the directory, defaults to Kibibyte}
                            {--KiB : Size in Kibibyte (default)}
                            {--MiB : Size in Mebibyte}
                            {--GiB : Size in Gibibyte}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Report the size of a mailbox/domain home directory.';

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
        } catch (InvalidArgumentException $exception) {
            $this->error($exception->getMessage());
            return 1;
        }
    }

    /**
     * Actually handle the command execution.
     * This wrapper method is needed to allow for easier
     * Exception management.
     *
     * @return void
     * @throws InvalidArgumentException
     */
    private function executeCommand()
    {
        $measurable = $this->findMeasurableMailbox() ?: $this->findMeasurableDomain();

        if ($this->handleRootFolderMeasurement()) {
            return;
        }

        if (!$measurable) {
            throw new InvalidArgumentException(sprintf('Could not find a mailbox or domain with the home directory "%s".',
                $this->argument('directory')));
        }

        $size = $this->getSizeInKibibyte();

        $measurable->sizeMeasurements()
            ->create([
                'size' => $size
            ]);
    }

    /**
     * Validate the size options and return the calculated folder size
     * in Kibibyte. If the validation fails, `false` is returned.
     *
     * @return int
     * @throws InvalidArgumentException
     */
    private function getSizeInKibibyte()
    {
        if ($this->areOptionsConflicting()) {
            throw new InvalidArgumentException('Your have specified conflicting arguments.');
        }
        $size = $this->argument('size');
        if (!filter_var($size, FILTER_VALIDATE_INT)) {
            throw new InvalidArgumentException('The size option must be a valid integer.');
        }
        if ($this->option('GiB')) {
            return $size * 1024 * 1024;
        }
        if ($this->option('MiB')) {
            return $size * 1024;
        }
        return $size;
    }

    /**
     * Checks whether multiple (conflicting) size units are supplied.
     *
     * @return bool
     */
    private function areOptionsConflicting()
    {
        return sizeof(array_intersect([
                'KiB',
                'MiB',
                'GiB'
            ], array_keys(array_filter($this->options())))) > 1;
    }

    /**
     * Find a measurable mailbox based on the supplied home directory.
     *
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    private function findMeasurableMailbox()
    {
        $homedir = $this->argument('directory');

        return Mailbox::query()
            ->where('homedir', $homedir)
            ->first();
    }

    /**
     * Find a measurable domain based on the supplied home directory.
     *
     * @return \Illuminate\Database\Eloquent\Model|null|object|static
     */
    private function findMeasurableDomain()
    {
        $homedir = $this->argument('directory');

        return Domain::query()
            ->where('homedir', $homedir)
            ->first();
    }

    /**
     * Check if the supplied home directory equals the root folder of
     * our MDA from config.
     * If yes, the measurement will be saved and `true` will be returned.
     * If not, `false` will be returned.
     *
     * @return bool
     * @throws InvalidArgumentException
     */
    private function handleRootFolderMeasurement()
    {
        if ($this->argument('directory') !== config('mum.mailboxes.root_directory')) {
            return false;
        }

        $size = $this->getSizeInKibibyte();

        SizeMeasurement::create([
            'size'            => $size,
            'measurable_id'   => null,
            'measurable_type' => null
        ]);

        return true;
    }
}
