@php $notesLayout = $notesLayout ?? 'default'; @endphp

@if($notesLayout === 'airport')
    <div class="la-field-row la-airport-contact-row">
        <div class="la-field" style="grid-column: span 6;">
            <label>Meet Option</label>
            <select name="meet_option" id="meet-option">
                <option value="" @selected($meetOld !== 'curbside' && $meetOld !== 'inside')>—</option>
                <option value="curbside" @selected($meetOld === 'curbside')>Curbside pickup</option>
                <option value="inside" @selected($meetOld === 'inside')>Inside pickup</option>
            </select>
        </div>
        <div class="la-field" style="grid-column: span 6;">
            <label>Phone Number</label>
            <div class="la-phone-wrap">
                <div class="la-phone-input-wrap">
                    <input type="tel" class="la-intl-phone" tabindex="-1" autocomplete="off" placeholder="Enter number">
                    <i class="bi bi-telephone la-phone-ico"></i>
                </div>
            </div>
        </div>
    </div>
    <div class="la-routing-notes-row la-routing-notes-airport">
        <div class="la-field">
            <label>Notes</label>
            <textarea tabindex="-1" style="min-height:70px;background:#fffde7;border:1px solid #999;width:100%;font-size:11px;"></textarea>
        </div>
        <div class="la-routing-side">
            <div class="la-field">
                <label class="la-lbl-red">Time In</label>
                <input type="time" tabindex="-1">
            </div>
        </div>
    </div>
@else
    <div class="la-routing-notes-row">
        <div class="la-field">
            <label>Notes</label>
            <textarea tabindex="-1" style="min-height:70px;background:#fffde7;border:1px solid #999;width:100%;font-size:11px;"></textarea>
        </div>
        <div class="la-routing-side">
            <div class="la-field">
                <label>Phone Number</label>
                <div class="la-phone-wrap">
                    <div class="la-phone-input-wrap">
                        <input type="tel" class="la-intl-phone" tabindex="-1" autocomplete="off" placeholder="Enter number">
                        <i class="bi bi-telephone la-phone-ico"></i>
                    </div>
                </div>
            </div>
            <div class="la-field">
                <label class="la-lbl-red">Time In</label>
                <input type="time" tabindex="-1">
            </div>
        </div>
    </div>
@endif
