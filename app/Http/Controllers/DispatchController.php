<?php

namespace App\Http\Controllers;

use App\Jobs\SendDriverAssignmentEmails;
use App\Models\Booking;
use App\Models\Driver;
use App\Models\Vehicle;
use App\Services\BookingEmailPayloadBuilder;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Schema;

class DispatchController extends Controller
{
    public function index(Request $request)
    {
        $dateInput = trim((string) $request->input('date', now()->format('Y-m-d')));
        try {
            $date = Carbon::createFromFormat('Y-m-d', $dateInput)->startOfDay();
        } catch (\Throwable $e) {
            try {
                $date = Carbon::parse($dateInput)->startOfDay();
            } catch (\Throwable $e2) {
                $date = now()->startOfDay();
            }
        }

        $filterSubmitted = $request->query->has('new_live')
            || $request->query->has('in_house')
            || $request->query->has('farm_out')
            || $request->query->has('settled')
            || $request->query->has('farm_in')
            || $request->query->has('quotes')
            || $request->query->has('q')
            || $request->query->has('adv')
            || $request->query->has('date_mode')
            || $request->query->has('date');

        $include = [
            'new_live' => $filterSubmitted ? $request->boolean('new_live') : true,
            'in_house' => $filterSubmitted ? $request->boolean('in_house') : true,
            'farm_out' => $filterSubmitted ? $request->boolean('farm_out') : true,
            'settled' => $filterSubmitted ? $request->boolean('settled') : false,
            'farm_in' => $filterSubmitted ? $request->boolean('farm_in') : true,
            'quotes' => $filterSubmitted ? $request->boolean('quotes') : false,
        ];

        $search = trim((string) $request->input('q', ''));

        $dateMode = strtolower(trim((string) $request->input('date_mode', '')));
        if ($dateMode === '' || $dateMode === 'all') {
            $dateMode = 'today';
        }

        $dateFilter = $this->resolveDateFilter($request, $dateMode, $date);
        if ($dateFilter['mode'] === 'all') {
            $dateFilter = $this->resolveDateFilter($request, 'today', $date);
        }
        $dateMode = $dateFilter['mode'];
        $dateFrom = $dateFilter['from'];
        $dateTo = $dateFilter['to'];
        $date = $dateFilter['anchor'];

        $advanced = [
            'statuses' => array_values(array_filter((array) $request->input('statuses', []))),
            'cars' => array_values(array_filter((array) $request->input('cars', []))),
            'drivers' => array_values(array_filter((array) $request->input('drivers', []))),
            'vehicle_types' => array_values(array_filter((array) $request->input('vehicle_types', []))),
            'confirmation' => trim((string) $request->input('confirmation', '')),
            'account' => trim((string) $request->input('account', '')),
            'billing_contact' => trim((string) $request->input('billing_contact', '')),
            'pax_last' => trim((string) $request->input('pax_last', '')),
            'pax_first' => trim((string) $request->input('pax_first', '')),
            'company_name' => trim((string) $request->input('company_name', '')),
            'client_ref' => trim((string) $request->input('client_ref', '')),
        ];

        $advancedActive = collect($advanced)->contains(function ($value) {
            return is_array($value) ? count($value) > 0 : $value !== '';
        });

        $query = Booking::query()
            ->notDraft()
            ->with(['vehicle', 'passengers', 'booker', 'accountSnapshot', 'returnService', 'driver']);

        if ($dateFrom && $dateTo) {
            $query->whereDate('pickup_date', '>=', $dateFrom->toDateString())
                ->whereDate('pickup_date', '<=', $dateTo->toDateString());
        }

        $query->orderByDesc('pickup_date')->orderBy('pickup_time')->orderBy('id');

        if ($search !== '') {
            $query->where(function ($bookingQuery) use ($search) {
                $bookingQuery
                    ->where('booking_id', 'like', '%' . $search . '%')
                    ->orWhere('pickup_location', 'like', '%' . $search . '%')
                    ->orWhere('dropoff_location', 'like', '%' . $search . '%')
                    ->orWhere('payment_status', 'like', '%' . $search . '%')
                    ->orWhere('service_option', 'like', '%' . $search . '%')
                    ->orWhere('note', 'like', '%' . $search . '%')
                    ->orWhere('po_client_ref', 'like', '%' . $search . '%')
                    ->orWhereHas('vehicle', function ($vehicleQuery) use ($search) {
                        $vehicleQuery
                            ->where('vehicle_name', 'like', '%' . $search . '%')
                            ->orWhere('vehicle_code', 'like', '%' . $search . '%');
                    })
                    ->orWhereHas('passengers', function ($passengerQuery) use ($search) {
                        $passengerQuery
                            ->where('first_name', 'like', '%' . $search . '%')
                            ->orWhere('last_name', 'like', '%' . $search . '%')
                            ->orWhere('phone_number', 'like', '%' . $search . '%')
                            ->orWhereRaw("CONCAT(first_name, ' ', last_name) like ?", ['%' . $search . '%']);
                    })
                    ->orWhereHas('accountSnapshot', function ($accountQuery) use ($search) {
                        $accountQuery
                            ->where('account_company_number', 'like', '%' . $search . '%')
                            ->orWhere('account_company_name', 'like', '%' . $search . '%');
                    });
            });
        }

        if ($advanced['confirmation'] !== '') {
            $query->where('booking_id', 'like', '%' . $advanced['confirmation'] . '%');
        }

        if ($advanced['account'] !== '') {
            $account = $advanced['account'];
            $query->whereHas('accountSnapshot', function ($accountQuery) use ($account) {
                $accountQuery
                    ->where('account_company_number', 'like', '%' . $account . '%')
                    ->orWhere('account_id', 'like', '%' . $account . '%');
            });
        }

        if ($advanced['billing_contact'] !== '') {
            $billing = $advanced['billing_contact'];
            $query->whereHas('accountSnapshot', function ($accountQuery) use ($billing) {
                $accountQuery->where('account_billing_name', 'like', '%' . $billing . '%');
            });
        }

        if ($advanced['company_name'] !== '') {
            $company = $advanced['company_name'];
            $query->whereHas('accountSnapshot', function ($accountQuery) use ($company) {
                $accountQuery->where('account_company_name', 'like', '%' . $company . '%');
            });
        }

        if ($advanced['client_ref'] !== '') {
            $ref = $advanced['client_ref'];
            $query->where(function ($q) use ($ref) {
                $q->where('po_client_ref', 'like', '%' . $ref . '%')
                    ->orWhere('booking_id', 'like', '%' . $ref . '%')
                    ->orWhereHas('accountSnapshot', function ($accountQuery) use ($ref) {
                        $accountQuery
                            ->where('account_company_number', 'like', '%' . $ref . '%')
                            ->orWhere('account_company_name', 'like', '%' . $ref . '%');
                    });
            });
        }

        if ($advanced['pax_first'] !== '') {
            $first = $advanced['pax_first'];
            $query->whereHas('passengers', function ($passengerQuery) use ($first) {
                $passengerQuery->where('first_name', 'like', '%' . $first . '%');
            });
        }

        if ($advanced['pax_last'] !== '') {
            $last = $advanced['pax_last'];
            $query->whereHas('passengers', function ($passengerQuery) use ($last) {
                $passengerQuery->where('last_name', 'like', '%' . $last . '%');
            });
        }

        if (count($advanced['cars']) > 0) {
            $query->whereIn('vehicle_id', $advanced['cars']);
        }

        if (count($advanced['drivers']) > 0) {
            $query->whereIn('driver_id', $advanced['drivers']);
        }

        if (count($advanced['vehicle_types']) > 0) {
            $codes = $advanced['vehicle_types'];
            $query->whereHas('vehicle', function ($vehicleQuery) use ($codes) {
                $vehicleQuery->whereIn('vehicle_code', $codes);
            });
        }

        $bookings = $query->get()->map(function (Booking $booking) {
            return $this->mapDispatchRow($booking);
        })->values();

        if (! $include['quotes']) {
            $bookings = $bookings->reject(fn ($row) => $row['status_key'] === 'quote')->values();
        }

        if (count($advanced['statuses']) > 0) {
            $statuses = $advanced['statuses'];
            $bookings = $bookings->filter(fn ($row) => in_array($row['status_key'], $statuses, true))->values();
        }

        $vehicles = Vehicle::query()
            ->when(Schema::hasColumn('vehicles', 'sort_order'), fn ($q) => $q->orderBy('sort_order'))
            ->orderBy('vehicle_name')
            ->get(['id', 'vehicle_name', 'vehicle_code']);

        $drivers = Driver::query()
            ->where('active', true)
            ->orderBy('name')
            ->get(['id', 'name', 'phone', 'email', 'picture', 'plate_number', 'car_make', 'car_model', 'vehicle_type']);

        $vehicleTypes = $vehicles
            ->pluck('vehicle_code')
            ->filter()
            ->unique()
            ->sort()
            ->values();

        $tripStatusOptions = self::tripStatusOptions();
        $statusOptions = $tripStatusOptions;

        $dateDisplay = $this->formatDateDisplay($dateMode, $date, $dateFrom, $dateTo);
        $prevShift = $this->shiftDateFilter($dateMode, $date, $dateFrom, $dateTo, -1);
        $nextShift = $this->shiftDateFilter($dateMode, $date, $dateFrom, $dateTo, 1);

        return view('pages.dispatches.index', [
            'date' => $date,
            'dateMode' => $dateMode,
            'dateFrom' => $dateFrom,
            'dateTo' => $dateTo,
            'include' => $include,
            'search' => $search,
            'advanced' => $advanced,
            'advancedActive' => $advancedActive,
            'bookings' => $bookings,
            'vehicles' => $vehicles,
            'drivers' => $drivers,
            'vehicleTypes' => $vehicleTypes,
            'statusOptions' => $statusOptions,
            'tripStatusOptions' => $tripStatusOptions,
            'prevDate' => $prevShift['date'],
            'nextDate' => $nextShift['date'],
            'prevDateFrom' => $prevShift['from'],
            'prevDateTo' => $prevShift['to'],
            'nextDateFrom' => $nextShift['from'],
            'nextDateTo' => $nextShift['to'],
            'prevDateMode' => $prevShift['mode'],
            'nextDateMode' => $nextShift['mode'],
            'dateDisplay' => $dateDisplay,
            'dateValue' => $date->format('Y-m-d'),
            'dateFromValue' => $dateFrom?->format('Y-m-d') ?? $date->format('Y-m-d'),
            'dateToValue' => $dateTo?->format('Y-m-d') ?? $date->format('Y-m-d'),
            'updateUrlTemplate' => url('/dispatches/__ID__'),
        ]);
    }

