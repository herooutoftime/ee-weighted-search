# Weighted Search - Gewichtete Suche

*Weighted Search* is an extension for Expression Engine which allows to sort search results based on their column occurrence.
E.g.: If the search value occurs in the resource's title it will have more weight as the occurrence in the resource's content. All single weights are added up to the resource's total weight and the search results are sorted by total weight.

## Requirements
For `Weighted Search` to work you will need at least EE 2.8. Versions below are missing needed hooks.

## Installation
* Clone / copy repository into `system/expressionengine/third_party`
* Use modman? This [way](https://github.com/colinmollenhour/modman).

## Backend (BE)
In EE-administration `Addons -> Extensions -> Weighted Search -> Settings` are multiple fields available to set their weight.

## Future

* Add custom fields in CP
