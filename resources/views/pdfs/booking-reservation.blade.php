<!DOCTYPE html>
<html lang="en">

<head>
  <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Booking Confirmation</title>
  {{-- DomPDF: use bundled DejaVu Sans only — webfonts (e.g. Abel) require storage/fonts cache and often fail. --}}
  <style>
    /* Load the font-face definition */
    @page {
      margin: 0;
    }

    body {
      font-family: DejaVu Sans, Helvetica, Arial, sans-serif;
      color: #333;
      line-height: 1.5;
      padding: 15px;
      margin: 0;
      font-size: 13px;
    }

    .container {
      max-width: 800px;
      margin: 0 auto;
    }

    .header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      margin-bottom: 30px;
      padding-bottom: 20px;
      border-bottom: 1px solid #eee;
    }

    .logo img {
      max-height: 60px;
    }

    .header-info {
      text-align: right;
    }
    .contact {
      font-size: 12px;
      color: #666;
    }

    .sections h2:not(.custom-large-heading) {
      margin: 5px 0 3px 0 !important;
      padding: 4px 15px;
      background: #9e7c1e !important;
      color: #ffffff !important;
      border-radius: 0;
      font-size: 16px;
    }

    .section h2 {
      background: #9e7c1e !important;
      margin: 0;
      padding: 6px 15px;
      font-size: 16px;
      color: #ffffff;
      letter-spacing: 0.7px;
      border-bottom: 1px solid #e0e0e0;
    }

    .section-light h2 {
      background: #EFEADB;
      margin: 0 0 20px 0;
      padding: 6px 15px;
      font-size: 16px;
      color: #333;
      border-bottom: 1px solid #e0e0e0;
    }

    .custom-large-heading {
      padding: 6px 15px !important;
      margin: 10px 0 3px 0 !important;
      background: #9e7c1e !important;
      font-size: 16px !important;
      color: #ffffff !important;
      border-bottom: 1px solid #e0e0e0 !important;
    }

    .section-content {
      padding: 0 0px 3px;
      margin: -2px 0 3px 0;
      line-height: 1.3;
    }

    .info-grid {
      display: grid;
      grid-template-columns: 1fr 1fr;
      gap: 15px;
      padding: 20px;
    }

    .info-item {
      display: flex;
      flex-direction: column;
    }

    .label {
      font-size: 13px;
      font-weight: 700;
      color: #666;
      margin-bottom: 3px;
    }

    .value {
      font-size: 13px;
      font-weight: 500;
      color: #333;
    }

    .status-confirmed {
      color: #28a745;
      font-weight: 600;
    }

    .status-pending {
      color: #ffc107;
      font-weight: 600;
    }

    .status-cancelled {
      color: #dc3545;
      font-weight: 600;
    }

    .total-amount {
      font-size: 16px;
      font-weight: 600;
      color: #333;
    }

    .payment-method {
      display: flex;
      align-items: center;
      gap: 8px;
    }

    .payment-method img {
      width: 30px;
      height: 20px;
      object-fit: contain;
    }

    .bottom-policy-content h3 {
      margin-top: 28px;
      font-size: 24px;
      margin-bottom: 15px;
    }

    .row {
      display: table;
      width: 100%;
      table-layout: fixed;
      border-spacing: 0;
    }

    .col-sm-3, .col-sm-9 {
      display: table-cell;
      vertical-align: top;
      padding: 4px 10px;
      box-sizing: border-box;
    }

    .col-sm-3 {
      width: 25%;
    }

    .no-top-padding{
      padding-top: 0px !important;
    }

    .col-sm-9 {
      width: 75%;
    }
    .section-light{
      margin-bottom: 8px !important;
    }

    .section-content p {
      margin: 6px 0;
      line-height: 1.3;
    }
  </style>
</head>