    private function resolveDateFilter(Request $request, string $mode, Carbon $fallback): array
    {
        $now = now()->startOfDay();
        $parse = function (?string $value) use ($fallback): Carbon {
            $value = trim((string) $value);
            if ($value === '') {
                return $fallback->copy();
            }
            try {
                return Carbon::createFromFormat('Y-m-d', $value)->startOfDay();
            } catch (\Throwable $e) {
                try {
                    return Carbon::parse($value)->startOfDay();
                } catch (\Throwable $e2) {
                    return $fallback->copy();
                }
            }
        };

        $date = $parse($request->input('date', $fallback->format('Y-m-d')));

        return match ($mode) {
            'all' => [
                'mode' => 'all',
                'anchor' => $date,
                'from' => null,
                'to' => null,
            ],
            'today' => [
                'mode' => 'today',
                'anchor' => $now->copy(),
                'from' => $now->copy(),
                'to' => $now->copy(),
            ],
            'tomorrow' => [
                'mode' => 'tomorrow',
                'anchor' => $now->copy()->addDay(),
                'from' => $now->copy()->addDay(),
                'to' => $now->copy()->addDay(),
            ],
            'yesterday' => [
                'mode' => 'yesterday',
                'anchor' => $now->copy()->subDay(),
                'from' => $now->copy()->subDay(),
                'to' => $now->copy()->subDay(),
            ],
            'week' => [
                'mode' => 'week',
                'anchor' => $now->copy(),
                'from' => $now->copy()->startOfWeek(),
                'to' => $now->copy()->endOfWeek(),
            ],
            'month' => [
                'mode' => 'month',
                'anchor' => $now->copy(),
                'from' => $now->copy()->startOfMonth(),
                'to' => $now->copy()->endOfMonth(),
            ],
            'range' => (function () use ($parse, $request, $date) {
                $from = $parse($request->input('date_from', $date->format('Y-m-d')));
                $to = $parse($request->input('date_to', $date->format('Y-m-d')));
                if ($to->lt($from)) {
                    [$from, $to] = [$to, $from];
                }

                return [
                    'mode' => 'range',
                    'anchor' => $from->copy(),
                    'from' => $from,
                    'to' => $to,
                ];
            })(),
            default => [
                'mode' => 'specific',
                'anchor' => $date,
                'from' => $date->copy(),
                'to' => $date->copy(),
            ],
        };
    }

