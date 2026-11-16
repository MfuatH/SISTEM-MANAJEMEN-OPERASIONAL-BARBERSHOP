<?php

namespace App\Http\Controllers;

use App\Http\Requests\SaveServiceRequest;
use App\Models\Service;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class ServiceController extends Controller
{
    public function active(): JsonResponse
    {
        $services = Service::query()->active()
            ->orderBy('name')
            ->orderBy('id')
            ->get(['id', 'name', 'current_price']);

        return response()->json(['data' => $services]);
    }

    public function index(): View
    {
        $services = Service::query()
            ->orderBy('archived_at')
            ->orderBy('name')
            ->orderBy('id')
            ->paginate(20);

        return view('master.services.index', ['services' => $services]);
    }

    public function create(): View
    {
        return view('master.services.form', ['service' => new Service]);
    }

    public function store(SaveServiceRequest $request): RedirectResponse
    {
        Service::create($request->validated());

        return redirect()->route('owner.services.index')->with('status', 'Layanan berhasil ditambahkan.');
    }

    public function edit(Service $service): View
    {
        return view('master.services.form', ['service' => $service]);
    }

    public function update(SaveServiceRequest $request, Service $service): RedirectResponse
    {
        $service->update($request->validated());

        return redirect()->route('owner.services.index')->with('status', 'Layanan berhasil diperbarui.');
    }

    public function archive(Service $service): RedirectResponse
    {
        Service::query()->active()->whereKey($service->id)->update(['archived_at' => now()]);

        return redirect()->route('owner.services.index')->with('status', 'Layanan diarsipkan.');
    }
}
