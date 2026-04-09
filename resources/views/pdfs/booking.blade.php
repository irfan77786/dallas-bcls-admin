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
              @if(!empty($bookingData['hours']))
                Hourly/As Directed
              @else
                To Airport
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
        </div>f
      </div>
      @endif
    </div>
  </div>
  <div class="sections" style="page-break-before: always;">
      <h2 class="custom-large-heading">Cancellation Policy: Cancellation, Deposit & Service Policy</h2>
      <div class="section-content">
        <p>
          Dallas Black Cars Limo Service strives to provide excellent service while maintaining a clear, fair, and simple cancellation, deposit, and service policy. By booking with us, you agree to the following terms.</p>
        <div class="row">
          <div class="col-sm-3">
          <strong class="mian-cc">Contact:</strong>
          </div>
          <div class="col-sm-9">
          Email: info@dallasblackcarslimoservice.com<br>
          Phone: +1 (214) 305-8671
          </div>
        </div>

      </div>
    </div>
    <div class="sections section-light">
      <h2>1. General Cancellation Policy:</h2>
      <div class="section-content">
        <p> Cancellations must occur during the stated timeframes for each vehicle type. Cancellations outside these periods will result in full charges for the reserved services.</p>



      </div>
    </div>
    <div class="sections section-light">
      <h2>2. Vehicle-Specific Cancellation Policy:</h2>
      <div class="section-content">
        <div class="row">
        <div class="col-sm-3 no-top-padding">
          <strong>Luxury Sedans:</strong>
        </div>
        <div class="col-sm-9 no-top-padding">
          Cancel at least 24 hours prior. Late cancellations: 100% charge.
        </div>
        </div>
        <div class="row">
        <div class="col-sm-3">
          <strong>SUVs:</strong>
        </div>
        <div class="col-sm-9">
          Cancel at least 24 hours prior. Late cancellations: 100% charge.
        </div>
        </div>
        <div class="row">
        <div class="col-sm-3">
          <strong>Luxury Vans:</strong>
        </div>
        <div class="col-sm-9">
          Cancel at least 72 hours prior. Late cancellations: Full charge applies.
        </div>
        </div>
        <div class="row">
        <div class="col-sm-3">
          <strong>Mini Buses:</strong>
        </div>
        <div class="col-sm-9">
          Cancel at least 7 days prior. Late cancellations: Full charge applies.
        </div>
        </div>
        <div class="row">
        <div class="col-sm-3">
          <strong>Motor Coaches:</strong>
        </div>
        <div class="col-sm-9">
          Cancel at least 7 days prior. Late cancellations: Full charge including any deposits.
        </div>
        </div>
      </div>
    </div>

    <div class="sections section-light">
      <h2>3. Deposit Policy:</h2>
      <div class="section-content">
        <p>

          - 50% non-refundable deposit due within 24 hours of signing the agreement.<br>
          - Final payment due at least 7 days before the reservation date.<br>
          - Written notice required for cancellations as per policy.<br>
          - If proper notice is not given, all payments are non-refundable.<br>
          - Credit card authorization form required for all bookings.</p>
      </div>
    </div>
    <div class="sections section-light">
      <h2>4. Alcohol and Illegal Substances:</h2>

      <div class="section-content">
        <p>
          - Alcohol permitted only for passengers 21 and older.<br>
          - Illegal substances strictly prohibited. Immediate cancellation with no refund.<br>
          - Smoking is not allowed in any vehicle.</p>
      </div>
    </div>
    <div class="sections section-light">
      <h2>5. Damage To Vehicle:</h2>
      <div class="section-content">
        <p>
          The client is responsible for any damage caused by passengers, including:<br>
          - Spills or stains needing special cleaning<br>
          - Burns, tears, or physical damage to interior/exterior<br>
          - Loss of revenue due to vehicle being out of service<br>
          Minimum charge for damage or cleaning is $250. Additional fees may apply.</p>
      </div>
    </div>

    <div class="sections section-light">
      <h2>6. Force Majeure:</h2>
      <div class="section-content">
        <p>
          We are not liable for interruptions or cancellations due to events beyond our control (e.g., weather, disasters, terrorism, mechanical issues). We will attempt to reschedule or refund (minus non-refundable costs).</p>
      </div>
    </div>
    <div class="sections section-light">
      <h2>7. Indemnification:</h2>
      <div class="section-content">
        <p>
          By booking, you agree to indemnify and hold Dallas Black Cars Limo Service harmless for any claims arising from:<br>
          - Your use of services<br>
          - Policy violations<br>
          - Damage caused by you or your party</p>

      </div>
    </div>
    <div class="sections section-light">

      <h2>8. Wait Time Policy:</h2>
      <div class="section-content">
        <div class="row">
        <div class="col-sm-3 no-top-padding">
           <strong>Airport Transfers:</strong>
        </div>
        <div class="col-sm-9 no-top-padding">
          30-minute grace period (domestic), 60 minutes (international). After that: $15 per 15 minutes.
        </div>
        </div>
        <div class="row">
        <div class="col-sm-3">
          <strong>Point-to-Point & Hourly:</strong>
        </div>
        <div class="col-sm-9">
          15-minute grace period. After that: $15 per 15 minutes.
        </div>
        </div>
      </div>
    </div>
    <div class="sections section-light">
      <h2>9. No-Show Policy:</h2>
      <div class="section-content">
        <p>
          No-shows are charged the full booking amount. Clients must confirm pickup details and stay in contact.</p>
      </div>
    </div>
    <div class="sections section-light">
      <h2>10. Special Event Policies:</h2>
      <div class="section-content">
        <p>
          - Events (weddings, concerts, etc.) require 14-day cancellation notice.<br>
          - 50% deposit required to confirm reservation.<br>
          - No refund if cancelled within 14 days of the event.</p>
      </div>
    </div>

    <div class="sections section-light">
      <h2>11. Refund Policy:</h2>
      <div class="section-content">
        <p>
          - Approved refunds are processed within 5–7 business days.<br>
          - No refunds for Motor Coaches, Mini Buses, or Special Events after cancellation window closes.</p>
      </div>
    </div>
    <div class="sections section-light">
      <h2>Thank you for choosing Dallas Black Cars Limo Service.</h2>
      <div class="section-content">
        <p>
          We are committed to fair and professional service.<br>
          Contact us: info@dallasblackcarslimoservice.com | +1 (214) 305-8671</p>
      </div>
    </div>


    <footer style="margin-top: 40px; padding-top: 20px; border-top: 1px solid #eee; text-align: center; font-size: 12px; color: #666;">
      <p>Thank you for choosing Dallas Black Car Service. If you have any questions about your booking, please contact our customer support.</p>
      <p>+1 214-305-8671 | info@dallasblackcarslimoservice.com</p>
    </footer>
  </div>
</body>

</html>