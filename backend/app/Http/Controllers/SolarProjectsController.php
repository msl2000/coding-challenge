<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

use App\Http\Resources\SolarProjectResource;
use App\Http\Resources\SolarProjectCollection;
use App\Models\SolarProject;
use Illuminate\Support\Facades\DB;

class SolarProjectsController extends Controller
{
    
    public function index()
    {
        return new SolarProjectCollection(SolarProject::paginate());
    }

    public function store(Request $request)
    {
        $data = $this->validate($request, [
            'system_size' => 'numeric|present|nullable',
            'title' => 'string|required',
            'site_latitude' => 'numeric|required',
            'site_longitude' => 'numeric|required',
        ]);

        $solarProject = SolarProject::create($data);

        return new SolarProjectResource($solarProject);
    }

    public function show(SolarProject $solar_project)
    {
        return new SolarProjectResource($solar_project);
    }

    public function update(Request $request, SolarProject $solar_project)
    {
        if ($request->isMethod('patch')) {
            $data = $this->validate($request, [
                'system_size' => 'numeric|nullable',
                'title' => 'string',
                'site_latitude' => 'numeric',
                'site_longitude' => 'numeric',
            ]);
        } else {
            $data = $this->validate($request, [
                'system_size' => 'numeric|present|nullable',
                'title' => 'string|required',
                'site_latitude' => 'numeric|required',
                'site_longitude' => 'numeric|required',
            ]);
        }

        $solar_project->update($data);

        return new SolarProjectResource($solar_project);
    }

    public function destroy(SolarProject $solar_project)
    {
        $solar_project->delete();

        return response()->noContent();
    }

    /**
     * Function can be tested through Tests\Feature\SolarProjectsTest
     * @DELETE /solar_projects
     * @Params [ids: Array|String]
     * 
     * @return NULL
     */
    public function destroyMany(Request $request) {
        /**
         * Checks whether the value of ids in the request is null
         * ids can be set as an array or a single value
         * 
         */
        if($request->input('ids'))
            /**
             * Created a database transaction to allow DB rollback on error.
             * Just added the is_array statements in case people tried adding an individual id 
             * to the request as a param
             * 
             * I chose this approach for it's simplicity and fallback measure. Using the DB::transaction function allows queries
             * to rollback to their initial state on error on any query. This prevents any data disjoint, integrity 
             * or orphan issues, should it be relevant in the future.
             * 
             * Using Laravel's query builder was a perfectly appropriate usecase for this scenario as the slight overhead
             * Laravel takes to generate this query is barely noticeable due to the simplicity of the query. No heavy joins
             * an assumably indexing is required.
             */
            DB::Transaction(function() use ($request) {
                //If statement in the event a user decides to put a single value as the request parameter for ids.
                if(is_array( $request->input('ids') ))
                    SolarProject::whereIn('uuid', $request->input('ids'))->delete();
                else
                    SolarProject::where('uuid', $request->input('ids'))->delete();
                
            });
        else
            throw new \Exception('Arguement ids must not be null');
    }
}
