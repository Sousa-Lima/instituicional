<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreServiceRequest;
use App\Http\Requests\UpdateServiceRequest;
use App\Http\Resources\ServiceResource;
use App\Models\Service;
use App\Support\SlugGenerator;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ServiceManageController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $status = $request->query('status', 'all');

        $query = Service::query()
            ->orderBy('order')
            ->orderBy('title');

        if ($status === 'published') {
            $query->published();
        }

        if ($status === 'draft') {
            $query->where('status', 'draft');
        }

        return ServiceResource::collection($query->get());
    }

    public function show(string $id): ServiceResource
    {
        $service = Service::query()->findOrFail($id);

        return new ServiceResource($service);
    }

    public function store(StoreServiceRequest $request): JsonResponse
    {
        $data = $request->validated();
        $data['slug'] = SlugGenerator::uniqueForModel(Service::class, $data['slug'] ?? $data['title']);

        $service = Service::query()->create($data);

        return response()->json((new ServiceResource($service))->toArray($request), JsonResponse::HTTP_CREATED);
    }

    public function update(UpdateServiceRequest $request, string $id): ServiceResource
    {
        $service = Service::query()->findOrFail($id);
        $data = $request->validated();

        if (array_key_exists('slug', $data) || array_key_exists('title', $data)) {
            $data['slug'] = SlugGenerator::uniqueForModel(
                Service::class,
                $data['slug'] ?? $service->slug,
                (string) $service->getKey()
            );
        }

        $service->fill($data);
        $service->save();

        return new ServiceResource($service->fresh());
    }

    public function destroy(string $id): JsonResponse
    {
        $service = Service::query()->findOrFail($id);
        $service->delete();

        return response()->json([], JsonResponse::HTTP_NO_CONTENT);
    }
}
