#!/bin/bash
for file in app/Http/Controllers/API/SiteController.php app/Http/Controllers/API/StaffController.php app/Http/Controllers/API/InventoryController.php; do
    # Add use statement if missing
    if ! grep -q "use App\\\Services\\\Logging\\\ActivityLogger;" "$file"; then
        sed -i '' '/namespace App\\Http\\Controllers\\API;/a\
use App\\Services\\Logging\\ActivityLogger;
' "$file"
    fi
    # Wait, simple sed might break. Let's use PHP script.
done
