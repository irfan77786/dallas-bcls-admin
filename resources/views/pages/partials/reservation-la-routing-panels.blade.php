{{-- ADDRESS --}}
<div class="la-addr-type-panel active" data-addr-panel="address">
    <select class="la-stored-addr-select" tabindex="-1">
        <option>Stored Addresses</option>
    </select>
    <div class="la-field-row">
        <div class="la-field position-relative" style="grid-column: span 12;">
            <label>Location Description/Name</label>
            <input type="text" id="la-addr-location-name" tabindex="-1" autocomplete="off" spellcheck="false">
            <div id="la-addr-location-name-suggestions" class="location-suggestions" aria-live="polite"></div>
        </div>
        <div class="la-field position-relative" style="grid-column: span 12;">
            <label>Street Address Line 1</label>
            <input type="text" id="la-addr-street1" autocomplete="off" spellcheck="false">
            <div id="la-addr-street1-suggestions" class="location-suggestions" aria-live="polite"></div>
        </div>
        <div class="la-field" style="grid-column: span 12;">
            <label>Street Address Line 2</label>
            <input type="text" id="la-addr-street2" tabindex="-1" autocomplete="off">
        </div>
        <div class="la-field" style="grid-column: span 5;">
            <label>City</label>
            <input type="text" id="la-addr-city" tabindex="-1" autocomplete="off">
        </div>
        <div class="la-field" style="grid-column: span 3;">
            <label>State/Prov</label>
            <select id="la-addr-state" tabindex="-1">
                <option value="">—</option>
                @foreach($usStates as $st)
                    <option value="{{ $st }}">{{ $st }}</option>
                @endforeach
            </select>
        </div>
        <div class="la-field" style="grid-column: span 2;">
            <label>Zip/Post</label>
            <input type="text" id="la-addr-zip" tabindex="-1" autocomplete="off">
        </div>
        <div class="la-field" style="grid-column: span 2;">
            <label>Country</label>
            <select id="la-addr-country" tabindex="-1">
                <option value="United States" selected>United States</option>
            </select>
        </div>
    </div>
    @include('pages.partials.reservation-la-routing-notes')
</div>

{{-- AIRPORT --}}
@php
    $airlines = $airlines ?? collect();
    $airports = $airports ?? collect();
    $laAirlineCode = '';
    $laAirlineName = '';
    $laSelectedAirlineId = '';
    $laAirportCode = '';
    $laAirportName = '';
    $laSelectedAirportId = '';
    $pickupFlightOld = $pickupFlightOld ?? '';

    foreach ($airlines as $airline) {
        $display = ($airline->iata_code ? $airline->iata_code . ' - ' : '') . $airline->name;
        if ($pickupFlightOld === $display || $pickupFlightOld === $airline->name) {
            $laAirlineCode = $airline->iata_code ?? '';
            $laAirlineName = $airline->name;
            $laSelectedAirlineId = (string) $airline->id;
            break;
        }
    }

    if ($laAirlineName === '' && $pickupFlightOld !== '') {
        if (preg_match('/^([A-Za-z0-9]{2,3})\s*-\s*(.+)$/', $pickupFlightOld, $m)) {
            $laAirlineCode = strtoupper($m[1]);
            $laAirlineName = trim(preg_replace('/\s*\([^)]*\)\s*$/', '', $m[2]));
        } else {
            $laAirlineName = $pickupFlightOld;
        }
    }

    $laPickupFlightValue = $pickupFlightOld;
    if ($laAirlineName !== '') {
        $laPickupFlightValue = $laAirlineCode !== ''
            ? $laAirlineCode . ' - ' . $laAirlineName
            : $laAirlineName;
    }
