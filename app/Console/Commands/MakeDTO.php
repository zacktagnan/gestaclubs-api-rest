<?php

namespace App\Console\Commands;

use App\Traits\CliOutputStyler;
use Illuminate\Console\Command;
use Illuminate\Filesystem\Filesystem;

class MakeDTO extends Command
{
    use CliOutputStyler;

    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'make:dto {name : The name of the DTO, including its namespace}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a new DTO file in a specific namespace.  :)';

    public function __construct(protected Filesystem $files, protected string $itemName = 'DTO')
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

        // namespace base: App\DTOs || el resto: puede ser proporcionado como namespace personalizado.
        $namespace = 'App\\' . $this->itemName . 's' . (!empty($parts) ? '\\' . implode('\\', $parts) : '');

        // Generando la ruta del archivo.
        $path = app_path($this->itemName . 's/' . implode('/', $parts) . '/' . $class . '.php');

        // Verificar si el archivo ya existe.
        if ($this->files->exists($path)) {
            // $this->fail('This ' . $this->itemName . ' already exists');

            $this->renderErrorMessage(
                "The specified {$this->itemName} already exists."
            );

            // return false; //
            // return 1; //
            // o, más recomendado, haciendo llamada a una constante de la clase Command:
            return self::FAILURE;
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

        // Obtener la ruta relativa para que se vea bien en consola
        $relativePath = str_replace(base_path() . '/', '', $path);

        // $this->newLine();
        // $this->info('INFO ' . $this->itemName . ' [' . $relativePath . '] created successfully.');
        // $this->newLine();

        $this->renderInfoMessage(
            "{$this->itemName} <span class=\"font-bold\">[{$relativePath}]</span> created successfully."
        );

        // $this->info($this->itemName . ' created successfully.');
        // $this->line('INFO ' . $this->itemName . ' [' . $path . '] created successfully.');
        // $this->info('INFO ' . $this->itemName . ' [' . $path . '] created successfully.');
        // $this->info('INFO  Console command [' . $path . '] created successfully.');
        // INFO  Console command [app/Console/Commands/MakeDTO.php] created successfully.

        // return true; //
        // En términos de consola, no es necesario retornar un valor booleano, sino el exit code del proceso.
        // En Laravel, si el comando se ejecuta correctamente, se asume que el código de salida es 0 (cero).
        // Si se produce un error, se puede lanzar una excepción o usar $this->fail() para indicar un error.
        // Y el exit code será 1 (uno) por defecto.
        // return 0; //
        // o, más recomendado, haciendo llamada a una constante de la clase Command:
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
        // Cargar el stub del archivo de plantilla.
        return $this->files->get(base_path(sprintf('stubs/%s.stub', strtolower($this->itemName))));
    }
}
