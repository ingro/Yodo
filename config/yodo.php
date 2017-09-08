<?php

return [
    /*
     * Root namespace where Yodo will search for Repositories
     */
    'repositoriesNamespace' => 'App\Repositories',

    /*
     * Root namespace where Yodo will search for Transformers
     */
    'transformersNamespace' => 'App\Transformers',

    /*
     * Http error code to use in case of ApiLimitNotValidException
     */
    'apiLimitExceptionHttpCode' => 400,

    /*
     * Http error code to use in case of ModelValidationException
     */
    'modelValidationExceptionHttpCode' => 422
];