<?php

namespace App\Helpers;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Http\Request;

class Filter
{
    /**
     * @var Model
     * @var $request
     */
    private $model, $request;

    /**
     * Initialize the search modal name & request params
     * Filter constructor.
     *
     * @param Model   $model
     * @param Request $request
     */
    public function __construct(Model $model, Request $request)
    {
        # Initialize model
        $this->model = $model;

        # Initialize request params
        $this->request = $request;
    }

    /**
     * @param $name
     * @param $arguments
     * @return Model
     */
    public function __call($name, $arguments)
    {
        return $this->model->$name(...$arguments);
    }

    /**
     * Generate status finding query corresponding to model
     *
     * @param mixed ...$columns
     */
    public function includeFilter(...$columns): void
    {
        foreach ($columns as $column) {
            # Collect the requested status params
            $includeData = $this->request->input($column) ?? [];

            # Generate the status finding query corresponding to model
            if (!empty($includeData)) $this->model = $this->model->whereIn($column, $includeData);
        }
    }

    /**
     * Generate type finding query corresponding to model
     *
     * @param array $columns
     */
    public function excludeFilter(...$columns): void
    {
        foreach ($columns as $column) {
            # Collect the requested type params
            $excludeData = $this->request->input("exclude_" . $column) ?? [];

            # Generate the status finding query corresponding to model
            if (!empty($excludeData)) $this->model = $this->model->whereNotIn("type", $excludeData);
        }
    }

    public function dateFilter()
    {
        # Collect the requested date params
        $startDate = $this->request->startDate;
        $endDate   = $this->request->endDate;

        # Generate the status finding query corresponding to model
        if (empty($startDate) && !empty($endDate)) {
            $this->model = $this->model->whereDate("created_at", "<=", $endDate);
        } elseif (empty($endDate) && !empty($startDate)) {
            $current     = Carbon::now()->format("Y-m-d");
            $this->model = $this->model->whereBetween("created_at", [$startDate, $current]);
        } elseif (!empty($endDate) && !empty($startDate)) {
            $this->model = $this->model->whereBetween("created_at", [$startDate, $endDate]);
        }
    }

    /**
     * Generate text finding query corresponding to model
     *
     * @param array $columns
     */
    public function textFilter(...$columns)
    {
        # Collect the requested text param
        $text = $this->request->text;

        # Generate the text finding query corresponding to model
        if (!empty($text)) {
            $this->model = $this->model->where(function ($query) use ($columns, $text) {
                foreach ($columns as $column) {
                    $query->orWhere($column, 'like', '%' . $text . '%');
                }
            });
        }
    }
}
