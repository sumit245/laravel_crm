<div class="row my-2">
  @if ($project->project_type == 1)
    {{-- Streetlight Installation Specific Display --}}
    @include("projects.project_task_streetlight")
  @else
    {{-- Existing Rooftop Installation Code --}}
    @include("projects.project_task_rooftop")
  @endif
</div>
