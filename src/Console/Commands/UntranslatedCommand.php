<?php
namespace RobinFranssen\AnalyzeLocale\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Input\InputOption;

class Untranslated extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'locale:untranslated';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scan all untranslated locale keys in this project.';

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
	public function fire()
	{
        $this->call('locale:scan',
            ['--show' => ['untranslated'], '--locale' => $this->option('locale')]);
	}

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return [
            ['locale', 'l', InputOption::VALUE_OPTIONAL, 'Scan specified locale for untranslated keys.', \App::getLocale()],
        ];
    }
}
