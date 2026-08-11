<?php

namespace App\Http\Controllers;

use App\Jobs\CreateBookingDocs;
use App\Services\BookingEmailPayloadBuilder;
use App\Models\Airport;
use App\Models\Airline;
use App\Models\Fbo;
use App\Models\Account;
use App\Models\Booker;
use App\Models\Booking;
use App\Models\BookingAccountSnapshot;
use App\Models\Driver;
use App\Models\FlightDetail;
use App\Models\Payment;
use App\Models\ReservationRoutingDraft;
use App\Models\ReturnService;
use App\Models\Vehicle;
use App\Services\BookingPricingService;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class ReservationController extends Controller
{
    /** USD per child seat; line total = this × `child_seat_quantity`. */
    private const CHILD_SEAT_PRICE_PER_SEAT_USD = 20.0;

    public function __construct(
        private BookingPricingService $pricing
    ) {
    }

    public function create()
    {
        return $this->reservationView('pages.reservation-v2');
    }

    public function createV2()
    {
        return $this->reservationView('pages.reservation-v2');
    }

    public function createLa()
    {
        $draftBooking = $this->getOrCreateLaDraftBooking();

        $routingDraft = [];
        if ($draftBooking && is_array($draftBooking->routing_information)) {
            $routingDraft = $draftBooking->routing_information;
        } elseif (Schema::hasTable('reservation_routing_drafts') && auth()->check()) {
            $draft = ReservationRoutingDraft::query()
                ->where('user_id', auth()->id())
                ->value('routing_information');
            $routingDraft = is_array($draft) ? $draft : [];
        }

        return $this->reservationView('pages.reservation-la', 'Add Reservation', [
            'nextConfirmationNumber' => $draftBooking?->booking_id ?? $this->nextPublicBookingId(),
            'routingDraft' => $routingDraft,
            'draftBooking' => $draftBooking,
            'formDefaults' => $draftBooking ? $this->draftBookingFormDefaults($draftBooking) : [],
        ]);
    }

    public function saveRoutingDraft(Request $request)
    {
        $validated = $request->validate([
            'draft_booking_id' => ['nullable', 'integer'],
            'routing_information' => ['nullable', 'array'],
            'routing_information.*.type' => ['required', 'string', Rule::in(['pickup', 'dropoff', 'stop', 'wait'])],
            'routing_information.*.label' => ['required', 'string', 'max:500'],
            'routing_information.*.submit_value' => ['required', 'string', 'max:1000'],
            'routing_information.*.source' => ['nullable', 'string', 'max:50'],
            'routing_information.*.payload' => ['nullable', 'array'],
            'pickup_location' => ['nullable', 'string', 'max:500'],
            'dropoff_location' => ['nullable', 'string', 'max:500'],
            'stop_locations' => ['nullable', 'array'],
            'stop_locations.*' => ['nullable', 'string', 'max:500'],
        ]);

        $routing = $this->cleanRoutingInformation($validated['routing_information'] ?? []);
        $draft = $this->resolveLaDraftBooking($validated['draft_booking_id'] ?? null);

        if ($draft) {
            $pickup = null;
            $dropoff = null;
            $stops = [];
            foreach ($routing as $row) {
                $type = $row['type'] ?? '';
                $value = $row['submit_value'] ?? '';
                if ($type === 'pickup' && $value !== '') {
                    $pickup = $value;
                } elseif ($type === 'dropoff' && $value !== '') {
                    $dropoff = $value;
                } elseif (in_array($type, ['stop', 'wait'], true) && $value !== '') {
                    $stops[] = $value;
                }
            }

            $payload = [
                'routing_information' => $routing ?: null,
                'stop_locations' => $stops ?: null,
            ];
            if ($pickup !== null) {
                $payload['pickup_location'] = $pickup;
            }
            if ($dropoff !== null) {
                $payload['dropoff_location'] = $dropoff;
            }
            if (array_key_exists('pickup_location', $validated) && filled($validated['pickup_location'])) {
                $payload['pickup_location'] = $validated['pickup_location'];
            }
            if (array_key_exists('dropoff_location', $validated) && filled($validated['dropoff_location'])) {
                $payload['dropoff_location'] = $validated['dropoff_location'];
            }
            if (array_key_exists('stop_locations', $validated)) {
                $payload['stop_locations'] = $this->cleanStopLocations($validated['stop_locations'] ?? []) ?: null;
            }

            $draft->fill($payload);
            $draft->save();
        }

        if (Schema::hasTable('reservation_routing_drafts') && auth()->check()) {
            ReservationRoutingDraft::query()->updateOrCreate(
                ['user_id' => auth()->id()],
                ['routing_information' => $routing]
            );
        }

        return response()->json([
            'success' => true,
            'count' => count($routing),
            'draft_booking_id' => $draft?->id,
        ]);
    }

    public function saveLaDraft(Request $request)
    {
        $validated = $request->validate([
            'draft_booking_id' => ['nullable', 'integer'],
            'note' => ['nullable', 'string', 'max:4000'],
            'notes' => ['nullable', 'array'],
            'notes.trip' => ['nullable', 'string', 'max:4000'],
            'notes.dispatch' => ['nullable', 'string', 'max:1000'],
            'notes.partner' => ['nullable', 'string', 'max:1000'],
            'notes.billto' => ['nullable', 'string', 'max:1000'],
            'notes.addr' => ['nullable', 'string', 'max:1000'],
            'notes.airport' => ['nullable', 'string', 'max:1000'],
            'notes.add_ts' => ['nullable', 'boolean'],
            'notes.hide_customer' => ['nullable', 'boolean'],
            'pickup_date' => ['nullable', 'date'],
            'pickup_time' => ['nullable', 'string', 'max:8'],
            'pickup_location' => ['nullable', 'string', 'max:500'],
            'dropoff_location' => ['nullable', 'string', 'max:500'],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'service_option' => ['nullable', 'string', 'max:50'],
            'pax_count' => ['nullable', 'integer', 'min:1', 'max:99'],
            'luggage_count' => ['nullable', 'integer', 'min:0', 'max:99'],
            'account_id' => ['nullable', 'integer'],
            'custom_total_price' => ['nullable', 'numeric', 'min:0'],
            'po_client_ref' => ['nullable', 'string', 'max:100'],
            'pickup_flight_details' => ['nullable', 'string', 'max:255'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'meet_option' => ['nullable', 'string', 'max:50'],
        ]);

        $draft = $this->resolveLaDraftBooking($validated['draft_booking_id'] ?? null);
        if (! $draft) {
            return response()->json(['success' => false, 'message' => 'Draft booking not found.'], 404);
        }

        $note = $validated['note'] ?? null;
        if ($note === null && isset($validated['notes']) && is_array($validated['notes'])) {
            $note = $this->buildCombinedNoteFromSections($validated['notes']);
        }

        $updates = array_filter([
            'note' => $note,
            'po_client_ref' => array_key_exists('po_client_ref', $validated)
                ? (filled($validated['po_client_ref']) ? trim((string) $validated['po_client_ref']) : null)
                : null,
            'pickup_date' => $validated['pickup_date'] ?? null,
            'pickup_time' => $validated['pickup_time'] ?? null,
            'pickup_location' => $validated['pickup_location'] ?? null,
            'dropoff_location' => $validated['dropoff_location'] ?? null,
            'vehicle_id' => $validated['vehicle_id'] ?? null,
            'service_option' => $validated['service_option'] ?? null,
            'pax_count' => $validated['pax_count'] ?? null,
            'luggage_count' => $validated['luggage_count'] ?? null,
            'total_price' => array_key_exists('custom_total_price', $validated) && $validated['custom_total_price'] !== null
                ? round((float) $validated['custom_total_price'], 2)
                : null,
        ], fn ($v) => $v !== null);

        // Allow clearing PO/Client Ref on draft save when explicitly sent empty.
        if (array_key_exists('po_client_ref', $validated) && ! filled($validated['po_client_ref'])) {
            $updates['po_client_ref'] = null;
        }

        if (array_key_exists('driver_id', $validated) && Schema::hasColumn('bookings', 'driver_id')) {
            $updates['driver_id'] = $validated['driver_id'] ?: null;
        }

        if ($updates) {
            $draft->fill($updates);
            $draft->save();
        }

        // Persist flight fields on draft passenger if provided
        if (filled($validated['pickup_flight_details'] ?? null) || filled($validated['flight_number'] ?? null) || filled($validated['meet_option'] ?? null)) {
            $this->syncDraftFlightFields($draft, $validated);
        }

        return response()->json([
            'success' => true,
            'draft_booking_id' => $draft->id,
            'booking_id' => $draft->booking_id,
            'updated_at' => optional($draft->updated_at)->toDateTimeString(),
        ]);
    }

    private function getOrCreateLaDraftBooking(): ?Booking
    {
        if (! auth()->check() || ! Schema::hasColumn('bookings', 'is_draft')) {
            return null;
        }

        $existing = Booking::query()
            ->where('is_draft', true)
            ->where('draft_user_id', auth()->id())
            ->latest('id')
            ->first();
        if ($existing) {
            return $existing;
        }

        $vehicleId = Vehicle::query()->orderBy('id')->value('id');
        if (! $vehicleId) {
            // Cannot satisfy NOT NULL vehicle_id without a vehicle row
            return null;
        }

        return Booking::create([
            'booking_id' => (string) $this->nextPublicBookingId(),
            'vehicle_id' => $vehicleId,
            'pickup_location' => '',
            'dropoff_location' => null,
            'stop_locations' => null,
            'routing_information' => [],
            'pickup_date' => now()->toDateString(),
            'pickup_time' => '00:00:00',
            'total_price' => 0,
            'payment_status' => 'Draft',
            'is_draft' => true,
            'draft_user_id' => auth()->id(),
            'from_admin_reservation' => true,
            'pax_count' => 1,
            'luggage_count' => 0,
            'service_option' => 'point_to_point',
            'note' => null,
        ]);
    }

    private function resolveLaDraftBooking(?int $draftBookingId): ?Booking
    {
        if (! auth()->check() || ! Schema::hasColumn('bookings', 'is_draft')) {
            return null;
        }

        $query = Booking::query()
            ->where('is_draft', true)
            ->where('draft_user_id', auth()->id());

        if ($draftBookingId) {
            $query->where('id', $draftBookingId);
        }

        return $query->latest('id')->first() ?: $this->getOrCreateLaDraftBooking();
    }

    private function draftBookingFormDefaults(Booking $booking): array
    {
        $hasRealTrip = filled($booking->pickup_location) && trim((string) $booking->pickup_location) !== '';

        return [
            'note' => $booking->note,
            'po_client_ref' => $booking->po_client_ref,
            'pickup_date' => $hasRealTrip && $booking->pickup_date
                ? (\Carbon\Carbon::parse($booking->pickup_date)->format('Y-m-d'))
                : null,
            'pickup_time' => $hasRealTrip && $booking->pickup_time
                ? substr((string) $booking->pickup_time, 0, 5)
                : null,
            'pickup_location' => $hasRealTrip ? $booking->pickup_location : '',
            'dropoff_location' => $booking->dropoff_location,
            // Don't force placeholder vehicle into the UI selector
            'vehicle_id' => null,
            'driver_id' => Schema::hasColumn('bookings', 'driver_id') ? $booking->driver_id : null,
            'service_option' => $booking->service_option ?: 'point_to_point',
            'pax_count' => $booking->pax_count ?: 1,
            'luggage_count' => $booking->luggage_count ?: 0,
            'custom_total_price' => ((float) $booking->total_price) > 0 ? $booking->total_price : null,
            'stop_locations' => $booking->stop_locations ?? [],
            'routing_information' => $booking->routing_information ?? [],
        ];
    }

    private function buildCombinedNoteFromSections(array $notes): string
    {
        $sections = [];
        $trip = trim((string) ($notes['trip'] ?? ''));
        $dispatch = trim((string) ($notes['dispatch'] ?? ''));
        $partner = trim((string) ($notes['partner'] ?? ''));
        $billto = trim((string) ($notes['billto'] ?? ''));
        $addr = trim((string) ($notes['addr'] ?? ''));
        $airport = trim((string) ($notes['airport'] ?? ''));

        if ($trip !== '') {
            $sections[] = $trip;
        }
        if ($dispatch !== '') {
            $sections[] = "[Dispatch Notes]\n".$dispatch;
        }
        if ($partner !== '') {
            $sections[] = "[Partner Notes]\n".$partner;
        }
        if ($billto !== '') {
            $sections[] = "[Bill To & Pax Notes]\n".$billto;
        }
        if ($addr !== '') {
            $sections[] = "[Address Notes]\n".$addr;
        }
        if ($airport !== '') {
            $sections[] = "[Airport Notes]\n".$airport;
        }

        $flags = [];
        if (! empty($notes['add_ts'])) {
            $flags[] = 'Add to T/S';
        }
        if (! empty($notes['hide_customer'])) {
            $flags[] = 'Hide From Customer';
        }
        if ($flags) {
            $sections[] = "[Flags]\n".implode(', ', $flags);
        }

        return mb_substr(implode("\n\n", $sections), 0, 4000);
    }

    private function syncDraftFlightFields(Booking $draft, array $validated): void
    {
        $passenger = $draft->passengers()->first();
        if (! $passenger) {
            $passenger = $draft->passengers()->create([
                'first_name' => 'Draft',
                'last_name' => 'Passenger',
                'email' => null,
                'phone_number' => null,
            ]);
        }

        $flight = $passenger->flightDetail;
        if (! $flight) {
            $flight = new FlightDetail(['passenger_id' => $passenger->id]);
        }
        if (array_key_exists('pickup_flight_details', $validated)) {
            $flight->pickup_flight_details = $validated['pickup_flight_details'];
        }
        if (array_key_exists('flight_number', $validated)) {
            $flight->flight_number = $validated['flight_number'];
        }
        if (array_key_exists('meet_option', $validated)) {
            $flight->meet_option = $validated['meet_option'];
        }
        $flight->no_flight_info = filled($flight->pickup_flight_details) || filled($flight->flight_number);
        $flight->passenger_id = $passenger->id;
        $flight->save();
    }

    public function edit(Booking $booking)
    {
        $booking->load([
            'passengers.flightDetail',
            'booker',
            'returnService',
            'breakdown',
            'accountSnapshot',
        ]);

        return $this->reservationView('pages.reservation-v2', 'Edit reservation', [
            'isEditMode' => true,
            'formAction' => route('bookings.update', $booking),
            'formMethod' => 'PUT',
            'formDefaults' => $this->reservationFormDefaults($booking),
            'bookingPaymentStatus' => $booking->payment_status,
        ]);
    }

    public function editLa(Booking $booking, Request $request)
    {
        $booking->load([
            'passengers.flightDetail',
            'booker',
            'returnService',
            'breakdown',
            'accountSnapshot',
            'driver',
            'vehicle',
        ]);

        $routingDraft = is_array($booking->routing_information ?? null)
            ? $booking->routing_information
            : [];

        return $this->reservationView('pages.reservation-la', 'Edit Reservation', [
            'isEditMode' => true,
            'isEmbed' => $request->boolean('embed'),
            'formAction' => route('bookings.update', $booking),
            'formMethod' => 'PUT',
            'formDefaults' => $this->reservationFormDefaults($booking),
            'routingDraft' => $routingDraft,
            'draftBooking' => null,
            'nextConfirmationNumber' => $booking->booking_id,
            'bookingPaymentStatus' => $booking->payment_status,
            'editingBooking' => $booking,
        ]);
    }

    private function reservationView(string $view, string $pageTitle = 'Reservation', array $extraData = [])
    {
        $vehicles = Vehicle::with(['carSeat'])
            ->when(Schema::hasColumn('vehicles', 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('vehicle_name')
            ->get();
        $drivers = Schema::hasTable('drivers')
            ? Driver::query()->where('active', true)->orderBy('name')->get()
            : collect();
        $googleMapsApiKey = config('services.google_maps.api_key');
        $stripePublishableKey = config('services.stripe.key');
        $stripeEnabled = (bool) (config('services.stripe.secret') && $stripePublishableKey);
        $airlines = Schema::hasTable('airlines')
            ? Airline::query()->orderBy('name')->get()
            : (Schema::hasTable('airports')
                ? Airport::query()->orderBy('name')->get()
                : collect());
        $airports = Schema::hasTable('airports')
            ? Airport::query()->orderBy('iata_code')->get()
            : collect();
        $fbos = Schema::hasTable('fbos')
            ? Fbo::query()->active()->orderBy('sort_order')->orderBy('name')->get()
            : collect();
        $accounts = Account::query()
            ->with('billingContact')
            ->orderBy('company_name')
            ->get(['id', 'company_number', 'company_name', 'email', 'address', 'phone']);
        $childSeatPricePerSeatUsd = self::CHILD_SEAT_PRICE_PER_SEAT_USD;

        return view($view, array_merge(
            compact('vehicles', 'drivers', 'googleMapsApiKey', 'stripePublishableKey', 'stripeEnabled', 'airlines', 'airports', 'fbos', 'accounts', 'childSeatPricePerSeatUsd', 'pageTitle'),
            $extraData
        ));
    }

    public function quote(Request $request)
    {
        $data = $this->validateTrip($request);

        // Outbound only (matches public site: return is priced after vehicle choice, e.g. /calculate-return-trip).
        $vehicles = Vehicle::with(['carSeat'])
            ->when(Schema::hasColumn('vehicles', 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('id')
            ->get();
        $distanceData = [];

        foreach ($vehicles as $vehicle) {
            if ($data['service_type'] === 'hourlyHire') {
                $distanceData[$vehicle->id] = $this->pricing->calculateDistanceWithStops(
                    $data['pickup_location'],
                    null,
                    [],
                    (float) $vehicle->base_fare,
                    (float) $vehicle->base_hourly_fare,
                    (float) $vehicle->per_km_rate,
                    (int) $data['select_hours']
                );
            } else {
                $distanceData[$vehicle->id] = $this->pricing->calculateDistanceWithStops(
                    $data['pickup_location'],
                    $data['dropoff_location'],
                    [],
                    (float) $vehicle->base_fare,
                    null,
                    (float) $vehicle->per_km_rate,
                    null
                );
            }
        }

        $errors = [];
        foreach ($distanceData as $id => $row) {
            if (isset($row['error'])) {
                $errors[$id] = $row['error'];
            }
        }

        return response()->json([
            'distance' => $distanceData,
            'errors' => $errors,
            'service_type' => $data['service_type'],
        ]);
    }

    /**
     * Price the return leg for a chosen vehicle (same role as public /calculate-return-trip).
     * Route: drop-off → pick-up; locations come from the main trip.
     */
    public function returnQuote(Request $request)
    {
        $request->validate([
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'pickup_location' => ['required', 'string', 'max:500'],
            'dropoff_location' => ['required', 'string', 'max:500'],
            'service_type' => ['required', Rule::in(['pointToPoint'])],
        ]);

        $vehicle = Vehicle::query()->findOrFail($request->integer('vehicle_id'));
        $leg = $this->derivedReturnLegLocations([
            'pickup_location' => $request->input('pickup_location'),
            'dropoff_location' => $request->input('dropoff_location'),
        ]);

        if ($leg['pickup'] === '' || $leg['dropoff'] === '') {
            return response()->json(['success' => false, 'message' => 'Pick-up and drop-off are required to price the return leg.'], 422);
        }

        $breakdown = $this->pricing->calculateDistanceWithStops(
            $leg['pickup'],
            $leg['dropoff'],
            [],
            (float) $vehicle->base_fare,
            null,
            (float) $vehicle->per_km_rate,
            null
        );

        if (isset($breakdown['error'])) {
            return response()->json(['success' => false, 'message' => $breakdown['error']], 422);
        }

        return response()->json([
            'success' => true,
            'price' => round((float) $breakdown['price'], 2),
            'distance_km' => $breakdown['distance_km'] ?? null,
            'baseFare' => $breakdown['baseFare'] ?? null,
            'perKmRate' => $breakdown['perKmRate'] ?? null,
        ]);
    }

    /**
     * Account select options for reservation form (Select2 + fallback native select).
     */
    public function accountOptions(Request $request)
    {
        $q = trim((string) $request->input('q', ''));
        $query = Account::query()->with('billingContact')->orderBy('company_name');
        if ($q !== '') {
            $query->where(function ($w) use ($q) {
                $w->where('company_name', 'like', '%' . $q . '%')
                    ->orWhere('company_number', 'like', '%' . $q . '%')
                    ->orWhere('email', 'like', '%' . $q . '%');
            });
        }

        $rows = $query->limit(100)->get(['id', 'company_number', 'company_name', 'email', 'address', 'phone']);
        $items = $rows->map(function (Account $acc) {
            return [
                'id' => $acc->id,
                'text' => $acc->company_name . ($acc->company_number ? ' (' . $acc->company_number . ')' : ''),
                'company_number' => $acc->company_number,
                'company_name' => $acc->company_name,
                'company_email' => $acc->email,
                'company_phone' => Account::formatUsPhone($acc->phone),
                'company_address' => $acc->address,
                'billing_name' => $acc->billingContact?->name,
                'billing_email' => $acc->billingContact?->email,
                'billing_phone' => $acc->billingContact ? Account::formatUsPhone($acc->billingContact->phone) : null,
            ];
        })->values();

        return response()->json(['results' => $items]);
    }

    public function store(Request $request)
    {
        $this->normalizeReservationRequest($request);

        $trip = $this->validateTrip($request);

        $saveWithoutPay = $request->boolean('save_without_pay');
        $stripeSecret = config('services.stripe.secret');
        $validated = $request->validate($this->reservationRules($request, (bool) ($stripeSecret && ! $saveWithoutPay)));

        $vehicle = Vehicle::with(['carSeat'])->findOrFail($validated['vehicle_id']);

        if ($trip['service_type'] === 'hourlyHire') {
            $breakdown = $this->pricing->calculateDistanceWithStops(
                $trip['pickup_location'],
                null,
                [],
                (float) $vehicle->base_fare,
                (float) $vehicle->base_hourly_fare,
                (float) $vehicle->per_km_rate,
                (int) $trip['select_hours']
            );
        } else {
            $breakdown = $this->pricing->calculateDistanceWithStops(
                $trip['pickup_location'],
                $trip['dropoff_location'],
                [],
                (float) $vehicle->base_fare,
                null,
                (float) $vehicle->per_km_rate,
                null
            );
        }

        if (isset($breakdown['error'])) {
            if ($request->expectsJson()) {
                return response()->json(['message' => $breakdown['error'], 'errors' => ['trip' => [$breakdown['error']]]], 422);
            }

            return back()->withErrors(['trip' => $breakdown['error']])->withInput();
        }

        $basePrice = (float) $breakdown['price'];
        $returnBreakdown = null;
        $returnPrice = 0.0;

        $returnLeg = $this->derivedReturnLegLocations($trip);

        if (! empty($validated['return_service']) && $trip['service_type'] === 'pointToPoint') {
            $returnBreakdown = $this->pricing->calculateDistanceWithStops(
                $returnLeg['pickup'],
                $returnLeg['dropoff'],
                [],
                (float) $vehicle->base_fare,
                null,
                (float) $vehicle->per_km_rate,
                null
            );
            if (isset($returnBreakdown['error'])) {
                if ($request->expectsJson()) {
                    return response()->json(['message' => $returnBreakdown['error'], 'errors' => ['return' => [$returnBreakdown['error']]]], 422);
                }

                return back()->withErrors(['return' => $returnBreakdown['error']])->withInput();
            }
            $returnPrice = (float) $returnBreakdown['price'];
        }

        $calculatedTotalPrice = round($basePrice + $returnPrice, 2);
        $baseTripTotal = array_key_exists('custom_total_price', $validated) && $validated['custom_total_price'] !== null
            ? round((float) $validated['custom_total_price'], 2)
            : $calculatedTotalPrice;

        $wantsChildSeat = $request->boolean('child_seat_required');
        $childSeatType = $wantsChildSeat ? ($validated['child_seat_type'] ?? null) : null;
        $childSeatQty = $wantsChildSeat ? ($validated['child_seat_quantity'] ?? null) : null;
        $childSeatQtyInt = ($wantsChildSeat && $childSeatQty !== null && $childSeatQty !== '')
            ? max(1, (int) $childSeatQty)
            : 0;
        $childSeatFee = ($wantsChildSeat && $childSeatType && $childSeatQtyInt > 0)
            ? round(self::CHILD_SEAT_PRICE_PER_SEAT_USD * $childSeatQtyInt, 2)
            : 0.0;

        $totalPrice = round($baseTripTotal + $childSeatFee, 2);
        $forOthers = $request->boolean('booking_for_someone_else');
        $wantsFlightFields = $request->boolean('no_flight_info');

        $draftBooking = null;
        $draftBookingId = (int) ($validated['draft_booking_id'] ?? $request->input('draft_booking_id') ?? 0);
        if ($draftBookingId > 0 && Schema::hasColumn('bookings', 'is_draft')) {
            $draftBooking = Booking::query()
                ->where('id', $draftBookingId)
                ->where('is_draft', true)
                ->where('draft_user_id', auth()->id())
                ->first();
        }

        try {
            $booking = DB::transaction(function () use ($trip, $validated, $vehicle, $breakdown, $returnBreakdown, $totalPrice, $forOthers, $returnLeg, $wantsFlightFields, $childSeatFee, $childSeatType, $childSeatQty, $draftBooking) {
                $booker = null;
                if ($forOthers) {
                    $booker = Booker::create([
                        'first_name' => $validated['booker_first_name'],
                        'last_name' => $validated['booker_last_name'],
                        'email' => $this->contactField($validated['booker_email'] ?? null),
                        'phone_number' => $this->contactField($validated['booker_number'] ?? null),
                    ]);
                }

                $returnServiceId = null;
                if (! empty($validated['return_service']) && $trip['service_type'] === 'pointToPoint') {
                    $rs = ReturnService::create([
                        'vehicle_id' => $vehicle->id,
                        'pickup_location' => $returnLeg['pickup'],
                        'dropoff_location' => $returnLeg['dropoff'],
                        'pickup_date' => Carbon::createFromFormat('Y-m-d', $validated['return_pickup_date'])->format('Y-m-d'),
                        'pickup_time' => $validated['return_pickup_time'],
                    ]);
                    $returnServiceId = $rs->id;
                }

                $customBookingId = $this->nextPublicBookingId();
                $dropoff = (string) ($trip['dropoff_location'] ?? '');
                $hours = $trip['service_type'] === 'hourlyHire' ? (int) $trip['select_hours'] : null;
                $accountSnapshot = $this->selectedAccountSnapshot($validated);
                $stopLocations = $this->cleanStopLocations($validated['stop_locations'] ?? []);
                $routingInformation = $this->cleanRoutingInformation($validated['routing_information'] ?? []);

                $bookingPayload = [
                    'booker_id' => $booker?->id,
                    'booking_id' => $draftBooking?->booking_id ?: $customBookingId,
                    'user_id' => null,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => ! empty($validated['driver_id']) ? (int) $validated['driver_id'] : null,
                    'pickup_location' => $trip['pickup_location'],
                    'dropoff_location' => $dropoff,
                    'stop_locations' => $stopLocations ?: null,
                    'pickup_date' => Carbon::createFromFormat('Y-m-d', $trip['pickup_date'])->format('Y-m-d'),
                    'pickup_time' => $trip['pickup_time'],
                    'total_price' => $totalPrice,
                    'payment_status' => 'Pending',
                    'return_service_id' => $returnServiceId,
                    'note' => $validated['note'] ?? null,
                    'po_client_ref' => filled($validated['po_client_ref'] ?? null) ? trim((string) $validated['po_client_ref']) : null,
                    'child_seat_type' => $childSeatFee > 0 ? $childSeatType : null,
                    'child_seat_quantity' => $childSeatFee > 0 ? (int) $childSeatQty : null,
                    'child_seat_fee' => $childSeatFee > 0 ? $childSeatFee : null,
                    'pax_count' => (int) $validated['pax_count'],
                    'luggage_count' => array_key_exists('luggage_count', $validated) && $validated['luggage_count'] !== null
                        ? (int) $validated['luggage_count']
                        : null,
                    'service_option' => $validated['service_option'],
                    'from_admin_reservation' => true,
                ];
                if (Schema::hasColumn('bookings', 'routing_information')) {
                    $bookingPayload['routing_information'] = $routingInformation ?: null;
                }
                if (Schema::hasColumn('bookings', 'is_draft')) {
                    $bookingPayload['is_draft'] = false;
                    $bookingPayload['draft_user_id'] = null;
                }

                if ($draftBooking) {
                    $draftBooking->fill($bookingPayload);
                    $draftBooking->save();
                    $booking = $draftBooking->fresh();

                    // Replace placeholder draft passengers with real passenger data
                    foreach ($booking->passengers as $oldPassenger) {
                        optional($oldPassenger->flightDetail)->delete();
                        $oldPassenger->delete();
                    }
                    if ($booking->breakdown) {
                        $booking->breakdown()->delete();
                    }
                } else {
                    $booking = Booking::create($bookingPayload);
                }

                $this->syncBookingAccountSnapshot($booking, $accountSnapshot);

                $passenger = $booking->passengers()->create([
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $this->contactField($validated['email'] ?? null),
                    'phone_number' => $this->contactField($validated['number'] ?? null),
                    'is_booking_for_others' => $forOthers,
                    'booker_first_name' => $forOthers ? ($validated['booker_first_name'] ?? '') : '',
                    'booker_last_name' => $forOthers ? ($validated['booker_last_name'] ?? '') : '',
                    'booker_email' => $forOthers ? $this->contactField($validated['booker_email'] ?? null) : '',
                    'booker_number' => $forOthers ? $this->contactField($validated['booker_number'] ?? null) : '',
                ]);

                $booking->breakdown()->create([
                    'booking_id' => $booking->id,
                    'base_fare' => $breakdown['baseFare'] ?? null,
                    'per_km_rate' => $breakdown['perKmRate'] ?? null,
                    'total_kms' => $trip['service_type'] === 'hourlyHire' ? null : ($breakdown['distance_km'] ?? null),
                    'hourly_fare' => $breakdown['hourlyFare'] ?? null,
                    'total_hours' => $hours,
                    'return_base_fare' => $returnBreakdown['baseFare'] ?? null,
                    'return_per_km_rate' => $returnBreakdown['perKmRate'] ?? null,
                    'return_total_kms' => $returnBreakdown['distance_km'] ?? null,
                ]);

                // Match public site: checkbox `no_flight_info` = "I have my flight details" (on = show/save fields).
                $hasFlight = ! empty($trip['is_airport'])
                    || ($wantsFlightFields && (
                        filled($validated['pickup_flight_details'] ?? null)
                        || filled($validated['flight_number'] ?? null)
                        || in_array($validated['meet_option'] ?? '', ['curbside', 'inside'], true)
                    ));

                if ($hasFlight) {
                    FlightDetail::create([
                        'passenger_id' => $passenger->id,
                        'pickup_flight_details' => $validated['pickup_flight_details'] ?? '',
                        'flight_number' => $validated['flight_number'] ?? '',
                        'meet_option' => $validated['meet_option'] ?? null,
                        'no_flight_info' => $wantsFlightFields,
                        'inside_pickup_fee' => 0,
                    ]);
                }

                return $booking->fresh(['vehicle', 'passengers', 'booker', 'breakdown', 'accountSnapshot']);
            });
        } catch (\Throwable $e) {
            report($e);

            if ($request->expectsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Could not save reservation: ' . $e->getMessage(),
                ], 500);
            }

            return back()->withErrors(['save' => 'Could not save reservation: ' . $e->getMessage()])->withInput();
        }

        $this->clearRoutingDraftForCurrentUser();

        $passenger = $booking->passengers->first();

        if ($saveWithoutPay) {
            if ($request->expectsJson()) {
                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->booking_id,
                    'redirect' => route('bookings.show', $booking->id),
                ]);
            }

            return redirect()
                ->route('bookings.show', $booking->id)
                ->with('success', 'Reservation #' . $booking->booking_id . ' saved without payment. Use “Send booking emails” to notify recipients.')
                ->with('show_booking_email_composer', true);
        }

        if ($stripeSecret) {
            try {
                \Stripe\Stripe::setApiKey($stripeSecret);
                $pmId = $validated['payment_method_id'];

                $customerDetails = $this->stripeCustomerDetails($validated, $forOthers);
                $customer = $this->findOrCreateStripeCustomer(
                    $customerDetails['email'],
                    $customerDetails['name'],
                    $customerDetails['phone']
                );

                $paymentMethod = \Stripe\PaymentMethod::retrieve($pmId);
                $paymentMethod->attach(['customer' => $customer->id]);

                // Admin reservation: authorize only; capture later in Stripe Dashboard or via API.
                $sanitizedPrice = (float) $totalPrice;
                $amountInCents = (int) round($sanitizedPrice * 100);

                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $customer->id,
                    'payment_method' => $pmId,
                    'capture_method' => 'manual',
                    'off_session' => true,
                    'confirm' => true,
                ]);

                $booking->stripe_customer_id = $customer->id;
                $booking->stripe_payment_method_id = $pmId;
                $booking->save();

                Payment::create([
                    'booking_id' => $booking->id,
                    'payment_method' => 'card',
                    'payment_status' => 'Pending',
                    'transaction_id' => $paymentIntent->id,
                    'amount' => $sanitizedPrice,
                ]);

                if ($paymentIntent->status === 'requires_action' || $paymentIntent->status === 'requires_source_action') {
                    return response()->json([
                        'success' => false,
                        'requires_action' => true,
                        'payment_intent_client_secret' => $paymentIntent->client_secret,
                        'booking_id' => $booking->booking_id,
                        'return_url' => route('bookings.show', $booking->id),
                    ]);
                }

                // Website only blocks requires_action + use_stripe_sdk; otherwise payment is accepted. Allow succeeded / processing.
                $paidLike = in_array($paymentIntent->status, ['succeeded', 'processing'], true);
                if (! $paidLike && $paymentIntent->status !== 'requires_capture') {
                    $this->deleteReservationCascade($booking);

                    return response()->json([
                        'success' => false,
                        'message' => 'Payment failed with status: ' . $paymentIntent->status,
                    ], 400);
                }

                $paymentRowStatus = ($paymentIntent->status === 'requires_capture') ? 'Authorized' : 'Paid';
                $bookingStatus = ($paymentIntent->status === 'requires_capture') ? 'Authorized' : 'Paid';

                $booking->payments()->where('transaction_id', $paymentIntent->id)->update(['payment_status' => $paymentRowStatus]);
                $booking->payment_status = $bookingStatus;
                $booking->save();

                $this->sendBookingEmails($booking);

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->booking_id,
                    'redirect' => route('bookings.show', $booking->id),
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                $this->deleteReservationCascade($booking);

                return response()->json([
                    'success' => false,
                    'message' => 'Stripe: ' . $e->getMessage(),
                ], 400);
            } catch (\Throwable $e) {
                report($e);
                $this->deleteReservationCascade($booking);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        Payment::create([
            'booking_id' => $booking->id,
            'payment_method' => 'admin_reservation',
            'payment_status' => 'Pending',
            'transaction_id' => 'ADMIN-' . $booking->id . '-' . time(),
            'amount' => $totalPrice,
        ]);

        $this->sendBookingEmails($booking);

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', 'Reservation #' . $booking->booking_id . ' created. Payment status: Pending. Confirmation emails sent.');
    }

    public function update(Request $request, Booking $booking)
    {
        $booking->load([
            'passengers.flightDetail',
            'booker',
            'returnService',
            'breakdown',
            'payments',
        ]);

        $this->normalizeReservationRequest($request);

        $trip = $this->validateTrip($request);
        $validated = $request->validate($this->reservationRules($request, false));
        $vehicle = Vehicle::with(['carSeat'])->findOrFail($validated['vehicle_id']);
        $attemptPayment = filled($request->input('payment_method_id'));

        if ($trip['service_type'] === 'hourlyHire') {
            $breakdown = $this->pricing->calculateDistanceWithStops(
                $trip['pickup_location'],
                null,
                [],
                (float) $vehicle->base_fare,
                (float) $vehicle->base_hourly_fare,
                (float) $vehicle->per_km_rate,
                (int) $trip['select_hours']
            );
        } else {
            $breakdown = $this->pricing->calculateDistanceWithStops(
                $trip['pickup_location'],
                $trip['dropoff_location'],
                [],
                (float) $vehicle->base_fare,
                null,
                (float) $vehicle->per_km_rate,
                null
            );
        }

        if (isset($breakdown['error'])) {
            return back()->withErrors(['trip' => $breakdown['error']])->withInput();
        }

        $basePrice = (float) $breakdown['price'];
        $returnBreakdown = null;
        $returnPrice = 0.0;
        $returnLeg = $this->derivedReturnLegLocations($trip);

        if (! empty($validated['return_service']) && $trip['service_type'] === 'pointToPoint') {
            $returnBreakdown = $this->pricing->calculateDistanceWithStops(
                $returnLeg['pickup'],
                $returnLeg['dropoff'],
                [],
                (float) $vehicle->base_fare,
                null,
                (float) $vehicle->per_km_rate,
                null
            );

            if (isset($returnBreakdown['error'])) {
                return back()->withErrors(['return' => $returnBreakdown['error']])->withInput();
            }

            $returnPrice = (float) $returnBreakdown['price'];
        }

        $calculatedTotalPrice = round($basePrice + $returnPrice, 2);
        $baseTripTotal = array_key_exists('custom_total_price', $validated) && $validated['custom_total_price'] !== null
            ? round((float) $validated['custom_total_price'], 2)
            : $calculatedTotalPrice;

        $wantsChildSeat = $request->boolean('child_seat_required');
        $childSeatType = $wantsChildSeat ? ($validated['child_seat_type'] ?? null) : null;
        $childSeatQty = $wantsChildSeat ? ($validated['child_seat_quantity'] ?? null) : null;
        $childSeatQtyInt = ($wantsChildSeat && $childSeatQty !== null && $childSeatQty !== '')
            ? max(1, (int) $childSeatQty)
            : 0;
        $childSeatFee = ($wantsChildSeat && $childSeatType && $childSeatQtyInt > 0)
            ? round(self::CHILD_SEAT_PRICE_PER_SEAT_USD * $childSeatQtyInt, 2)
            : 0.0;

        $totalPrice = round($baseTripTotal + $childSeatFee, 2);
        $forOthers = $request->boolean('booking_for_someone_else');
        $wantsFlightFields = $request->boolean('no_flight_info');
        $hadLockedPayment = $this->bookingHasLockedPayment($booking);
        $originalTotalPrice = (float) $booking->total_price;

        try {
            DB::transaction(function () use ($booking, $trip, $validated, $vehicle, $breakdown, $returnBreakdown, $totalPrice, $forOthers, $returnLeg, $wantsFlightFields, $childSeatFee, $childSeatType, $childSeatQty) {
                $booking->load([
                    'passengers.flightDetail',
                    'booker',
                    'returnService',
                    'breakdown',
                ]);

                $existingBooker = $booking->booker;
                $booker = null;
                if ($forOthers) {
                    if ($existingBooker) {
                        $existingBooker->update([
                            'first_name' => $validated['booker_first_name'],
                            'last_name' => $validated['booker_last_name'],
                            'email' => $this->contactField($validated['booker_email'] ?? null),
                            'phone_number' => $this->contactField($validated['booker_number'] ?? null),
                        ]);
                        $booker = $existingBooker;
                    } else {
                        $booker = Booker::create([
                            'first_name' => $validated['booker_first_name'],
                            'last_name' => $validated['booker_last_name'],
                            'email' => $this->contactField($validated['booker_email'] ?? null),
                            'phone_number' => $this->contactField($validated['booker_number'] ?? null),
                        ]);
                    }
                }

                $existingReturnService = $booking->returnService;
                $returnServiceId = null;
                if (! empty($validated['return_service']) && $trip['service_type'] === 'pointToPoint') {
                    $returnPayload = [
                        'vehicle_id' => $vehicle->id,
                        'pickup_location' => $returnLeg['pickup'],
                        'dropoff_location' => $returnLeg['dropoff'],
                        'pickup_date' => Carbon::createFromFormat('Y-m-d', $validated['return_pickup_date'])->format('Y-m-d'),
                        'pickup_time' => $validated['return_pickup_time'],
                    ];

                    if ($existingReturnService) {
                        $existingReturnService->update($returnPayload);
                        $returnServiceId = $existingReturnService->id;
                    } else {
                        $returnServiceId = ReturnService::create($returnPayload)->id;
                    }
                }

                $dropoff = (string) ($trip['dropoff_location'] ?? '');
                $hours = $trip['service_type'] === 'hourlyHire' ? (int) $trip['select_hours'] : null;
                $accountSnapshot = $this->selectedAccountSnapshot($validated);
                $stopLocations = $this->cleanStopLocations($validated['stop_locations'] ?? []);
                $routingInformation = $this->cleanRoutingInformation($validated['routing_information'] ?? []);

                $updatePayload = [
                    'booker_id' => $booker?->id,
                    'vehicle_id' => $vehicle->id,
                    'driver_id' => array_key_exists('driver_id', $validated)
                        ? (! empty($validated['driver_id']) ? (int) $validated['driver_id'] : null)
                        : $booking->driver_id,
                    'pickup_location' => $trip['pickup_location'],
                    'dropoff_location' => $dropoff,
                    'stop_locations' => $stopLocations ?: null,
                    'pickup_date' => Carbon::createFromFormat('Y-m-d', $trip['pickup_date'])->format('Y-m-d'),
                    'pickup_time' => $trip['pickup_time'],
                    'total_price' => $totalPrice,
                    'return_service_id' => $returnServiceId,
                    'note' => $validated['note'] ?? null,
                    'po_client_ref' => filled($validated['po_client_ref'] ?? null) ? trim((string) $validated['po_client_ref']) : null,
                    'child_seat_type' => $childSeatFee > 0 ? $childSeatType : null,
                    'child_seat_quantity' => $childSeatFee > 0 ? (int) $childSeatQty : null,
                    'child_seat_fee' => $childSeatFee > 0 ? $childSeatFee : null,
                    'pax_count' => (int) $validated['pax_count'],
                    'luggage_count' => array_key_exists('luggage_count', $validated) && $validated['luggage_count'] !== null
                        ? (int) $validated['luggage_count']
                        : null,
                    'service_option' => $validated['service_option'],
                    'from_admin_reservation' => true,
                ];
                if (Schema::hasColumn('bookings', 'routing_information')) {
                    $updatePayload['routing_information'] = $routingInformation ?: null;
                }
                $booking->update($updatePayload);

                $this->syncBookingAccountSnapshot($booking, $accountSnapshot);

                if (! $forOthers && $existingBooker) {
                    $existingBooker->delete();
                }

                if (empty($validated['return_service']) || $trip['service_type'] !== 'pointToPoint') {
                    if ($existingReturnService) {
                        $existingReturnService->delete();
                    }
                }

                $passenger = $booking->passengers->first();
                $passengerPayload = [
                    'first_name' => $validated['first_name'],
                    'last_name' => $validated['last_name'],
                    'email' => $this->contactField($validated['email'] ?? null),
                    'phone_number' => $this->contactField($validated['number'] ?? null),
                    'is_booking_for_others' => $forOthers,
                    'booker_first_name' => $forOthers ? ($validated['booker_first_name'] ?? '') : '',
                    'booker_last_name' => $forOthers ? ($validated['booker_last_name'] ?? '') : '',
                    'booker_email' => $forOthers ? $this->contactField($validated['booker_email'] ?? null) : '',
                    'booker_number' => $forOthers ? $this->contactField($validated['booker_number'] ?? null) : '',
                ];

                if ($passenger) {
                    $passenger->update($passengerPayload);
                } else {
                    $passenger = $booking->passengers()->create($passengerPayload);
                }

                $breakdownPayload = [
                    'booking_id' => $booking->id,
                    'base_fare' => $breakdown['baseFare'] ?? null,
                    'per_km_rate' => $breakdown['perKmRate'] ?? null,
                    'total_kms' => $trip['service_type'] === 'hourlyHire' ? null : ($breakdown['distance_km'] ?? null),
                    'hourly_fare' => $breakdown['hourlyFare'] ?? null,
                    'total_hours' => $hours,
                    'return_base_fare' => $returnBreakdown['baseFare'] ?? null,
                    'return_per_km_rate' => $returnBreakdown['perKmRate'] ?? null,
                    'return_total_kms' => $returnBreakdown['distance_km'] ?? null,
                ];

                if ($booking->breakdown) {
                    $booking->breakdown->update($breakdownPayload);
                } else {
                    $booking->breakdown()->create($breakdownPayload);
                }

                $hasFlight = ! empty($trip['is_airport'])
                    || ($wantsFlightFields && (
                        filled($validated['pickup_flight_details'] ?? null)
                        || filled($validated['flight_number'] ?? null)
                        || in_array($validated['meet_option'] ?? '', ['curbside', 'inside'], true)
                    ));

                if ($hasFlight) {
                    $flightPayload = [
                        'pickup_flight_details' => $validated['pickup_flight_details'] ?? '',
                        'flight_number' => $validated['flight_number'] ?? '',
                        'meet_option' => $validated['meet_option'] ?? null,
                        'no_flight_info' => $wantsFlightFields,
                        'inside_pickup_fee' => 0,
                    ];

                    if ($passenger->flightDetail) {
                        $passenger->flightDetail->update($flightPayload);
                    } else {
                        $passenger->flightDetail()->create($flightPayload);
                    }
                } elseif ($passenger->flightDetail) {
                    $passenger->flightDetail()->delete();
                }

                $booking->payments()
                    ->where('payment_status', 'Pending')
                    ->update(['amount' => $totalPrice]);
            });
        } catch (\Throwable $e) {
            report($e);

            return back()->withErrors(['save' => 'Could not update reservation: ' . $e->getMessage()])->withInput();
        }

        if ($attemptPayment) {
            if ($hadLockedPayment) {
                $message = 'This reservation already has a paid or authorized payment and cannot be charged again from the edit screen.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 422);
                }

                return back()->withErrors(['payment' => $message])->withInput();
            }

            $stripeSecret = config('services.stripe.secret');
            if (! $stripeSecret) {
                $message = 'Stripe is not configured.';

                if ($request->expectsJson()) {
                    return response()->json(['success' => false, 'message' => $message], 500);
                }

                return back()->withErrors(['payment' => $message])->withInput();
            }

            try {
                \Stripe\Stripe::setApiKey($stripeSecret);

                $customerDetails = $this->stripeCustomerDetails($validated, $forOthers);
                $customer = $this->findOrCreateStripeCustomer(
                    $customerDetails['email'],
                    $customerDetails['name'],
                    $customerDetails['phone'],
                    $booking->stripe_customer_id
                );

                $pmId = (string) $request->input('payment_method_id');
                $paymentMethod = \Stripe\PaymentMethod::retrieve($pmId);
                if (empty($paymentMethod->customer)) {
                    $paymentMethod->attach(['customer' => $customer->id]);
                }

                $sanitizedPrice = (float) $totalPrice;
                $amountInCents = (int) round($sanitizedPrice * 100);

                $paymentIntent = \Stripe\PaymentIntent::create([
                    'amount' => $amountInCents,
                    'currency' => 'usd',
                    'customer' => $customer->id,
                    'payment_method' => $pmId,
                    'capture_method' => 'manual',
                    'off_session' => true,
                    'confirm' => true,
                ]);

                $booking->stripe_customer_id = $customer->id;
                $booking->stripe_payment_method_id = $pmId;
                $booking->save();

                $payment = $this->storePendingPaymentIntent($booking, $paymentIntent->id, $sanitizedPrice);

                if ($paymentIntent->status === 'requires_action' || $paymentIntent->status === 'requires_source_action') {
                    return response()->json([
                        'success' => false,
                        'requires_action' => true,
                        'payment_intent_client_secret' => $paymentIntent->client_secret,
                        'booking_id' => $booking->booking_id,
                        'return_url' => route('bookings.show', $booking->id),
                    ]);
                }

                $paidLike = in_array($paymentIntent->status, ['succeeded', 'processing'], true);
                if (! $paidLike && $paymentIntent->status !== 'requires_capture') {
                    return response()->json([
                        'success' => false,
                        'message' => 'Payment failed with status: ' . $paymentIntent->status . '. Reservation changes were saved.',
                    ], 400);
                }

                $paymentRowStatus = ($paymentIntent->status === 'requires_capture') ? 'Authorized' : 'Paid';
                $bookingStatus = ($paymentIntent->status === 'requires_capture') ? 'Authorized' : 'Paid';

                $payment->payment_status = $paymentRowStatus;
                $payment->save();
                $booking->payment_status = $bookingStatus;
                $booking->save();

                $this->sendBookingEmails($booking->fresh(['vehicle', 'passengers', 'booker', 'breakdown', 'accountSnapshot']));

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->booking_id,
                    'redirect' => route('bookings.show', $booking->id),
                ]);
            } catch (\Stripe\Exception\ApiErrorException $e) {
                return response()->json([
                    'success' => false,
                    'message' => 'Stripe: ' . $e->getMessage() . '. Reservation changes were saved.',
                ], 400);
            } catch (\Throwable $e) {
                report($e);

                return response()->json([
                    'success' => false,
                    'message' => $e->getMessage() . '. Reservation changes were saved.',
                ], 500);
            }
        }

        $message = 'Reservation #' . $booking->booking_id . ' updated successfully.';
        if (
            strtolower((string) $booking->payment_status) === 'authorized'
            && round($originalTotalPrice, 2) !== round($totalPrice, 2)
        ) {
            $message .= ' Booking price was updated in the system only. Stripe authorization was not changed.';
        } elseif ($hadLockedPayment && round($originalTotalPrice, 2) !== round($totalPrice, 2)) {
            $message .= ' Existing paid payment records were not changed.';
        }

        if ($request->boolean('embed') || $request->input('_embed')) {
            return redirect()
                ->route('bookings.edit-la', ['booking' => $booking, 'embed' => 1, 'saved' => 1])
                ->with('success', $message);
        }

        return redirect()
            ->route('bookings.show', $booking->id)
            ->with('success', $message);
    }

    public function finalizeReservation(Request $request)
    {
        $request->validate(['booking_id' => 'required|string']);

        $booking = Booking::where('booking_id', $request->booking_id)
            ->with(['payments', 'passengers', 'booker', 'vehicle', 'breakdown'])
            ->first();

        if (! $booking) {
            return response()->json(['success' => false, 'message' => 'Booking not found'], 404);
        }

        $payment = $booking->payments()
            ->where('payment_status', 'Pending')
            ->orderByDesc('created_at')
            ->first();

        if (! $payment) {
            $done = $booking->payments()->whereIn('payment_status', ['Authorized', 'Paid'])->first();
            if ($done) {
                return response()->json(['success' => true, 'booking_id' => $booking->booking_id, 'redirect' => route('bookings.show', $booking->id)]);
            }

            return response()->json(['success' => false, 'message' => 'No pending payment found.'], 404);
        }

        $stripeSecret = config('services.stripe.secret');
        if (! $stripeSecret) {
            return response()->json(['success' => false, 'message' => 'Stripe not configured.'], 500);
        }

        try {
            \Stripe\Stripe::setApiKey($stripeSecret);
            $intent = \Stripe\PaymentIntent::retrieve($payment->transaction_id);

            if (in_array($intent->status, ['succeeded', 'processing'], true)) {
                $payment->payment_status = 'Paid';
                $payment->save();
                $booking->payment_status = 'Paid';
                $booking->save();

                $this->sendBookingEmails($booking->fresh(['vehicle', 'passengers', 'booker', 'breakdown', 'accountSnapshot']));

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->booking_id,
                    'redirect' => route('bookings.show', $booking->id),
                ]);
            }

            if ($intent->status === 'requires_capture') {
                $payment->payment_status = 'Authorized';
                $payment->save();
                $booking->payment_status = 'Authorized';
                $booking->save();

                $this->sendBookingEmails($booking->fresh(['vehicle', 'passengers', 'booker', 'breakdown', 'accountSnapshot']));

                return response()->json([
                    'success' => true,
                    'booking_id' => $booking->booking_id,
                    'redirect' => route('bookings.show', $booking->id),
                ]);
            }

            return response()->json(['success' => false, 'message' => 'Payment not complete yet. Status: ' . $intent->status]);
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['success' => false, 'message' => $e->getMessage()], 500);
        }
    }

    private function sendBookingEmails(Booking $booking): void
    {
        try {
            $booking->load(['vehicle', 'passengers', 'booker', 'breakdown', 'accountSnapshot']);
            $bookingData = BookingEmailPayloadBuilder::build($booking);

            (new CreateBookingDocs($bookingData, (string) $booking->booking_id))->handle();
        } catch (\Throwable $e) {
            Log::error('sendBookingEmails failed before or during CreateBookingDocs', [
                'booking_db_id' => $booking->id,
                'message' => $e->getMessage(),
                'exception' => $e,
            ]);
            report($e);
        }
    }

    private function deleteReservationCascade(?Booking $booking): void
    {
        if (! $booking) {
            return;
        }
        $booking->load('passengers');
        foreach ($booking->passengers as $p) {
            FlightDetail::where('passenger_id', $p->id)->delete();
        }
        $booking->payments()->delete();
        $booking->passengers()->delete();
        if ($booking->breakdown) {
            $booking->breakdown()->delete();
        }
        $rsId = $booking->return_service_id;
        $bookerId = $booking->booker_id;
        $booking->delete();
        if ($rsId) {
            ReturnService::where('id', $rsId)->delete();
        }
        if ($bookerId) {
            Booker::where('id', $bookerId)->delete();
        }
    }

    /**
     * Return trip follows the public site: from = outbound drop-off, to = outbound pick-up.
     */
    private function derivedReturnLegLocations(array $trip): array
    {
        return [
            'pickup' => (string) ($trip['dropoff_location'] ?? ''),
            'dropoff' => (string) ($trip['pickup_location'] ?? ''),
        ];
    }

    private function normalizeReservationRequest(Request $request): void
    {
        $meet = $request->input('meet_option');
        if ($meet === 'none' || $meet === '') {
            $request->merge(['meet_option' => null]);
        }

        if ($request->input('luggage_count') === '' || $request->input('luggage_count') === null) {
            $request->merge(['luggage_count' => null]);
        }

        if ($request->input('pax_count') === '' || $request->input('pax_count') === null) {
            $request->merge(['pax_count' => 1]);
        }

        foreach (['email', 'number', 'booker_email', 'booker_number'] as $field) {
            if ($request->input($field) === '') {
                $request->merge([$field => null]);
            }
        }

        if ($request->input('payment_method_id') === '') {
            $request->merge(['payment_method_id' => null]);
        }

        $request->merge([
            'stop_locations' => $this->cleanStopLocations($request->input('stop_locations', [])),
            'routing_information' => $this->cleanRoutingInformation(
                $this->decodeRoutingInformationInput($request->input('routing_information'))
            ),
        ]);

        $this->mergeServiceOptionToServiceType($request);
    }

    private function bookingHasLockedPayment(Booking $booking): bool
    {
        $status = strtolower(trim((string) $booking->payment_status));
        if (in_array($status, ['paid', 'authorized'], true)) {
            return true;
        }

        return $booking->payments()->whereIn('payment_status', ['Paid', 'Authorized'])->exists();
    }

    private function stripeCustomerDetails(array $validated, bool $forOthers): array
    {
        $name = $forOthers
            ? trim(($validated['booker_first_name'] ?? '') . ' ' . ($validated['booker_last_name'] ?? ''))
            : trim(($validated['first_name'] ?? '') . ' ' . ($validated['last_name'] ?? ''));

        return [
            'name' => $name,
            'email' => $forOthers ? ($validated['booker_email'] ?? '') : ($validated['email'] ?? ''),
            'phone' => $forOthers ? ($validated['booker_number'] ?? '') : ($validated['number'] ?? ''),
        ];
    }

    private function findOrCreateStripeCustomer(string $email, string $name, string $phone, ?string $existingCustomerId = null): \Stripe\Customer
    {
        if ($existingCustomerId) {
            try {
                $customer = \Stripe\Customer::retrieve($existingCustomerId);
                if (! empty($customer->id)) {
                    return $customer;
                }
            } catch (\Throwable $e) {
                // Fall back to email lookup / create.
            }
        }

        $email = trim($email);
        $name = trim($name);
        $phone = trim($phone);

        if ($email !== '') {
            $existing = \Stripe\Customer::all(['email' => $email, 'limit' => 1]);
            if (count($existing->data) > 0) {
                return $existing->data[0];
            }
        }

        $params = [];
        if ($name !== '') {
            $params['name'] = $name;
        }
        if ($email !== '') {
            $params['email'] = $email;
        }
        if ($phone !== '') {
            $params['phone'] = $phone;
        }

        return \Stripe\Customer::create($params);
    }

    private function storePendingPaymentIntent(Booking $booking, string $transactionId, float $amount): Payment
    {
        $payment = $booking->payments()
            ->where('payment_status', 'Pending')
            ->latest('id')
            ->first();

        if ($payment) {
            $payment->update([
                'payment_method' => 'card',
                'transaction_id' => $transactionId,
                'amount' => $amount,
            ]);

            return $payment->fresh();
        }

        return $booking->payments()->create([
            'payment_method' => 'card',
            'payment_status' => 'Pending',
            'transaction_id' => $transactionId,
            'amount' => $amount,
        ]);
    }

    private function reservationRules(Request $request, bool $requirePaymentMethod): array
    {
        $rules = [
            'vehicle_id' => ['required', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
            'account_id' => ['nullable', 'exists:accounts,id'],
            'first_name' => ['required', 'string', 'max:255'],
            'last_name' => ['required', 'string', 'max:255'],
            'email' => ['nullable', 'email', 'max:255'],
            'number' => ['nullable', 'string', 'max:30'],
            'custom_total_price' => ['nullable', 'numeric', 'min:0.01'],
            'booking_for_someone_else' => ['nullable', 'boolean'],
            'booker_first_name' => [Rule::requiredIf(fn () => $request->boolean('booking_for_someone_else')), 'nullable', 'string', 'max:255'],
            'booker_last_name' => [Rule::requiredIf(fn () => $request->boolean('booking_for_someone_else')), 'nullable', 'string', 'max:255'],
            'booker_email' => ['nullable', 'email', 'max:255'],
            'booker_number' => ['nullable', 'string', 'max:30'],
            'pickup_flight_details' => ['nullable', 'string', 'max:500'],
            'flight_number' => ['nullable', 'string', 'max:50'],
            'meet_option' => ['nullable', 'string', Rule::in(['curbside', 'inside'])],
            'no_flight_info' => ['nullable', 'boolean'],
            'return_service' => ['nullable', 'boolean'],
            'return_pickup_date' => [Rule::requiredIf(fn () => $request->boolean('return_service') && $request->input('service_type') === 'pointToPoint'), 'nullable', 'date_format:Y-m-d'],
            'return_pickup_time' => [Rule::requiredIf(fn () => $request->boolean('return_service') && $request->input('service_type') === 'pointToPoint'), 'nullable', 'date_format:H:i'],
            'note' => ['nullable', 'string', 'max:4000'],
            'po_client_ref' => ['nullable', 'string', 'max:100'],
            'child_seat_required' => ['nullable', 'boolean'],
            'child_seat_type' => [
                Rule::requiredIf(fn () => $request->boolean('child_seat_required')),
                'nullable',
                'string',
                Rule::in(['forward_toddler', 'rear_infant', 'booster']),
            ],
            'child_seat_quantity' => [
                Rule::requiredIf(fn () => $request->boolean('child_seat_required')),
                'nullable',
                'integer',
                'min:1',
                'max:20',
            ],
            'pax_count' => ['required', 'integer', 'min:1', 'max:99'],
            'luggage_count' => ['nullable', 'integer', 'min:0', 'max:99'],
            'service_option' => ['required', 'string', Rule::in(['from_airport', 'to_airport', 'point_to_point', 'hourly_as_directed'])],
            'stop_locations' => ['nullable', 'array'],
            'stop_locations.*' => ['nullable', 'string', 'max:500'],
            'routing_information' => ['nullable', 'array'],
            'routing_information.*.type' => ['nullable', 'string', 'max:20'],
            'routing_information.*.label' => ['nullable', 'string', 'max:500'],
            'routing_information.*.submit_value' => ['nullable', 'string', 'max:1000'],
            'routing_information.*.source' => ['nullable', 'string', 'max:50'],
            'routing_information.*.payload' => ['nullable', 'array'],
            'draft_booking_id' => ['nullable', 'integer'],
        ];

        if ($requirePaymentMethod) {
            $rules['payment_method_id'] = ['required', 'string'];
        } else {
            $rules['payment_method_id'] = ['nullable', 'string'];
        }

        return $rules;
    }

    private function reservationFormDefaults(Booking $booking): array
    {
        $passenger = $booking->passengers->first();
        $flight = $passenger?->flightDetail;
        $booker = $booking->booker;
        $returnService = $booking->returnService;
        $breakdown = $booking->breakdown;
        $childSeatFee = (float) ($booking->child_seat_fee ?? 0);
        $customTripAmount = round(max(0, (float) $booking->total_price - $childSeatFee), 2);
        $hasFlightDetails = (bool) $flight && (
            (bool) $flight->no_flight_info
            || filled($flight->pickup_flight_details)
            || filled($flight->flight_number)
            || filled($flight->meet_option)
        );
        $serviceOption = $booking->service_option;
        if (! $serviceOption) {
            $serviceOption = ($breakdown && filled($breakdown->total_hours))
                ? 'hourly_as_directed'
                : 'point_to_point';
        }

        return [
            'first_name' => $passenger?->first_name,
            'last_name' => $passenger?->last_name,
            'email' => $passenger?->email,
            'number' => $passenger?->phone_number,
            'booking_for_someone_else' => (bool) ($passenger?->is_booking_for_others || $booker),
            'booker_first_name' => $booker?->first_name ?? $passenger?->booker_first_name,
            'booker_last_name' => $booker?->last_name ?? $passenger?->booker_last_name,
            'booker_email' => $booker?->email ?? $passenger?->booker_email,
            'booker_number' => $booker?->phone_number ?? $passenger?->booker_number,
            'pickup_location' => $booking->pickup_location,
            'dropoff_location' => $booking->dropoff_location,
            'stop_locations' => array_values(array_filter((array) ($booking->stop_locations ?? []), fn ($v) => is_string($v) && trim($v) !== '')),
            'routing_information' => is_array($booking->routing_information ?? null) ? $booking->routing_information : [],
            'pickup_date' => $booking->pickup_date
                ? \Carbon\Carbon::parse($booking->pickup_date)->format('Y-m-d')
                : null,
            'pickup_time' => substr((string) $booking->pickup_time, 0, 5),
            'return_service' => (bool) $returnService,
            'return_pickup_date' => $returnService?->pickup_date,
            'return_pickup_time' => $returnService ? substr((string) $returnService->pickup_time, 0, 5) : null,
            'no_flight_info' => $hasFlightDetails,
            'pickup_flight_details' => $flight?->pickup_flight_details,
            'flight_number' => $flight?->flight_number,
            'meet_option' => $flight?->meet_option,
            'note' => $booking->note,
            'po_client_ref' => $booking->po_client_ref,
            'child_seat_required' => filled($booking->child_seat_type) && (int) ($booking->child_seat_quantity ?? 0) > 0,
            'child_seat_type' => $booking->child_seat_type,
            'child_seat_quantity' => $booking->child_seat_quantity,
            'pax_count' => $booking->pax_count ?: 1,
            'luggage_count' => $booking->luggage_count,
            'service_option' => $serviceOption,
            'vehicle_id' => $booking->vehicle_id,
            'driver_id' => $booking->driver_id,
            'account_id' => $booking->accountSnapshot?->account_id,
            'account_company_number' => $booking->accountSnapshot?->account_company_number,
            'account_company_name' => $booking->accountSnapshot?->account_company_name,
            'account_company_email' => $booking->accountSnapshot?->account_company_email,
            'account_company_phone' => $booking->accountSnapshot?->account_company_phone,
            'account_company_address' => $booking->accountSnapshot?->account_company_address,
            'account_billing_name' => $booking->accountSnapshot?->account_billing_name,
            'account_billing_email' => $booking->accountSnapshot?->account_billing_email,
            'account_billing_phone' => $booking->accountSnapshot?->account_billing_phone,
            'select_hours' => $breakdown?->total_hours ?: 3,
            'custom_total_price' => $customTripAmount > 0 ? $customTripAmount : null,
            'is_airport' => in_array($serviceOption, ['from_airport', 'to_airport'], true) || $hasFlightDetails,
        ];
    }

    private function selectedAccountSnapshot(array $validated): array
    {
        $id = (isset($validated['account_id']) && $validated['account_id'] !== null)
            ? (int) $validated['account_id']
            : null;

        if (! $id) {
            return [
                'account_id' => null,
                'account_company_number' => null,
                'account_company_name' => null,
                'account_company_email' => null,
                'account_company_phone' => null,
                'account_company_address' => null,
                'account_billing_name' => null,
                'account_billing_email' => null,
                'account_billing_phone' => null,
            ];
        }

        $account = Account::query()->with('billingContact')->find($id);
        if (! $account) {
            return [
                'account_id' => null,
                'account_company_number' => null,
                'account_company_name' => null,
                'account_company_email' => null,
                'account_company_phone' => null,
                'account_company_address' => null,
                'account_billing_name' => null,
                'account_billing_email' => null,
                'account_billing_phone' => null,
            ];
        }

        return [
            'account_id' => $account->id,
            'account_company_number' => $account->company_number,
            'account_company_name' => $account->company_name,
            'account_company_email' => $account->email,
            'account_company_phone' => Account::formatUsPhone($account->phone),
            'account_company_address' => $account->address,
            'account_billing_name' => $account->billingContact?->name,
            'account_billing_email' => $account->billingContact?->email,
            'account_billing_phone' => Account::formatUsPhone($account->billingContact?->phone),
        ];
    }

    private function syncBookingAccountSnapshot(Booking $booking, array $accountSnapshot): void
    {
        $accountId = $accountSnapshot['account_id'] ?? null;
        $hasCompanyOrBilling = collect($accountSnapshot)
            ->except('account_id')
            ->contains(fn ($v) => filled($v));

        if (! filled($accountId) && ! $hasCompanyOrBilling) {
            $booking->accountSnapshot()->delete();

            return;
        }

        BookingAccountSnapshot::query()->updateOrCreate(
            ['booking_id' => $booking->id],
            [
                'account_id' => filled($accountId) ? $accountId : null,
                'account_company_number' => $accountSnapshot['account_company_number'] ?? null,
                'account_company_name' => $accountSnapshot['account_company_name'] ?? null,
                'account_company_email' => $accountSnapshot['account_company_email'] ?? null,
                'account_company_phone' => $accountSnapshot['account_company_phone'] ?? null,
                'account_company_address' => $accountSnapshot['account_company_address'] ?? null,
                'account_billing_name' => $accountSnapshot['account_billing_name'] ?? null,
                'account_billing_email' => $accountSnapshot['account_billing_email'] ?? null,
                'account_billing_phone' => $accountSnapshot['account_billing_phone'] ?? null,
            ]
        );
    }

    private function contactField(mixed $value): string
    {
        if ($value === null) {
            return '';
        }

        return trim((string) $value);
    }

    private function cleanStopLocations(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $out = [];
        foreach ($raw as $value) {
            if (! is_string($value)) {
                continue;
            }
            $trimmed = trim($value);
            if ($trimmed !== '') {
                $out[] = $trimmed;
            }
        }

        return array_values($out);
    }

    private function decodeRoutingInformationInput(mixed $raw): array
    {
        if (is_string($raw)) {
            $decoded = json_decode($raw, true);
            return is_array($decoded) ? $decoded : [];
        }

        return is_array($raw) ? $raw : [];
    }

    private function cleanRoutingInformation(mixed $raw): array
    {
        if (! is_array($raw)) {
            return [];
        }

        $allowedTypes = ['pickup', 'dropoff', 'stop', 'wait'];
        $out = [];

        foreach ($raw as $row) {
            if (! is_array($row)) {
                continue;
            }

            $type = strtolower(trim((string) ($row['type'] ?? '')));
            $label = trim((string) ($row['label'] ?? ''));
            $submitValue = trim((string) ($row['submit_value'] ?? $label));
            if (! in_array($type, $allowedTypes, true) || $label === '' || $submitValue === '') {
                continue;
            }

            $payload = $row['payload'] ?? [];
            if (! is_array($payload)) {
                $payload = [];
            }

            $out[] = [
                'type' => $type,
                'label' => mb_substr($label, 0, 500),
                'submit_value' => mb_substr($submitValue, 0, 1000),
                'source' => mb_substr(trim((string) ($row['source'] ?? ($payload['source'] ?? 'address'))), 0, 50),
                'payload' => $payload,
            ];
        }

        return array_values($out);
    }

    private function clearRoutingDraftForCurrentUser(): void
    {
        if (! Schema::hasTable('reservation_routing_drafts') || ! auth()->check()) {
            return;
        }

        ReservationRoutingDraft::query()->where('user_id', auth()->id())->delete();
    }

    /**
     * Maps admin service dropdown to pricing/trip validation (point-to-point vs hourly).
     * Legacy reservation form sends only `service_type` (radios); v2 sends `service_option`.
     */
    private function mergeServiceOptionToServiceType(Request $request): void
    {
        $option = $request->input('service_option');
        if ($option !== null && $option !== '') {
            $request->merge([
                'service_type' => $option === 'hourly_as_directed' ? 'hourlyHire' : 'pointToPoint',
            ]);

            return;
        }

        $st = $request->input('service_type');
        if ($st === 'hourlyHire') {
            $request->merge(['service_option' => 'hourly_as_directed']);
        } elseif ($st === 'pointToPoint' || (is_string($st) && $st !== '')) {
            $request->merge(['service_option' => 'point_to_point']);
        }
    }

    private function validateTrip(Request $request): array
    {
        $serviceType = $request->input('service_type');

        $rules = [
            'service_type' => ['required', Rule::in(['pointToPoint', 'hourlyHire'])],
            'pickup_location' => ['required', 'string', 'max:500'],
            'pickup_date' => ['required', 'date_format:Y-m-d'],
            'pickup_time' => ['required', 'date_format:H:i'],
            'is_airport' => ['nullable', 'boolean'],
        ];

        if ($serviceType === 'pointToPoint') {
            $rules['dropoff_location'] = ['required', 'string', 'max:500'];
        } elseif ($serviceType === 'hourlyHire') {
            $rules['dropoff_location'] = ['nullable', 'string', 'max:500'];
            $rules['select_hours'] = ['required', 'integer', 'min:1', 'max:24'];
        }

        return $request->validate($rules);
    }

    private function nextPublicBookingId(): int
    {
        $latest = Booking::orderBy('id', 'desc')->first();
        $lastNumericId = 41101;
        if ($latest && preg_match('/(\d+)/', (string) $latest->booking_id, $matches)) {
            $lastNumericId = (int) $matches[1] + 1;
        }

        return $lastNumericId;
    }
}
