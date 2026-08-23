<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCustomerRequest;
use App\Http\Requests\UpdateCustomerRequest;
use App\Models\Customer;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Throwable;

class CustomerController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): View
    {
        $customers = Customer::query()
            ->latest()
            ->latest('id')
            ->paginate(12);

        return view('customers.index', [
            'customers' => $customers,
        ]);
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View
    {
        return view('customers.create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreCustomerRequest $request): RedirectResponse
    {
        $validated = $request->validated();

        $photoPath = $request->file('photo')->store('customers', 'public');

        try {
            Customer::create([
                'name' => $validated['name'],
                'email' => $validated['email'],
                'phone' => $validated['phone'],
                'photo_path' => $photoPath,
            ]);
        } catch (Throwable $exception) {
            $this->deletePhoto($photoPath);

            throw $exception;
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente cadastrado com sucesso.');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(Customer $customer): View
    {
        return view('customers.edit', [
            'customer' => $customer,
        ]);
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(UpdateCustomerRequest $request, Customer $customer): RedirectResponse
    {
        $validated = $request->validated();
        $oldPhotoPath = $customer->photo_path;
        $newPhotoPath = null;

        if ($request->hasFile('photo')) {
            $newPhotoPath = $request
                ->file('photo')
                ->store('customers', 'public');

            $validated['photo_path'] = $newPhotoPath;
        }

        unset($validated['photo']);

        try {
            $customer->update($validated);
        } catch (Throwable $exception) {
            if ($newPhotoPath !== null) {
                $this->deletePhoto($newPhotoPath);
            }

            throw $exception;
        }

        if ($newPhotoPath !== null && $oldPhotoPath !== $newPhotoPath) {
            $this->deletePhoto($oldPhotoPath);
        }

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente atualizado com sucesso.');
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(Customer $customer): RedirectResponse
    {
        $photoPath = $customer->photo_path;

        $customer->delete();
        $this->deletePhoto($photoPath);

        return redirect()
            ->route('customers.index')
            ->with('success', 'Cliente excluído com sucesso.');
    }

    private function deletePhoto(string $photoPath): void
    {
        if (! Storage::disk('public')->delete($photoPath)) {
            Log::warning('Não foi possível remover uma foto de cliente do storage público.', [
                'photo_path' => $photoPath,
            ]);
        }
    }
}
