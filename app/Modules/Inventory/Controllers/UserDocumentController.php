<?php

declare(strict_types=1);

namespace App\Modules\Inventory\Controllers;

use App\Modules\Core\Http\Controllers\Controller;
use App\Modules\Core\Concerns\EscapesSearchTerm;

use App\Modules\Inventory\Requests\UserDocument\StoreUserDocumentRequest;
use App\Modules\Inventory\Resources\UserDocumentResource;
use App\Modules\Inventory\Models\UserDocument;
use App\Modules\Inventory\Services\UserDocumentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;

class UserDocumentController extends Controller
{
    use EscapesSearchTerm;

    public function __construct(
        private readonly UserDocumentService $userDocumentService,
    ) {}
    /**
     * GET /api/user-documents
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->authorize('viewAny', UserDocument::class);

        $query = UserDocument::with('user')->latest();

        // Only show own documents for non-admin/non-manager
        $user = auth('api')->user();
        if (! $user->hasAnyPermission(['users.view', 'user_documents.view'])) {
            $query->where('user_id', $user->id);
        } elseif ($request->filled('user_id')) {
            $query->where('user_id', $request->integer('user_id'));
        }

        if ($request->filled('file_type')) {
            $query->where('file_type', $request->input('file_type'));
        }
        if ($request->filled('search')) {
            $term = $this->escapeLike($request->input('search'));
            $query->where('title', 'like', "%{$term}%");
        }

        return UserDocumentResource::collection($query->paginate(15));
    }

    /**
     * POST /api/user-documents
     */
    public function store(StoreUserDocumentRequest $request): JsonResponse
    {
        $this->authorize('create', UserDocument::class);

        $document = $this->userDocumentService->create(
            file: $request->file('file'),
            data: $request->validated(),
            userId: auth('api')->id(),
        );

        return (new UserDocumentResource($document))
            ->response()
            ->setStatusCode(201);
    }

    /**
     * GET /api/user-documents/{userDocument}
     */
    public function show(UserDocument $userDocument): UserDocumentResource
    {
        $this->authorize('view', $userDocument);

        $userDocument->load('user');

        return new UserDocumentResource($userDocument);
    }

    /**
     * GET /api/user-documents/{userDocument}/download
     */
    public function download(UserDocument $userDocument): \Symfony\Component\HttpFoundation\StreamedResponse
    {
        $this->authorize('view', $userDocument);

        abort_unless(Storage::disk('private')->exists($userDocument->file_path), 404, 'File not found.');

        return Storage::disk('private')->download(
            $userDocument->file_path,
            $userDocument->title . '.' . pathinfo($userDocument->file_path, PATHINFO_EXTENSION),
        );
    }

    /**
     * DELETE /api/user-documents/{userDocument}
     */
    public function destroy(UserDocument $userDocument): JsonResponse
    {
        $this->authorize('delete', $userDocument);

        $this->userDocumentService->delete($userDocument);
        return response()->json(['message' => 'Document deleted successfully.']);
    }

}