<?php namespace Ingruz\Yodo\Helpers;

class RulesMerger {
    public static function merge($rules, $op) {
        $result = [];

        if (empty ($rules))
        {
            return $result;
        }

        // If the operation is not a known one returns just the `save` array if preset, otherwise the full $rules array
        if ($op !== 'update' and $op !== 'create') {
            if (isset($rules['save'])) {
                return $rules['save'];
            }

            return $rules;
        }

        // If the $rules array has no key `save` return the full $rules array
        if (! isset($rules['save']) and ! isset($rules[$op]))
        {
            return $rules;
        }

        // If the $rules has no key `save` but one named as the operation return just that
        if (! isset($rules['save']) and isset($rules[$op]))
        {
            return $rules[$op];
        }

        $merged = (isset($rules[$op])) ? array_merge_recursive($rules['save'], $rules[$op]) : $rules['save'];

        foreach ($merged as $field => $rules)
        {
            if (is_array($rules))
            {
                $result[$field] = implode('|', $rules);
            } else
            {
                $result[$field] = $rules;
            }
        }

        return $result;
    }
}
