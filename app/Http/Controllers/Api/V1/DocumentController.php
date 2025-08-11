<?php
namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Documents\StoreDocumentRequest;
use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class DocumentController extends Controller {
    public function index(Request $request) {
        $tenantId = $request->attributes->get('tenant_id');
        $q = Document::where('tenant_id', $tenantId)
            ->when($request->folder_id, fn($x) => $x->where('folder_id', $request->folder_id))
            ->orderByDesc('id');
        return $q->paginate(20);
    }

    public function store(StoreDocumentRequest $request) {
        $tenantId = $request->attributes->get('tenant_id');
        $file = $request->file('file');
        $path = $file->store('documents/'.$tenantId, 'public');
        $doc = Document::create([
            'tenant_id' => $tenantId,
            'folder_id' => $request->folder_id,
            'title' => $request->title,
            'path' => $path,
            'mime' => $file->getClientMimeType(),
            'size' => $file->getSize(),
            'checksum' => hash_file('sha256', $file->getRealPath()),
            'created_by' => $request->user()->id,
        ]);
        return response()->json($doc, 201);
    }

    public function show(Request $request, int $id) {
        $tenantId = $request->attributes->get('tenant_id');
        $doc = Document::where('tenant_id', $tenantId)->findOrFail($id);
        return $doc;
    }

    public function destroy(Request $request, int $id) {
        $tenantId = $request->attributes->get('tenant_id');
        $doc = Document::where('tenant_id', $tenantId)->findOrFail($id);
        \Illuminate\Support\Facades\Storage::disk('public')->delete($doc->path);
        $doc->delete();
        return response()->json(['message' => 'deleted']);
    }
}
