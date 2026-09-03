<?php

namespace App\Models;

class ActivityLog
{
    /**
     * Shim/wrapper to map legacy manual log creations to Spatie Activitylog engine.
     */
    public static function create(array $attributes)
    {
        $activity = activity();

        // Map causer (user)
        if (isset($attributes['user_id'])) {
            $user = User::find($attributes['user_id']);
            if ($user) {
                $activity->causedBy($user);
            }
        } elseif (auth()->check()) {
            $activity->causedBy(auth()->user());
        }

        // Map subject (entity)
        if (isset($attributes['entity_type']) && isset($attributes['entity_id'])) {
            $modelClass = 'App\\Models\\' . $attributes['entity_type'];
            if (class_exists($modelClass)) {
                $model = $modelClass::find($attributes['entity_id']);
                if ($model) {
                    $activity->performedOn($model);
                }
            }
        }

        // Map properties (old/new snapshots)
        $properties = [];
        if (isset($attributes['old_values'])) {
            $properties['old'] = $attributes['old_values'];
        }
        if (isset($attributes['new_values'])) {
            $properties['new'] = $attributes['new_values'];
        }
        if (isset($attributes['metadata'])) {
            $properties['metadata'] = $attributes['metadata'];
        }

        if (!empty($properties)) {
            $activity->withProperties($properties);
        }

        // Map event & description
        $event = $attributes['action'] ?? 'activity';
        $activity->event($event);

        return $activity->log($event);
    }
}
