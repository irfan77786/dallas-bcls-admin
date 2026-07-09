@php
    $childSeatRequired = $formBool('child_seat_required');
    $childSeatQty = (int) $formValue('child_seat_quantity', 0);
    $childSeatTypeOld = $formValue('child_seat_type');
    $childSeatRequiredSelected = ($childSeatRequired && $childSeatTypeOld) ? $childSeatTypeOld : '';
    $paxCount = (int) $formValue('pax_count', 1);
    $luggageCount = (int) $formValue('luggage_count', 0);
    $durationHours = (int) $formValue('select_hours', 1);
    $puDate = $formValue('pickup_date');
    $puTime = $formValue('pickup_time');
    $laDateTimeDisplay = '';
    if ($puDate && $puTime) {
        try {
            $laDateTimeDisplay = \Carbon\Carbon::parse($puDate . ' ' . $puTime)->format('m/d/Y h:i A');
        } catch (\Throwable $e) {
            $laDateTimeDisplay = $puDate . ' ' . $puTime;
        }
    }
    $laPricingLines = [
        'Fare', 'Per Hour', 'Extra Stops', 'Service Charge', 'Per Unit', 'OT/Wait Time',
        'STC Surch', 'Gratuity', 'Fuel Surch', 'Initial set of Miles', 'Remaining set of Miles',
        'Gratuity', 'Discount', 'Discount', 'International Arrival Fee', 'Child Seat Fee', 'Meet and Greet Fee',
    ];
    $laDiscountLines = ['Discount'];
