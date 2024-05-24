<?php

namespace App\Console\Commands\Search;

use App\Models\Keyword;
use Elastic\Elasticsearch\Client;
use Illuminate\Console\Command;

class IndexControl extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scout:control
    {model : Model name without prefix App\\Models}
    {action : action, can be index,re-index,delete}
    {--range= : id range}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Parts index control. Actions :
     - index : tạo index và import
     - create : tạo index
     - delete : xóa index
     - re-index : xóa và tạo lại index sau đó import
     - re-create : xóa và tạo lại index
     - flush : xóa document trong index
     - import : import document vào index
    ';


    protected $model = '';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->model = 'App\\Models\\'.$this->argument('model');
        $action = $this->argument('action');

        $pipe = match ($action) {
            'create' => ['create'],
            're-index' => ['delete', 'create', 'import'],
            're-create' => ['delete', 'create'],
            'flush' => ['flush'],
            'import' => ['import'],
            'delete' => ['delete'],
            default => throw new \Exception('Action not supported : '.$action),
        };

        foreach ($pipe as $action) {
            $this->{$action.'Index'}();
        }

        return Command::SUCCESS;
    }

    protected function flushIndex()
    {
        \Artisan::call('scout:flush', ['model' => $this->model], $this->getOutput());
    }

    protected function createIndex()
    {
        \Artisan::call('scout:import2', ['searchable' => $this->model], $this->getOutput());
    }

    protected function deleteIndex()
    {
        try {
            if (app(Client::class)->indices()->existsAlias(['name' => $this->get_index_name()])->asBool()) {
                $result = app(Client::class)->indices()->getAlias(['name' => $this->get_index_name()]);
                $result = $result->asArray();
                if (count($result)) {
                    foreach ($result as $k => $v) {
                        app(Client::class)->indices()->delete(['index' => $k]);
                        $this->warn('Deleted index '.$k);
                        break;
                    }
                }
            } elseif (app(Client::class)->indices()->exists(['index' => $this->get_index_name()])->asBool()) {
                app(Client::class)->indices()->delete(['index' => $this->get_index_name()]);
                $this->warn('Deleted index '.$this->get_index_name());
            }
        } catch (\Exception $ex) {
            $this->warn($ex->getMessage());
        }
    }

    protected function importIndex()
    {
        \Artisan::call('scout:import3', ['model' => $this->model], $this->getOutput());
    }

    protected function get_index_name()
    {
        return match ($this->model) {
            Keyword::class => config('scout.prefix').'keywords',
        };
    }
}
