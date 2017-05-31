<?php namespace Ingruz\Yodo\Traits;

trait ClassNameInspectorTrait
{
    /**
     * @return array
     */
    protected function getClassNameParts()
    {
        return explode('\\', get_called_class());
    }

    /**
     * @param array $ns
     * @return string
     */
    protected function getRootNamespace($ns)
    {
        return reset($ns);
    }

    /**
     * @param string $search
     * @param string $replace
     * @param array $ns
     * @return string
     */
    protected function getRelatedClassName($search, $replace, $ns)
    {
        return str_replace($search, $replace, end($ns));
    }
}
