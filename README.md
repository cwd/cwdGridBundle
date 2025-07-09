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
