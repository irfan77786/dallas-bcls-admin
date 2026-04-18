<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Artisan;
use App\Models\Review;
use App\Models\Vehicle;
use App\Models\CarSeat;
use App\Models\Booking;
use Illuminate\Support\Str;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;


class DashboardController extends Controller
{
    /**
     * Show the dashboard with total reviews and a list of reviews.
     */
    public function index()
    {
        $totalBookings = Booking::count();
        $totalRevenue = (float) Booking::sum('total_price');
        $activeVehicles = Vehicle::where('active', true)->count();
        $totalVehicles = Vehicle::count();
        $totalReviews = Schema::hasTable('reviews') ? Review::count() : 0;
        $totalFeedback = 0;
        if (Schema::hasTable('feedback')) {
            $totalFeedback = \App\Models\Feedback::count();
        } elseif (Schema::hasTable('feedbacks')) {
            $totalFeedback = \DB::table('feedbacks')->count();
        }

        $paidBookings = Booking::whereRaw('LOWER(payment_status) = ?', ['paid'])->count();
        $pendingBookings = Booking::whereRaw('LOWER(payment_status) in (?, ?)', ['pending', 'unpaid'])->count();

        $startOfCurrentMonth = Carbon::now()->startOfMonth();
        $startOfLastMonth = Carbon::now()->subMonthNoOverflow()->startOfMonth();
        $endOfLastMonth = Carbon::now()->subMonthNoOverflow()->endOfMonth();

        $bookingsCurrentMonth = Booking::whereBetween('created_at', [$startOfCurrentMonth, Carbon::now()])->count();
        $bookingsLastMonth = Booking::whereBetween('created_at', [$startOfLastMonth, $endOfLastMonth])->count();
        $bookingGrowthPercent = $bookingsLastMonth > 0
            ? (($bookingsCurrentMonth - $bookingsLastMonth) / $bookingsLastMonth) * 100
            : ($bookingsCurrentMonth > 0 ? 100 : 0);

        $monthlyBookingsData = collect(range(5, 0))
            ->map(function ($monthsAgo) {
                $date = Carbon::now()->subMonths($monthsAgo);
                return [
                    'label' => $date->format('M'),
                    'count' => Booking::whereYear('created_at', $date->year)
                        ->whereMonth('created_at', $date->month)
                        ->count(),
                ];
            })
            ->push([
                'label' => Carbon::now()->format('M'),
                'count' => Booking::whereYear('created_at', Carbon::now()->year)
                    ->whereMonth('created_at', Carbon::now()->month)
                    ->count(),
            ]);

        $recentBookings = Booking::with(['vehicle', 'booker', 'passengers'])
            ->latest()
            ->take(10)
            ->get();

        return view('pages.dashboard', [
            'stats' => [
                'totalBookings' => $totalBookings,
                'totalRevenue' => $totalRevenue,
                'activeVehicles' => $activeVehicles,
                'totalVehicles' => $totalVehicles,
                'totalReviews' => $totalReviews,
                'totalFeedback' => $totalFeedback,
                'paidBookings' => $paidBookings,
                'pendingBookings' => $pendingBookings,
                'bookingsCurrentMonth' => $bookingsCurrentMonth,
                'bookingsLastMonth' => $bookingsLastMonth,
                'bookingGrowthPercent' => round($bookingGrowthPercent, 1),
            ],
            'recentBookings' => $recentBookings,
            'monthlyBookingLabels' => $monthlyBookingsData->pluck('label')->values(),
            'monthlyBookingCounts' => $monthlyBookingsData->pluck('count')->values(),
        ]);
    }
    
    public function vehicle()
    {
        $query = Vehicle::with(['carSeat']);
        if (Schema::hasColumn('vehicles', 'sort_order')) {
            $query->orderBy('sort_order');
        }
        $vehicles = $query->orderBy('id')->get();

        return view('pages.vehicle', [
            'vehicles' => $vehicles,
            'canReorderVehicles' => Schema::hasColumn('vehicles', 'sort_order'),
        ]);
    }

