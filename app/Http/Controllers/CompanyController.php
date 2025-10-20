<?php

namespace App\Http\Controllers;

use App\Models\Company;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class CompanyController extends Controller
{
    /**
     * Display a listing of all companies with their compounds and images.
     *
     * @return JsonResponse
     */
    public function index(): JsonResponse
    {
        try {
            $companies = Company::with(['compounds' => function($query) {
                $query->select('id', 'company_id', 'project', 'project_en', 'project_ar', 'location', 'location_en', 'location_ar', 'images', 'status', 'completion_progress')
                      ->orderBy('project', 'asc');
            }])
            ->orderBy('name', 'asc')
            ->get();

            // Process each company
            $processedCompanies = $companies->map(function($company) {
                // Process compound images
                $compounds = $company->compounds->map(function($compound) {
                    return [
                        'id' => $compound->id,
                        'name' => $compound->project,
                        'project' => $compound->project,
                        'project_en' => $compound->project_en,
                        'project_ar' => $compound->project_ar,
                        'location' => $compound->location,
                        'location_en' => $compound->location_en,
                        'location_ar' => $compound->location_ar,
                        'status' => $compound->status,
                        'completion_progress' => $compound->completion_progress,
                        'images' => $compound->images_urls,
                    ];
                });

                return [
                    'id' => $company->id,
                    'name' => $company->name,
                    'name_en' => $company->name_en,
                    'name_ar' => $company->name_ar,
                    'logo' => $company->logo_url,
                    'email' => $company->email,
                    'number_of_compounds' => $company->number_of_compounds,
                    'number_of_available_units' => $company->number_of_available_units,
                    'compounds' => $compounds,
                ];
            });

            return response()->json([
                'success' => true,
                'count' => $processedCompanies->count(),
                'data' => $processedCompanies
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Display the specified company with compounds and users.
     *
     * @param int $id
     * @return JsonResponse
     */
    public function show(int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (!$company) {
                return response()->json([
                    'success' => false,
                    'error' => 'Company not found'
                ], 404);
            }

            // Get company's compounds with unit counts
            $compounds = $company->compounds()
                ->withCount([
                    'units as total_units',
                    'units as sold_units' => function($query) {
                        $query->where('is_sold', 1);
                    },
                    'units as available_units' => function($query) {
                        $query->where('is_sold', 0);
                    }
                ])
                ->orderBy('project', 'asc')
                ->get();

            // Process images for each compound
            $processedCompounds = $compounds->map(function($compound) {
                return [
                    'id' => $compound->id,
                    'name' => $compound->project,
                    'project' => $compound->project,
                    'project_en' => $compound->project_en,
                    'project_ar' => $compound->project_ar,
                    'location' => $compound->location,
                    'location_en' => $compound->location_en,
                    'location_ar' => $compound->location_ar,
                    'status' => $compound->status,
                    'completion_progress' => $compound->completion_progress,
                    'total_units' => $compound->total_units,
                    'sold_units' => $compound->sold_units,
                    'available_units' => $compound->available_units,
                    'images' => $compound->images_urls,
                ];
            });

            // Get company's users (sales agents)
            $users = $company->users()
                ->select(['id', 'name', 'email', 'role', 'phone'])
                ->orderBy('name', 'asc')
                ->get();

            $result = [
                'id' => $company->id,
                'name' => $company->name,
                'name_en' => $company->name_en,
                'name_ar' => $company->name_ar,
                'logo' => $company->logo_url,
                'email' => $company->email,
                'number_of_compounds' => $company->number_of_compounds,
                'number_of_available_units' => $company->number_of_available_units,
                'compounds' => $processedCompounds,
                'users' => $users,
            ];

            return response()->json([
                'success' => true,
                'data' => $result
            ], 200);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }
}
