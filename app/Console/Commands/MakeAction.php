<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeAction extends Command
{
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
    protected $description = 'Create a new Action file in a specific namespace.';

    public function __construct(protected Filesystem $files, protected string $itemName = 'Action')
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $name = $this->argument('name');

        $parts = explode('\\', $name);
        $class = array_pop($parts);

        // namespace base: App\Actions || el resto: puede ser proporcionado como namespace personalizado.
        $namespace = 'App\\' . $this->itemName . 's' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');

        // Generando la ruta del archivo.
        $path = app_path($this->itemName . 's/' . implode('/', $parts) . '/' . $class . '.php');

        // Verificar si el archivo ya existe.
        if ($this->files->exists($path)) {
            $this->fail('This ' . $this->itemName . ' already exists');

            return false; //
        }

        $this->makeDirectory($path);

        // Obtener el contenido del stub y reemplazar los marcadores de posición (placeholders).
        $stub = $this->getStub();
        $stub = str_replace(
            ['{{ namespace }}', '{{ class }}'],
            [$namespace, $class],
            $stub
        );

        // Guardar el archivo generado en la ruta especificada y notificación al usuario.
        $this->files->put($path, $stub);

        $this->info($this->itemName . ' created successfully.');

        return true; //
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
        // Cargar el stub del archivo de plantilla.
        return $this->files->get(base_path('stubs/action.stub'));
    }
}
