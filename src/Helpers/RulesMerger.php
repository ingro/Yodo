<?php namespace Ingruz\Yodo\Helpers;

class RulesMerger {
    /**
     * @param array $rules
     * @param string $operation
     * @return array
     */
    public static function merge($rules, $operation) {
        $result = [];

        if (empty ($rules))
        {
            return $result;
        }

        // If the operation is not a known one returns just the `save` array if preset, otherwise the full $rules array
        if ($operation !== 'update' and $operation !== 'create') {
            if (isset($rules['save'])) {
                return $rules['save'];
            }

            return $rules;
        }

        // If the $rules array has no key `save` return the full $rules array
        if (! isset($rules['save']) and ! isset($rules[$operation]))
        {
            return $rules;
        }

        // If the $rules has no key `save` but one named as the operation return just that
        if (! isset($rules['save']) and isset($rules[$operation]))
        {
            return $rules[$operation];
        }

        $merged = (isset($rules[$operation])) ? array_merge_recursive($rules['save'], $rules[$operation]) : $rules['save'];

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