<body>
  <div class="container">
    <header style="width: 100%; display: table; margin-bottom: 7px;">
      <div style="display: table-row;">
        <div style="display: table-cell; vertical-align: middle; width: 62%;">
          @php
          // Never fetch remote URLs during PDF render: slow/blocked networks hit max_execution_time.
          $logoData = '';
          $logoMime = 'image/png';
          $localLogo = public_path('img/black-car-service-dallas-logo.png');
          if (is_file($localLogo)) {
              $logoData = base64_encode((string) file_get_contents($localLogo));
          }
          @endphp
          @if($logoData !== '')
          <img src="data:{{ $logoMime }};base64,{{ $logoData }}" alt="Logo" style="height: 60px;" />
          @else
          <div style="font-weight: bold; font-size: 14px;">Dallas Black Cars Limo Service</div>
          @endif
        </div>
        <div style="text-align: right;">
          <div style="font-size: 12px; text-align: left;">
            <div style="font-weight: bold; font-size: 12px;">Dallas Black Cars Limo Service</div>
            <div>100 Crescent Court, 7th Floor</div>
            <div>Dallas, TX 75201</div>
            <div><strong>Phone:</strong>&nbsp;+1 (214) 305-8671</div>
            <div><strong>Email:</strong>&nbsp;info@dallasblackcarslimoservice.com</div>
           </div>
        </div>
      </div>
    </header>
    
    <div class="sections">
    <h2 class="custom-large-heading section-light">Booking Confirmation #{{ $bookingData['booking_id'] ?? 'N/A' }}</h2>
    @if(!empty($bookingData['personal_message']))
    <div style="padding: 12px 14px; margin: 10px 0 14px; background: #faf8f3; border-left: 4px solid #9e7c1e; font-size: 12px; line-height: 1.45;">
      <strong style="display:block; margin-bottom:6px; color:#9e7c1e;">Personal message</strong>
      <span style="white-space: pre-wrap;">{{ $bookingData['personal_message'] }}</span>
    </div>
    @endif
    <div class="section-content">
      <div class="section">
        <div style="text-align: right; font-size: 12px;">
          <strong>Last Modified On:</strong> {{ now()->format('m/d/Y h:i A') }}
        </div>
        <div class="section-content">
          {{-- Pickup Date --}}
          @if(!empty($bookingData['pickup_date']))
          <div class="row">
            <div class="col-sm-3 no-top-padding"><strong class="mian-cc">Pick-up Date:</strong></div>
            <div class="col-sm-9 no-top-padding">
              {{ \Carbon\Carbon::parse($bookingData['pickup_date'])->format('m/d/Y - l') }}
            </div>
          </div>
          @endif

          {{-- Pickup Time --}}
          @if(!empty($bookingData['pickup_time']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Pick-up Time:</strong></div>
            <div class="col-sm-9">{{ \Carbon\Carbon::parse($bookingData['pickup_time'])->format('h:i A') }}</div>
          </div>
          @endif
          
          {{-- Hours --}}
          @if(!empty($bookingData['hours'] ?? null))
            <div class="row">
              <div class="col-sm-3"><strong class="mian-cc">Hours:</strong></div>
              <div class="col-sm-9">{{ $bookingData['hours'] ?? 'N/A' }}</div>
            </div>
          @endif

          {{-- Service Type --}}
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Service Type:</strong></div>
            <div class="col-sm-9">
              @if(!empty($bookingData['service_option_label']))
                {{ $bookingData['service_option_label'] }}
              @elseif(!empty($bookingData['hours']))
                Hourly/As Directed
              @else
                Point-to-point
              @endif
            </div>
          </div>

          {{-- Passenger --}}
          @if(!empty($bookingData['passenger_name']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Passenger:</strong></div>
            <div class="col-sm-9">{{ $bookingData['passenger_name'] }}</div>
          </div>
          @endif

          {{-- Client Ref# --}}
          @if(!empty($bookingData['booking_id']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Client Ref#:</strong></div>
            <div class="col-sm-9">N/A</div>
          </div>
          @endif

          {{-- Phone Number --}}
          @if(!empty($bookingData['phone']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Phone Number:</strong></div>
            <div class="col-sm-9">{{ $bookingData['phone'] }}</div>
          </div>
          @endif

          {{-- No. of Pass --}}
          @if(!empty($bookingData['passengers']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">No. of Pass:</strong></div>
            <div class="col-sm-9">{{ $bookingData['passengers'] }}</div>
          </div>
          @endif

          {{-- Vehicle Type --}}
          @if(!empty($bookingData['vehicle_type']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Vehicle Type:</strong></div>
            <div class="col-sm-9">{{ $bookingData['vehicle_type'] }}</div>
          </div>
          @endif

          {{-- Primary/Billing Contact --}}
          @if(!empty($bookingData['booker_first_name']) || !empty($bookingData['booker_last_name']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Primary/Billing Contact:</strong></div>
            <div class="col-sm-9">{{ trim($bookingData['booker_first_name'] . ' ' . $bookingData['booker_last_name']) }}</div>
          </div>
          @endif

          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Passenger Email:</strong></div>
            <div class="col-sm-9">{{ $bookingData['email'] }}</div>
          </div>

          {{-- Payment Method --}}
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Payment Method:</strong></div>
            <div class="col-sm-9">Credit Card</div>
          </div>
        </div>
      </div>
      
      {{-- Booker Info (if booking for others) --}}
      @if(!empty($bookingData['isBookingForOthers']) && ($bookingData['booker_first_name'] || $bookingData['booker_last_name'] || $bookingData['booker_email'] || $bookingData['booker_number']))
      <div class="sections section-light">
        <h2>Booker Information</h2>
        <div class="section-content">
          @if($bookingData['booker_first_name'] || $bookingData['booker_last_name'])
          <div class="row">
            <div class="col-sm-3 no-top-padding"><strong class="mian-cc">Booker Name:</strong></div>
            <div class="col-sm-9 no-top-padding">{{ trim($bookingData['booker_first_name'] . ' ' . $bookingData['booker_last_name']) }}</div>
          </div>
          @endif

          @if($bookingData['booker_email'])
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Booker Email:</strong></div>
            <div class="col-sm-9">{{ $bookingData['booker_email'] }}</div>
          </div>
          @endif

          @if($bookingData['booker_number'])
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Booker Phone:</strong></div>
            <div class="col-sm-9">{{ $bookingData['booker_number'] }}</div>
          </div>
          @endif
        </div>
      </div>
      @else
      <div class="sections section-light">
        <h2>Booker Information:</h2>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 no-top-padding">
              ******  Information not provided  ******
            </div>
          </div>
        </div>
      </div>
      @endif

      @if(!empty($bookingData['account']['company_name']))
      <div class="sections section-light">
        <h2>Account & Billing Contact</h2>
        <div class="section-content">
          <div class="row"><div class="col-sm-3 no-top-padding"><strong class="mian-cc">Company #:</strong></div><div class="col-sm-9 no-top-padding">{{ $bookingData['account']['company_number'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Company Name:</strong></div><div class="col-sm-9">{{ $bookingData['account']['company_name'] }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Company Email:</strong></div><div class="col-sm-9">{{ $bookingData['account']['company_email'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Company Phone:</strong></div><div class="col-sm-9">{{ $bookingData['account']['company_phone'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Company Address:</strong></div><div class="col-sm-9">{{ $bookingData['account']['company_address'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Billing Name:</strong></div><div class="col-sm-9">{{ $bookingData['account']['billing_name'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Billing Email:</strong></div><div class="col-sm-9">{{ $bookingData['account']['billing_email'] ?: 'N/A' }}</div></div>
          <div class="row"><div class="col-sm-3"><strong class="mian-cc">Billing Phone:</strong></div><div class="col-sm-9">{{ $bookingData['account']['billing_phone'] ?: 'N/A' }}</div></div>
        </div>
      </div>
      @endif

      {{-- Trip Routing Information --}}
      @if(!empty($bookingData['pickup_location']) || !empty($bookingData['dropoff_location']) || !empty($bookingData['hours']))
      <div class="sections section-light">
        <h2>Trip Routing Information:</h2>
        <div class="section-content">
          @if(!empty($bookingData['pickup_location']))
          <div class="row">
            <div class="col-sm-3 no-top-padding"><strong class="mian-cc">Pick-up Location:</strong></div>
            <div class="col-sm-9 no-top-padding">{{ $bookingData['pickup_location'] }}</div>
          </div>
          @endif

          @foreach(($bookingData['stop_locations'] ?? []) as $i => $stopLocation)
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Stop {{ $i + 1 }}:</strong></div>
            <div class="col-sm-9">{{ $stopLocation }}</div>
          </div>
          @endforeach

          @if(!empty($bookingData['hours']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Stop Location:</strong></div>
            <div class="col-sm-9">STOP AS DIRECTED</div>
          </div>
          @endif

          @if(!empty($bookingData['dropoff_location']))
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Drop-off Location:</strong></div>
            <div class="col-sm-9">{{ $bookingData['dropoff_location'] }}</div>
          </div>
          @endif
        </div>
      </div>
      @else
      <div class="sections section-light">
        <h2>Trip Routing Information:</h2>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 no-top-padding">
              ******  Information not provided  ******
            </div>
          </div>
        </div>
      </div>
      @endif

      @if($bookingData['flight_details'] && $bookingData['flight_details']['flight_number'] && $bookingData['flight_details']['pickup_flight_details'])
      <div class="sections section-light">
        <h2>Flight/Airport Information</h2>
        <div class="section-content">
          @if($bookingData['flight_details']['flight_number'])
          <div class="row">
            <div class="col-sm-3 no-top-padding"><strong class="mian-cc">Flight Number:</strong></div>
            <div class="col-sm-9 no-top-padding">{{ $bookingData['flight_details']['flight_number'] }}</div>
          </div>
          @endif
          @if($bookingData['flight_details']['pickup_flight_details'])
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Pickup Flight Details:</strong></div>
            <div class="col-sm-9">{{ $bookingData['flight_details']['pickup_flight_details'] }}</div>
          </div>
          @endif
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Meet Option:</strong></div>
            <div class="col-sm-9">{{ $bookingData['flight_details']['meet_option'] ?? 'Not Specified!' }}</div>
          </div>
        </div>
      </div>
      @else
      <div class="sections section-light">
        <h2>Flight/Airport Information:</h2>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 no-top-padding">
              ******  Information not provided  ******
            </div>
          </div>
        </div>
      </div>
      @endif

      {{-- Notes / Comments --}}
      <div class="sections section-light">
        <h2>Notes/Comments:</h2>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 no-top-padding">
              ******  {{ $bookingData['special_instructions'] ? $bookingData['special_instructions'] : 'Information not provided' }}  ******
            </div>
          </div>
        </div>
      </div>

      {{-- Charges & Fees --}}
      @if(isset($bookingData['total_amount']))
      <div class="sections section-light">
        <h2>Charges & Fees:</h2>
        <div class="section-content">
          {{-- Fare --}}
          <div class="row">
            <div class="col-sm-3 no-top-padding"><strong class="mian-cc">Fare (All inclusive):</strong></div>
            <div class="col-sm-9 no-top-padding"><strong>${{ number_format($bookingData['total_amount'], 2) }}</strong></div>
          </div>

          {{-- Other Charges --}}
          <div class="row">
            <div class="col-sm-3"><strong class="mian-cc">Other charges:</strong></div>
            <div class="col-sm-9"><strong>$0.00</strong></div>
          </div>

          {{-- Payment Deposits --}}
          <div class="row" style="color: #28a745;">
            <div class="col-sm-3"><strong class="mian-cc">Payment/Deposits:</strong></div>
            <div class="col-sm-9"><strong>$0.00</strong></div>
          </div>

          {{-- Total Amount --}}
          <div class="row" style="color: red;">
            <div class="col-sm-3"><strong class="mian-cc">Total Due:</strong></div>
            <div class="col-sm-9"><strong>${{ number_format($bookingData['total_amount'], 2) }}</strong></div>
          </div>
        </div>
      </div>
      @else
      <div class="sections section-light">
        <h2>Charges & Fees:</h2>
        <div class="section-content">
          <div class="row">
            <div class="col-sm-12 no-top-padding">
              ******  Information not provided  ******
            </div>
          </div>
        </div>
      </div>
      @endif
    </div>
  </div>

  @include('pdfs.partials.legacy-cancellation-policy-pdf')
  @include('pdfs.partials.fifa-2026-event-policy-pdf')
  @include('pdfs.partials.booking-pdf-thank-you-closing')

    <footer style="margin-top: 12px; padding-top: 10px; border-top: 1px solid #eee; text-align: center; font-size: 11px; color: #666;">
      <p>Thank you for choosing Dallas Black Car Service. If you have any questions about your booking, please contact our customer support.</p>
      <p>+1 214-305-8671 | info@dallasblackcarslimoservice.com</p>
    </footer>
  </div>
</body>

</html>