@endphp
<div class="la-addr-type-panel" data-addr-panel="airport">
    <div class="la-stored-row">
        <div class="la-field">
            <label>Stored Airports</label>
            <div class="la-stored-with-btn">
                <select class="la-stored-cyan" id="la-stored-airport">
                    <option value=""></option>
                    @foreach($airports as $airport)
                        @php
                            $airportLabel = ($airport->iata_code ? $airport->iata_code . ' - ' : '') . $airport->name;
                        @endphp
                        <option value="{{ $airport->id }}"
                            data-code="{{ $airport->iata_code }}"
                            data-name="{{ $airport->name }}"
                            @selected((string) $laSelectedAirportId === (string) $airport->id)>
                            {{ $airportLabel }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="la-btn-dots" tabindex="-1">...</button>
            </div>
        </div>
        <div class="la-field">
            <label>Stored Airline</label>
            <div class="la-stored-with-btn">
                <select class="la-stored-pink" id="la-stored-airline">
                    <option value=""></option>
                    @foreach($airlines as $airline)
                        @php
                            $airlineLabel = ($airline->iata_code ? $airline->iata_code . ' - ' : '') . $airline->name;
                        @endphp
                        <option value="{{ $airline->id }}"
                            data-code="{{ $airline->iata_code }}"
                            data-name="{{ $airline->name }}"
                            @selected((string) $laSelectedAirlineId === (string) $airline->id)>
                            {{ $airlineLabel }}
                        </option>
                    @endforeach
                </select>
                <button type="button" class="la-btn-dots" tabindex="-1">...</button>
            </div>
        </div>
    </div>
    <div class="la-field-row">
        <div class="la-field position-relative" style="grid-column: span 3;">
            <label>Airport Code</label>
            <input type="text" id="la-airport-code" value="{{ $laAirportCode }}" autocomplete="off" spellcheck="false" maxlength="3" style="text-transform:uppercase;">
            <div id="la-airport-code-suggestions" class="location-suggestions" aria-live="polite"></div>
        </div>
        <div class="la-field" style="grid-column: span 9;">
            <label>Airport Name</label>
            <input type="text" id="la-airport-name" value="{{ $laAirportName }}" autocomplete="off" spellcheck="false">
        </div>
        <div class="la-field position-relative" style="grid-column: span 3;">
            <label>Airline Code</label>
            <input type="text" id="la-airline-code" value="{{ $laAirlineCode }}" autocomplete="off" spellcheck="false" maxlength="3" style="text-transform:uppercase;">
            <div id="la-airline-code-suggestions" class="location-suggestions" aria-live="polite"></div>
        </div>
        <div class="la-field" style="grid-column: span 5;">
            <label>Airline Name</label>
            <input type="text" id="la-airline-name" value="{{ $laAirlineName }}" autocomplete="off" spellcheck="false">
            <input type="hidden" name="pickup_flight_details" id="pickup-flight-details" value="{{ $laPickupFlightValue }}">
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Flight #</label>
            <input type="text" name="flight_number" id="flight_number" value="{{ $formValue('flight_number') }}">
        </div>
        <div class="la-field" style="grid-column: span 3;">
            <label>Arr/Dep AP</label>
            <input type="text" id="la-airport-arr-dep" tabindex="-1" autocomplete="off">
        </div>
        <div class="la-field" style="grid-column: span 3;">
            <label>Terminal/Gate</label>
            <input type="text" id="la-airport-terminal" tabindex="-1" autocomplete="off">
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Airport Instructions</label>
            <select id="la-airport-instructions" tabindex="-1">
                <option value=""></option>
                <option value="Arrive">Arrive</option>
                <option value="Depart">Depart</option>
                <option value="Meet and Greet">Meet and Greet</option>
            </select>
        </div>
        <div class="la-field" style="grid-column: span 2;">
            <label>ETA/ETD</label>
            <input type="text" id="la-airport-eta-etd" tabindex="-1" autocomplete="off">
        </div>
    </div>
    @include('pages.partials.reservation-la-routing-notes', ['notesLayout' => 'airport', 'meetOld' => $meetOld])
</div>

{{-- SEAPORT --}}
<div class="la-addr-type-panel" data-addr-panel="seaport">
    <div class="la-stored-row">
        <div class="la-field">
            <label>Stored Seaports</label>
            <div class="la-stored-with-btn">
                <select class="la-stored-cyan" tabindex="-1"><option></option></select>
                <button type="button" class="la-btn-dots" tabindex="-1">...</button>
            </div>
        </div>
        <div class="la-field">
            <label>Cruise Ship</label>
            <div class="la-stored-with-btn">
                <select class="la-stored-pink" tabindex="-1"><option></option></select>
                <button type="button" class="la-btn-dots" tabindex="-1">...</button>
            </div>
        </div>
    </div>
    <div class="la-field-row">
        <div class="la-field" style="grid-column: span 3;">
            <label>Seaport Code</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 9;">
            <label>Port of Call Name</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>Cruise Ship Name</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>Cruise Line Name</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Arriving From/Departing To</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Seaport Instructions</label>
            <select tabindex="-1"><option></option></select>
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>ETA/ETD</label>
            <input type="text" tabindex="-1">
        </div>
    </div>
    @include('pages.partials.reservation-la-routing-notes')
</div>

{{-- FBO --}}
<div class="la-addr-type-panel" data-addr-panel="fbo">
    <div class="la-field" style="margin-bottom:4px;">
        <label>Stored FBOs</label>
        <div class="la-stored-with-btn">
            <select class="la-stored-pink" tabindex="-1"><option></option></select>
            <button type="button" class="la-btn-dots" tabindex="-1">...</button>
        </div>
    </div>
    <div class="la-field-row">
        <div class="la-field" style="grid-column: span 9;">
            <label>Location Description/ FBO Name</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 3;">
            <label>Tail#</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 12;">
            <label>Street Address Line 1</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 12;">
            <label>Street Address Line 2</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>City</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>State/Prov</label>
            <select tabindex="-1"><option>Texas</option>@foreach($usStates as $st)<option>{{ $st }}</option>@endforeach</select>
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Zip/Post</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 8;">
            <label>Country</label>
            <select tabindex="-1"><option>United States</option></select>
        </div>
    </div>
    @include('pages.partials.reservation-la-routing-notes')
</div>

{{-- POI --}}
<div class="la-addr-type-panel" data-addr-panel="poi">
    <div class="la-field" style="margin-bottom:4px;">
        <label>Points of Interest</label>
        <select class="la-stored-cyan" tabindex="-1" style="width:100%;height:20px;border:1px solid #999;"><option></option></select>
    </div>
    <div class="la-field-row">
        <div class="la-field la-poi-save-row" style="grid-column: span 12;">
            <div class="la-field" style="margin:0;">
                <label>Location Description/ Name</label>
                <input type="text" tabindex="-1">
            </div>
            <label class="la-poi-save-label"><input type="checkbox" tabindex="-1"> Save POI?</label>
        </div>
        <div class="la-field" style="grid-column: span 12;">
            <label>Street Address Line 1</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 12;">
            <label>Street Address Line 2</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>City</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>State/Prov</label>
            <select tabindex="-1"><option>Texas</option>@foreach($usStates as $st)<option>{{ $st }}</option>@endforeach</select>
        </div>
        <div class="la-field" style="grid-column: span 4;">
            <label>Zip/Post</label>
            <input type="text" tabindex="-1">
        </div>
        <div class="la-field" style="grid-column: span 8;">
            <label>Country</label>
            <select tabindex="-1"><option>United States</option></select>
        </div>
    </div>
    @include('pages.partials.reservation-la-routing-notes')
</div>
