<?php

namespace AwStudio\DynamicAttributes;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Model;

class Attribute extends Model
{
    /**
     * Attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'key', 'value', 'cast', 'model_type', 'model_id',
    ];

    /**
     * Database table name.
     *
     * @var string
     */
    public $table = 'attributes';

    /**
     * Get the casts array.
     *
     * @return array
     */
    public function getCasts()
    {
        return array_merge(
            parent::getCasts(),
            $this->getDynamicCasts()
        );
    }

    /**
     * Get dynamic model attribute casts.
     *
     * @return array
     */
    public function getDynamicCasts()
    {
        if (! array_key_exists('cast', $this->attributes)) {
            return [];
        }

        if (! $this->attributes['cast']) {
            return [];
        }

        return ['value' => $this->attributes['cast']];
    }

    /**
     * Set model attribute.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        parent::setAttribute($key, $value);

        if ($key == 'value') {
            $this->cast = gettype($value);
        }

        return $this;
    }

    /**
     * Get cast from the given value.
     *
     * @param  mixed $value
     * @return void
     */
    public function getCastFromValue(mixed $value)
    {
        if ($value instanceof Carbon) {
            return 'datetime';
        }

        return gettype($value);
    }
}
