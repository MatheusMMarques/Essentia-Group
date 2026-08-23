<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class StoreCustomerRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $email = $this->input('email');
        $phone = $this->input('phone');

        $this->merge([
            'email' => is_string($email) ? strtolower(trim($email)) : $email,
            'phone' => is_string($phone) ? preg_replace('/\D+/', '', $phone) : $phone,
        ]);
    }

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:customers,email'],
            'phone' => ['required', 'string', 'regex:/^\d{10,15}$/'],
            'photo' => ['required', 'image', 'mimes:jpg,jpeg,png,webp', 'max:2048'],
        ];
    }

    public function messages(): array
    {
        return [
            'name.required' => 'Informe o nome do cliente.',
            'name.string' => 'O nome deve ser um texto válido.',
            'name.max' => 'O nome não pode ter mais que 100 caracteres.',
            'email.required' => 'Informe o e-mail do cliente.',
            'email.email' => 'Informe um e-mail válido.',
            'email.max' => 'O e-mail não pode ter mais que 255 caracteres.',
            'email.unique' => 'Já existe um cliente cadastrado com este e-mail.',
            'phone.required' => 'Informe o telefone do cliente.',
            'phone.string' => 'O telefone deve ser um texto válido.',
            'phone.regex' => 'O telefone deve conter entre 10 e 15 dígitos.',
            'photo.required' => 'A foto é obrigatória.',
            'photo.image' => 'O arquivo selecionado deve ser uma imagem.',
            'photo.mimes' => 'A foto deve estar nos formatos JPG, PNG ou WebP.',
            'photo.mimetypes' => 'A foto deve estar nos formatos JPG, PNG ou WebP.',
            'photo.max' => 'A foto deve ter no máximo 2 MB.',
            'photo.uploaded' => 'Não foi possível enviar a foto. Verifique se o arquivo possui no máximo 2 MB e tente novamente.',
        ];
    }

    public function attributes(): array
    {
        return [
            'name' => 'nome',
            'email' => 'e-mail',
            'phone' => 'telefone',
            'photo' => 'foto',
        ];
    }
}