    /**
     * Persist vehicle row order after drag-and-drop (sort_order 1..n).
     */
    public function reorderVehicles(Request $request)
    {
        if (! Schema::hasColumn('vehicles', 'sort_order')) {
            return response()->json([
                'success' => false,
                'message' => 'Run migrations to enable vehicle ordering.',
            ], 422);
        }

        $request->validate([
            'vehicle_ids' => 'required|array|min:1',
            'vehicle_ids.*' => 'integer|exists:vehicles,id',
        ]);

        $submitted = array_values(array_map('intval', $request->input('vehicle_ids')));
        if (count($submitted) !== count(array_unique($submitted))) {
            return response()->json(['success' => false, 'message' => 'Duplicate vehicle ids.'], 422);
        }

        $expected = Vehicle::query()->orderBy('sort_order')->orderBy('id')->pluck('id')->map(fn ($id) => (int) $id)->sort()->values()->all();
        $sortedSubmit = $submitted;
        sort($sortedSubmit);

        if ($expected !== $sortedSubmit) {
            return response()->json(['success' => false, 'message' => 'Invalid vehicle list for reorder.'], 422);
        }

        DB::transaction(function () use ($submitted) {
            foreach ($submitted as $index => $id) {
                Vehicle::whereKey($id)->update(['sort_order' => $index + 1]);
            }
        });

        return response()->json(['success' => true, 'message' => 'Order saved.']);
    }
    
    
    
    public function booking(){
          $bookings = Booking::with(['vehicle'])->get();
        //   dd($bookings);
         return view('pages.booking',compact('bookings'));
    }
    
