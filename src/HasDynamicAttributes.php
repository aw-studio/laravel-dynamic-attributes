<?php

namespace AwStudio\DynamicAttributes;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\MorphMany;

/**
 * @mixin \Illuminate\Database\Eloquent\Model
 * @property-read \Illuminate\Database\Eloquent\Collection $dynamicAttributes
 */
trait HasDynamicAttributes
{
    /**
     * Dynamic attributes relationship.
     *
     * @return MorphMany
     */
    public function dynamicAttributes(): MorphMany
    {
        return $this->morphMany($this->getDynamicAttributeModel(), 'model');
    }

    /**
     * Get attribute model.
     *
     * @return string
     */
    public function getDynamicAttributeModel()
    {
        return property_exists($this, 'attributesModel')
            ? $this->attributesModel
            : Attribute::class;
    }

    /**
     * Set a given attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setAttribute($key, $value)
    {
        if (! $this->isDynamicAttribute($key)) {
            return parent::setAttribute($key, $value);
        }

        return $this->setDynamicAttribute($key, $value);
    }

    /**
     * Set a given dynamic attribute on the model.
     *
     * @param  string $key
     * @param  mixed  $value
     * @return $this
     */
    public function setDynamicAttribute($key, $value)
    {
        $attribute = $this->firstOrNewDynamicAttribute($key);

        $attribute->value = $value;

        return $this;
    }

    /**
     * Get first or dynamic attribute or create a new one.
     *
     * @param  string          $key
     * @return Attribute|mixed
     */
    public function firstOrNewDynamicAttribute($key)
    {
        $attribute = $this->getRelationValue('dynamicAttributes')
            ->where('key', $key)
            ->first();

        if ($attribute) {
            return $attribute;
        }

        $attribute = $this->dynamicAttributes()->make(['key' => $key]);

        $this->setRelation(
            'dynamicAttributes',
            $this->getRelationValue('dynamicAttributes')->push($attribute)
        );

        return $attribute;
    }

    /**
     * Perform any actions that are necessary after the model is saved.
     *
     * @param  array $options
     * @return void
     */
    protected function finishSave(array $options)
    {
        $this->getRelationValue('dynamicAttributes')->each->save();

        parent::finishSave($options);
    }

    /**
     * Insert the given attributes and set the ID on the model.
     *
     * @param  \Illuminate\Database\Eloquent\Builder $query
     * @param  array                                 $attributes
     * @return void
     */
    protected function insertAndSetId(Builder $query, $attributes)
    {
        $id = $query->insertGetId($attributes, $keyName = $this->getKeyName());

        $this->setAttribute($keyName, $id);

        $this->getRelationValue('dynamicAttributes')->each->setAttribute('model_id', $id);
    }

    /**
     * Determine if the given attribute may be mass assigned.
     *
     * @param  string $key
     * @return bool
     */
    public function isFillable($key)
    {
        return $this->isDynamicAttribute($key) ? true : parent::isFillable($key);
    }

    /**
     * Set dynamic model attribute casts.
     *
     * @param  array $casts
     * @return $this
     */
    public function setDynamicAttributeCasts(array $casts)
    {
        foreach ($casts as $key => $cast) {
            $this->setAttributeCast($key, $cast);
        }

        return $this;
    }

    /**
     * Set a cast for a dynamic attribute with the given name.
     *
     * @param  string $key
     * @param  string $cast
     * @return $this
     */
    public function setDynamicAttributeCast($key, $cast)
    {
        $this->firstOrNewDynamicAttribute($key)->cast = $cast;

        return $this;
    }

    /**
     * Get model attributes.
     *
     * @return array
     */
    public function getAttributes()
    {
        return array_merge(
            parent::getAttributes(),
            $this->getDynamicAttributes()
        );
    }

    /**
     * Get all of the current attributes on the model for an insert operation.
     *
     * @return array
     */
    protected function getAttributesForInsert()
    {
        return parent::getAttributes();
    }

    /**
     * Get dynamic attributes.
     *
     * @return array
     */
    public function getDynamicAttributes()
    {
        return $this
            ->getRelationValue('dynamicAttributes')
            ->mapWithKeys(function (Attribute $attribute) {
                return [$attribute->key => $attribute->value];
            })
            ->toArray();
    }

    /**
     * Get attribute by name.
     *
     * @param  string $key
     * @return mixed
     */
    public function getAttribute($key)
    {
        return $this->isDynamicAttribute($key)
            ? $this->firstOrNewDynamicAttribute($key)->value
            : parent::getAttribute($key);
    }

    /**
     * Determine if the given attribute is dynamic.
     *
     * @param  string $key
     * @return bool
     */
    public function isDynamicAttribute($key)
    {
        return ! in_array($key, $this->getStaticAttributeNames());
    }

    /**
     * Get a list of attributes that are static.
     *
     * @return array
     */
    public function getStaticAttributeNames()
    {
        $names = [$this->getKeyName(), ...$this->getFillable()];

        if ($this->usesTimestamps()) {
            $names[] = $this->getUpdatedAtColumn();
            $names[] = $this->getCreatedAtColumn();
        }

        if (property_exists($this, 'staticAttributes')) {
            $names = [...$names, $this->staticAttributes];
        }

        return $names;
    }

    /**
     * `whereAttributes` query scope.
     *
     * @param  Builder $query
     * @param  string  $key
     * @param  array   ...$parameters
     * @return void
     */
    public function scopeWhereAttribute($query, $key, ...$parameters)
    {
        $query->whereHas('dynamicAttributes', function ($subQuery) use ($key, $parameters) {
            $subQuery
                ->where('key', $key)
                ->where('value', ...$parameters);
        });
    }
}
