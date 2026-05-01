<?php
// app/Http/Controllers/BookingController.php

namespace App\Http\Controllers;

use App\Mail\BookingReservationComposerMail;
use App\Models\Booking;
use App\Models\Vehicle;
use App\Services\BookingEmailPayloadBuilder;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

class BookingController extends Controller
{
    private function nextPublicBookingId(): int
    {
        $latest = Booking::orderBy('id', 'desc')->first();
        $lastNumericId = 41101;
        if ($latest && preg_match('/(\d+)/', (string) $latest->booking_id, $matches)) {
            $lastNumericId = (int) $matches[1] + 1;
        }

        return $lastNumericId;
    }

    public function index(Request $request)
    {
        $perPage = (int) $request->input('per_page', 10);
        if (! in_array($perPage, [10, 25, 50, 100], true)) {
            $perPage = 10;
        }

        $query = Booking::query()
            ->with(['vehicle', 'passengers'])
            ->latest();

        if ($search = trim((string) $request->input('search'))) {
            $query->where(function ($bookingQuery) use ($search) {
                $bookingQuery
                    ->where('booking_id', 'like', '%' . $search . '%')
                    ->orWhere('pickup_location', 'like', '%' . $search . '%')
                    ->orWhere('dropoff_location', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('service_option', 'like', '%' . $search . '%')
                    ->orWhere('id', 'like', '%' . $search . '%')
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery->where('vehicle_name', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('passengers', function ($passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $search . '%']);
                    });
            });
        }

        if ($paymentStatus = $request->input('payment_status')) {
            $query->where('payment_status', $paymentStatus);
        }

        if ($serviceOption = $request->input('service_option')) {
            $query->where('service_option', $serviceOption);
        }

        if ($vehicleId = $request->input('vehicle_id')) {
            $query->where('vehicle_id', $vehicleId);
        }

        if ($dateFrom = $request->input('date_from')) {
            $query->whereDate('pickup_date', '>=', $dateFrom);
        }

        if ($dateTo = $request->input('date_to')) {
            $query->whereDate('pickup_date', '<=', $dateTo);
        }

        $bookings = $query->paginate($perPage)->withQueryString();
        $vehicles = Vehicle::query()
            ->when(Schema::hasColumn('vehicles', 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('vehicle_name')
            ->get(['id', 'vehicle_name']);
        $paymentStatuses = Booking::query()
            ->select('payment_status')
            ->whereNotNull('payment_status')
            ->distinct()
            ->orderBy('payment_status')
            ->pluck('payment_status');

        return view('pages.bookings.index', compact('bookings', 'vehicles', 'paymentStatuses'));
    }
public function show($id)
{
    $booking = Booking::with([
        'vehicle',
        'passengers.flightDetail',
        'booker',
        'returnService',
        'breakdown'
    ])->findOrFail($id);

    $breakdown = $booking->breakdown;

    $travelInfo = null;

    if (!$breakdown) {
        // No breakdown data available, fallback or handle error as needed
        return view('pages.bookings.show', compact('booking', 'travelInfo'));
    }

    if (empty($breakdown->total_kms)) {
        // Hourly booking (assuming total_kms empty means hourly)
        $hours = (int) ($breakdown->total_hours ?? 0);
        $fare = (float) ($breakdown->hourly_fare ?? 0);

        $travelInfo = [
            'type' => 'hourly',
            'hours' => $hours,
            'fare' => $fare,
        ];
    } else {
        // Point-to-point booking
        $distance = (float) $breakdown->total_kms;
        $fare = (float) ($breakdown->base_fare ?? 0) + ($distance * (float) ($breakdown->per_km_rate ?? 0));

        $travelInfo = [
            'type' => 'point_to_point',
            'distance' => $distance,
            'fare' => $fare,
        ];
    }

    return view('pages.bookings.show', compact('booking', 'travelInfo'));
    }

    public function destroy($id)
    {
        $booking = Booking::with(['passengers.flightDetail', 'booker', 'returnService', 'breakdown', 'payments'])->findOrFail($id);

        DB::transaction(function () use ($booking) {
            foreach ($booking->passengers as $passenger) {
                if ($passenger->flightDetail) {
                    $passenger->flightDetail()->delete();
                }
            }

            $booking->payments()->delete();
            if ($booking->breakdown) {
                $booking->breakdown()->delete();
            }
            $booking->passengers()->delete();

            $returnServiceId = $booking->return_service_id;
            $bookerId = $booking->booker_id;

            $booking->delete();

            if ($returnServiceId) {
                \App\Models\ReturnService::where('id', $returnServiceId)->delete();
            }
            if ($bookerId) {
                \App\Models\Booker::where('id', $bookerId)->delete();
            }
        });

        return redirect()
            ->route('bookings.index')
            ->with('success', 'Reservation deleted successfully.');
    }

    public function duplicate($id)
    {
        $booking = Booking::with([
            'passengers.flightDetail',
            'booker',
            'returnService',
            'breakdown',
        ])->findOrFail($id);

        $newBooking = DB::transaction(function () use ($booking) {
            $newBookerId = null;
            if ($booking->booker) {
                $newBooker = $booking->booker->replicate();
                $newBooker->save();
                $newBookerId = $newBooker->id;
            }

            $newReturnServiceId = null;
            if ($booking->returnService) {
                $newReturnService = $booking->returnService->replicate();
                $newReturnService->save();
                $newReturnServiceId = $newReturnService->id;
            }

            $newBooking = $booking->replicate();
            $newBooking->booking_id = (string) $this->nextPublicBookingId();
            $newBooking->booker_id = $newBookerId;
            $newBooking->return_service_id = $newReturnServiceId;
            $newBooking->payment_status = 'Pending';
            $newBooking->stripe_customer_id = null;
            $newBooking->stripe_payment_method_id = null;
            $newBooking->save();

            if ($booking->breakdown) {
                $newBreakdown = $booking->breakdown->replicate();
                $newBreakdown->booking_id = $newBooking->id;
                $newBreakdown->save();
            }

            foreach ($booking->passengers as $passenger) {
                $newPassenger = $passenger->replicate();
                $newPassenger->booking_id = $newBooking->id;
                $newPassenger->save();

                if ($passenger->flightDetail) {
                    $newFlight = $passenger->flightDetail->replicate();
                    $newFlight->passenger_id = $newPassenger->id;
                    $newFlight->save();
                }
            }

            return $newBooking;
        });

        return redirect()
            ->back()
            ->with('success', 'Reservation duplicated successfully. Payment status set to Pending.');
    }

    /**
     * Send customer-style and admin-style booking emails with PDF (after “save without pay”).
     */
    public function sendComposerEmails(Request $request, $id)
    {
        $booking = Booking::with([
            'vehicle',
            'passengers.flightDetail',
            'booker',
            'returnService',
            'breakdown',
        ])->findOrFail($id);

        $request->validate([
            'personal_message' => ['nullable', 'string', 'max:500'],
            'customer_email_1' => ['nullable', 'email'],
            'customer_email_2' => ['nullable', 'email'],
            'customer_email_3' => ['nullable', 'email'],
            'admin_email_1' => ['nullable', 'email'],
            'admin_email_2' => ['nullable', 'email'],
            'admin_email_3' => ['nullable', 'email'],
        ]);

        $customerRecipients = [];
        $adminRecipients = [];

        for ($i = 1; $i <= 3; $i++) {
            if ($request->boolean("customer_send_$i")) {
                $email = trim((string) $request->input("customer_email_$i"));
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->composerErrorResponse($request, [
                        "customer_email_$i" => ['Enter a valid email when “Send” is checked for this row.'],
                    ]);
                }
                $customerRecipients[] = $email;
            }
        }

        for ($i = 1; $i <= 3; $i++) {
            if ($request->boolean("admin_send_$i")) {
                $email = trim((string) $request->input("admin_email_$i"));
                if ($email === '' || ! filter_var($email, FILTER_VALIDATE_EMAIL)) {
                    return $this->composerErrorResponse($request, [
                        "admin_email_$i" => ['Enter a valid email when “Send” is checked for this row.'],
                    ]);
                }
                $adminRecipients[] = $email;
            }
        }

        if ($customerRecipients === [] && $adminRecipients === []) {
            return $this->composerErrorResponse($request, [
                'recipients' => ['Select at least one recipient and provide a valid email.'],
            ]);
        }

        try {
            $bookingData = BookingEmailPayloadBuilder::build($booking);
        } catch (\Throwable $e) {
            report($e);

            return $this->composerErrorResponse($request, [
                'booking' => [$e->getMessage()],
            ]);
        }

        $bookingData['personal_message'] = $request->input('personal_message') ?: null;

        $dir = public_path('pdfs');
        if (! is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        $pdfPath = $dir . DIRECTORY_SEPARATOR . $booking->booking_id . '-reservation-composer.pdf';

        try {
            if (function_exists('set_time_limit')) {
                @set_time_limit(120);
            }
            $pdf = Pdf::loadView('pdfs.booking-reservation', ['bookingData' => $bookingData]);
            file_put_contents($pdfPath, $pdf->output());
        } catch (\Throwable $e) {
            Log::error('Booking composer PDF failed', ['booking_id' => $booking->booking_id, 'message' => $e->getMessage()]);
            report($e);

            return $this->composerErrorResponse($request, [
                'pdf' => ['Could not generate PDF: ' . $e->getMessage()],
            ]);
        }

        $attachPath = is_file($pdfPath) && filesize($pdfPath) > 0 ? $pdfPath : null;

        $sent = 0;
        foreach ($customerRecipients as $email) {
            Mail::to($email)->send(new BookingReservationComposerMail($bookingData, false, false, $attachPath));
            $sent++;
        }
        foreach ($adminRecipients as $email) {
            Mail::to($email)->send(new BookingReservationComposerMail($bookingData, true, false, $attachPath));
            $sent++;
        }

        $message = $sent . ' email(s) sent with booking PDF attached.';

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => $message,
            ]);
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', $message);
    }

