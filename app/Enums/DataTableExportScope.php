<?php

namespace App\Enums;

enum DataTableExportScope: string
{
    case FilteredAll = 'filtered_all';
    case CurrentPage = 'current_page';
    case PageRange = 'page_range';
    case DateRange = 'date_range';
    case RowLimit = 'row_limit';
    case AllRecords = 'all_records';
}
