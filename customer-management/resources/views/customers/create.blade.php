@extends('layouts.app')

@section('title', 'Cadastrar cliente')

@section('content')
    <div class="mx-auto max-w-3xl">
        <div class="mb-6">
            <a href="{{ route('customers.index') }}" class="text-sm font-medium text-indigo-600 hover:text-indigo-800">← Voltar para clientes</a>
            <h1 class="mt-3 text-2xl font-bold tracking-tight text-slate-900 sm:text-3xl">Cadastrar cliente</h1>
            <p class="mt-2 text-sm text-slate-600">Preencha os dados abaixo para adicionar um cliente.</p>
        </div>

        <form action="{{ route('customers.store') }}" method="POST" enctype="multipart/form-data" class="rounded-2xl border border-slate-200 bg-white p-5 shadow-sm sm:p-8">
            @csrf

            @include('customers._form')

            <div class="mt-8 flex flex-col-reverse gap-3 border-t border-slate-200 pt-6 sm:flex-row sm:justify-end">
                <a href="{{ route('customers.index') }}" class="inline-flex justify-center rounded-xl border border-slate-300 px-4 py-2.5 text-sm font-semibold text-slate-700 hover:bg-slate-50">Cancelar</a>
                <button type="submit" class="inline-flex justify-center rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm hover:bg-indigo-700 focus:outline-none focus:ring-4 focus:ring-indigo-200">Cadastrar cliente</button>
            </div>
        </form>
    </div>
@endsection