    /**
     * @param  array<string, array<int, string>|string>  $errors
     */
    private function composerErrorResponse(Request $request, array $errors)
    {
        if ($request->expectsJson()) {
            return response()->json([
                'success' => false,
                'message' => collect($errors)->flatten()->first() ?? 'Something went wrong.',
                'errors' => $errors,
            ], 422);
        }

        return back()->withErrors($errors)->withInput();
    }

    private function getDistanceBetweenAddresses(string $origin, string $destination): ?float
{
    $apiKey = config('services.google_maps.api_key'); // Make sure you set this in config/services.php and .env

    $response = Http::get('https://maps.googleapis.com/maps/api/distancematrix/json', [
        'origins' => $origin,
        'destinations' => $destination,
        'key' => 'AIzaSyBtkrakOJ7xvcL2FIF5XbhCV1PIaNKz4zQ',
        'units' => 'metric',
    ]);

    if ($response->ok()) {
        $data = $response->json();

        if (
            isset($data['status'], $data['rows'][0]['elements'][0]['status']) &&
            $data['status'] === 'OK' &&
            $data['rows'][0]['elements'][0]['status'] === 'OK'
        ) {
            $distanceMeters = $data['rows'][0]['elements'][0]['distance']['value'];
            $distanceKm = $distanceMeters / 1000; // Convert meters to kilometers
            return round($distanceKm, 2);
        }
    }

    return null; // Return null if something went wrong
}
}