   public function saveVehicle(Request $request)
{
    
   
    // ✅ 1. Validate request data
    $validated = $request->validate([
        'vehicle_name' => 'required|string|max:255',
        'vehicle_code' => 'required|string|max:100|unique:vehicles,vehicle_code',
        'vehicle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        'number_of_passengers' => 'required|integer|min:1',
        'luggage_capacity' => 'required|integer|min:0',
        'greeting_fee' => 'required|numeric|min:0',
        'description' => 'required|string',
        'active' => 'nullable|boolean',
        'car_seats' => 'nullable|array',
        'car_seats.*.category' => 'required_with:car_seats|string',
        'car_seats.*.quantity' => 'required_with:car_seats|integer|min:1',
        'car_seats.*.rate' => 'required_with:car_seats|numeric|min:0|regex:/^\d*(\.\d{1,2})?$/',
        'base_fare' => 'required|numeric|min:0',
        'base_hourly_fare' => 'required|numeric|min:0',
        'per_km_rate' => 'required|numeric|min:0'

    ]);
    
    
    

    // ✅ 2. Create vehicle
    try {
        
        $imagePath = null;
if ($request->hasFile('vehicle_image')) {
    $image = $request->file('vehicle_image');
    $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
    $imagePath = $image->storeAs('vehicles', $imageName, 'public');
}


        $createPayload = [
            'vehicle_name' => $validated['vehicle_name'],
            'vehicle_code' => $validated['vehicle_code'],
            'number_of_passengers' => $validated['number_of_passengers'],
            'luggage_capacity' => $validated['luggage_capacity'],
            'greeting_fee' => $validated['greeting_fee'],
            'description' => $validated['description'],
            'active' => $request->has('active'), // checkbox boolean
            'slug' => Str::slug($validated['vehicle_name']),
            'vehicle_image' => $imagePath,
            'base_fare' => $validated['base_fare'],
            'base_hourly_fare' => $validated['base_hourly_fare'],
            'per_km_rate' => $validated['per_km_rate'],
        ];

        if (Schema::hasColumn('vehicles', 'sort_order')) {
            $createPayload['sort_order'] = (int) (Vehicle::max('sort_order') ?? 0) + 1;
        }

        $vehicle = Vehicle::create($createPayload);

        // ✅ 3. Optionally save car seats if provided
        if (!empty($validated['car_seats'])) {
            
            foreach ($validated['car_seats'] as $seat) {
                
                    CarSeat::create([
                        'vehicle_id' => $vehicle->id,
                        'category' => $seat['category'],
                        'quantity' => $seat['quantity'],
                        'rate' => $seat['rate'],
                    ]);
               
            }
        }

        // ✅ 4. Redirect with success message
        return back()->with('success', 'Vehicle created successfully.');

    } catch (\Exception $e) {
        // ✅ 5. In case of error, return to the form with the error message
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
}
public function getVehicleData($id)
{
    $vehicle =  Vehicle::with('carSeat')->findOrFail($id);

    return response()->json([
        'status' => true,
        'vehicle' => $vehicle
    ]);
}
public function updateVehicle(Request $request)
{
    $validated = $request->validate([
        'vehicle_id' => 'required|exists:vehicles,id',
        'vehicle_name' => 'required|string|max:255',
        'vehicle_code' => 'required|string|max:100|unique:vehicles,vehicle_code,' . $request->vehicle_id,
        'vehicle_image' => 'nullable|image|mimes:jpeg,png,jpg,gif,svg,webp|max:2048',
        'number_of_passengers' => 'required|integer|min:1',
        'luggage_capacity' => 'required|integer|min:0',
        'greeting_fee' => 'required|numeric|min:0',
        'base_fare' => 'required|numeric|min:0',
        'base_hourly_fare' => 'required|numeric|min:0',
        'per_km_rate' => 'required|numeric|min:0',
        'description' => 'required|string',
        'car_seats' => 'nullable|array',
        'car_seats.*.category' => 'required_with:car_seats|string',
        'car_seats.*.quantity' => 'required_with:car_seats|integer|min:1',
        'car_seats.*.rate' => 'required_with:car_seats|numeric|min:0',
    ]);

    try {
        $vehicle = Vehicle::findOrFail($request->vehicle_id);

        // Handle image
        $imagePath = $vehicle->vehicle_image;
        if ($request->hasFile('vehicle_image')) {
            $image = $request->file('vehicle_image');
            $imageName = time() . '_' . uniqid() . '.' . $image->getClientOriginalExtension();
            $imagePath = $image->storeAs('vehicles', $imageName, 'public');
        }

        $vehicle->update([
            'vehicle_name' => $validated['vehicle_name'],
            'vehicle_code' => $validated['vehicle_code'],
            'number_of_passengers' => $validated['number_of_passengers'],
            'luggage_capacity' => $validated['luggage_capacity'],
            'greeting_fee' => $validated['greeting_fee'],
            'base_fare' => $validated['base_fare'],
            'base_hourly_fare' => $validated['base_hourly_fare'],
            'per_km_rate' => $validated['per_km_rate'],
            'description' => $validated['description'],
            'active' => $request->has('active'),
            'vehicle_image' => $imagePath,
            'slug' => Str::slug($validated['vehicle_name']),
        ]);

        // Update seats (simplified: delete and re-insert)
        CarSeat::where('vehicle_id', $vehicle->id)->delete();

        if (!empty($validated['car_seats'])) {
            foreach ($validated['car_seats'] as $seat) {
                CarSeat::create([
                    'vehicle_id' => $vehicle->id,
                    'category' => $seat['category'],
                    'quantity' => $seat['quantity'],
                    'rate' => $seat['rate'],
                ]);
            }
        }

        return back()->with('success', 'Vehicle updated successfully.');

    } catch (\Exception $e) {
        return back()->withErrors(['error' => $e->getMessage()])->withInput();
    }
}

    public function destroy($id)
{
    $vehicle = Vehicle::findOrFail($id);

    // Delete the image from storage if exists
    // if ($vehicle->vehicle_image && \Storage::disk('public')->exists($vehicle->vehicle_image)) {
    //     \Storage::disk('public')->delete($vehicle->vehicle_image);
    // }

    $vehicle->delete();

    return response()->json(['success' => true, 'message' => 'Vehicle deleted successfully.']);
}

    /**
     * Clear application cache.
     */
    public function clearCache()
    {
        Artisan::call('cache:clear');
        return back()->with('success', 'Cache cleared successfully!');
    }
}
