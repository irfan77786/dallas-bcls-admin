<?php

namespace App\Http\Controllers;

use App\Models\Driver;
use DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class DriverController extends Controller
{
    public function index(): View
    {
        return view('pages.drivers.index');
    }

    public function data(Request $request)
    {
        $query = Driver::query()->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('picture_html', function (Driver $driver) {
                $url = $driver->pictureUrl();
                if (! $url) {
                    return '<span class="drv-avatar drv-avatar--empty" title="No photo"><i class="ik ik-user"></i></span>';
                }

                return '<img src="' . e($url) . '" alt="" class="drv-avatar" loading="lazy">';
            })
            ->editColumn('phone', function (Driver $driver) {
                return $driver->phone ?: '—';
            })
            ->editColumn('address', function (Driver $driver) {
                $address = trim((string) $driver->address);
                if ($address === '') {
                    return '—';
                }

                return e(\Illuminate\Support\Str::limit($address, 40));
            })
            ->editColumn('vehicle_type', function (Driver $driver) {
                return $driver->vehicle_type ?: '—';
            })
            ->addColumn('vehicle_info', function (Driver $driver) {
                $label = $driver->vehicleLabel();

                return $label !== '' ? e($label) : '—';
            })
            ->editColumn('plate_number', function (Driver $driver) {
                return $driver->plate_number
                    ? '<span class="drv-mono">' . e($driver->plate_number) . '</span>'
                    : '—';
            })
            ->editColumn('capacity', function (Driver $driver) {
                return $driver->capacity !== null ? (string) $driver->capacity : '—';
            })
            ->addColumn('status', function (Driver $driver) {
                if ($driver->active) {
                    return '<span class="badge badge-success">Active</span>';
                }

                return '<span class="badge badge-secondary">Inactive</span>';
            })
            ->addColumn('action', function (Driver $driver) {
                $id = (int) $driver->id;

                return '<div class="table-actions drv-actions text-right" style="white-space:nowrap;">'
                    . '<a href="#" class="drv-view" data-id="' . $id . '" title="View">'
                    . '<i class="ik ik-eye f-16"></i></a>'
                    . '<a href="#" class="drv-edit" data-id="' . $id . '" title="Edit">'
                    . '<i class="ik ik-edit-2 f-16 text-primary"></i></a>'
                    . '<a href="#" class="drv-delete text-danger" data-id="' . $id . '" title="Delete">'
                    . '<i class="ik ik-trash-2 f-16"></i></a>'
                    . '</div>';
            })
            ->rawColumns(['picture_html', 'plate_number', 'status', 'action'])
            ->make(true);
    }

    public function forEdit(Driver $driver): JsonResponse
    {
        return response()->json([
            'driver' => [
                'id' => $driver->id,
                'name' => $driver->name,
                'phone' => $driver->phone,
                'address' => $driver->address,
                'picture' => $driver->picture,
                'picture_url' => $driver->pictureUrl(),
                'vehicle_type' => $driver->vehicle_type,
                'car_make' => $driver->car_make,
                'car_model' => $driver->car_model,
                'year' => $driver->year,
                'color' => $driver->color,
                'capacity' => $driver->capacity,
                'plate_number' => $driver->plate_number,
                'vin' => $driver->vin,
                'active' => (bool) $driver->active,
                'created_at' => $driver->created_at?->toIso8601String(),
                'updated_at' => $driver->updated_at?->toIso8601String(),
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateDriverPayload($request);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $driver = new Driver;
            $this->fillDriver($driver, $data, $request);
            $driver->active = $request->boolean('active', true);

            if ($request->hasFile('picture')) {
                $driver->picture = $this->storePicture($request->file('picture'));
            }

            $driver->save();
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not create driver.'], 500);
        }

        return response()->json([
            'message' => 'Driver created successfully.',
            'driver' => ['id' => $driver->id],
        ], 201);
    }

    public function update(Request $request, Driver $driver): JsonResponse
    {
        $validator = $this->validateDriverPayload($request);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }

        $data = $validator->validated();

        try {
            $this->fillDriver($driver, $data, $request);
            $driver->active = $request->boolean('active', true);

            if ($request->boolean('remove_picture')) {
                $this->deletePictureFile($driver->picture);
                $driver->picture = null;
            }

            if ($request->hasFile('picture')) {
                $this->deletePictureFile($driver->picture);
                $driver->picture = $this->storePicture($request->file('picture'));
            }

            $driver->save();
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not update driver.'], 500);
        }

        return response()->json(['message' => 'Driver updated successfully.']);
    }

    public function destroy(Driver $driver): JsonResponse
    {
        try {
            $this->deletePictureFile($driver->picture);
            $driver->delete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not delete driver.'], 500);
        }

        return response()->json(['message' => 'Driver deleted successfully.']);
    }

    private function fillDriver(Driver $driver, array $data, Request $request): void
    {
        $driver->name = $data['name'];
        $driver->phone = $data['phone'] ?? null;
        $driver->address = $data['address'] ?? null;
        $driver->vehicle_type = $data['vehicle_type'] ?? null;
        $driver->car_make = $data['car_make'] ?? null;
        $driver->car_model = $data['car_model'] ?? null;
        $driver->year = $data['year'] ?? null;
        $driver->color = $data['color'] ?? null;
        $driver->capacity = array_key_exists('capacity', $data) && $data['capacity'] !== null && $data['capacity'] !== ''
            ? (int) $data['capacity']
            : null;
        $driver->plate_number = $data['plate_number'] ?? null;
        $driver->vin = $data['vin'] ?? null;
    }

    private function validateDriverPayload(Request $request): \Illuminate\Validation\Validator
    {
        return Validator::make($request->all(), [
            'name' => ['required', 'string', 'max:255'],
            'phone' => ['nullable', 'string', 'max:30'],
            'address' => ['nullable', 'string', 'max:500'],
            'vehicle_type' => ['nullable', 'string', 'max:255'],
            'car_make' => ['nullable', 'string', 'max:255'],
            'car_model' => ['nullable', 'string', 'max:255'],
            'year' => ['nullable', 'string', 'max:10'],
            'color' => ['nullable', 'string', 'max:50'],
            'capacity' => ['nullable', 'integer', 'min:1', 'max:100'],
            'plate_number' => ['nullable', 'string', 'max:50'],
            'vin' => ['nullable', 'string', 'max:64'],
            'active' => ['nullable'],
            'remove_picture' => ['nullable'],
            'picture' => ['nullable', 'image', 'mimes:jpeg,png,jpg,gif,webp', 'max:2048'],
        ]);
    }

    private function storePicture($file): string
    {
        $name = time() . '_' . uniqid() . '.' . $file->getClientOriginalExtension();

        return $file->storeAs('drivers', $name, 'public');
    }

    private function deletePictureFile(?string $path): void
    {
        if (! $path) {
            return;
        }

        if (Storage::disk('public')->exists($path)) {
            Storage::disk('public')->delete($path);
        }
    }
}
