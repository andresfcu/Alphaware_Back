<?php
namespace App\Http\Requests\Documents;
use Illuminate\Foundation\Http\FormRequest;

class StoreDocumentRequest extends FormRequest {
    public function rules(): array {
        return [
            'title' => 'required|string|max:255',
            'file'  => 'required|file|max:20480',
            'folder_id' => 'nullable|integer'
        ];
    }
}
