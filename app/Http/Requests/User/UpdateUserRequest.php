<?php
namespace App\Http\Requests\User;

use Illuminate\Foundation\Http\FormRequest;

class UpdateUserRequest extends FormRequest
{
    public function authorize(): bool { return true; }
    public function rules(): array
    {
        return [
            'name' => 'sometimes|required|string|max:100',
            'email' => 'sometimes|required|email:rfc,dns|unique:users,email,' . $this->route('id'),
            'password' => 'nullable|string|min:8',
        ];
    }
}
