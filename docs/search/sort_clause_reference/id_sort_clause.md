# Id Sort Clause

The [`Location\Id` Sort Clause](https://github.com/ibexa/core/blob/main/src/contracts/Repository/Values/Content/Query/SortClause/Location/Id.php)
sorts search results by the ID of the Location.

## Arguments

- `sortDirection` (optional) - Query or LocationQuery constant, either `Query::SORT_ASC` or `Query::SORT_DESC`.

## Example

``` php
$query = new LocationQuery();
$query->sortClauses = [new SortClause\Location\Id()];
```
