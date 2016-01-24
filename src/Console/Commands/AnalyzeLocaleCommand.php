<?php
namespace RobinFranssen\AnalyzeLocale\Console\Commands;

use Illuminate\Console\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

class AnalyzeLocaleCommand extends Command {

	/**
	 * The console command name.
	 *
	 * @var string
	 */
	protected $name = 'locale:scan';

	/**
	 * The console command description.
	 *
	 * @var string
	 */
	protected $description = 'Scan your project for invalid/untranslated locale keys.';


    /**
     * @var array Available options.
     */
    protected $availableOptions = ['all', 'allkeys', 'untranslated', 'invalid'];

    /**
     * @var \Symfony\Component\Console\Helper\ProgressHelper
     */
    protected $progressBar;


    /**
     * @var
     */
    protected $phpFiles;

    /**
     * Contains all the results;
     *
     * @var array
     */
    protected $all = [];

    /**
     * Contains untranslated keys.
     *
     * @var array
     */
    protected $untranslatedKeys = [];

    /**
     * contains invalid keys.
     *
     * @var array
     */
    protected $invalidKeys = [];

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
        //Validate --show= option values.
        foreach($this->option('show') as $option) {
            if (! in_array($option, $this->availableOptions)) {
                $this->error(sprintf('Undefined --show option "%s" used.', $option));
                exit();
            }
        }

        //Setting app locale while command is running.
        //setting fallback locale to same locale.
        //else Lang::has(key) will report a key translated when the key is not for the defined locale.
        \Lang::setLocale($this->option('locale'));
        \Lang::setFallback($this->option('locale'));

        //Make regexiterator with php files.
        $this->info('Preparing files');
        $this->findPHPFiles();

        //Count total files.
        $totalFiles = iterator_count($this->phpFiles);

        //Search for the translation keys in file.
        $this->info('Searching translationkeys in '. $totalFiles .' files');
        $this->findTranslationKeysInFiles();

        $this->info('Analyzing translation keys for <comment>'.$this->option('locale').'</comment> locale');
        $this->findInvalidKeys();
        $this->findUntranslatedKeys();

        if (in_array('all', $this->option('show'))
            || in_array('allkeys', $this->option('show'))) {
            $this->info('Keys used by this laravel application');
            $this->showAllResults();
        }

        if (in_array('all', $this->option('show'))
            || in_array('invalid', $this->option('show'))) {
            $this->info('Invalid keys used: ');
            $this->showInvalidKeys();
        }

