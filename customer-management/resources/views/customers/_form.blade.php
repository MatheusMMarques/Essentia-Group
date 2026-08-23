@php
    $isEditing = isset($customer);
    $inputClass = 'mt-2 block w-full rounded-xl border bg-white px-3.5 py-2.5 text-sm text-slate-900 shadow-sm outline-none transition placeholder:text-slate-400 focus:ring-4';
    $validClass = 'border-slate-300 focus:border-indigo-500 focus:ring-indigo-100';
    $invalidClass = 'border-red-400 focus:border-red-500 focus:ring-red-100';
@endphp

<div class="grid gap-6 sm:grid-cols-2">
    <div class="sm:col-span-2">
        <label for="name" class="text-sm font-medium text-slate-700">Nome completo</label>
        <input
            type="text"
            id="name"
            name="name"
            value="{{ old('name', $customer->name ?? '') }}"
            maxlength="100"
            autocomplete="name"
            required
            autofocus
            class="{{ $inputClass }} {{ $errors->has('name') ? $invalidClass : $validClass }}"
            aria-invalid="{{ $errors->has('name') ? 'true' : 'false' }}"
            @error('name') aria-describedby="name-error" @enderror
        >
        @error('name')
            <p id="name-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="email" class="text-sm font-medium text-slate-700">E-mail</label>
        <input
            type="email"
            id="email"
            name="email"
            value="{{ old('email', $customer->email ?? '') }}"
            maxlength="255"
            autocomplete="email"
            required
            class="{{ $inputClass }} {{ $errors->has('email') ? $invalidClass : $validClass }}"
            aria-invalid="{{ $errors->has('email') ? 'true' : 'false' }}"
            @error('email') aria-describedby="email-error" @enderror
        >
        @error('email')
            <p id="email-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div>
        <label for="phone" class="text-sm font-medium text-slate-700">Telefone</label>
        <input
            type="tel"
            id="phone"
            name="phone"
            value="{{ old('phone', $customer->phone ?? '') }}"
            maxlength="20"
            autocomplete="tel"
            inputmode="tel"
            required
            data-phone-input
            placeholder="(48) 99999-9999"
            class="{{ $inputClass }} {{ $errors->has('phone') ? $invalidClass : $validClass }}"
            aria-invalid="{{ $errors->has('phone') ? 'true' : 'false' }}"
            @error('phone') aria-describedby="phone-error" @enderror
        >
        @error('phone')
            <p id="phone-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>

    <div class="sm:col-span-2">
        <label for="photo" class="text-sm font-medium text-slate-700">
            {{ $isEditing ? 'Nova foto' : 'Foto' }}
        </label>
        <div class="mt-2 flex flex-col gap-4 rounded-xl border border-dashed border-slate-300 bg-slate-50 p-4 sm:flex-row sm:items-center">
            <div class="relative size-20 shrink-0 overflow-hidden rounded-xl bg-slate-200">
                @if ($isEditing)
                    <img
                        src="{{ asset('storage/'.$customer->photo_path) }}"
                        alt="Foto atual de {{ $customer->name }}"
                        class="relative z-10 size-full object-cover"
                        data-photo-preview
                        data-image-fallback
                    >
                @else
                    <img src="" alt="Pré-visualização da foto" class="relative z-10 hidden size-full object-cover" data-photo-preview>
                @endif
                <span class="absolute inset-0 flex items-center justify-center text-xs font-medium text-slate-500" data-fallback>Sem foto</span>
            </div>
            <div class="min-w-0 flex-1">
                <input
                    type="file"
                    id="photo"
                    name="photo"
                    accept="image/jpeg,image/png,image/webp"
                    {{ $isEditing ? '' : 'required' }}
                    data-photo-input
                    class="block w-full cursor-pointer text-sm text-slate-600 file:mr-4 file:rounded-lg file:border-0 file:bg-indigo-50 file:px-3 file:py-2 file:font-medium file:text-indigo-700 hover:file:bg-indigo-100"
                    aria-invalid="{{ $errors->has('photo') ? 'true' : 'false' }}"
                    @error('photo') aria-describedby="photo-error" @enderror
                >
                <p class="mt-2 text-xs text-slate-500">
                    JPG, PNG ou WebP, com no máximo 2 MB.{{ $isEditing ? ' Deixe vazio para manter a foto atual.' : '' }}
                </p>
            </div>
        </div>
        @error('photo')
            <p id="photo-error" class="mt-1.5 text-sm text-red-600">{{ $message }}</p>
        @enderror
    </div>
</div>