    private function formatDateDisplay(string $mode, Carbon $date, ?Carbon $from, ?Carbon $to): string
    {
        return match ($mode) {
            'all' => 'All Dates',
            'today' => 'Today ('.$date->format('m/d/Y').')',
            'tomorrow' => 'Tomorrow ('.$date->format('m/d/Y').')',
            'yesterday' => 'Yesterday ('.$date->format('m/d/Y').')',
            'week' => ($from && $to)
                ? $from->format('m/d/Y').' - '.$to->format('m/d/Y')
                : 'This Week',
            'month' => $date->format('M Y'),
            'range' => ($from && $to)
                ? ($from->equalTo($to)
                    ? $from->format('m/d/Y')
                    : $from->format('m/d/Y').' - '.$to->format('m/d/Y'))
                : $date->format('m/d/Y'),
            default => $date->format('m/d/Y'),
        };
    }

    private function shiftDateFilter(string $mode, Carbon $date, ?Carbon $from, ?Carbon $to, int $dir): array
    {
        if ($mode === 'all') {
            return [
                'mode' => 'all',
                'date' => $date->format('Y-m-d'),
                'from' => null,
                'to' => null,
            ];
        }

        if ($mode === 'week' || $mode === 'month' || $mode === 'range') {
            $from = ($from ?? $date)->copy()->addDays($dir);
            $to = ($to ?? $date)->copy()->addDays($dir);

            return [
                'mode' => $mode === 'week' || $mode === 'month' ? 'range' : $mode,
                'date' => $from->format('Y-m-d'),
                'from' => $from->format('Y-m-d'),
                'to' => $to->format('Y-m-d'),
            ];
        }

        $anchor = $date->copy()->addDays($dir);

        return [
            'mode' => 'specific',
            'date' => $anchor->format('Y-m-d'),
            'from' => $anchor->format('Y-m-d'),
            'to' => $anchor->format('Y-m-d'),
        ];
    }

