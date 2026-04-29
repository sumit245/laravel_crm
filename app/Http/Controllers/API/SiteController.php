<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Site;
use Illuminate\Http\Request;
use App\Services\Logging\ActivityLogger;

/**
 * Site / Panchayat Management — manages project sites (locations where work happens). For
 * streetlight projects, a "site" represents a panchayat with a ward structure and target pole
 * count. Sites are imported from Excel with district-panchayat-ward hierarchy. Supports pole
 * imports per site, bulk operations, and downloadable import templates.
 *
 * Data Flow:
 *   Excel import → Parse district/panchayat/ward/pole count → Create Streetlight or Site
 *   records → Assign via Tasks → Show: Display poles underneath site → Bulk
 *   delete/import operations
 *
 * @depends-on Site, Streetlight, Pole, StreetlightTask, City, Project, SiteImport, StreetlightImport, SitePoleImport, ActivityLogger
 * @business-domain Site Management
 * @package App\Http\Controllers\API
 */
class SiteController extends Controller
{
    protected ActivityLogger $activityLogger;

    public function __construct(ActivityLogger $activityLogger)
    {
        $this->activityLogger = $activityLogger;
    }
 /**
  * Display a listing of the resource.
  */
 public function index(Request $request)
 {
  $perPage = $request->integer('per_page', config('crm.pagination.per_page', 50));
  return Site::with('project')->paginate($perPage);
 }

 /**
  * Store a newly created resource in storage.
  */
 public function store(Request $request)
 {
  try {
   $validated = $request->validate([
    'project_id' => 'required|exists:projects,id',
    'site_name'  => 'required|string',
    'state'      => 'required|string',
    'district'   => 'required|string',
    'location'   => 'required|string',
   ]);
   $site = Site::create($validated);
   $this->activityLogger->log('site', 'created', $site, [
       'description' => "Created site {$site->site_name}"
   ]);
   return response()->json([
    'message' => 'Site Created Successfully!',
    'data'    => $site,
   ], 201);

  } catch (\Illuminate\Validation\ValidationException $e) {
   return response()->json([
    'errors' => $e->errors(),
   ], 422);
  } catch (\Exception $e) {
   return response()->json([
    'message' => 'Something went wrong',
    'error'   => $e->getMessage()], 500);
  }
 }
 /**
  * Display the specified resource.
  */
 public function show($id)
 {
  return Site::with('project')->findOrFail($id);
 }

 /**
  * Update the specified resource in storage.
  */
 public function update(Request $request, $id)
 {
  $site = Site::findOrFail($id);
  $beforeAfter = $this->activityLogger->diff($site);
  $validated = $request->validate([
   'project_id' => 'sometimes|exists:projects,id',
   'site_name'  => 'sometimes|string|max:255',
   'state'      => 'sometimes|string',
   'district'   => 'sometimes|string',
   'location'   => 'sometimes|string',
  ]);
  $site->update($validated);
  $this->activityLogger->log('site', 'updated', $site, array_merge([
      'description' => "Updated site {$site->site_name}"
  ], $beforeAfter));
  return $site;
 }

 /**
  * Remove the specified resource from storage.
  */
 public function destroy($id)
 {
  $site = Site::findOrFail($id);
  $siteName = $site->site_name;
  $site->delete();
  $this->activityLogger->log('site', 'deleted', null, [
      'description' => "Deleted site {$siteName} (#{$id})"
  ]);
  return response()->json(['message' => 'Site deleted']);
 }
}
