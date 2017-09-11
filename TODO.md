# Yodo - Todo

- write more tests
  - test repository method `getAll` with no limits
  - test more merge rules combinations
  - test the use of custom repositoriesNamespace and transformersNamespace
  - test the use of custom http code for exceptions
  - test the use of YodoServiceProvider and the correct publication of `config/yodo.php` (how?)
- handle failing during update/create/delete that don't cause exceptions?
- improve docs (more examples, public APIS, create a static site?)
- handle sorting by a field of a relation? (see `OrderRepository` in inspiration project)
- think about a syntax that will translate a query parameter to a Eloquent's query scope
- use Laravel 5.5 built-in trasformers?
- validate requests via Laravel Form Request Validation instead of define rules in the repository?