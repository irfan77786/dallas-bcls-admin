@php
    $driver = $driver ?? ($bookingData['driver'] ?? []);
    $vehicleLabel = $driver['vehicle_label']
        ?? trim(($driver['year'] ?? '') . ' ' . ($driver['car_make'] ?? '') . ' ' . ($driver['car_model'] ?? '') . (!empty($driver['color']) ? ' (' . $driver['color'] . ')' : ''));
    $vehicleLabel = trim((string) $vehicleLabel);
@endphp

@if(!empty($driver) && !empty($driver['name']))
<table cellpadding="0" cellspacing="0" width="100%" style="margin: 0 0 20px; border-collapse: separate; border-spacing: 0; background: #ffffff; border: 1px solid #e8e0d0; border-radius: 10px; overflow: hidden;">
    <tr>
        <td style="background: linear-gradient(135deg, #9e7c1e 0%, #7a5f14 100%); padding: 12px 16px;">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td style="font-size: 11px; letter-spacing: 0.12em; text-transform: uppercase; color: rgba(255,255,255,0.85); font-weight: 700;">
                        Assigned driver
                    </td>
                    @if(!empty($driver['vehicle_type']))
                    <td align="right" style="font-size: 12px; color: #fff;">
                        <span style="display: inline-block; background: rgba(255,255,255,0.18); border: 1px solid rgba(255,255,255,0.28); border-radius: 999px; padding: 3px 10px; font-weight: 600;">
                            {{ $driver['vehicle_type'] }}
                        </span>
                    </td>
                    @endif
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 18px 16px 8px; background: #faf8f3;">
            <table cellpadding="0" cellspacing="0" width="100%">
                <tr>
                    <td width="88" valign="top" style="padding-right: 14px;">
                        @if(!empty($driver['picture_url']))
                            <img src="{{ $driver['picture_url'] }}" alt="{{ $driver['name'] ?? 'Driver' }}" width="80" height="80" style="display: block; width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 3px solid #ffffff; box-shadow: 0 4px 12px rgba(122, 95, 20, 0.22);">
                        @else
                            <table cellpadding="0" cellspacing="0" width="80" height="80" style="width: 80px; height: 80px; border-radius: 50%; background: #ebe4d4; border: 3px solid #ffffff;">
                                <tr>
                                    <td align="center" valign="middle" style="font-size: 28px; color: #9e7c1e; font-weight: 700; height: 74px;">
                                        {{ strtoupper(substr($driver['name'] ?? 'D', 0, 1)) }}
                                    </td>
                                </tr>
                            </table>
                        @endif
                    </td>
                    <td valign="middle">
                        <div style="font-size: 20px; line-height: 1.25; font-weight: 700; color: #1a1a1a; margin: 0 0 6px;">
                            {{ $driver['name'] ?? '—' }}
                        </div>
                        @if(!empty($driver['phone']))
                            <div style="margin: 0 0 4px;">
                                <a href="tel:{{ preg_replace('/[^0-9+]/', '', $driver['phone']) }}" style="color: #7a5f14; text-decoration: none; font-size: 14px; font-weight: 600;">
                                    {{ $driver['phone'] }}
                                </a>
                            </div>
                        @endif
                        @if(!empty($driver['address']))
                            <div style="font-size: 13px; color: #5c5c5c; line-height: 1.4;">
                                {{ $driver['address'] }}
                            </div>
                        @endif
                    </td>
                </tr>
            </table>
        </td>
    </tr>
    <tr>
        <td style="padding: 4px 16px 16px; background: #faf8f3;">
            <table cellpadding="0" cellspacing="0" width="100%" style="background: #ffffff; border: 1px solid #efe8da; border-radius: 8px;">
                <tr>
                    <td colspan="2" style="padding: 10px 12px 6px; font-size: 10px; letter-spacing: 0.1em; text-transform: uppercase; color: #9e7c1e; font-weight: 700;">
                        Vehicle details
                    </td>
                </tr>
                @if($vehicleLabel !== '')
                <tr>
                    <td style="width: 38%; padding: 6px 12px; font-size: 12px; color: #7a7a7a; vertical-align: top;">Make / Model</td>
                    <td style="padding: 6px 12px; font-size: 13px; color: #222; font-weight: 600; vertical-align: top;">{{ $vehicleLabel }}</td>
                </tr>
                @endif
                @if(!empty($driver['year']) && $vehicleLabel === '')
                <tr>
                    <td style="width: 38%; padding: 6px 12px; font-size: 12px; color: #7a7a7a;">Year</td>
                    <td style="padding: 6px 12px; font-size: 13px; color: #222; font-weight: 600;">{{ $driver['year'] }}</td>
                </tr>
                @endif
                @if(!empty($driver['color']) && !str_contains($vehicleLabel, '(' . $driver['color'] . ')'))
                <tr>
                    <td style="width: 38%; padding: 6px 12px; font-size: 12px; color: #7a7a7a;">Color</td>
                    <td style="padding: 6px 12px; font-size: 13px; color: #222; font-weight: 600;">{{ $driver['color'] }}</td>
                </tr>
                @endif
                @if(!empty($driver['capacity']))
                <tr>
                    <td style="width: 38%; padding: 6px 12px; font-size: 12px; color: #7a7a7a;">Capacity</td>
                    <td style="padding: 6px 12px; font-size: 13px; color: #222; font-weight: 600;">{{ $driver['capacity'] }} passenger{{ (int) $driver['capacity'] === 1 ? '' : 's' }}</td>
                </tr>
                @endif
                @if(!empty($driver['plate_number']))
                <tr>
                    <td style="width: 38%; padding: 6px 12px; font-size: 12px; color: #7a7a7a;">Plate number</td>
                    <td style="padding: 6px 12px; font-size: 13px; color: #222; font-weight: 700; letter-spacing: 0.04em; font-family: Consolas, Monaco, monospace;">{{ $driver['plate_number'] }}</td>
                </tr>
                @endif
                @if(!empty($driver['vin']))
                <tr>
                    <td style="width: 38%; padding: 6px 12px 12px; font-size: 12px; color: #7a7a7a; vertical-align: top;">VIN</td>
                    <td style="padding: 6px 12px 12px; font-size: 12px; color: #444; font-family: Consolas, Monaco, monospace; word-break: break-all;">{{ $driver['vin'] }}</td>
                </tr>
                @elseif(!empty($driver['plate_number']) || !empty($driver['capacity']) || $vehicleLabel !== '')
                <tr>
                    <td colspan="2" style="padding-bottom: 6px;"></td>
                </tr>
                @else
                <tr>
                    <td colspan="2" style="padding: 6px 12px 12px; font-size: 13px; color: #888;">No vehicle details on file.</td>
                </tr>
                @endif
            </table>
        </td>
    </tr>
</table>
@endif
