<?php

namespace App\Console\Commands;

use App\Traits\CliOutputStyler;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeAction extends Command
{
    use CliOutputStyler;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:action {name : The name of the Action, including its namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new Action file in a specific namespace.  :)';

    public function __construct(protected Filesystem $files, protected string $itemName = 'Action')
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $name = $this->argument('name');

        $parts = explode('\\', $name);
        $class = array_pop($parts);

        $namespace = 'App\\' . $this->itemName . 's' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');

        $path = app_path($this->itemName . 's/' . implode('/', $parts) . '/' . $class . '.php');

        if ($this->files->exists($path)) {
            $this->renderErrorMessage(
                "The specified {$this->itemName} already exists."
            );

            return self::FAILURE;
        }

        $this->makeDirectory($path);

        $stub = $this->getStub();
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $class],
            $stub
        );

        $this->files->put($path, $stub);

        $relativePath = str_replace(base_path() . '/', '', $path);

        $this->renderInfoMessage(
            "{$this->itemName} <span class=\"font-bold\">[{$relativePath}]</span> created successfully."
        );

        return self::SUCCESS;
    }

    protected function makeDirectory($path): void
    {
        if (!$this->files->isDirectory(dirname($path))) {
            $this->files->makeDirectory(dirname($path), 0755, true);
        }
    }

    /**
     * @throws FileNotFoundException
     */
    public function getStub(): string
    {
        return $this->files->get(base_path('stubs/action.stub'));
    }
}
