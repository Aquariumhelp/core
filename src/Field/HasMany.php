<?php

namespace Terranet\Administrator\Field;

use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
use Terranet\Administrator\Architect;
use Terranet\Administrator\Exception;
use Terranet\Administrator\Field\Traits\HandlesRelation;
use Terranet\Administrator\Modules\Faked;

class HasMany extends Field
{
    use HandlesRelation;

    /** @var string */
    public $icon = 'list-ul';

    /** @var null|Closure */
    protected $query;

    /**
     * @param Closure $query
     *
     * @return $this
     */
    public function withQuery(Closure $query)
    {
        $this->query = $query;

        return $this;
    }

    /**
     * @param string $icon
     *
     * @return self
     */
    public function setIcon(string $icon): self
    {
        $this->icon = $icon;

        return $this;
    }

    /**
     * @param Builder $query
     * @param Model $model
     * @param string $direction
     *
     * @return Builder
     */
    public function sortBy(Builder $query, Model $model, string $direction): Builder
    {
        return $query->withCount($this->id())->orderBy("{$this->id()}_count", $direction);
    }

    /**
     * @return array
     */
    protected function onIndex(): array
    {
        $relation = $this->relation();
        $related = $relation->getRelated();

        // apply a query
        if ($this->query instanceof Closure) {
            $relation = \call_user_func_array($this->query, [$relation]);
        }

        if ($module = Architect::resourceByEntity($related)) {
            $url = route('scaffold.index', [
                'module' => $module->url(),
                $related->getKeyName() => $related->getKey(),
                'viaResource' => is_a($this, BelongsToMany::class)
                    ? app('scaffold.module')->url()
                    : Str::singular(app('scaffold.module')->url()),
                'viaResourceId' => $this->model->getKey(),
            ]);
        }

        return [
            'module' => $module,
            'count' => $relation->count(),
            'url' => $url ?? null,
        ];
    }

    /**
     * @return array
     * @throws Exception
     *
     */
    protected function onView(): array
    {
        $relation = $this->relation();
        $related = $relation->getRelated();

        // apply a query
        if ($this->query instanceof Closure) {
            $relation = \call_user_func_array($this->query, [$relation]);
        }

        if (!$module = $this->relationModule()) {
            // Build a runtime module
            $module = Faked::make($related);
        }
        $columns = $module->columns()->each->disableSorting();
        $actions = $module->actions();

        return [
            'module' => $module ?? null,
            'columns' => $columns ?? null,
            'actions' => $actions ?? null,
            'relation' => $relation ?? null,
            'items' => $relation ? $relation->getResults() : null,
        ];
    }
}