    public function update(Request $request, Booking $booking)
    {
        $validated = $request->validate([
            'pickup_date' => ['nullable', 'string', 'max:40'],
            'pickup_time' => ['nullable', 'string', 'max:40'],
            'dropoff_time' => ['nullable', 'string', 'max:40'],
            'spot_time' => ['nullable', 'string', 'max:40'],
            'trip_status' => ['nullable', 'string', 'max:40', 'in:' . implode(',', array_keys(self::tripStatusOptions()))],
            'vehicle_id' => ['nullable', 'integer', 'exists:vehicles,id'],
            'driver_id' => ['nullable', 'integer', 'exists:drivers,id'],
        ]);

        $previousDriverId = $booking->driver_id ? (int) $booking->driver_id : null;

        if (array_key_exists('pickup_date', $validated) && filled($validated['pickup_date'])) {
            try {
                $booking->pickup_date = Carbon::parse($validated['pickup_date'])->format('Y-m-d');
            } catch (\Throwable $e) {
                $booking->pickup_date = $validated['pickup_date'];
            }
        }

        if (array_key_exists('pickup_time', $validated) && filled($validated['pickup_time'])) {
            try {
                $booking->pickup_time = Carbon::parse($validated['pickup_time'])->format('H:i:s');
            } catch (\Throwable $e) {
                $booking->pickup_time = $validated['pickup_time'];
            }
        }

        if (array_key_exists('dropoff_time', $validated)) {
            $booking->dropoff_time = filled($validated['dropoff_time']) ? $validated['dropoff_time'] : null;
        }

        if (array_key_exists('spot_time', $validated)) {
            $booking->spot_time = filled($validated['spot_time']) ? $validated['spot_time'] : null;
        }

        if (array_key_exists('trip_status', $validated)) {
            $booking->trip_status = $validated['trip_status'];
        }

        if (array_key_exists('vehicle_id', $validated)) {
            $booking->vehicle_id = $validated['vehicle_id'] ?: null;
        }

        if (array_key_exists('driver_id', $validated)) {
            $booking->driver_id = $validated['driver_id'] ?: null;
        }

        $booking->save();
        $booking->load(['vehicle', 'passengers', 'booker', 'accountSnapshot', 'returnService', 'driver']);

        $newDriverId = $booking->driver_id ? (int) $booking->driver_id : null;
        $driverAssignedOrChanged = $newDriverId !== null && $newDriverId !== $previousDriverId;

        if ($driverAssignedOrChanged) {
            // Run immediately so OK always triggers mail without needing a queue worker.
            SendDriverAssignmentEmails::dispatchSync($booking->id);
        }

        return response()->json([
            'ok' => true,
            'row' => $this->mapDispatchRow($booking),
            'driver_email_sent' => $driverAssignedOrChanged,
        ]);
    }

