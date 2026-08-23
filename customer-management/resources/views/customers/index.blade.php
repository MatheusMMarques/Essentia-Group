@extends('layouts.app')

@section('title', 'Clientes')

@section('content')
    <div class="flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between">
        <div>
            <h1 class="text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Clientes</h1>
            <p class="mt-2 text-sm text-slate-600">Consulte e gerencie os clientes cadastrados.</p>
        </div>
        <a href="{{ route('customers.create') }}" class="inline-flex items-center justify-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">
            <span class="text-lg leading-none" aria-hidden="true">+</span>
            Cadastrar cliente
        </a>
    </div>

    <div class="mt-8 flex items-center justify-between border-b border-slate-200 pb-3">
        <p class="text-sm font-medium text-slate-700">
            {{ $customers->total() }} {{ $customers->total() === 1 ? 'cliente cadastrado' : 'clientes cadastrados' }}
        </p>
        <p class="text-xs text-slate-500">Mais recentes primeiro</p>
    </div>

    @if ($customers->isEmpty())
        <div class="mt-8 rounded-2xl border border-dashed border-slate-300 bg-white px-6 py-16 text-center">
            <div class="mx-auto flex size-12 items-center justify-center rounded-full bg-indigo-50 text-2xl text-indigo-600" aria-hidden="true">+</div>
            <h2 class="mt-4 text-base font-semibold text-slate-900">Nenhum cliente cadastrado</h2>
            <p class="mx-auto mt-2 max-w-sm text-sm text-slate-500">Cadastre o primeiro cliente para começar a gerenciar seus dados.</p>
            <a href="{{ route('customers.create') }}" class="mt-6 inline-flex rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-700">Cadastrar primeiro cliente</a>
        </div>
    @else
        <div class="mt-6 grid gap-4 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($customers as $customer)
                <article class="flex min-w-0 flex-col rounded-2xl border border-slate-200 bg-white p-5 shadow-sm transition hover:border-slate-300 hover:shadow-md">
                    <div class="flex min-w-0 items-start gap-4">
                        <div class="relative size-14 shrink-0 overflow-hidden rounded-xl bg-indigo-50">
                            <span class="absolute inset-0 flex items-center justify-center font-semibold text-indigo-700" data-fallback>{{ mb_strtoupper(mb_substr($customer->name, 0, 1)) }}</span>
                            <img
                                src="{{ asset('storage/'.$customer->photo_path) }}"
                                alt="Foto de {{ $customer->name }}"
                                class="relative size-full object-cover"
                                loading="lazy"
                                data-image-fallback
                            >
                        </div>
                        <div class="min-w-0">
                            <h2 class="truncate font-semibold text-slate-900">{{ $customer->name }}</h2>
                            <p class="mt-1 truncate text-sm text-slate-500">{{ $customer->email }}</p>
                            <p class="mt-1 text-sm text-slate-600" data-phone-display>{{ $customer->phone }}</p>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center gap-2 border-t border-slate-100 pt-4">
                        <a href="{{ route('customers.edit', $customer) }}" class="inline-flex flex-1 justify-center rounded-lg border border-slate-300 px-3 py-2 text-sm font-semibold text-slate-700 hover:bg-slate-50">Editar</a>
                        <form action="{{ route('customers.destroy', $customer) }}" method="POST" class="flex-1" data-delete-form>
                            @csrf
                            @method('DELETE')
                            <button
                                type="button"
                                class="w-full rounded-lg border border-red-200 px-3 py-2 text-sm font-semibold text-red-700 hover:bg-red-50"
                                data-delete-trigger
                                data-customer-name="{{ $customer->name }}"
                            >
                                Excluir
                            </button>
                        </form>
                    </div>
                </article>
            @endforeach
        </div>

        @if ($customers->hasPages())
            <div class="mt-8">
                {{ $customers->links() }}
            </div>
        @endif
    @endif

    <dialog
        class="m-auto w-[calc(100%-2rem)] max-w-md rounded-2xl bg-white p-0 text-slate-900 shadow-2xl backdrop:bg-slate-950/50 backdrop:backdrop-blur-sm"
        data-delete-modal
        aria-labelledby="delete-modal-title"
        aria-describedby="delete-modal-description"
    >
        <div class="p-6 sm:p-7" data-delete-modal-content>
            <div class="flex size-11 items-center justify-center rounded-full bg-red-100 text-red-600" aria-hidden="true">
                <svg class="size-6" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 9v4m0 4h.01M10.3 3.8 2.2 18a2 2 0 0 0 1.7 3h16.2a2 2 0 0 0 1.7-3L13.7 3.8a2 2 0 0 0-3.4 0Z" />
                </svg>
            </div>

            <h2 id="delete-modal-title" class="mt-5 text-lg font-semibold text-slate-900">Excluir cliente?</h2>
            <p id="delete-modal-description" class="mt-2 text-sm leading-6 text-slate-600">
                Você está prestes a excluir <strong class="font-semibold text-slate-900" data-delete-customer-name></strong>.
                Esta ação é permanente e não pode ser desfeita.
            </p>

            <div class="mt-7 flex flex-col-reverse gap-3 sm:flex-row sm:justify-end">
                <button type="button" class="rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50 focus:outline-none focus:ring-4 focus:ring-slate-200" data-delete-cancel>
                    Cancelar
                </button>
                <button type="button" class="rounded-xl bg-red-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-red-700 focus:outline-none focus:ring-4 focus:ring-red-200" data-delete-confirm>
                    Excluir cliente
                </button>
            </div>
        </div>
    </dialog>
@endsection
