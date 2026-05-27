<?php

namespace App\View\Composers;

use App\Services\Settings\OrganizationSettingsService;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;

class OrganizationBrandingComposer
{
    public function __construct(
        private OrganizationSettingsService $organizationSettings
    ) {
    }

    public function compose(View $view): void
    {
        if (! Schema::hasTable('organization_settings')) {
            $view->with('organizationBranding', null);

            return;
        }

        $view->with('organizationBranding', $this->organizationSettings->get());
    }
}