    public static function tripStatusOptions(): array
    {
        return [
            'unassigned' => 'Unassigned',
            'assigned' => 'Assigned',
            'offered' => 'Offered',
            'ontheway' => 'On The Way',
            'arrived' => 'Arrived',
            'customer_in_car' => 'Customer In Car',
            'done' => 'Done',
            'cancelled' => 'Cancelled',
            'late_cancel' => 'Late Cancel',
            'cancel_by_affiliate' => 'Cancel By Affiliate',
        ];
    }

    private function mapDispatchRow(Booking $booking): array
    {
        $passenger = $booking->passengers->first();
        $passengerName = $passenger
            ? trim(($passenger->first_name ?? '') . ' ' . ($passenger->last_name ?? ''))
            : '';
        $passengerPhone = $passenger->phone_number ?? '';
        $passengerEmail = trim((string) ($passenger->email ?? ''));
        if ($passengerEmail === '' && $booking->booker) {
            $passengerEmail = trim((string) ($booking->booker->email ?? ''));
        }

        $paymentStatus = strtolower(trim((string) ($booking->payment_status ?? '')));
        $canSendPaymentLink = ! in_array($paymentStatus, ['paid', 'authorized'], true)
            && (float) ($booking->total_price ?? 0) >= 0.5;

        $tripStatuses = self::tripStatusOptions();
        $storedStatus = strtolower(trim((string) ($booking->trip_status ?? '')));

        if ($storedStatus !== '' && isset($tripStatuses[$storedStatus])) {
            $statusKey = $storedStatus;
            $statusLabel = $tripStatuses[$storedStatus];
        } else {
            $payment = strtolower(trim((string) $booking->payment_status));
            $hasVehicle = filled($booking->vehicle_id);

            if (in_array($payment, ['paid', 'authorized', 'settled', 'done'], true)) {
                $statusKey = 'done';
                $statusLabel = 'Done';
            } elseif (filled($booking->vehicle_id) === false && in_array($payment, ['quote', 'quoted'], true)) {
                $statusKey = 'unassigned';
                $statusLabel = 'Unassigned';
            } elseif ($hasVehicle) {
                $statusKey = 'assigned';
                $statusLabel = 'Assigned';
            } else {
                $statusKey = 'unassigned';
                $statusLabel = 'Unassigned';
            }
        }

        $svcLabel = BookingEmailPayloadBuilder::serviceOptionLabel($booking->service_option);
        if ($svcLabel === '') {
            $svcLabel = (string) ($booking->service_option ?: '—');
        }

        $puDate = '';
        $puDateIso = '';
        $puTime = '';
        $doTime = (string) ($booking->dropoff_time ?? '');
        $spotTime = (string) ($booking->spot_time ?? '');

        if ($booking->pickup_date) {
            try {
                $parsedDate = Carbon::parse($booking->pickup_date);
                $puDate = $parsedDate->format('m/d/Y');
                $puDateIso = $parsedDate->format('Y-m-d');
            } catch (\Throwable $e) {
                $puDate = (string) $booking->pickup_date;
                $puDateIso = (string) $booking->pickup_date;
            }
        }
        if ($booking->pickup_time) {
            try {
                $puTime = Carbon::parse($booking->pickup_time)->format('g:i A');
            } catch (\Throwable $e) {
                $puTime = (string) $booking->pickup_time;
            }
        }

        $poRef = (string) ($booking->po_client_ref ?? '');
        if ($poRef === '') {
            $poRef = $booking->accountSnapshot?->account_company_number
                ?: '';
        }

        $vehicleCode = (string) ($booking->vehicle?->vehicle_code ?? '');
        $vehicleName = (string) ($booking->vehicle?->vehicle_name ?? '');
        $driverName = (string) ($booking->driver?->name ?? '');
        $driverPicture = $booking->driver?->pictureUrl();

        return [
            'id' => $booking->id,
            'svc_type' => $svcLabel,
            'conf' => $booking->booking_id ?: (string) $booking->id,
            'po_ref' => $poRef,
            'status_key' => $statusKey,
            'status_label' => $statusLabel,
            'has_note' => filled($booking->note),
            'note' => (string) ($booking->note ?? ''),
            'pu_date' => $puDate,
            'pu_date_iso' => $puDateIso,
            'pu_time' => $puTime,
            'do_time' => $doTime,
            'spot_time' => $spotTime,
            'pu_location' => (string) ($booking->pickup_location ?? ''),
            'do_location' => (string) ($booking->dropoff_location ?? ''),
            'veh_code' => $vehicleCode,
            'vehicle_id' => $booking->vehicle_id,
            'driver_id' => $booking->driver_id,
            'driver' => $driverName,
            'driver_picture' => $driverPicture,
            'car' => $vehicleName !== '' ? $vehicleName : $vehicleCode,
            'passenger_name' => $passengerName,
            'pax' => $booking->pax_count !== null ? $booking->pax_count : ($booking->passengers->count() ?: ''),
            'lug' => $booking->luggage_count !== null ? $booking->luggage_count : '',
            'priority' => '',
            'passenger_phone' => $passengerPhone,
            'passenger_email' => $passengerEmail,
            'can_send_payment_link' => $canSendPaymentLink,
            'is_round_trip' => filled($booking->return_service_id),
            'total' => (float) ($booking->total_price ?? 0),
            'payment_status' => (string) ($booking->payment_status ?? ''),
            'edit_url' => route('bookings.edit', $booking->id),
            'edit_la_url' => route('bookings.edit-la', $booking->id),
            'show_url' => route('bookings.show', $booking->id),
            'destroy_url' => route('bookings.destroy', $booking->id),
        ];
    }
}
