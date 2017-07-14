<?php
/**
 * Laravel IDE Helper Generator
 *
 * @author    Barry vd. Heuvel <barryvdh@gmail.com>
 * @copyright 2014 Barry vd. Heuvel / Fruitcake Studio (http://www.fruitcakestudio.nl)
 * @license   http://www.opensource.org/licenses/mit-license.php MIT
 * @link      https://github.com/barryvdh/laravel-ide-helper
 */

namespace SleepingOwl\Admin\Console\Commands;

use Barryvdh\LaravelIdeHelper\Console\GeneratorCommand as IdeHelperGeneratorCommand;
use SleepingOwl\Admin\Console\Generator;
use Illuminate\Config\Repository as ConfigRepository;
use Illuminate\Filesystem\Filesystem;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Input\InputArgument;

/**
 * A command to generate autocomplete information for your IDE
 *
 * @author Aios Dave <aioslike@gmail.com>
 */
class GeneratorCommand extends IdeHelperGeneratorCommand
{

    /**
     * The console command name.
     *
     * @var string
     */
    protected $name = 'sleepingowl:ide:generate';

    /**
     * Execute the console command.
     *
     * @return void
     */
    public function fire()
    {
        if (file_exists(base_path() . '/vendor/compiled.php') ||
            file_exists(base_path() . '/bootstrap/cache/compiled.php') ||
            file_exists(base_path() . '/storage/framework/compiled.php')) {
            $this->error(
                'Error generating IDE Helper: first delete your compiled file (php artisan clear-compiled)'
            );
        } else {
            $filename = $this->argument('filename');
            $format = $this->option('format');

            // Strip the php extension
            if (substr($filename, -4, 4) == '.php') {
                $filename = substr($filename, 0, -4);
            }

            $filename .= '.' . $format;

            if ($this->option('memory')) {
                $this->useMemoryDriver();
            }


            $helpers = '';
            if ($this->option('helpers') || ($this->config->get('ide-helper.include_helpers'))) {
                foreach ($this->config->get('ide-helper.helper_files', array()) as $helper) {
                    if (file_exists($helper)) {
                        $helpers .= str_replace(array('<?php', '?>'), '', $this->files->get($helper));
                    }
                }
            } else {
                $helpers = '';
            }

            $generator = new Generator($this->config, $this->view, $this->getOutput(), $helpers);
            $content = $generator->generate($format);
            $written = $this->files->put($filename, $content);

            if ($written !== false) {
                $this->info("A new helper file was written to $filename");
            } else {
                $this->error("The helper file could not be created at $filename");
            }
        }
    }
}