        if (in_array('all', $this->option('show'))
            || in_array('untranslated', $this->option('show'))) {
            $this->info('Untranslated keys:');
            $this->showUntranslatedKeys();
        }
	}

	/**
	 * Get the console command arguments.
	 *
	 * @return array
	 */
	protected function getArguments()
	{
		return [
            //['example', InputArgument::REQUIRED, 'An example argument.'],
        ];
	}

	/**
	 * Get the console command options.
	 *
	 * @return array
	 */
	protected function getOptions()
	{
		return [
            ['locale', 'l', InputOption::VALUE_OPTIONAL, 'Scan files for specified locale', \App::getLocale()],
            [
                'show',
                null,
                InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY,
                'Show all information about the translation keys. --show=[all|allkeys|untranslated|invalid]',
                ['all']],
            ['debug', null, InputOption::VALUE_NONE, 'Show debug information', null],
		];
	}

    /**
     *
     */
    private function findPHPFiles()
    {
        $directory = new \RecursiveDirectoryIterator(base_path());
        $iterator = new \RecursiveIteratorIterator($directory);
        $this->phpFiles = new \RegexIterator($iterator, '/^.+\.php$/i', \RecursiveRegexIterator::GET_MATCH);
    }

    /**
     * Scan all file contents for the lang functions off laravel.
     * @lang(key), lang(key) and Lang::get(key[, [something something])
     */
    private function findTranslationKeysInFiles()
    {
        $this->progressBar = $this->getHelperSet()->get('progress');
        $this->progressBar->start($this->output, iterator_count($this->phpFiles));

        $all = [];

        foreach ($this->phpFiles as $file => $a) {
            $this->progressBar->advance();

            $keys = [];
            $matches = [];
            $fileContent = file_get_contents($file);

            /**
             * Match all @lang and trans functioncalls in file. Usually only in blade.php files.
             *
             * Ignores symfony's translator. Always translates through $instance->trans()
             * This translator is found in testfiles.
             *
             **/

            $localePattern = "/((?<=lang::get\\([\\'|\"])|(?<=(?<!\\-\\>)trans\\([\\'|\"])|(?<=(?<!trans)choice\\([\\'|\"])|(?<=lang::has\\([\\'|\"])|(?<=@lang\\([\\'|\"])|(?<=@lang\\([\\'|\"])).*?(?=[\\'|\"])/uism";
            if (preg_match_all($localePattern, $fileContent, $matches)) {
                foreach($matches[0] as $match) {
                    $keys[$match] = $file;
                }

                $all = array_merge($all, $keys);
            }
        }

        $this->all = $all; //Only unique values.

        $this->progressBar->finish();
    }

    /**
     * Helper function for nice presentation.
     *
     * @param array $keys
     * @return array
     */
    private function analyzeKeys(array $keys)
    {
        $all = [];

        foreach ($keys as $key => $file)
        {
            list($namespace, $group, $item) = \Lang::parseKey($key);
            $all[$namespace][$group][] = $item;
        }


        ksort($all);

        return $all;
    }


    /**
     * Finds the invalid keys from the result array.
     *
     * @return array returns array with valid translation keys.
     */
    private function findInvalidKeys()
    {
        foreach($this->all as $key => $file) {
            if ($this->option('debug')) {
                $this->comment('Checking if key is invalid: ' . $key);
            }
            if (strpos($key, '.') === false) {
                $this->invalidKeys[$key] = $file;
            }
        }
    }

    /**
     * Finds the untranslated keys from the result array.
     *
     * @return array
     */
    private function findUntranslatedKeys()
    {
        foreach(array_diff(array_keys($this->all), array_keys($this->invalidKeys)) as $key) {
            if ($this->option('debug')) {
                $format = 'Translation for %s in locale <comment>%s</comment> found: %s';
                $this->comment(sprintf($format, $key, $this->option('locale'), (\Lang::has($key) ? 'Yes' : 'No')));
            }
            if (!\Lang::has($key)) {
                $this->untranslatedKeys[$key] = $this->all[$key];
            }
        }
    }

    /**
     * Show table with invalid keys.
     */
    private function showInvalidKeys()
    {
        if (empty($this->invalidKeys)) {
            $this->info('No invalid keys found in project files');
            return;
        }
        $table = new Table($this->output);
        $table->setHeaders([
            'Key',
            'File'
        ]);

        foreach ($this->invalidKeys as $key => $file)
        {
            $table->addRow([$key, str_replace(base_path(), '', $file)]);
        }

        $table->render();
    }

    /**
     *
     */
    private function showUntranslatedKeys()
    {
        if (empty($this->untranslatedKeys)) {
            $this->info('All keys seem te be translated, congratz!');
            return;
        }

        $analyzedKeys = $this->analyzeKeys($this->untranslatedKeys);

        $table = new Table($this->output);
        $table->setHeaders([
            'Package',
            'File',
            'Item',
            'Full key',
        ]);


        foreach ($analyzedKeys as $packageName => $package)
        {

            foreach ($package as $file => $keys)
            {
                $isPackage = (!empty($packageName) && $packageName != "*");
                $row[0] =  $isPackage ? $packageName : '<comment>No package</comment>';
                $row[1] = $file;

                foreach ($keys as $key)
                {
                    $row[2] = $key;
                    $row[3] = sprintf('%s%s.%s', ($isPackage ? $packageName.'::': ''), $file, $key);

                    $table->addRow($row);
                }
            }
        }

        $table->render();
    }

    /**
     *
     */
    private function showAllResults()
    {
        $checkmark = mb_convert_encoding('&#10004;', 'UTF-8', 'HTML-ENTITIES');
        $crossmark = mb_convert_encoding('&#10060;', 'UTF-8', 'HTML-ENTITIES');

        $info = '<info>%s</info>';

        $table = new Table($this->output);
        $table->setHeaders([
            'Key',
            'Translated item',
            'Invalid item',
            'File',
        ]);

        foreach ($this->all as $key => $file)
        {
            $translated = (!in_array($key, array_keys($this->untranslatedKeys)));
            $invalid    = (in_array($key, array_keys($this->invalidKeys)));

            $table->addRow([
                $key,
                sprintf($info, ($translated && !$invalid  ? $checkmark: '')),
                sprintf($info, ($invalid ? $checkmark: '')),
                str_replace(base_path(), '', $file),
            ]);
        }

        $table->render();

    }

}