@endphp
<div class="la-col la-col-right">
    <div class="la-right-body">
        {{-- Section 1: Date/Time, Res. By, Status --}}
        <div class="la-right-section">
            <div class="la-right-kv-row">
                <span class="la-right-kv-label">Date/Time:</span>
                <span class="la-right-kv-value" id="la-right-datetime">{{ $laDateTimeDisplay ?: '—' }}</span>
            </div>
            <div class="la-right-kv-row">
                <span class="la-right-kv-label">Res. By:</span>
                <span class="la-right-kv-value">Admin</span>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Status:</label>
                <select tabindex="-1">
                    <option selected>Unassigned</option>
                    <option>Assigned</option>
                    <option>Confirmed</option>
                </select>
            </div>
        </div>

        {{-- Section 2: Trip & passenger details --}}
        <div class="la-right-section">
            <div class="la-right-grid-4">
                <div class="la-right-field">
                    <label>Duration:</label>
                    <input type="number" id="la-duration" min="1" max="24" value="{{ $durationHours }}" step="1">
                </div>
                <div class="la-right-field">
                    <label>Est Drv Time:</label>
                    <input type="text" tabindex="-1" placeholder="">
                </div>
                <div class="la-right-field">
                    <label># of Pax:</label>
                    <input type="number" name="pax_count" id="pax_count" min="1" max="99" value="{{ $paxCount }}" required>
                </div>
                <div class="la-right-field">
                    <label>Luggage:</label>
                    <input type="number" name="luggage_count" id="luggage_count" min="0" max="99" value="{{ $luggageCount }}">
                </div>
            </div>
            <div class="la-right-grid-3">
                <div class="la-right-field">
                    <label>Accessible:</label>
                    <select tabindex="-1"><option selected>No</option><option>Yes</option></select>
                </div>
                <div class="la-right-field">
                    <label>Child Seat Required:</label>
                    <select id="la-child-seat-required-ui">
                        <option value="" @selected($childSeatRequiredSelected === '')>No</option>
                        <option value="forward_toddler" @selected($childSeatRequiredSelected === 'forward_toddler')>Yes, forward facing (Toddler)</option>
                        <option value="rear_infant" @selected($childSeatRequiredSelected === 'rear_infant')>Yes, rear facing (Infant)</option>
                        <option value="booster" @selected($childSeatRequiredSelected === 'booster')>Yes, Booster Seat</option>
                    </select>
                </div>
                <div class="la-right-field">
                    <label>Child Seat Count:</label>
                    <input type="number" id="la-child-seat-count-ui" min="0" max="20" value="{{ $childSeatQty }}" @disabled($childSeatRequiredSelected === '')>
                </div>
            </div>
            <div class="la-right-links la-child-seat-header">
                <a href="#" id="la-toggle-child-seats">Additional Child Seats</a>
                <span class="la-red-text">Total Seats (<span id="la-total-seats">{{ $childSeatQty }}</span>)</span>
            </div>
            <div class="la-child-seat-panel" id="la-child-seat-panel">
                <div class="la-child-seat-panel-title">Additional Child Seats Info</div>
                <div class="la-child-seat-add-row">
                    <div class="la-right-field">
                        <label>Child Seat Type:</label>
                        <select id="la-child-seat-type-ui">
                            <option value="forward_toddler" @selected($formValue('child_seat_type') === 'forward_toddler')>Forward facing (Toddler)</option>
                            <option value="rear_infant" @selected($formValue('child_seat_type') === 'rear_infant')>Rear facing (Infant)</option>
                            <option value="booster" @selected($formValue('child_seat_type') === 'booster')>Booster Seat</option>
                        </select>
                    </div>
                    <div class="la-right-field">
                        <label>Child Seat Count:</label>
                        <input type="number" id="la-child-seat-add-qty" min="1" max="20" value="1">
                    </div>
                    <button type="button" class="la-btn-add-seat" id="la-child-seat-add-btn">ADD</button>
                </div>
                <ul class="la-child-seat-added" id="la-child-seat-added-list"></ul>
            </div>
        </div>

        {{-- Section 3: Service & Vehicle --}}
        <div class="la-right-section">
            <div class="la-right-lr-row">
                <label class="la-right-lr-label la-lbl-green" for="service_option">Service Type</label>
                <select name="service_option" id="service_option" required>
                    <option value="from_airport" @selected($serviceOptionOld==='from_airport')>From Airport</option>
                    <option value="to_airport" @selected($serviceOptionOld==='to_airport')>To Airport</option>
                    <option value="point_to_point" @selected($serviceOptionOld==='point_to_point')>Point to point</option>
                    <option value="hourly_as_directed" @selected($serviceOptionOld==='hourly_as_directed')>Hourly / as directed</option>
                </select>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label la-lbl-green">Vehicle Type</label>
                <div class="la-compact-select" id="vehicle-select">
                    <button type="button" class="la-compact-select-trigger" aria-haspopup="listbox">
                        <span class="la-compact-select-value">{{ $selectedVehicleLabel }}</span>
                    </button>
                    <span class="la-compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                    <div class="la-compact-select-panel">
                        <input type="text" class="la-compact-select-search" placeholder="Search vehicle…" autocomplete="off">
                        <ul class="la-compact-select-list" role="listbox">
                            @foreach ($vehicles as $v)
                                @php $vLabel = $v->vehicle_name . ' (' . $v->number_of_passengers . ' PAX)'; @endphp
                                <li class="la-compact-select-option {{ (string) $formValue('vehicle_id') === (string) $v->id ? 'selected' : '' }}"
                                    data-value="{{ $v->id }}" data-label="{{ $vLabel }}" role="option">{{ $vLabel }}</li>
                            @endforeach
                        </ul>
                    </div>
                    <input type="hidden" name="vehicle_id" id="vehicle-id" value="{{ $formValue('vehicle_id') }}">
                </div>
            </div>
        </div>

        {{-- Section 4: Promo Code --}}
        <div class="la-right-section">
            <div class="la-right-lr-row">
                <label class="la-right-lr-label la-lbl-green">Promo Code</label>
                <select tabindex="-1"><option>---- NOT ASSIGNED ----</option></select>
            </div>
        </div>

        {{-- Section 5: Fulfillment type --}}
        <div class="la-right-section la-right-section-farm">
            <div class="la-right-radios">
                <label><input type="radio" name="la_aff" value="inhouse" checked tabindex="-1"> In-House</label>
                <label><input type="radio" name="la_aff" value="farmin" tabindex="-1"> Farm-in</label>
                <label><input type="radio" name="la_aff" value="farmout" tabindex="-1"> Farm-out</label>
            </div>
        </div>

        {{-- Section 6: Affiliate & farming --}}
        <div class="la-right-section">
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">eFarm-out:</label>
                <div class="la-right-toggle">
                    <label><input type="radio" name="la_efarm" value="manual" tabindex="-1"> Manually</label>
                    <label><input type="radio" name="la_efarm" value="lanet" checked tabindex="-1"> <i class="bi bi-globe-americas" style="font-size:11px;color:#06c;"></i> LA Net</label>
                </div>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">eFarm Status:</label>
                <input type="text" value="Not Farmed Out" readonly tabindex="-1">
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Affiliate:</label>
                <div class="la-right-affiliate-row">
                    <input type="text" tabindex="-1">
                    <button type="button" class="la-btn-mini" tabindex="-1">...</button>
                    <button type="button" class="la-btn-mini" tabindex="-1">CL</button>
                </div>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Reference #:</label>
                <input type="text" tabindex="-1">
            </div>
        </div>

        <div class="la-right-section">
        <div class="la-right-rate-btns">
            <button type="button" tabindex="-1">Apply Rate Table</button>
            <button type="button" tabindex="-1">Log Wait Time(s)</button>
        </div>

        {{-- Pricing tabs --}}
        <div class="la-price-tabs" data-la-price-tabs>
            <div class="la-price-tab active" data-price-tab="primary">Primary</div>
            <div class="la-price-tab" data-price-tab="secondary">Secondary</div>
            <div class="la-price-tab" data-price-tab="farmout">Farm-out Costs</div>
        </div>
        <div class="la-price-panel active" data-price-panel="primary">
            @foreach ($laPricingLines as $line)
                <div class="la-price-line {{ $line === 'Discount' ? 'la-discount' : '' }}"
                    @if($line === 'Child Seat Fee') id="la-price-child-seat-row" data-price-line="child-seat" @endif>
                    <span>{{ $line }}</span>
                    <input type="text" class="la-price-qty" value="{{ $line === 'Child Seat Fee' ? '0' : '1' }}" title="Qty"
                        @if($line === 'Child Seat Fee') id="la-price-child-seat-qty" @endif>
                    <input type="text" class="la-price-rate" value="{{ $line === 'Child Seat Fee' ? number_format($childSeatPricePerSeatUsd ?? 20, 2) : '0.00' }}" title="Rate"
                        @if($line === 'Child Seat Fee') id="la-price-child-seat-rate" @endif>
                    <input type="text" class="la-price-pct" value="0" title="%"
                        @if($line === 'Child Seat Fee') id="la-price-child-seat-pct" @endif>
                    <input type="text" class="la-price-total" value="0.00" readonly title="Total"
                        @if($line === 'Child Seat Fee') id="la-price-child-seat-total" @endif>
                </div>
            @endforeach
        </div>
        <div class="la-price-panel" data-price-panel="secondary">
            <div class="la-price-line"><span>Fare</span><input type="text" class="la-price-qty" value="1"><input type="text" class="la-price-rate" value="0.00"><input type="text" class="la-price-pct" value="0"><input type="text" class="la-price-total" value="0.00" readonly></div>
        </div>
        <div class="la-price-panel" data-price-panel="farmout">
            <div class="la-price-line"><span>Farm-out Cost</span><input type="text" class="la-price-qty" value="1"><input type="text" class="la-price-rate" value="0.00"><input type="text" class="la-price-pct" value="0"><input type="text" class="la-price-total" value="0.00" readonly></div>
        </div>

        <div class="la-right-totals la-totals">
            <div class="la-totals-row grand">
                <span>Grand Total</span>
                <span>
                    <select class="la-currency" tabindex="-1"><option>USD ($)</option></select>
                    <input type="number" name="custom_total_price" id="custom_total_price" min="0.01" step="0.01" value="{{ $formValue('custom_total_price') }}" placeholder="0.00">
                </span>
            </div>
            <div class="la-totals-row payments">
                <span>Payments/Deposits</span>
                <input type="text" id="la-payments-total" value="0.00" readonly tabindex="-1">
            </div>
            <div class="la-totals-row due">
                <span>Total Due</span>
                <input type="text" id="la-total-due" value="0.00" readonly tabindex="-1">
            </div>
        </div>
        </div>

        {{-- Driver / car assignment --}}
        <div class="la-right-section la-right-assign">
            <div class="la-price-tabs" data-la-assign-tabs>
                <div class="la-price-tab active" data-assign-tab="primary">Primary</div>
                <div class="la-price-tab" data-assign-tab="secondary">Secondary</div>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Driver:</label>
                <select tabindex="-1"><option>— Unassigned —</option></select>
            </div>
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Car:</label>
                <select tabindex="-1"><option>— Unassigned —</option></select>
            </div>
        </div>

        <div class="la-right-section">
            <div class="la-right-lr-row">
                <label class="la-right-lr-label">Rental Agreement:</label>
                <select tabindex="-1"><option>—</option></select>
            </div>
        </div>

        <div class="la-right-payment">
            <div class="la-right-payment-head">Payment</div>
            <div class="la-right-payment-body">
                <div class="la-right-payment-total">
                    <span>Total to charge</span>
                    <strong id="la-payment-total-display">$0.00</strong>
                </div>

                @if(!empty($stripeEnabled))
                    <input type="hidden" name="payment_method_id" id="payment_method_id" value="">
                    <div class="la-right-field">
                        <label for="card-name-reservation">Name on card <span class="text-danger">*</span></label>
                        <input type="text" id="card-name-reservation" class="form-control" autocomplete="cc-name" placeholder="As shown on card">
                    </div>
                    <div class="la-right-field">
                        <label>Card details <span class="text-danger">*</span></label>
                        <div id="reservation-card-element" class="form-control"></div>
                        <div id="reservation-card-errors" class="text-danger small mt-1"></div>
                    </div>
                    <div class="la-right-payment-actions">
                        <button type="button" class="la-btn-pay-reservation" id="btn-reservation-pay">
                            <span id="btn-reservation-text"><i class="bi bi-credit-card"></i> Pay &amp; create reservation</span>
                            <span id="btn-reservation-spinner" class="spinner-border spinner-border-sm d-none ml-1" role="status" aria-hidden="true"></span>
                        </button>
                        <button type="submit" name="save_without_pay" value="1" class="la-btn-save-without-pay" formnovalidate title="Save booking only — no charge">
                            Save without pay
                        </button>
                    </div>
                @else
                    <div class="la-right-payment-note">
                        Stripe not configured. Booking saves as <strong>Pending</strong>.
                    </div>
                    <div class="la-right-payment-actions">
                        <button type="submit" class="la-btn-pay-reservation" id="btn-reservation-submit-fallback">
                            <i class="bi bi-check"></i> Create reservation
                        </button>
                        <button type="submit" name="save_without_pay" value="1" class="la-btn-save-without-pay" formnovalidate title="Save booking only — no payment record">
                            Save without pay
                        </button>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <div class="la-right-footer">
        <button type="submit" name="save_without_pay" value="1" class="la-btn-save-reservation" formnovalidate>SAVE RESERVATION</button>
    </div>
</div>
