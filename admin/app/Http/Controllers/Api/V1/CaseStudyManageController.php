<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreCaseStudyRequest;
use App\Http\Requests\UpdateCaseStudyRequest;
use App\Http\Resources\CaseStudyResource;
use App\Models\CaseStudy;
use App\Support\SlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class CaseStudyManageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->query('status', 'all');

        $query = CaseStudy::query()
            ->orderByDesc('featured')
            ->orderByDesc('updated_at');

        if ($status === 'published') {
            $query->published();
        }

        if ($status === 'draft') {
            $query->where('status', 'draft');
        }

        return CaseStudyResource::collection($query->get());
    }

    public function show(string $id): CaseStudyResource
    {
        $case = CaseStudy::query()->findOrFail($id);

        return new CaseStudyResource($case);
    }

    public function store(StoreCaseStudyRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = SlugGenerator::uniqueForModel(CaseStudy::class, $data['slug'] ?? $data['title']);

        $caseStudy = CaseStudy::query()->create($data);

        return response()->json((new CaseStudyResource($caseStudy))->toArray($request), JsonResponse::HTTP_CREATED);
    }

    public function update(UpdateCaseStudyRequest $request, string $id): CaseStudyResource
    {
        $caseStudy = CaseStudy::query()->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = SlugGenerator::uniqueForModel(
                CaseStudy::class,
                $data['slug'] ?? $caseStudy->slug,
                (string) $caseStudy->getKey()
            );
        }

        $caseStudy->fill($data);
        $caseStudy->save();

        return new CaseStudyResource($caseStudy->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $caseStudy = CaseStudy::query()->findOrFail($id);
        $caseStudy->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
