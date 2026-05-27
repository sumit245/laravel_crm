<div class="py-2">
  <p class="text-muted mb-3">
    <strong>{{ number_format($surveyedPolesCount ?? 0) }}</strong>
    surveyed pole{{ ($surveyedPolesCount ?? 0) === 1 ? '' : 's' }} in this project
    @if (!empty($poleDeepLinkParams['vendor']) || !empty($poleDeepLinkParams['ward']))
      <span class="ms-1 small">(filtered by assignment)</span>
    @endif
  </p>

  @include('projects.partials.poles-unified-filters', ['tab' => 'surveyed'])

  <x-datatable
    id="projectSurveyedPoles"
    title=""
    :columns="[
      ['title' => 'Pole Number', 'width' => '10%'],
      ['title' => 'Beneficiary', 'width' => '9%'],
      ['title' => 'Beneficiary Contact', 'width' => '9%'],
      ['title' => 'District', 'width' => '8%', 'orderable' => false, 'searchable' => false, 'columnFilter' => true],
      ['title' => 'Block', 'width' => '8%', 'orderable' => false, 'searchable' => false, 'columnFilter' => true],
      ['title' => 'Panchayat', 'width' => '9%', 'orderable' => false, 'searchable' => false, 'columnFilter' => true],
      ['title' => 'IMEI', 'width' => '8%'],
      ['title' => 'SIM Number', 'width' => '8%'],
      ['title' => 'Battery', 'width' => '8%'],
      ['title' => 'Panel', 'width' => '8%'],
      ['title' => 'Installed', 'width' => '7%'],
      ['title' => 'Bill Raised', 'width' => '7%'],
      ['title' => 'RMS Status', 'width' => '8%'],
    ]"
    :exportEnabled="true"
    :exportRoute="route('projects.surveyedPoles.export', $project)"
    :exportConfig="['dateColumns' => [['key' => 'created_at', 'label' => 'Created date']]]"
    :importEnabled="false"
    :bulkDeleteEnabled="true"
    :bulkDeleteRoute="route('poles.bulkDelete')"
    :serverSide="true"
    :ajaxUrl="$projectSurveyedPolesDataUrl"
    :deferLoading="$surveyedPolesCount ?? 0"
    pageLength="50"
    searchPlaceholder="Search poles..."
    :filters="[]"
  />
</div>
