<?php

namespace App\Http\Controllers;

use App\Models\Unit;
use App\Models\Compound;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;

class ShareLinkController extends Controller
{
    /**
     * Get shareable link data for a unit or compound
     *
     * @param Request $request
     * @return JsonResponse
     */
    public function getShareData(Request $request): JsonResponse
    {
        try {
            $type = $request->get('type');
            $id = $request->get('id');

            if (!$type || !$id) {
                return response()->json([
                    'success' => false,
                    'error' => 'Type and ID are required',
                    'message' => 'Please provide type (unit or compound) and id parameters'
                ], 400);
            }

            if (!in_array($type, ['unit', 'compound'])) {
                return response()->json([
                    'success' => false,
                    'error' => 'Invalid type',
                    'message' => 'Type must be either "unit" or "compound"'
                ], 400);
            }

            // Use environment APP_URL or request URL for share links
            $appUrl = config('app.url');

            // If APP_URL is not set or is localhost, use the current request URL
            if (!$appUrl || $appUrl === 'http://localhost') {
                $appUrl = $request->getSchemeAndHttpHost();
            }

            $baseUrl = $appUrl . "/storage";
            $shareBaseUrl = $appUrl;

            if ($type === 'unit') {
                return $this->getUnitShareData($id, $baseUrl, $shareBaseUrl);
            } else {
                return $this->getCompoundShareData($id, $baseUrl, $shareBaseUrl);
            }

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'error' => 'Database error',
                'message' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * Get share data for a unit
     *
     * @param int $id
     * @param string $baseUrl
     * @param string $shareBaseUrl
     * @return JsonResponse
     */
    private function getUnitShareData(int $id, string $baseUrl, string $shareBaseUrl): JsonResponse
    {
        $unit = Unit::with(['compound.company'])->find($id);

        if (!$unit) {
            return response()->json([
                'success' => false,
                'error' => 'Unit not found'
            ], 404);
        }

        // Process images - use the model accessors
        $images = $unit->images_urls;
        $compoundImages = $unit->compound->images_urls ?? [];

        // Calculate total area
        $totalArea = ($unit->garden_area ?? 0) + ($unit->roof_area ?? 0) +
                    ($unit->basement_area ?? 0) + ($unit->garage_area ?? 0);

        // Generate share URL
        $shareUrl = $shareBaseUrl . "/share/unit/" . $id;

        // Generate shareable title and description
        $title = $unit->unit_name ?? "Unit {$unit->unit_code}";
        $bedsText = $unit->number_of_beds ? "{$unit->number_of_beds} beds" : "";
        $description = "{$unit->unit_type} in {$unit->compound->name}" .
                      ($bedsText ? " - {$bedsText}" : "");

        // Get first image for preview
        $previewImage = !empty($images) ? $images[0] : ($unit->compound->company->logo_url ?? null);

        return response()->json([
            'success' => true,
            'type' => 'unit',
            'data' => [
                'id' => $unit->id,
                'unit_name' => $unit->unit_name,
                'unit_code' => $unit->unit_code,
                'unit_type' => $unit->unit_type,
                'number_of_beds' => $unit->number_of_beds,
                'base_price' => $unit->base_price,
                'total_price' => $unit->total_price,
                'total_area' => $totalArea,
                'images' => $images,
                'available' => !$unit->is_sold,
                'is_sold' => $unit->is_sold,
                'status' => $unit->status,
                'compound' => [
                    'id' => $unit->compound->id,
                    'name' => $unit->compound->name ?? $unit->compound->project,
                    'location' => $unit->compound->location,
                    'images' => $compoundImages
                ],
                'company' => [
                    'id' => $unit->compound->company->id ?? null,
                    'name' => $unit->compound->company->name ?? null,
                    'logo' => $unit->compound->company->logo_url ?? null,
                    'email' => $unit->compound->company->email ?? null
                ]
            ],
            'share' => [
                'url' => $shareUrl,
                'title' => $title,
                'description' => $description,
                'image' => $previewImage,
                'whatsapp_url' => "https://wa.me/?text=" . urlencode("Check out this unit: {$title}\n{$description}\n{$shareUrl}"),
                'facebook_url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($shareUrl),
                'twitter_url' => "https://twitter.com/intent/tweet?text=" . urlencode($title) . "&url=" . urlencode($shareUrl),
                'email_url' => "mailto:?subject=" . urlencode($title) . "&body=" . urlencode("{$description}\n\n{$shareUrl}")
            ]
        ], 200);
    }

    /**
     * Get share data for a compound
     *
     * @param int $id
     * @param string $baseUrl
     * @param string $shareBaseUrl
     * @return JsonResponse
     */
    private function getCompoundShareData(int $id, string $baseUrl, string $shareBaseUrl): JsonResponse
    {
        $compound = Compound::with(['company', 'units'])
            ->withCount([
                'units as total_units',
                'units as available_units' => function($q) {
                    $q->where('is_sold', 0)->where('status', 'available');
                }
            ])
            ->find($id);

        if (!$compound) {
            return response()->json([
                'success' => false,
                'error' => 'Compound not found'
            ], 404);
        }

        // Process images - use the model accessor
        $images = $compound->images_urls;

        // Get price range
        $minPrice = $compound->units()->min('base_price');
        $maxPrice = $compound->units()->max('base_price');

        // Generate share URL
        $shareUrl = $shareBaseUrl . "/share/compound/" . $id;

        // Generate shareable title and description
        $title = $compound->name ?? $compound->project;
        $priceRange = ($minPrice && $maxPrice) ? " - Prices from EGP " . number_format($minPrice) : "";
        $description = "{$title} in {$compound->location}{$priceRange} - {$compound->available_units} units available";

        // Get first image for preview
        $previewImage = !empty($images) ? $images[0] : ($compound->company->logo_url ?? null);

        return response()->json([
            'success' => true,
            'type' => 'compound',
            'data' => [
                'id' => $compound->id,
                'name' => $compound->name ?? $compound->project,
                'location' => $compound->location,
                'images' => $images,
                'total_units' => $compound->total_units,
                'available_units' => $compound->available_units,
                'min_price' => $minPrice,
                'max_price' => $maxPrice,
                'company' => [
                    'id' => $compound->company->id ?? null,
                    'name' => $compound->company->name ?? null,
                    'logo' => $compound->company->logo_url ?? null,
                    'email' => $compound->company->email ?? null
                ]
            ],
            'share' => [
                'url' => $shareUrl,
                'title' => $title,
                'description' => $description,
                'image' => $previewImage,
                'whatsapp_url' => "https://wa.me/?text=" . urlencode("Check out this compound: {$title}\n{$description}\n{$shareUrl}"),
                'facebook_url' => "https://www.facebook.com/sharer/sharer.php?u=" . urlencode($shareUrl),
                'twitter_url' => "https://twitter.com/intent/tweet?text=" . urlencode($title) . "&url=" . urlencode($shareUrl),
                'email_url' => "mailto:?subject=" . urlencode($title) . "&body=" . urlencode("{$description}\n\n{$shareUrl}")
            ]
        ], 200);
    }

    /**
     * Process image URLs
     *
     * @param string|null $imagesJson
     * @param string $baseUrl
     * @return array
     */
    private function processImages(?string $imagesJson, string $baseUrl): array
    {
        if (!$imagesJson) {
            return [];
        }

        $images = [];
        $imageArray = json_decode($imagesJson, true);

        if (is_array($imageArray)) {
            foreach ($imageArray as $img) {
                if (strpos($img, 'http://') === 0 || strpos($img, 'https://') === 0) {
                    $images[] = $img;
                } else {
                    $images[] = $baseUrl . '/' . $img;
                }
            }
        }

        return $images;
    }
}
