<?php

namespace App\Console\Commands;

use Illuminate\Foundation\Console\RouteListCommand as BaseRouteListCommand;
use Symfony\Component\Console\Input\InputOption;

class RouteListCommand extends BaseRouteListCommand
{
    /**
     * Get the column names to show.
     *
     * Some tooling passes a --columns flag to route:list. Keep the option for
     * compatibility and apply it to JSON output where the column list is direct.
     *
     * @return array
     */
    protected function getColumns()
    {
        if (! $this->option('json')) {
            return parent::getColumns();
        }

        $columns = $this->option('columns');

        if (empty($columns)) {
            return parent::getColumns();
        }

        $requestedColumns = $this->parseColumns($columns);

        return array_filter(
            parent::getColumns(),
            fn ($column) => in_array($column, $requestedColumns, true)
        );
    }

    /**
     * Convert the given routes to JSON.
     *
     * @param  \Illuminate\Support\Collection  $routes
     * @return string
     */
    protected function asJson($routes)
    {
        return $routes
            ->map(function ($route) {
                if (array_key_exists('middleware', $route)) {
                    $route['middleware'] = empty($route['middleware']) ? [] : explode("\n", $route['middleware']);
                }

                return $route;
            })
            ->values()
            ->toJson();
    }

    /**
     * Get the console command options.
     *
     * @return array
     */
    protected function getOptions()
    {
        return array_merge(parent::getOptions(), [
            ['columns', null, InputOption::VALUE_OPTIONAL | InputOption::VALUE_IS_ARRAY, 'Columns to include in JSON output'],
        ]);
    }
}
