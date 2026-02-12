<?php

namespace Masterfri\SmartRelations\Processor\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\Relation;
use Masterfri\SmartRelations\Exception\MalformedDataException;
use Masterfri\SmartRelations\Exception\IntegrityException;

trait ConvertToModel
{
    use CheckNull;
    
    /**
     * Convert value to eloquent model instance
     * 
     * @param Relation $relation
     * @param mixed $value
     * 
     * @return Model|null
     */ 
    protected function toModel(Relation $relation, $value)
    {
        if ($this->isNullEquivalent($value)) {
            return null;
        }
        
        if ($value instanceof Model) {
            return $value;
        }
        
        if (is_array($value)) {
            return $this->createFromArray($relation, $value);
        }
        
        if (is_scalar($value)) {
            return $this->findByPk($relation, $value);
        }
        
        throw new MalformedDataException('Unexpected data format');
    }
    
    /**
     * Create model instance from array
     * 
     * @param Relation $relation
     * @param array $value
     * 
     * @return Model
     */ 
    protected function createFromArray(Relation $relation, array $value)
    {
        $related = $relation->getRelated();
        $pk = $related->getKeyName();
        
        if (isset($value[$pk])) {
            $instance = $this->findByPk($relation, $value[$pk]);
            $instance->fill($value);
        } else {
            $instance = $related->newInstance($value);
        }
        
        return $instance;
    }
    
    /**
     * Retrieve model by its primary key
     * 
     * @param Relation $relation
     * @param int|string $pk
     * 
     * @return Model|null
     */ 
    protected function findByPk(Relation $relation, $pk)
    {
        $model = $relation->getRelated()->resolveRouteBinding($pk);
        
        if ($model === null) {
            throw new IntegrityException(
                sprintf('Related model does not exist: %s', $pk)
            );
        }
        
        return $model;
    }
}
