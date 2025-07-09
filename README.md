## Available Column Types:
### EnumType
The column is designed to display and filter data based on a PHP-backed `enum`. It automatically generates a dropdown filter containing all the enum cases, making it easy for users to filter the grid by a specific enum value. `EnumType`
The display values and the filter options are automatically translated using Symfony's translator. The enum's case value is used as the translation key.
#### Options
- `class` (required): The fully qualified class name of the backed enum.
- `allOptionsLabel`: The translation key for the default "all" option in the filter dropdown. Defaults to `'all'`.
- `translatable`: When `true` (default), the enum value will be passed through the translator.

#### Usage Example
Here's how to configure a column in your grid: `EnumType`
``` php
->add(new EnumType('companyAddressState', 'certificate.companyAddressState', [
    'label' => 'State',
    'class' => State::class,
    'allOptionsLabel' => 'all states',
]))
```
This column requires a corresponding PHP enum definition:
``` php
<?php

namespace App\Domain\Country;

enum State: string
{
    case BURGENLAND = 'BURGENLAND';
    case CARINTHIA = 'CARINTHIA';
    case LOWER_AUSTRIA = 'LOWER_AUSTRIA';
    // ... other cases
}
```
And for the translations, you can define them in your translation files (e.g., `messages.en.yaml`):
``` yaml
# Austrian states
"all states": "All states"
BURGENLAND: "Burgenland"
CARINTHIA: "Carinthia"
LOWER_AUSTRIA: "Lower Austria"
# ... other translations
```


## AbstractColumn Options
### sqlSortFields
The `sqlSortFields` option allows you to define custom order for a column using one or more different database fields. This is particularly useful when the displayed column's data is not ideal for direct sorting, or when a multi-level sort is required.
**Important:** This feature is currently only implemented for the Doctrine adapter.
When a user clicks to sort a column that has `sqlSortFields` defined, the grid will order the results by the columns listed in the option, in the sequence they are provided.
#### Usage Example
In this example, the grid displays a `certificate.certificateNumber`. When the user sorts by this column, the underlying query will sort the data first by `certificate.year` and then by `certificate.internalCertificateNumber`.
``` php
->add(new TextType('certificateNumber', 'certificate.certificateNumber', [
    'label' => 'Certificate Number',
    'sqlSortFields' => ['certificate.year', 'certificate.internalCertificateNumber'],
]))
```
