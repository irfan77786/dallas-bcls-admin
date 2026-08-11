@extends('layouts.main')
@section('title', $pageTitle ?? 'Add Reservation')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
@if(!empty($stripeEnabled))
<script src="https://js.stripe.com/v3/"></script>
@endif
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/css/intlTelInput.css">
@if(!empty($isEmbed))
<style>
    html.la-embed-pending .header-top,
    html.la-embed-pending .header-top-nav,
    html.la-embed-pending .header-main-nav,
    html.la-embed-pending #header-main-nav,
    html.la-embed-pending .header-nav-toggle,
    html.la-embed-pending .footer,
    html.la-embed-pending .chat,
    html.la-embed-pending #chat,
    html.la-embed-pending .modalmenu,
    body.la-embed-mode .header-top,
    body.la-embed-mode .header-top-nav,
    body.la-embed-mode .header,
    body.la-embed-mode .header-main-nav,
    body.la-embed-mode #header-main-nav,
    body.la-embed-mode .header-nav-toggle,
    body.la-embed-mode .footer,
    body.la-embed-mode .chat,
    body.la-embed-mode #chat,
    body.la-embed-mode .modalmenu {
        display: none !important;
    }
    html.la-embed-pending .page-wrap,
    html.la-embed-pending .main-content,
    body.la-embed-mode .page-wrap,
    body.la-embed-mode .main-content {
        margin: 0 !important;
        padding: 0 !important;
    }
    html.la-embed-pending .wrapper,
    body.la-embed-mode .wrapper {
        padding-top: 0 !important;
        min-height: 0 !important;
    }
    body.la-embed-mode .la-reservation-page {
        margin: 0 !important;
    }
</style>
@endif
<style>
    .la-reservation-page {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
        color: #222;
        margin: -10px -12px 0;
        background: #e8e8e8;
    }
    .la-reservation-page * { box-sizing: border-box; }
    .la-reservation-page input,
    .la-reservation-page select,
    .la-reservation-page textarea,
    .la-reservation-page button {
        font-family: Tahoma, Arial, sans-serif;
        font-size: 11px;
    }
    .la-topbar {
        display: flex;
        align-items: center;
        justify-content: space-between;
        gap: 8px;
        padding: 4px 8px;
        background: #f0f0f0;
        border-bottom: 1px solid #bbb;
    }
    .la-conf-row { display: flex; align-items: center; gap: 6px; }
    .la-conf-row label { margin: 0; font-weight: 700; }
    .la-conf-input { width: 72px; height: 22px; border: 1px solid #999; padding: 2px 4px; background: #fff; text-align: center; font-weight: 700; }
    .la-btn-read {
        background: #c00; color: #fff; border: 1px solid #900;
        padding: 2px 8px; font-weight: 700; cursor: pointer; height: 22px;
    }
    .la-warning-banner {
        flex: 1;
        background: #ffffcc;
        border: 1px solid #e6e600;
        color: #c00;
        font-weight: 700;
        text-align: center;
        padding: 4px 8px;
        font-size: 11px;
    }
    .la-toolbar { display: flex; flex-wrap: wrap; gap: 2px; }
    .la-tool-btn {
        display: inline-flex; flex-direction: column; align-items: center;
        background: #f8f8f8; border: 1px solid #aaa; padding: 2px 6px;
        min-width: 52px; cursor: pointer; color: #333; text-decoration: none;
    }
    .la-tool-btn:hover { background: #e8e8e8; text-decoration: none; color: #000; }
    .la-tool-btn i { font-size: 14px; line-height: 1; margin-bottom: 1px; }
    .la-tool-btn span { font-size: 9px; line-height: 1.1; }
    .la-tool-btn.la-tool-primary { background: #dceeff; border-color: #6a9fd8; }
    .la-columns {
        display: grid;
        grid-template-columns: 1fr 1.15fr 1fr;
        gap: 4px;
        padding: 4px;
        min-height: calc(100vh - 180px);
    }
    @media (max-width: 1400px) {
        .la-columns { grid-template-columns: 1fr; }
    }
    .la-col {
        background: #f5f5f5;
        border: 1px solid #aaa;
        display: flex;
        flex-direction: column;
        min-width: 0;
    }
    .la-tabs {
        display: flex;
        border-bottom: 1px solid #888;
        background: #ddd;
    }
    .la-tab {
        padding: 4px 10px;
        border: 1px solid #888;
        border-bottom: none;
        background: #ccc;
        cursor: pointer;
        margin-right: 2px;
        font-weight: 700;
        position: relative;
        top: 1px;
    }
    .la-tab.active { background: #f5f5f5; }
    .la-tab-panel { display: none; padding: 4px; flex: 1; }
    .la-tab-panel.active { display: block; }
    .la-section { margin-bottom: 4px; }
    .la-section-title {
        background: #b8d4f0;
        border: 1px solid #7aa3cc;
        padding: 2px 6px;
        font-weight: 700;
        font-size: 11px;
    }
    .la-section-body { padding: 4px; border: 1px solid #ccc; border-top: none; background: #fff; }
    .la-section-body.pink { background: #fce4ec; }
    .la-section-body.yellow { background: #fffde7; }
    .la-field-row {
        display: grid;
        grid-template-columns: repeat(12, 1fr);
        gap: 3px;
        margin-bottom: 3px;
        align-items: end;
    }
    .la-field { min-width: 0; }
    .la-field label {
        display: block;
        font-size: 10px;
        margin-bottom: 1px;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .la-field input,
    .la-field select,
    .la-field textarea {
        width: 100%;
        height: 20px;
        border: 1px solid #999;
        padding: 1px 3px;
        background: #fff;
    }
    .la-field input[type="date"],
    .la-field input[type="time"] {
        cursor: pointer;
    }
    .la-field textarea { height: 48px; resize: vertical; }
    .la-field textarea.la-notes-lg { height: 56px; }
    .la-field .la-readonly { background: #eee; }
    .la-links { font-size: 10px; margin: 2px 0 4px; }
    .la-links a { color: #06c; margin-right: 8px; }
    .la-radio-bar {
        display: flex; flex-wrap: wrap; gap: 8px; align-items: center;
        padding: 3px 4px; background: #f0f0f0; border: 1px solid #ccc; margin-bottom: 3px;
    }
    .la-radio-bar label { margin: 0; font-size: 10px; display: inline-flex; align-items: center; gap: 3px; }
    .la-addr-actions { display: flex; gap: 4px; margin: 3px 0; }
    .la-addr-actions button {
        background: #c00; color: #fff; border: 1px solid #900;
        padding: 1px 8px; font-size: 10px; cursor: default;
    }
    .la-vehicle-bar {
        background: #b8d4f0; border: 1px solid #7aa3cc;
        padding: 3px 6px; font-weight: 700; margin: 4px 0;
    }
    .la-notes-block { margin-bottom: 3px; }
    .la-notes-block .la-notes-head {
        display: flex; justify-content: space-between; align-items: center;
        background: #fff9c4; border: 1px solid #ccc; border-bottom: none; padding: 2px 4px;
    }
    .la-notes-block textarea {
        width: 100%; border: 1px solid #ccc; background: #fffde7;
        padding: 3px; font-size: 11px; min-height: 52px;
    }
    .la-pricing-table { width: 100%; border-collapse: collapse; font-size: 10px; }
    .la-pricing-table th,
    .la-pricing-table td {
        border: 1px solid #bbb; padding: 1px 2px; text-align: center;
    }
    .la-pricing-table th { background: #ddd; font-weight: 700; }
    .la-pricing-table input {
        width: 100%; height: 18px; border: 1px solid #999; text-align: right; padding: 0 2px;
    }
    .la-pricing-table .la-label-col { text-align: left; font-weight: 600; background: #f8f8f8; white-space: nowrap; }
    .la-totals { margin-top: 4px; border: 1px solid #888; }
    .la-totals-row {
        display: flex; justify-content: space-between; align-items: center;
        padding: 2px 6px; border-bottom: 1px solid #ccc; font-weight: 700;
    }
    .la-totals-row:last-child { border-bottom: none; }
    .la-totals-row.grand { color: #228b22; background: #fff; }
    .la-totals-row.payments { color: #06c; }
    .la-totals-row.due { color: #c00; background: #ffe0e0; }
    .la-totals-row input {
        width: 80px; height: 20px; border: 1px solid #999; text-align: right; font-weight: 700;
    }
    .la-assign-tabs { display: flex; gap: 2px; margin: 4px 0 2px; }
    .la-assign-tab {
        padding: 2px 8px; border: 1px solid #888; background: #ddd;
        font-size: 10px; cursor: pointer;
    }
    .la-assign-tab.active { background: #fff; font-weight: 700; }
    .la-footer-link { text-align: right; padding: 4px; font-size: 10px; }
    .la-footer-link a { color: #06c; }
    .la-hidden-functional { display: none !important; }
    .location-suggestions {
        position: absolute; top: 100%; left: 0; width: 100%;
        background: #fff; z-index: 10050; border: 1px solid #999;
        max-height: 200px; overflow-y: auto; box-shadow: 0 2px 6px rgba(0,0,0,.15);
        display: none;
    }
    .suggestion-item {
        padding: 4px 6px; cursor: pointer; border-bottom: 1px solid #eee;
        font-size: 11px;
    }
    .suggestion-item:hover { background: #f0f0f0; }
    .suggestion-item .main-text { font-weight: 600; display: block; }
    .suggestion-item .sub-text { color: #666; font-size: 10px; display: block; }
    .la-compact-select { position: relative; }
    .la-compact-select-trigger {
        width: 100%; height: 20px; border: 1px solid #999; background: #fff;
        text-align: left; padding: 0 18px 0 3px; overflow: hidden;
        white-space: nowrap; text-overflow: ellipsis; cursor: pointer;
    }
    .la-compact-select-arrow {
        position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
        pointer-events: none; font-size: 9px; color: #666;
    }
    .la-compact-select-panel {
        position: absolute; top: calc(100% + 1px); left: 0; right: 0;
        background: #fff; border: 1px solid #999; z-index: 1050;
        max-height: 220px; overflow: hidden; display: none;
        box-shadow: 0 4px 12px rgba(0,0,0,.12);
    }
    .la-compact-select.open .la-compact-select-panel { display: block; }
    .la-compact-select-search {
        width: 100%; border: none; border-bottom: 1px solid #ddd;
        padding: 4px 6px; outline: none;
    }
    .la-compact-select-list {
        list-style: none; margin: 0; padding: 0; max-height: 180px; overflow-y: auto;
    }
    .la-compact-select-option {
        padding: 4px 6px; cursor: pointer; border-bottom: 1px solid #f0f0f0;
    }
    .la-compact-select-option:hover,
    .la-compact-select-option.selected { background: #e8f0ff; }
    .la-payment-panel { padding: 8px; }
    .la-payment-panel .form-control { font-size: 12px; min-height: 32px; }
    #reservation-card-element {
        padding: 8px !important; min-height: 38px;
        background: #fff !important; border: 1px solid #999 !important;
    }
    .pac-container { z-index: 10050 !important; }
    .la-col-middle .la-timing-bar { background: #fff9c4; padding: 4px; border: 1px solid #ccc; margin-bottom: 4px; }

    /* Middle column — routing & notes (LA layout) */
    .la-col-middle { background: #fff; padding: 4px; }
    .la-mid-timing { margin-bottom: 4px; }
    .la-mid-timing .la-field label { color: #228b22; font-weight: 700; }
    .la-addr-box { border: 1px solid #888; background: #fff; margin-bottom: 4px; }
    .la-addr-tabs {
        display: flex; flex-wrap: wrap; background: #ececec;
        border-bottom: 1px solid #888; padding: 2px 2px 0;
    }
    .la-addr-tab {
        display: inline-flex; align-items: center; gap: 3px;
        padding: 3px 8px; margin-right: 2px; margin-bottom: 2px;
        border: 1px solid #aaa; border-bottom: none; background: #ddd;
        font-size: 10px; cursor: default; color: #222;
    }
    .la-addr-tab.active { background: #b8d4f0; font-weight: 700; color: #06c; border-bottom: 2px solid #06c; }
    .la-addr-tab { cursor: pointer; }
    .la-addr-tab .bi { font-size: 11px; color: #06c; }
    .la-addr-type-panel { display: none; }
    .la-addr-type-panel.active { display: block; }
    .la-stored-row { display: flex; gap: 6px; margin-bottom: 4px; }
    .la-stored-row > .la-field { flex: 1; min-width: 0; }
    .la-stored-with-btn { display: flex; gap: 2px; align-items: center; }
    .la-stored-with-btn select { flex: 1; min-width: 0; height: 20px; border: 1px solid #999; font-size: 11px; }
    .la-btn-dots {
        flex-shrink: 0; width: 22px; height: 20px; padding: 0; border: 1px solid #999;
        background: #ececec; font-size: 10px; font-weight: 700; line-height: 1; cursor: default;
    }
    .la-stored-cyan { background: #d6e8ff !important; }
    .la-stored-pink { background: #ffd6d6 !important; }
    .la-lbl-red { color: #c00 !important; font-weight: 600; }
    .la-poi-save-row { display: flex; align-items: flex-end; gap: 8px; }
    .la-poi-save-row .la-field { flex: 1; }
    .la-poi-save-row label.la-poi-save-label { margin: 0; font-size: 10px; white-space: nowrap; padding-bottom: 2px; }
    .la-addr-box-body { padding: 4px; }
    .la-stored-addr-select {
        width: 100%; height: 20px; border: 1px solid #999;
        background: #d6e8ff; margin-bottom: 4px; font-size: 11px;
    }
    .la-routing-locations-priority {
        display: grid; grid-template-columns: 1fr 1fr; gap: 4px;
        margin-bottom: 4px; padding-bottom: 4px; border-bottom: 1px solid #ccc;
    }
    .la-routing-locations-priority .la-field label { font-weight: 700; color: #228b22; }
    @media (max-width: 1100px) {
        .la-routing-locations-priority { grid-template-columns: 1fr; }
    }
    .la-routing-notes-row {
        display: grid; grid-template-columns: 1fr 130px; gap: 4px; align-items: start;
        margin-top: 4px;
    }
    .la-airport-contact-row { margin-top: 2px; }
    .la-routing-notes-airport { grid-template-columns: 1fr 90px; }
    .la-routing-side .la-field { margin-bottom: 3px; }
    .la-routing-actions {
        display: flex; align-items: center; justify-content: space-between;
        margin-top: 4px; padding-top: 4px; border-top: 1px solid #ddd;
    }
    .la-routing-radios { display: flex; flex-wrap: wrap; gap: 10px; align-items: center; }
    .la-routing-radios label {
        margin: 0; font-size: 10px; font-weight: 600; color: #c00;
        display: inline-flex; align-items: center; gap: 3px;
    }
    .la-btn-create {
        height: 22px; padding: 0 14px; border: 1px solid #666;
        background: linear-gradient(180deg, #f8f8f8 0%, #d8d8d8 100%);
        font-weight: 700; font-size: 10px; cursor: default;
    }
    .la-routing-footer-bar {
        background: #d0d0d0; border: 1px solid #aaa; border-top: none;
        padding: 2px 6px; font-weight: 700; font-size: 10px;
    }
    .la-routing-footer-empty {
        padding: 4px 6px; font-size: 10px; color: #444;
        border: 1px solid #ccc; border-top: none; background: #fafafa;
    }
    .la-routing-stored-list { border: 1px solid #ccc; border-top: none; background: #fafafa; }
    .la-routing-stored-list .la-routing-footer-empty { border: none; }
    .la-routing-stored-row {
        display: flex; align-items: center; justify-content: space-between; gap: 8px;
        padding: 3px 6px; font-size: 10px; font-weight: 600;
        background: #fff8c6; border-bottom: 1px solid #e0d48a;
    }
    .la-routing-stored-row:last-child { border-bottom: none; }
    .la-routing-stored-row[data-source="address"] { background: #d6e8ff; border-bottom-color: #b8d4f0; }
    .la-routing-stored-row[data-source="airport"] { background: #fff8c6; border-bottom-color: #e0d48a; }
    .la-routing-stored-row[data-source="fbo"] { background: #ffd6e8; border-bottom-color: #f0b8d0; }
    .la-routing-stored-main { flex: 1; min-width: 0; }
    .la-routing-source-tag {
        display: inline-block; margin-right: 4px; padding: 0 4px;
        font-size: 9px; font-weight: 700; letter-spacing: 0.02em;
        border: 1px solid transparent; border-radius: 2px; vertical-align: baseline;
    }
    .la-routing-source-tag.is-address { background: #c5dbf7; border-color: #8eb6e0; color: #1a4a7a; }
    .la-routing-source-tag.is-airport { background: #f5e89a; border-color: #c9b84a; color: #6a5a00; }
    .la-routing-source-tag.is-fbo { background: #f5b8d0; border-color: #d07098; color: #7a2048; }
    .la-routing-not-verified { color: #c00; font-weight: 700; margin-left: 4px; }
    .la-routing-stored-actions { display: inline-flex; gap: 8px; flex-shrink: 0; }
    .la-routing-edit {
        color: #222; text-decoration: underline; cursor: pointer; font-weight: 600; background: none; border: none; padding: 0; font-size: 10px;
    }
    .la-routing-delete {
        color: #c00; text-decoration: underline; cursor: pointer; font-weight: 700; background: none; border: none; padding: 0; font-size: 10px;
    }
    .la-routing-addr-pane.d-none { display: none !important; }
    .la-mid-notes-block {
        border: 1px solid #bbb; margin-bottom: 4px; background: #fff;
    }
    .la-mid-notes-head {
        display: flex; justify-content: space-between; align-items: center;
        padding: 2px 6px; background: #ececec; border-bottom: 1px solid #bbb;
        font-weight: 700; font-size: 11px;
    }
    .la-mid-notes-limit { font-size: 9px; color: #555; font-weight: normal; }
    .la-mid-notes-block textarea {
        width: 100%; min-height: 72px; border: none; border-bottom: 1px solid #bbb;
        background: #fffde7; padding: 4px 6px; font-size: 11px; resize: vertical;
    }
    .la-mid-notes-foot {
        display: flex; justify-content: space-between; align-items: center;
        padding: 3px 6px; background: #f5f5f5;
    }
    .la-mid-notes-foot-left { display: flex; gap: 10px; align-items: center; }
    .la-mid-notes-foot label { margin: 0; font-size: 10px; color: #c00; font-weight: 600; }
    .la-btn-save-notes {
        height: 20px; padding: 0 10px; border: 1px solid #888;
        background: #e8e8e8; font-size: 10px; font-weight: 700; cursor: pointer;
    }
    .la-btn-save-notes:hover { background: #f4f4f4; }
    .la-btn-save-notes.saved {
        background: #d4edda; border-color: #7bb98a; color: #155724;
    }
    .la-routing-addr-pane.d-none { display: none !important; }

    /* Left column — Bill To & Pax (LA layout) */
    .la-col-left { background: #fff; }
    .la-left-conf {
        display: flex; align-items: center; gap: 4px;
        padding: 4px 6px; border-bottom: 1px solid #ccc; background: #f5f5f5;
    }
    .la-left-conf label { margin: 0; font-weight: 700; font-size: 11px; }
    .la-conf-mini { width: 28px; height: 20px; border: 1px solid #999; background: #fff; padding: 0 2px; }
    .la-conf-main {
        width: 56px; height: 20px; border: 1px solid #999; background: #fff;
        color: #c00; font-weight: 700; text-align: center; padding: 0 2px;
    }
    .la-left-body { padding: 4px; }
    .la-left-block { margin-bottom: 6px; padding-bottom: 6px; border-bottom: 1px solid #e0e0e0; }
    .la-left-block:last-child { border-bottom: none; margin-bottom: 0; }
    .la-lbl-green { color: #228b22; font-weight: 600; }
    .la-lbl-blue { color: #06c; font-weight: 600; }
    .la-input-pink,
    .la-input-pink.la-compact-select-trigger { background: #ffd6d6 !important; }
    .la-input-agent { background: #d6e8ff !important; }
    .la-input-cl-wrap { display: flex; gap: 2px; align-items: center; }
    .la-input-cl-wrap input,
    .la-input-cl-wrap select,
    .la-input-cl-wrap .la-compact-select { flex: 1; min-width: 0; }
    .la-btn-cl {
        flex-shrink: 0; width: 22px; height: 20px; padding: 0;
        border: 1px solid #999; background: #f0f0f0; font-size: 9px; font-weight: 700; cursor: default;
    }
    .la-btn-create-acct {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        height: 20px;
        padding: 0 6px;
        border: 1px solid #999;
        background: #f8f8f8;
        font-size: 10px;
        white-space: nowrap;
        cursor: pointer;
        text-decoration: none;
        color: #222;
        line-height: 1;
    }
    .la-account-row {
        display: flex;
        align-items: center;
        gap: 6px;
        flex-wrap: nowrap;
        margin-bottom: 4px;
    }
    .la-account-row > .la-account-label {
        font-size: 10px;
        font-weight: 700;
        white-space: nowrap;
        margin: 0;
        flex-shrink: 0;
    }
    .la-account-row .la-compact-select {
        flex: 1 1 auto;
        min-width: 0;
    }
    .la-account-row .la-btn-create-acct { flex-shrink: 0; }
    .la-account-row .la-account-copy {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        font-size: 10px;
        font-weight: 700;
        margin: 0;
        white-space: nowrap;
        flex-shrink: 0;
        cursor: default;
    }
    .la-account-row .la-account-copy input[type="checkbox"] {
        margin: 0;
        width: 14px;
        height: 14px;
        flex-shrink: 0;
    }
    .la-phone-wrap { display: flex; gap: 2px; align-items: center; width: 100%; }
    .la-phone-input-wrap {
        position: relative; flex: 1; min-width: 0; width: 100%;
        --iti-spacer-horizontal: 2px;
        --iti-arrow-padding: 1px;
        --iti-selected-country-arrow-padding: 2px;
        --iti-flag-height: 11px;
    }
    .la-phone-input-wrap .iti { width: 100%; display: block; }
    .la-phone-input-wrap .iti__tel-input,
    .la-phone-input-wrap input[type="tel"] {
        width: 100% !important;
        height: 20px !important;
        font-size: 11px !important;
        padding-right: 20px !important;
        padding-left: 34px !important;
        border: 1px solid #999;
        border-radius: 0;
    }
    .la-phone-input-wrap .iti__country-container {
        height: 20px;
        width: auto !important;
        min-width: 0;
    }
    .la-phone-input-wrap .iti__selected-country {
        height: 20px; min-height: 20px;
        padding: 0 1px 0 3px;
        gap: 0;
    }
    .la-phone-input-wrap .iti__selected-country-primary { padding: 0 1px; }
    .la-phone-input-wrap .iti__flag-container { padding: 0 1px 0 0; }
    .la-phone-input-wrap .iti__arrow {
        margin-left: 0; margin-right: 0;
        border-top-width: 3px; border-left-width: 3px; border-right-width: 3px;
    }
    .la-phone-input-wrap .la-phone-ico {
        position: absolute; right: 4px; top: 50%; transform: translateY(-50%);
        pointer-events: none; color: #666; font-size: 11px; line-height: 1; z-index: 2;
    }
    .iti__dropdown-content { z-index: 10060 !important; font-size: 11px; }
    .la-link-heading {
        color: #06c; text-decoration: underline; font-weight: 700;
        font-size: 11px; display: inline-block; margin-bottom: 4px; cursor: pointer;
    }
    .la-additional-passengers-panel { display: none; }
    .la-additional-passengers-panel.open { display: block; }
    .la-sub-heading { font-weight: 700; font-size: 11px; margin-bottom: 3px; }
    .la-btn-add {
        float: right; height: 20px; padding: 0 10px; border: 1px solid #888;
        background: #e8e8e8; font-weight: 700; font-size: 10px; cursor: default;
    }
    .la-passenger-list-bar {
        background: #d8d8d8; border: 1px solid #bbb; padding: 2px 6px;
        font-weight: 700; font-size: 10px; margin-top: 4px;
    }
    .la-passenger-list-empty { padding: 4px 6px; font-size: 10px; color: #555; border: 1px solid #ddd; border-top: none; }
    .la-greeting-row { display: grid; grid-template-columns: 1fr 1fr; gap: 4px; align-items: start; }
    .la-greeting-left { display: flex; flex-direction: column; gap: 3px; }
    .la-btn-sign-creator {
        height: 22px; padding: 0 6px; border: 1px solid #999; background: #fff;
        font-size: 10px; text-align: left; cursor: default;
    }
    .la-agent-row { display: flex; gap: 2px; align-items: center; }
    .la-agent-row select { flex: 1; min-width: 0; }
    .la-agent-row .la-agent-pct { width: 44px; flex-shrink: 0; text-align: right; }
    .la-agent-row .la-agent-type { width: 32px; flex-shrink: 0; }
    .la-field-icon-label { display: flex; align-items: center; gap: 3px; }
    .la-field-icon-label .bi-person-fill { color: #228b22; font-size: 11px; }
    .la-clearfix::after { content: ''; display: table; clear: both; }

    /* Right column — LA dispatch / pricing layout */
    .la-col-right { background: #ececec; font-size: 10px; }
    .la-right-body { padding: 3px; flex: 1; overflow-y: auto; }
    .la-right-section {
        border: 1px solid #a8a8a8;
        background: #fff;
        padding: 3px 4px;
        margin-bottom: 4px;
    }
    .la-right-section:last-child { margin-bottom: 0; }
    .la-right-section-farm {
        background: #ffffcc;
        border-color: #ccc;
        padding: 4px 6px;
    }
    .la-right-kv-row {
        display: flex;
        align-items: baseline;
        gap: 4px;
        margin-bottom: 2px;
        font-size: 10px;
        line-height: 1.3;
    }
    .la-right-kv-row:last-child { margin-bottom: 0; }
    .la-right-kv-label { font-weight: 700; white-space: nowrap; }
    .la-right-kv-value { flex: 1; min-width: 0; }
    .la-right-lr-row {
        display: grid;
        grid-template-columns: 102px minmax(0, 1fr);
        align-items: center;
        gap: 4px;
        margin-bottom: 2px;
        font-size: 10px;
    }
    .la-right-lr-row:last-child { margin-bottom: 0; }
    .la-right-lr-label {
        display: block;
        font-size: 10px;
        font-weight: 700;
        margin: 0;
        white-space: nowrap;
        text-align: right;
        padding-right: 2px;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .la-lbl-green { color: #228b22; }
    .la-right-grid-4 {
        display: grid; grid-template-columns: repeat(4, 1fr); gap: 3px;
        margin-bottom: 3px; align-items: end;
    }
    .la-right-grid-3 {
        display: grid; grid-template-columns: 1.1fr 1.6fr 0.9fr; gap: 3px;
        margin-bottom: 3px; align-items: end;
    }
    .la-right-field label { display: block; font-size: 10px; margin-bottom: 1px; font-weight: 700; }
    .la-right-field input,
    .la-right-field select {
        width: 100%; height: 20px; border: 1px solid #999; padding: 0 3px; background: #fff; font-size: 10px;
    }
    .la-right-field input:disabled,
    .la-right-field input[readonly] { background: #f0f0f0; color: #555; }
    .la-right-lr-row input,
    .la-right-lr-row select { width: 100%; min-width: 0; height: 20px; border: 1px solid #999; padding: 0 3px; background: #fff; font-size: 10px; box-sizing: border-box; }
    .la-right-lr-row > input[readonly] { background: #f0f0f0; color: #555; }
    .la-right-lr-row .la-right-toggle { width: 100%; min-width: 0; }
    .la-right-lr-row .la-compact-select { width: 100%; min-width: 0; }
    .la-right-lr-row .la-compact-select-trigger { height: 20px; font-size: 10px; }
    .la-right-links {
        display: flex;
        justify-content: space-between;
        align-items: center;
        font-size: 10px;
        margin: 0;
        padding-top: 1px;
    }
    .la-right-links a { color: #06c; text-decoration: underline; }
    .la-right-links .la-red-text { color: #c00; font-weight: 700; }
    .la-right-radios {
        display: flex; flex-wrap: wrap; gap: 10px; align-items: center;
        margin: 0; font-size: 10px;
    }
    .la-right-radios label { margin: 0; display: inline-flex; align-items: center; gap: 3px; font-weight: 400; }
    .la-right-toggle {
        display: inline-flex; border: 1px solid #888; width: 100%;
    }
    .la-right-toggle label {
        margin: 0; padding: 2px 8px; border-right: 1px solid #888;
        background: #ddd; cursor: default; font-size: 10px;
        flex: 1; text-align: center; display: inline-flex; align-items: center; justify-content: center; gap: 3px;
    }
    .la-right-toggle label:last-child { border-right: none; }
    .la-right-toggle label:has(input:checked) { background: #b8d4f0; font-weight: 700; }
    .la-right-affiliate-row { display: flex; gap: 2px; align-items: center; width: 100%; min-width: 0; }
    .la-right-affiliate-row input { flex: 1; min-width: 0; height: 20px; border: 1px solid #999; font-size: 10px; padding: 0 3px; }
    .la-right-affiliate-row .la-btn-mini {
        flex-shrink: 0; width: 22px; height: 20px; padding: 0; border: 1px solid #999;
        background: #ececec; font-size: 10px; font-weight: 700; cursor: default;
    }
    .la-right-rate-btns { display: flex; gap: 4px; margin: 0 0 4px; }
    .la-right-rate-btns button {
        flex: 1; height: 22px; border: 1px solid #666;
        background: linear-gradient(180deg, #f8f8f8 0%, #d0d0d0 100%);
        font-weight: 700; font-size: 10px; cursor: default;
    }
    .la-price-tabs { display: flex; gap: 2px; margin-bottom: 2px; }
    .la-price-tab {
        padding: 2px 8px; border: 1px solid #888; background: #ddd;
        font-size: 10px; cursor: pointer;
    }
    .la-price-tab.active { background: #fff; font-weight: 700; border-bottom-color: #fff; }
    .la-price-panel { display: none; border: 1px solid #888; background: #fff; padding: 2px; }
    .la-price-panel.active { display: block; }
    .la-price-line {
        display: grid; grid-template-columns: 1fr 42px 42px 36px 52px; gap: 2px;
        align-items: center; margin-bottom: 1px; font-size: 10px;
    }
    .la-price-line > span { padding: 0 2px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
    .la-price-line.la-discount > span { color: #c00; }
    .la-price-line input {
        width: 100%; height: 18px; border: 1px solid #999; text-align: right; padding: 0 2px; font-size: 10px;
    }
    .la-price-line input[readonly] { background: #eee; }
    .la-right-totals { border: none; margin-top: 3px; background: transparent; padding: 0; }
    .la-right-totals.la-totals { border: 1px solid #888; }
    .la-right-totals .la-totals-row { padding: 2px 4px; font-size: 10px; }
    .la-right-totals .la-totals-row input { width: 72px; }
    .la-right-totals .la-currency { width: 52px; height: 20px; margin-right: 4px; }
    .la-right-assign .la-price-tabs { margin-bottom: 3px; }
    .la-right-assign .la-right-lr-row select {
        width: 100%;
        min-width: 0;
        height: 20px;
        border: 1px solid #999;
        padding: 0 3px;
        background: #fff;
        font-size: 10px;
        box-sizing: border-box;
    }
    .la-assign-driver-wrap {
        display: flex;
        align-items: center;
        gap: 4px;
        min-width: 0;
        flex: 1;
    }
    .la-assign-driver-wrap select { flex: 1; min-width: 0; }
    .la-driver-preview {
        width: 18px;
        height: 18px;
        border-radius: 50%;
        object-fit: cover;
        border: 1px solid #999;
        flex-shrink: 0;
        background: #eee;
    }
    .la-driver-preview.is-empty { display: none !important; }
    .la-right-footer {
        display: flex; justify-content: flex-end; align-items: center;
        padding: 4px 3px; border-top: 1px solid #aaa; background: #e0e0e0;
    }
    .la-btn-save-reservation {
        height: 26px; padding: 0 16px; border: 1px solid #666;
        background: linear-gradient(180deg, #f8f8f8 0%, #d0d0d0 100%);
        font-weight: 700; font-size: 11px; cursor: pointer;
    }
    .la-btn-save-reservation:hover { background: linear-gradient(180deg, #fff 0%, #d8d8d8 100%); }
    .la-right-payment {
        margin-top: 4px; border: 1px solid #888; background: #fff;
    }
    .la-right-payment-head {
        background: #d0d0d0; border-bottom: 1px solid #aaa;
        padding: 2px 6px; font-weight: 700; font-size: 10px;
    }
    .la-right-payment-body { padding: 4px; }
    .la-right-payment-total {
        display: flex; justify-content: space-between; align-items: baseline;
        margin-bottom: 4px; font-size: 10px;
    }
    .la-right-payment-total strong { font-size: 12px; color: #060; }
    .la-right-payment .la-right-field { margin-bottom: 4px; }
    .la-right-payment .la-right-field label { font-size: 10px; }
    .la-right-payment .form-control {
        width: 100%; height: 22px; border: 1px solid #999;
        font-size: 11px; padding: 0 4px;
    }
    .la-right-payment #reservation-card-element {
        height: auto; min-height: 34px; padding: 6px 4px !important;
    }
    .la-right-payment-actions {
        display: flex; flex-wrap: wrap; gap: 4px; margin-top: 4px;
    }
    .la-btn-pay-reservation {
        flex: 1 1 100%; min-height: 24px; padding: 2px 8px;
        border: 1px solid #2d6a2d; background: linear-gradient(180deg, #e8f5e8 0%, #b8ddb8 100%);
        font-weight: 700; font-size: 10px; cursor: pointer; color: #133;
    }
    .la-btn-pay-reservation:hover { background: linear-gradient(180deg, #f0faf0 0%, #c8e8c8 100%); }
    .la-btn-save-without-pay {
        flex: 1 1 100%; min-height: 22px; padding: 2px 8px;
        border: 1px solid #888; background: #f4f4f4;
        font-size: 10px; cursor: pointer; color: #333;
    }
    .la-btn-save-without-pay:hover { background: #fff; }
    .la-right-payment-note {
        font-size: 10px; color: #444; margin-bottom: 4px;
        padding: 4px; background: #f0f7ff; border: 1px solid #bcd;
    }
    .la-child-seat-panel {
        display: none; border: 1px solid #aaa; background: #f8f8f8;
        padding: 4px; margin-bottom: 4px;
    }
    .la-child-seat-panel.open { display: block; }
    .la-child-seat-panel-title { font-weight: 700; font-size: 10px; margin-bottom: 4px; }
    .la-child-seat-add-row {
        display: grid; grid-template-columns: 1fr 80px auto; gap: 4px; align-items: end;
    }
    .la-btn-add-seat {
        height: 20px; padding: 0 10px; border: 1px solid #666;
        background: linear-gradient(180deg, #f8f8f8 0%, #d0d0d0 100%);
        font-weight: 700; font-size: 10px; cursor: pointer;
    }
    .la-child-seat-added { margin-top: 4px; font-size: 10px; }
    .la-child-seat-added li { margin-bottom: 2px; }
</style>
@endpush

@section('content')
@php
    $formDefaults = $formDefaults ?? [];
    $formValue = fn ($key, $default = null) => old($key, $formDefaults[$key] ?? $default);
    $formBool = fn ($key, $default = false) => in_array(old($key, $formDefaults[$key] ?? $default), [true, 1, '1', 'true', 'on', 'yes'], true);
    $isEditMode = !empty($isEditMode);
    $isEmbed = !empty($isEmbed);
    $formAction = $formAction ?? route('reservation.store');
    $formMethod = strtoupper($formMethod ?? 'POST');
    $draftBookingId = (!$isEditMode && isset($draftBooking) && $draftBooking) ? $draftBooking->id : null;
    $pickupFlightOld = $formValue('pickup_flight_details');
    $meetOld = $formValue('meet_option');
    $serviceOptionOld = $formValue('service_option', 'point_to_point');
    $selectedVehicle = $vehicles->firstWhere('id', (int) $formValue('vehicle_id'));
    $selectedVehicleLabel = $selectedVehicle ? $selectedVehicle->vehicle_name : '---- NOT ASSIGNED ----';
    $selectedAccount = ($accounts ?? collect())->firstWhere('id', (int) $formValue('account_id'));
    $selectedAccountLabel = $selectedAccount
        ? ($selectedAccount->company_name . ($selectedAccount->company_number ? ' (' . $selectedAccount->company_number . ')' : ''))
        : 'Select account';
    $usStates = ['AL','AK','AZ','AR','CA','CO','CT','DE','FL','GA','HI','ID','IL','IN','IA','KS','KY','LA','ME','MD','MA','MI','MN','MS','MO','MT','NE','NV','NH','NJ','NM','NY','NC','ND','OH','OK','OR','PA','RI','SC','SD','TN','TX','UT','VT','VA','WA','WV','WI','WY','DC'];
    $laAirlinesJson = ($airlines ?? collect())->map(fn ($a) => [
        'id' => $a->id,
        'code' => $a->iata_code,
        'name' => $a->name,
    ])->values();
    $laAirportsJson = ($airports ?? collect())->map(fn ($a) => [
        'id' => $a->id,
        'code' => $a->iata_code,
        'name' => $a->name,
        'city' => $a->city,
        'state' => $a->state ?? null,
    ])->values();
    $laFbosJson = ($fbos ?? collect())->map(fn ($f) => [
        'id' => $f->id,
        'name' => $f->name,
        'airportCode' => $f->airport_code,
        'street1' => $f->address_line1,
        'street2' => $f->address_line2,
        'city' => $f->city,
        'state' => $f->state,
        'zip' => $f->zip,
        'country' => $f->country ?: 'United States',
        'label' => $f->displayLabel(),
    ])->values();
@endphp

<div class="la-reservation-page">
    @if ($errors->any())
        <div class="alert alert-danger m-2 py-2" style="font-size:12px;">
            <ul class="mb-0 pl-3">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form method="post" action="{{ $formAction }}" id="reservation-form" novalidate>
        @csrf
        @if($formMethod !== 'POST')
            @method($formMethod)
        @endif
        @if($isEmbed)
            <input type="hidden" name="_embed" value="1">
        @endif
        <input type="hidden" name="is_airport" id="is_airport" value="{{ $formBool('is_airport') ? '1' : '0' }}">
        <input type="hidden" name="booking_for_someone_else" id="booking_for_someone_else" value="{{ $formBool('booking_for_someone_else') ? '1' : '0' }}">
        <input type="hidden" name="return_service" id="return_service" value="{{ $formBool('return_service') ? '1' : '0' }}">
        <input type="hidden" name="no_flight_info" id="no_flight_info" value="{{ $formBool('no_flight_info', true) ? '1' : '0' }}">
        <input type="hidden" name="child_seat_required" id="child_seat_required" value="{{ $formBool('child_seat_required') ? '1' : '0' }}">
        <input type="hidden" name="child_seat_type" id="child_seat_type" value="{{ $formValue('child_seat_type') }}">
        <input type="hidden" name="child_seat_quantity" id="child_seat_quantity" value="{{ $formValue('child_seat_quantity') }}">
        <input type="hidden" name="draft_booking_id" id="draft_booking_id" value="{{ $draftBookingId }}">

        <div class="la-topbar">
            <div class="la-warning-banner" style="flex:1;">
                @if($isEditMode)
                    Editing reservation #{{ $nextConfirmationNumber ?? ($editingBooking->booking_id ?? '') }}. Update fields and save to apply changes.
                @elseif(!empty($draftBookingId))
                    Draft booking #{{ $nextConfirmationNumber ?? $draftBookingId }} is open. Notes, routing, and airport details auto-save to this booking. Complete and save to finalize.
                @else
                    ATTENTION! This reservation was created but has not been saved. You must save it before leaving this screen.
                @endif
            </div>
        </div>

        <div class="la-columns">
            {{-- LEFT COLUMN --}}
            <div class="la-col la-col-left">
                <div class="la-left-conf">
                    <label>Conf#</label>
                    <input type="text" class="la-conf-mini" readonly tabindex="-1">
                    <input type="text" class="la-conf-main" id="la-conf-number" value="{{ $nextConfirmationNumber ?? '' }}" readonly tabindex="-1">
                    <input type="text" class="la-conf-mini" readonly tabindex="-1">
                </div>

                <div class="la-tabs" data-la-tabs="left">
                    <div class="la-tab active" data-tab="billto">Bill To &amp; Pax</div>
                </div>

                <div class="la-tab-panel active" data-panel="billto">
                    <div class="la-left-body">
                        {{-- Account --}}
                        <div class="la-left-block">
                            <div class="la-account-row">
                                <label class="la-account-label">Account</label>
                                <div class="la-compact-select la-input-pink" id="account-select">
                                    <button type="button" class="la-compact-select-trigger la-input-pink" aria-haspopup="listbox">
                                        <span class="la-compact-select-value">{{ $selectedAccountLabel }}</span>
                                    </button>
                                    <span class="la-compact-select-arrow"><i class="bi bi-chevron-down"></i></span>
                                    <div class="la-compact-select-panel">
                                        <input type="text" class="la-compact-select-search" placeholder="Search…" autocomplete="off">
                                        <ul class="la-compact-select-list" role="listbox">
                                            <li class="la-compact-select-option {{ (string) $formValue('account_id') === '' ? 'selected' : '' }}" data-value="" data-label="Select account" role="option">Select account</li>
                                            @foreach(($accounts ?? collect()) as $acc)
                                                @php
                                                    $accLabel = $acc->company_name . ($acc->company_number ? ' (' . $acc->company_number . ')' : '');
                                                    $accFilter = mb_strtolower(trim(($acc->company_name ?? '') . ' ' . ($acc->company_number ?? '') . ' ' . ($acc->email ?? '')), 'UTF-8');
                                                @endphp
                                                <li class="la-compact-select-option {{ (string) $formValue('account_id') === (string) $acc->id ? 'selected' : '' }}"
                                                    data-value="{{ $acc->id }}" data-label="{{ $accLabel }}" data-filter-text="{{ $accFilter }}" role="option">{{ $accLabel }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                    <input type="hidden" name="account_id" id="account_id" value="{{ $formValue('account_id') }}">
                                </div>
                                <a href="{{ route('accounts.index') }}" target="_blank" class="la-btn-create-acct">Create New Acct</a>
                                <label class="la-account-copy"><input type="checkbox" tabindex="-1"> Copy from here</label>
                            </div>
                        </div>

                        {{-- Billing / Company --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-field-icon-label"><i class="bi bi-person-fill"></i> Billing Contact</label>
                                    <div class="la-input-cl-wrap">
                                        <input type="text" class="la-input-pink" id="account_billing_name_view" readonly tabindex="-1">
                                        <button type="button" class="la-btn-cl" tabindex="-1">CL</button>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Company Name</label>
                                    <div class="la-input-cl-wrap">
                                        <input type="text" id="account_company_name_view" readonly tabindex="-1">
                                        <button type="button" class="la-btn-cl" tabindex="-1">CL</button>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Booked By --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-field-icon-label"><i class="bi bi-person-fill"></i> Booked By First</label>
                                    <input type="text" name="booker_first_name" id="booker_first_name" value="{{ $formValue('booker_first_name') }}">
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Booked By Last Name</label>
                                    <div class="la-input-cl-wrap">
                                        <input type="text" name="booker_last_name" id="booker_last_name" value="{{ $formValue('booker_last_name') }}">
                                        <button type="button" class="la-btn-cl" tabindex="-1">CL</button>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Booked By Phone</label>
                                    <div class="la-phone-wrap">
                                        <div class="la-phone-input-wrap">
                                            <input type="tel" class="la-intl-phone" name="booker_number" id="booker_number" value="{{ $formValue('booker_number') }}" autocomplete="tel">
                                            <i class="bi bi-telephone la-phone-ico"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Booked By Email</label>
                                    <input type="email" name="booker_email" id="booker_email" value="{{ $formValue('booker_email') }}">
                                </div>
                            </div>
                        </div>

                        {{-- Passenger --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-green la-field-icon-label"><i class="bi bi-person-fill"></i> Passenger First</label>
                                    <input type="text" name="first_name" value="{{ $formValue('first_name') }}" required>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-green">Passenger Last Name</label>
                                    <div class="la-input-cl-wrap">
                                        <input type="text" name="last_name" value="{{ $formValue('last_name') }}" required>
                                        <button type="button" class="la-btn-cl" tabindex="-1">CL</button>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-green">Passenger Phone</label>
                                    <div class="la-phone-wrap">
                                        <div class="la-phone-input-wrap">
                                            <input type="tel" class="la-intl-phone" name="number" id="passenger_number" value="{{ $formValue('number') }}" autocomplete="tel">
                                            <i class="bi bi-telephone la-phone-ico"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-green">Passenger Email</label>
                                    <input type="email" name="email" value="{{ $formValue('email') }}">
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-blue">Alternate Contact</label>
                                    <input type="text" tabindex="-1">
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label class="la-lbl-blue">Alt. Contact Phone #</label>
                                    <div class="la-phone-wrap">
                                        <div class="la-phone-input-wrap">
                                            <input type="tel" class="la-intl-phone" tabindex="-1" autocomplete="off">
                                            <i class="bi bi-telephone la-phone-ico"></i>
                                        </div>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>PO/Client Ref #</label>
                                    <input type="text" name="po_client_ref" id="po_client_ref" value="{{ $formValue('po_client_ref') }}" maxlength="100" autocomplete="off">
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Booker IATA</label>
                                    <input type="text" tabindex="-1">
                                </div>
                                <div class="la-field" style="grid-column: span 12;">
                                    <label>Voucher #</label>
                                    <input type="text" tabindex="-1">
                                </div>
                            </div>
                        </div>

                        {{-- Additional Passengers --}}
                        <div class="la-left-block la-clearfix">
                            <a href="#" class="la-link-heading" id="la-toggle-additional-passengers">Additional Passengers</a>
                            <div class="la-additional-passengers-panel" id="la-additional-passengers-panel">
                                <div class="la-sub-heading">Additional Passengers Info</div>
                                <div class="la-field-row">
                                    <div class="la-field" style="grid-column: span 6;">
                                        <label class="la-lbl-green">First Name</label>
                                        <input type="text" tabindex="-1">
                                    </div>
                                    <div class="la-field" style="grid-column: span 6;">
                                        <label class="la-lbl-green">Last Name</label>
                                        <input type="text" tabindex="-1">
                                    </div>
                                    <div class="la-field" style="grid-column: span 6;">
                                        <label class="la-lbl-green">Phone</label>
                                        <div class="la-phone-wrap">
                                            <div class="la-phone-input-wrap">
                                                <input type="tel" class="la-intl-phone" tabindex="-1" autocomplete="off">
                                                <i class="bi bi-telephone la-phone-ico"></i>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="la-field" style="grid-column: span 6;">
                                        <label class="la-lbl-green">Email</label>
                                        <input type="text" tabindex="-1">
                                    </div>
                                </div>
                                <button type="button" class="la-btn-add" tabindex="-1">ADD</button>
                                <div class="la-passenger-list-bar">Additional Passenger List</div>
                                <div class="la-passenger-list-empty">No Additional Passengers</div>
                            </div>
                        </div>

                        {{-- Group / Greeting --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Group Name</label>
                                    <select tabindex="-1"><option></option></select>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Occasion</label>
                                    <select tabindex="-1"><option></option></select>
                                </div>
                            </div>
                            <div class="la-greeting-row">
                                <div class="la-greeting-left">
                                    <div class="la-field">
                                        <label>Greeting Sign</label>
                                        <select tabindex="-1"><option>Yes</option><option>No</option></select>
                                    </div>
                                    <button type="button" class="la-btn-sign-creator" tabindex="-1">a Launch Sign Creator</button>
                                </div>
                                <div class="la-field">
                                    <label>Greeting Sign Notes</label>
                                    <textarea tabindex="-1" style="height:52px;"></textarea>
                                </div>
                            </div>
                        </div>

                        {{-- Notifications --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Send Confirmations</label>
                                    <select tabindex="-1"><option>Do Not Send</option><option>Send</option></select>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Change Notifications</label>
                                    <select tabindex="-1"><option>Do Not Send</option><option>Send</option></select>
                                </div>
                            </div>
                        </div>

                        {{-- Agents --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 12;">
                                    <label>Primary Agent</label>
                                    <div class="la-agent-row">
                                        <select class="la-input-agent" tabindex="-1"><option></option></select>
                                        <input type="text" class="la-agent-pct" value="0.000" tabindex="-1">
                                        <select class="la-agent-type" tabindex="-1"><option>%</option></select>
                                    </div>
                                </div>
                                <div class="la-field" style="grid-column: span 12;">
                                    <label>Secondary Agent</label>
                                    <div class="la-agent-row">
                                        <select class="la-input-agent" tabindex="-1"><option></option></select>
                                        <input type="text" class="la-agent-pct" value="0.000" tabindex="-1">
                                        <select class="la-agent-type" tabindex="-1"><option>%</option></select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Referral / Alias / Time Zone --}}
                        <div class="la-left-block">
                            <div class="la-field-row">
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Referral Source</label>
                                    <select tabindex="-1"><option></option></select>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Arr. By</label>
                                    <select tabindex="-1"><option></option></select>
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>ORES Alias</label>
                                    <input type="text" value="legacyonels" tabindex="-1">
                                </div>
                                <div class="la-field" style="grid-column: span 6;">
                                    <label>Alias</label>
                                    <select tabindex="-1"><option>legacyonels</option></select>
                                </div>
                                <div class="la-field" style="grid-column: span 12;">
                                    <label>Time Zone</label>
                                    <select tabindex="-1"><option></option></select>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            {{-- MIDDLE COLUMN --}}
            <div class="la-col la-col-middle">
                {{-- Date / Time row --}}
                <div class="la-mid-timing">
                    <div class="la-field-row">
                        <div class="la-field" style="grid-column: span 4;">
                            <label>PU Date <span class="text-danger">*</span></label>
                            <input type="date" name="pickup_date" id="pickup_date" value="{{ $formValue('pickup_date') }}" required>
                        </div>
                        <div class="la-field" style="grid-column: span 4;">
                            <label>PU Time <span class="text-danger">*</span></label>
                            <input type="time" name="pickup_time" id="pickup_time" value="{{ $formValue('pickup_time') }}" required>
                        </div>
                        <div class="la-field" style="grid-column: span 4;">
                            <label>DO Time</label>
                            <input type="time" tabindex="-1">
                        </div>
                        <div class="la-field" style="grid-column: span 4;">
                            <label>Spot Time</label>
                            <input type="time" tabindex="-1">
                        </div>
                        <div class="la-field" style="grid-column: span 4;">
                            <label>Gar-Out Time</label>
                            <input type="time" tabindex="-1">
                        </div>
                        <div class="la-field" style="grid-column: span 4;">
                            <label>Gar-In Time</label>
                            <input type="time" tabindex="-1">
                        </div>
                    </div>
                </div>

                {{-- Address / routing box --}}
                <div class="la-addr-box">
                    <div class="la-addr-tabs" id="la-addr-tabs">
                        <div class="la-addr-tab active" data-addr-tab="address"><i class="bi bi-building"></i> Address</div>
                        <div class="la-addr-tab" data-addr-tab="airport"><i class="bi bi-airplane"></i> Airport</div>
                        <div class="la-addr-tab" data-addr-tab="seaport"><i class="bi bi-water"></i> Seaport</div>
                        <div class="la-addr-tab" data-addr-tab="fbo"><i class="bi bi-airplane-engines"></i> FBO</div>
                        <div class="la-addr-tab" data-addr-tab="poi"><i class="bi bi-geo-alt-fill"></i> POI</div>
                    </div>
                    <div class="la-addr-box-body">
                        @include('pages.partials.reservation-la-routing-panels', [
                            'formValue' => $formValue,
                            'meetOld' => $meetOld,
                            'usStates' => $usStates,
                            'pickupFlightOld' => $pickupFlightOld,
                            'airlines' => $airlines ?? collect(),
                            'airports' => $airports ?? collect(),
                            'fbos' => $fbos ?? collect(),
                        ])

                        <div class="la-routing-actions">
                            <div class="la-routing-radios">
                                <label><input type="radio" name="la_routing_type" value="pickup" checked> Pick-up</label>
                                <label><input type="radio" name="la_routing_type" value="dropoff"> Drop-off</label>
                                <label><input type="radio" name="la_routing_type" value="stop" tabindex="-1"> Stop</label>
                                <label><input type="radio" name="la_routing_type" value="wait" tabindex="-1"> Wait</label>
                            </div>
                            <button type="button" class="la-btn-create" id="la-btn-create-routing" tabindex="-1">CREATE</button>
                        </div>
                    </div>
                    <div class="la-routing-footer-bar">Stored Routing Information</div>
                    <div class="la-routing-stored-list" id="la-stored-routing-list">
                        <div class="la-routing-footer-empty" id="la-stored-routing-empty">No routing info stored</div>
                    </div>
                    <div id="la-stop-locations-hidden" class="la-hidden-functional"></div>
                    <input type="hidden" name="pickup_location" id="pickup_location" value="{{ $formValue('pickup_location') }}" required>
                    <input type="hidden" name="dropoff_location" id="dropoff_location" value="{{ $formValue('dropoff_location') }}">
                    <input type="hidden" name="routing_information" id="routing_information" value="{{ e(json_encode($routingDraft ?? [])) }}">
                </div>

                {{-- Trip Notes --}}
                <div class="la-mid-notes-block" data-notes-block="trip">
                    <div class="la-mid-notes-head">
                        <span>Trip Notes</span>
                        <span class="la-mid-notes-limit">4000</span>
                    </div>
                    <textarea id="la-trip-notes" maxlength="4000" placeholder="">{{ $formValue('note') }}</textarea>
                    <input type="hidden" name="note" id="note" value="{{ $formValue('note') }}">
                    <div class="la-mid-notes-foot">
                        <div class="la-mid-notes-foot-left">
                            <label><input type="checkbox" id="la-note-add-ts" tabindex="-1"> Add to T/S</label>
                            <label><input type="checkbox" id="la-note-hide-customer" tabindex="-1"> Hide From Customer</label>
                        </div>
                        <button type="button" class="la-btn-save-notes" id="la-btn-save-trip-notes">SAVE NOTES</button>
                    </div>
                </div>

                {{-- Dispatch Notes --}}
                <div class="la-mid-notes-block" data-notes-block="dispatch">
                    <div class="la-mid-notes-head">
                        <span>Dispatch Notes</span>
                        <span class="la-mid-notes-limit">1000</span>
                    </div>
                    <textarea id="la-dispatch-notes" maxlength="1000"></textarea>
                    <div class="la-mid-notes-foot">
                        <span></span>
                        <button type="button" class="la-btn-save-notes" id="la-btn-save-dispatch-notes">SAVE NOTES</button>
                    </div>
                </div>

                {{-- Partner Notes --}}
                <div class="la-mid-notes-block" data-notes-block="partner">
                    <div class="la-mid-notes-head">
                        <span>Partner Notes</span>
                        <span class="la-mid-notes-limit">1000</span>
                    </div>
                    <textarea id="la-partner-notes" maxlength="1000"></textarea>
                </div>

                {{-- Bill To & Pax Notes --}}
                <div class="la-mid-notes-block" data-notes-block="billto">
                    <div class="la-mid-notes-head"><span>Bill To &amp; Pax Notes</span></div>
                    <textarea id="la-billto-notes" maxlength="1000"></textarea>
                </div>

                <div class="la-field la-hidden-functional" id="wrap-hours">
                    <select id="select_hours" name="select_hours">
                        @for ($h = 1; $h <= 24; $h++)
                            <option value="{{ $h }}" @selected($formValue('select_hours', '3') == $h)>{{ $h }}</option>
                        @endfor
                    </select>
                </div>

                <div class="la-hidden-functional">
                    <input type="date" name="return_pickup_date" id="return_pickup_date" value="{{ $formValue('return_pickup_date') }}">
                    <input type="time" name="return_pickup_time" id="return_pickup_time" value="{{ $formValue('return_pickup_time') }}">
                    <label><input type="checkbox" id="return_service_cb"> Return</label>
                </div>
            </div>

            {{-- RIGHT COLUMN --}}
            @include('pages.partials.reservation-la-right-column', [
                'formValue' => $formValue,
                'formBool' => $formBool,
                'serviceOptionOld' => $serviceOptionOld,
                'selectedVehicleLabel' => $selectedVehicleLabel,
                'vehicles' => $vehicles,
                'drivers' => $drivers ?? collect(),
                'stripeEnabled' => $stripeEnabled ?? false,
                'childSeatPricePerSeatUsd' => $childSeatPricePerSeatUsd ?? 20,
            ])
        </div>
    </form>
</div>
@endsection

@push('script')
@if(!empty($googleMapsApiKey))
<script>
window.initReservationPlaces = function () {
    if (typeof google === 'undefined' || !google.maps || !google.maps.places) return;

    function setAirportHidden(place, hiddenEl) {
        if (!hiddenEl) return;
        var isAirport = false;
        if (place.name && place.name.toLowerCase().indexOf('airport') !== -1) isAirport = true;
        if (place.types && place.types.indexOf('airport') !== -1) isAirport = true;
        (place.address_components || []).forEach(function (c) {
            (c.types || []).forEach(function (t) {
                if (t === 'airport') isAirport = true;
            });
        });
        hiddenEl.value = isAirport ? '1' : '0';
        var nfl = document.getElementById('no_flight_info');
        if (nfl && isAirport) nfl.value = '1';
    }

    function parseGoogleAddressComponents(components) {
        var parsed = {
            street_number: '',
            route: '',
            city: '',
            state: '',
            zip: '',
            country: 'United States'
        };
        (components || []).forEach(function (c) {
            var types = c.types || [];
            if (types.indexOf('street_number') >= 0) parsed.street_number = c.long_name;
            if (types.indexOf('route') >= 0) parsed.route = c.long_name;
            if (types.indexOf('locality') >= 0) parsed.city = c.long_name;
            if (!parsed.city && types.indexOf('sublocality') >= 0) parsed.city = c.long_name;
            if (!parsed.city && types.indexOf('administrative_area_level_2') >= 0) parsed.city = c.long_name;
            if (types.indexOf('administrative_area_level_1') >= 0) parsed.state = c.short_name;
            if (types.indexOf('postal_code') >= 0) parsed.zip = c.long_name;
            if (types.indexOf('country') >= 0) parsed.country = c.long_name;
        });
        return parsed;
    }

    function fillLaAddressFieldsFromPlace(place, input) {
        var parsed = parseGoogleAddressComponents(place.address_components || []);
        var street1 = document.getElementById('la-addr-street1');
        var street2 = document.getElementById('la-addr-street2');
        var city = document.getElementById('la-addr-city');
        var state = document.getElementById('la-addr-state');
        var zip = document.getElementById('la-addr-zip');
        var country = document.getElementById('la-addr-country');
        var locationName = document.getElementById('la-addr-location-name');

        var line1 = [parsed.street_number, parsed.route].filter(Boolean).join(' ').trim();
        if (!line1 && place.formatted_address) {
            line1 = place.formatted_address.split(',')[0] || '';
        }

        if (street1) street1.value = line1 || place.formatted_address || '';
        if (city) city.value = parsed.city;
        if (zip) zip.value = parsed.zip;
        if (state && parsed.state) state.value = parsed.state;
        if (country && parsed.country) {
            var opts = country.querySelectorAll('option');
            for (var i = 0; i < opts.length; i++) {
                if (opts[i].value === parsed.country || opts[i].textContent === parsed.country) {
                    country.value = opts[i].value;
                    break;
                }
            }
        }
        if (locationName && place.name) {
            if (!input || input.id === 'la-addr-location-name' || !locationName.value.trim()) {
                locationName.value = place.name;
            }
        }
        if (street2) street2.value = '';
    }

    function setupCustomAutocomplete(inputId, suggestionsListId, hiddenAirportFieldId, onPlaceSelect) {
        var input = document.getElementById(inputId);
        var suggestionsContainer = document.getElementById(suggestionsListId);
        var hiddenAirport = hiddenAirportFieldId ? document.getElementById(hiddenAirportFieldId) : null;
        if (!input || !suggestionsContainer || input.getAttribute('data-places-bound') === '1') return;
        input.setAttribute('data-places-bound', '1');

        var autocompleteService = new google.maps.places.AutocompleteService();
        var placesService = new google.maps.places.PlacesService(document.createElement('div'));
        var debounceTimer = null;

        input.addEventListener('input', function () {
            clearTimeout(debounceTimer);
            var query = this.value.trim();
            if (hiddenAirport && query.length === 0) hiddenAirport.value = '0';
            if (query.length < 2) {
                suggestionsContainer.innerHTML = '';
                suggestionsContainer.style.display = 'none';
                return;
            }
            debounceTimer = setTimeout(function () {
                autocompleteService.getPlacePredictions({
                    input: query,
                    types: ['geocode', 'establishment'],
                    componentRestrictions: { country: 'us' }
                }, function (predictions, status) {
                    if (status !== google.maps.places.PlacesServiceStatus.OK || !predictions) {
                        suggestionsContainer.style.display = 'none';
                        return;
                    }
                    suggestionsContainer.innerHTML = '';
                    predictions.forEach(function (prediction) {
                        var sf = prediction.structured_formatting || {};
                        var mainTxt = sf.main_text || '';
                        var subTxt = sf.secondary_text || '';
                        var item = document.createElement('div');
                        item.className = 'suggestion-item';
                        item.innerHTML = '<span class="main-text">' + mainTxt + '</span><span class="sub-text">' + subTxt + '</span>';
                        item.addEventListener('click', function () {
                            placesService.getDetails({
                                placeId: prediction.place_id,
                                fields: ['formatted_address', 'name', 'address_components', 'types']
                            }, function (place, st) {
                                if (st === google.maps.places.PlacesServiceStatus.OK && place) {
                                    input.value = place.formatted_address || (subTxt ? mainTxt + ', ' + subTxt : mainTxt);
                                    suggestionsContainer.style.display = 'none';
                                    if (hiddenAirport) setAirportHidden(place, hiddenAirport);
                                    if (typeof onPlaceSelect === 'function') onPlaceSelect(place, input);
                                }
                            });
                        });
                        suggestionsContainer.appendChild(item);
                    });
                    suggestionsContainer.style.display = 'block';
                });
            }, 400);
        });

        document.addEventListener('click', function (e) {
            if (!input.contains(e.target) && !suggestionsContainer.contains(e.target)) {
                suggestionsContainer.style.display = 'none';
            }
        });
    }

    setupCustomAutocomplete('la-addr-street1', 'la-addr-street1-suggestions', null, fillLaAddressFieldsFromPlace);
    setupCustomAutocomplete('la-addr-location-name', 'la-addr-location-name-suggestions', null, fillLaAddressFieldsFromPlace);
};
</script>
<script src="https://maps.googleapis.com/maps/api/js?key={{ $googleMapsApiKey }}&libraries=places&callback=initReservationPlaces" async defer></script>
@endif

<script src="https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/intlTelInput.min.js"></script>
<script>
(function () {
    var form = document.getElementById('reservation-form');
    if (!form) return;

    var LA_INTL_UTILS = 'https://cdn.jsdelivr.net/npm/intl-tel-input@23.8.0/build/js/utils.js';
    var laIntlPhones = [];

    function applyDialCodeToInput(input, iti, onCountryChange) {
        var data = iti.getSelectedCountryData();
        if (!data || !data.dialCode) return;
        var dialStr = '+' + data.dialCode;
        var val = (input.value || '').trim();

        if (onCountryChange) {
            var nationalPart = val.replace(/^\+\d+\s*/, '').trim();
            input.value = nationalPart ? dialStr + ' ' + nationalPart : dialStr + ' ';
            return;
        }

        if (!val) {
            input.value = dialStr + ' ';
            return;
        }
        if (val === '+' || /^\+?\d{0,4}\s*$/.test(val)) {
            input.value = dialStr + ' ';
        }
    }

    function initLaIntlPhones() {
        if (typeof window.intlTelInput !== 'function') return;
        document.querySelectorAll('.la-intl-phone').forEach(function (input) {
            if (input.getAttribute('data-intl-bound') === '1') return;
            input.setAttribute('data-intl-bound', '1');

            var initial = (input.value || '').trim();
            var iti = window.intlTelInput(input, {
                initialCountry: 'us',
                nationalMode: false,
                autoInsertDialCode: true,
                formatOnDisplay: true,
                countrySearch: true,
                utilsScript: LA_INTL_UTILS
            });

            if (initial) {
                iti.setNumber(initial);
            } else {
                applyDialCodeToInput(input, iti);
            }

            input.addEventListener('countrychange', function () {
                applyDialCodeToInput(input, iti, true);
            });

            laIntlPhones.push({ input: input, iti: iti });
        });
    }

    function syncLaIntlPhoneValues() {
        laIntlPhones.forEach(function (row) {
            var num = row.iti.getNumber();
            if (num) {
                row.input.value = num;
            }
        });
    }

    window.syncLaIntlPhoneValues = syncLaIntlPhoneValues;
    initLaIntlPhones();
})();
</script>

<script>
(function () {
    var form = document.getElementById('reservation-form');
    if (!form) return;

    var token = document.querySelector('meta[name="csrf-token"]').getAttribute('content');
    var reservationStripeEnabled = @json(!empty($stripeEnabled));
    var isEditMode = @json(!empty($isEditMode));
    var isEmbed = @json(!empty($isEmbed));
    var editingBookingId = @json(isset($editingBooking) ? $editingBooking->id : null);
    var storeUrl = @json($isEditMode ? ($formAction ?? route('reservation.store')) : route('reservation.store'));
    var routingDraftUrl = @json(route('reservation.routing-draft'));
    var laDraftSaveUrl = @json(route('reservation.la-draft'));
    var laDraftBookingId = @json($isEditMode ? null : $draftBookingId);
    var laRoutingDraft = @json($routingDraft ?? []);
    var laRoutingDraftTimer = null;
    var laNotesDraftTimer = null;
    var finalizeUrl = @json(route('reservation.finalize'));
    var laAirlines = @json($laAirlinesJson);
    var laAirports = @json($laAirportsJson);
    var laFbos = @json($laFbosJson);
    var CHILD_SEAT_PRICE_PER_SEAT_USD = @json((float) ($childSeatPricePerSeatUsd ?? 20));
    var laChildSeatLines = [];
    var laTripBaseAmount = 0;

    function serviceType() {
        var sel = document.getElementById('service_option');
        return sel && sel.value === 'hourly_as_directed' ? 'hourlyHire' : 'pointToPoint';
    }

    function initAddrTypeTabs() {
        var tabs = document.querySelectorAll('.la-addr-tab[data-addr-tab]');
        var panels = document.querySelectorAll('.la-addr-type-panel');
        tabs.forEach(function (tab) {
            tab.addEventListener('click', function () {
                var key = tab.getAttribute('data-addr-tab');
                tabs.forEach(function (t) { t.classList.remove('active'); });
                panels.forEach(function (p) {
                    p.classList.toggle('active', p.getAttribute('data-addr-panel') === key);
                });
                tab.classList.add('active');
                syncRoutingPanes();
            });
        });
    }

    function syncLaPickupFlightDetails() {
        var codeEl = document.getElementById('la-airline-code');
        var nameEl = document.getElementById('la-airline-name');
        var hiddenEl = document.getElementById('pickup-flight-details');
        if (!hiddenEl) return;

        var code = codeEl ? codeEl.value.trim().toUpperCase() : '';
        var name = nameEl ? nameEl.value.trim() : '';
        if (code && name) hiddenEl.value = code + ' - ' + name;
        else if (name) hiddenEl.value = name;
        else if (code) hiddenEl.value = code;
        else hiddenEl.value = '';
    }

    function applyLaAirline(code, name, id) {
        var codeEl = document.getElementById('la-airline-code');
        var nameEl = document.getElementById('la-airline-name');
        var storedEl = document.getElementById('la-stored-airline');
        var suggestions = document.getElementById('la-airline-code-suggestions');

        if (codeEl) codeEl.value = code || '';
        if (nameEl) nameEl.value = name || '';
        if (storedEl) storedEl.value = id ? String(id) : '';
        if (suggestions) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
        }
        syncLaPickupFlightDetails();
    }

    function findLaAirlineByCode(query) {
        var q = (query || '').trim().toUpperCase();
        if (!q) return [];
        return laAirlines.filter(function (a) {
            return (a.code || '').toUpperCase().indexOf(q) === 0;
        });
    }

    function renderLaAirlineSuggestions(matches) {
        var box = document.getElementById('la-airline-code-suggestions');
        if (!box) return;

        box.innerHTML = '';
        if (!matches.length) {
            box.style.display = 'none';
            return;
        }

        matches.forEach(function (airline) {
            var item = document.createElement('div');
            item.className = 'suggestion-item';
            item.setAttribute('role', 'option');
            item.innerHTML = '<span class="main-text">' + (airline.code || '') + '</span>'
                + '<span class="sub-text">' + (airline.name || '') + '</span>';
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                applyLaAirline(airline.code || '', airline.name || '', airline.id);
            });
            box.appendChild(item);
        });
        box.style.display = 'block';
    }

    function initLaAirlines() {
        var storedEl = document.getElementById('la-stored-airline');
        var codeEl = document.getElementById('la-airline-code');
        var nameEl = document.getElementById('la-airline-name');
        var suggestions = document.getElementById('la-airline-code-suggestions');

        if (storedEl) {
            storedEl.addEventListener('change', function () {
                var opt = storedEl.options[storedEl.selectedIndex];
                if (!opt || !opt.value) {
                    applyLaAirline('', '', '');
                    return;
                }
                applyLaAirline(opt.getAttribute('data-code') || '', opt.getAttribute('data-name') || '', opt.value);
            });
        }

        if (codeEl) {
            codeEl.addEventListener('input', function () {
                codeEl.value = codeEl.value.toUpperCase();
                var matches = findLaAirlineByCode(codeEl.value);
                renderLaAirlineSuggestions(matches);

                var exact = matches.find(function (a) {
                    return (a.code || '').toUpperCase() === codeEl.value.trim().toUpperCase();
                });
                if (exact) {
                    if (nameEl) nameEl.value = exact.name || '';
                    if (storedEl) storedEl.value = String(exact.id);
                } else if (storedEl) {
                    storedEl.value = '';
                }
                syncLaPickupFlightDetails();
            });

            codeEl.addEventListener('blur', function () {
                window.setTimeout(function () {
                    if (suggestions) suggestions.style.display = 'none';
                }, 150);
            });

            codeEl.addEventListener('focus', function () {
                if (codeEl.value.trim()) {
                    renderLaAirlineSuggestions(findLaAirlineByCode(codeEl.value));
                }
            });
        }

        if (nameEl) {
            nameEl.addEventListener('input', syncLaPickupFlightDetails);
        }
    }

    function applyLaAirport(code, name, id) {
        var codeEl = document.getElementById('la-airport-code');
        var nameEl = document.getElementById('la-airport-name');
        var storedEl = document.getElementById('la-stored-airport');
        var suggestions = document.getElementById('la-airport-code-suggestions');

        if (codeEl) codeEl.value = code || '';
        if (nameEl) nameEl.value = name || '';
        if (storedEl) storedEl.value = id ? String(id) : '';
        if (suggestions) {
            suggestions.innerHTML = '';
            suggestions.style.display = 'none';
        }
    }

    function findLaAirportByCode(query) {
        var q = (query || '').trim().toUpperCase();
        if (!q) return [];
        return laAirports.filter(function (a) {
            return (a.code || '').toUpperCase().indexOf(q) === 0;
        });
    }

    function renderLaAirportSuggestions(matches) {
        var box = document.getElementById('la-airport-code-suggestions');
        if (!box) return;

        box.innerHTML = '';
        if (!matches.length) {
            box.style.display = 'none';
            return;
        }

        matches.forEach(function (airport) {
            var item = document.createElement('div');
            item.className = 'suggestion-item';
            item.setAttribute('role', 'option');
            var sub = airport.name || '';
            if (airport.city) sub += (sub ? ' · ' : '') + airport.city;
            if (airport.state) sub += (airport.city ? ', ' : ' · ') + airport.state;
            item.innerHTML = '<span class="main-text">' + (airport.code || '') + '</span>'
                + '<span class="sub-text">' + sub + '</span>';
            item.addEventListener('mousedown', function (e) {
                e.preventDefault();
                applyLaAirport(airport.code || '', airport.name || '', airport.id);
            });
            box.appendChild(item);
        });
        box.style.display = 'block';
    }

    function initLaAirports() {
        var storedEl = document.getElementById('la-stored-airport');
        var codeEl = document.getElementById('la-airport-code');
        var nameEl = document.getElementById('la-airport-name');
        var suggestions = document.getElementById('la-airport-code-suggestions');

        if (storedEl) {
            storedEl.addEventListener('change', function () {
                var opt = storedEl.options[storedEl.selectedIndex];
                if (!opt || !opt.value) {
                    applyLaAirport('', '', '');
                    return;
                }
                applyLaAirport(opt.getAttribute('data-code') || '', opt.getAttribute('data-name') || '', opt.value);
            });
        }

        if (codeEl) {
            codeEl.addEventListener('input', function () {
                codeEl.value = codeEl.value.toUpperCase();
                var matches = findLaAirportByCode(codeEl.value);
                renderLaAirportSuggestions(matches);

                var exact = matches.find(function (a) {
                    return (a.code || '').toUpperCase() === codeEl.value.trim().toUpperCase();
                });
                if (exact) {
                    if (nameEl) nameEl.value = exact.name || '';
                    if (storedEl) storedEl.value = String(exact.id);
                } else if (storedEl) {
                    storedEl.value = '';
                }
            });

            codeEl.addEventListener('blur', function () {
                window.setTimeout(function () {
                    if (suggestions) suggestions.style.display = 'none';
                }, 150);
            });

            codeEl.addEventListener('focus', function () {
                if (codeEl.value.trim()) {
                    renderLaAirportSuggestions(findLaAirportByCode(codeEl.value));
                }
            });
        }
    }

    function syncRoutingPanes() {
        /* Pick-up / drop-off fields removed from UI; values sync via routing CREATE */
    }

    function initRoutingRadios() {
        document.querySelectorAll('input[name="la_routing_type"]').forEach(function (radio) {
            radio.addEventListener('change', function () {
                syncRoutingPanes();
                prefillLaAddressFormForRoutingType(radio.value);
            });
        });
        syncRoutingPanes();
    }

    var LA_ROUTING_PREFIX = { pickup: 'PU', dropoff: 'DO', stop: 'ST', wait: 'WAIT' };

    function getActiveAddrPanel() {
        var panel = document.querySelector('.la-addr-type-panel.active');
        return panel ? (panel.getAttribute('data-addr-panel') || 'address') : 'address';
    }

    function switchAddrTab(key) {
        var tabs = document.querySelectorAll('.la-addr-tab[data-addr-tab]');
        var panels = document.querySelectorAll('.la-addr-type-panel');
        tabs.forEach(function (t) {
            t.classList.toggle('active', t.getAttribute('data-addr-tab') === key);
        });
        panels.forEach(function (p) {
            p.classList.toggle('active', p.getAttribute('data-addr-panel') === key);
        });
        syncRoutingPanes();
    }

    function getLaAddressFormData() {
        var val = function (id) {
            var el = document.getElementById(id);
            return el ? (el.value || '').trim() : '';
        };
        return {
            source: 'address',
            locationName: val('la-addr-location-name'),
            street1: val('la-addr-street1'),
            street2: val('la-addr-street2'),
            city: val('la-addr-city'),
            state: val('la-addr-state'),
            zip: val('la-addr-zip'),
            country: val('la-addr-country') || 'United States',
            notes: val('la-addr-notes'),
            phone: val('la-addr-phone'),
            timeIn: val('la-addr-time-in')
        };
    }

    function getLaAirportFormData() {
        var val = function (id) {
            var el = document.getElementById(id);
            return el ? (el.value || '').trim() : '';
        };
        var storedAirport = document.getElementById('la-stored-airport');
        var storedAirline = document.getElementById('la-stored-airline');
        return {
            source: 'airport',
            pickupDate: val('pickup_date'),
            pickupTime: val('pickup_time'),
            airportCode: val('la-airport-code').toUpperCase(),
            airportName: val('la-airport-name'),
            airlineCode: val('la-airline-code').toUpperCase(),
            airlineName: val('la-airline-name'),
            flightNumber: val('flight_number'),
            arrDep: val('la-airport-arr-dep'),
            terminal: val('la-airport-terminal'),
            instructions: val('la-airport-instructions'),
            etaEtd: val('la-airport-eta-etd'),
            meetOption: val('meet-option'),
            phone: val('la-airport-phone'),
            notes: val('la-airport-notes'),
            timeIn: val('la-airport-time-in'),
            storedAirportId: storedAirport ? storedAirport.value : '',
            storedAirlineId: storedAirline ? storedAirline.value : ''
        };
    }

    function getLaFboFormData() {
        var val = function (id) {
            var el = document.getElementById(id);
            return el ? (el.value || '').trim() : '';
        };
        var storedFbo = document.getElementById('la-stored-fbo');
        return {
            source: 'fbo',
            locationName: val('la-fbo-name'),
            tailNumber: val('la-fbo-tail'),
            street1: val('la-fbo-street1'),
            street2: val('la-fbo-street2'),
            city: val('la-fbo-city'),
            state: val('la-fbo-state'),
            zip: val('la-fbo-zip'),
            country: val('la-fbo-country') || 'United States',
            notes: val('la-addr-notes'),
            phone: val('la-addr-phone'),
            timeIn: val('la-addr-time-in'),
            storedFboId: storedFbo ? storedFbo.value : ''
        };
    }

    function buildLaRoutingLabel(data) {
        if (data.source === 'airport') {
            var bits = [];
            if (data.airportCode) bits.push(data.airportCode);
            else if (data.airportName) bits.push(data.airportName);
            if (data.airlineCode) bits.push(data.airlineCode);
            else if (data.airlineName) bits.push(data.airlineName);
            if (data.flightNumber) bits.push(data.flightNumber);
            return bits.join(' ').trim() || data.airportName || 'Airport';
        }
        if (data.source === 'fbo') {
            var fboBits = [];
            if (data.locationName) fboBits.push(data.locationName);
            if (data.tailNumber) fboBits.push('Tail ' + data.tailNumber);
            if (data.city) fboBits.push(data.city);
            return fboBits.join(' ').trim() || data.street1 || 'FBO';
        }
        var bits = [];
        var head = data.locationName || data.street1;
        if (head) bits.push(head);
        if (data.street1 && data.locationName && data.street1 !== data.locationName) bits.push(data.street1);
        if (data.city) bits.push(data.city);
        if (data.state) bits.push(data.state);
        if (data.zip) bits.push(data.zip);
        return bits.join(' ').trim();
    }

    function buildLaRoutingSubmitValue(data) {
        if (data.source === 'airport') {
            var parts = [];
            if (data.airportCode && data.airportName) parts.push(data.airportCode + ' - ' + data.airportName);
            else if (data.airportName) parts.push(data.airportName);
            else if (data.airportCode) parts.push(data.airportCode);
            if (data.airlineCode || data.airlineName) {
                parts.push([data.airlineCode, data.airlineName].filter(Boolean).join(' - '));
            }
            if (data.flightNumber) parts.push('Flight ' + data.flightNumber);
            if (data.terminal) parts.push('Terminal/Gate ' + data.terminal);
            if (data.arrDep) parts.push(data.arrDep);
            return parts.join(', ').trim() || buildLaRoutingLabel(data);
        }
        var parts = [];
        if (data.locationName) parts.push(data.locationName);
        if (data.tailNumber) parts.push('Tail# ' + data.tailNumber);
        if (data.street1) parts.push(data.street1);
        if (data.street2) parts.push(data.street2);
        var cityLine = [data.city, data.state, data.zip].filter(Boolean).join(', ');
        if (cityLine) parts.push(cityLine);
        if (data.country && data.country !== 'United States') parts.push(data.country);
        return parts.join(', ').trim() || data.street1 || data.locationName;
    }

    function hideLaStoredRoutingEmpty() {
        var empty = document.getElementById('la-stored-routing-empty');
        if (empty) empty.style.display = 'none';
    }

    function showLaStoredRoutingEmptyIfNeeded() {
        var list = document.getElementById('la-stored-routing-list');
        var empty = document.getElementById('la-stored-routing-empty');
        if (!list || !empty) return;
        var hasRows = !!list.querySelector('.la-routing-stored-row');
        empty.style.display = hasRows ? 'none' : '';
    }

    function appendLaStoredRoutingRow(type, label, submitValue, payload, options) {
        var list = document.getElementById('la-stored-routing-list');
        if (!list) return null;
        hideLaStoredRoutingEmpty();
        options = options || {};

        payload = payload || { source: 'address' };
        if (type === 'pickup' || type === 'dropoff') {
            var existing = list.querySelector('.la-routing-stored-row[data-routing-type="' + type + '"]');
            if (existing) existing.remove();
        }

        var prefix = LA_ROUTING_PREFIX[type] || 'PU';
        var source = payload.source || 'address';
        var sourceLabels = { address: 'Address', airport: 'Airport', fbo: 'FBO' };
        var row = document.createElement('div');
        row.className = 'la-routing-stored-row';
        row.setAttribute('data-routing-type', type);
        row.setAttribute('data-submit-value', submitValue);
        row.setAttribute('data-source', source);
        row.setAttribute('data-label', label);
        row.setAttribute('data-payload', JSON.stringify(payload));

        var main = document.createElement('span');
        main.className = 'la-routing-stored-main';
        var sourceTag = document.createElement('span');
        sourceTag.className = 'la-routing-source-tag is-' + source;
        sourceTag.textContent = sourceLabels[source] || 'Address';
        main.appendChild(sourceTag);
        main.appendChild(document.createTextNode(prefix + ': ' + label + ' '));
        if (source === 'airport') {
            var unverified = document.createElement('span');
            unverified.className = 'la-routing-not-verified';
            unverified.textContent = 'Not verified';
            main.appendChild(unverified);
        }
        row.appendChild(main);

        var actions = document.createElement('span');
        actions.className = 'la-routing-stored-actions';
        var editBtn = document.createElement('button');
        editBtn.type = 'button';
        editBtn.className = 'la-routing-edit';
        editBtn.textContent = 'Edit';
        editBtn.addEventListener('click', function () {
            editLaStoredRoutingRow(row);
        });
        var delBtn = document.createElement('button');
        delBtn.type = 'button';
        delBtn.className = 'la-routing-delete';
        delBtn.textContent = 'Delete';
        delBtn.addEventListener('click', function () {
            deleteLaStoredRoutingRow(row);
        });
        actions.appendChild(editBtn);
        actions.appendChild(delBtn);
        row.appendChild(actions);

        list.appendChild(row);
        if (!options.skipPersist) syncLaRoutingPersistence();
        return row;
    }

    function collectLaStoredRoutingRows() {
        var list = document.getElementById('la-stored-routing-list');
        if (!list) return [];
        var rows = [];
        list.querySelectorAll('.la-routing-stored-row').forEach(function (row) {
            var payload = {};
            try { payload = JSON.parse(row.getAttribute('data-payload') || '{}'); } catch (e) { payload = {}; }
            rows.push({
                type: row.getAttribute('data-routing-type') || 'pickup',
                label: row.getAttribute('data-label') || (row.querySelector('.la-routing-stored-main') || row).textContent
                    .replace(/^(PU|DO|ST|WAIT):\s*/i, '')
                    .replace(/\s*Not verified\s*$/i, '')
                    .trim(),
                submit_value: row.getAttribute('data-submit-value') || '',
                source: row.getAttribute('data-source') || (payload.source || 'address'),
                payload: payload
            });
        });
        return rows;
    }

    function syncLaRoutingHiddenInput() {
        var hidden = document.getElementById('routing_information');
        if (!hidden) return;
        hidden.value = JSON.stringify(collectLaStoredRoutingRows());
    }

    function persistLaRoutingDraft() {
        syncLaRoutingHiddenInput();
        // Edit mode must not write into the new-reservation draft booking.
        if (isEditMode || !routingDraftUrl || !laDraftBookingId) return;
        clearTimeout(laRoutingDraftTimer);
        laRoutingDraftTimer = setTimeout(function () {
            var rows = collectLaStoredRoutingRows();
            var pu = document.getElementById('pickup_location');
            var dof = document.getElementById('dropoff_location');
            fetch(routingDraftUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    draft_booking_id: laDraftBookingId,
                    routing_information: rows,
                    pickup_location: pu ? pu.value : '',
                    dropoff_location: dof ? dof.value : ''
                })
            }).catch(function () { /* ignore draft save errors */ });
        }, 250);
    }

    function syncLaRoutingPersistence() {
        syncLaRoutingHiddenInput();
        persistLaRoutingDraft();
    }

    function hydrateLaStoredRoutingFromDraft(rows) {
        var list = document.getElementById('la-stored-routing-list');
        if (!list || !Array.isArray(rows) || !rows.length) return false;

        list.querySelectorAll('.la-routing-stored-row').forEach(function (row) { row.remove(); });
        rows.forEach(function (row) {
            if (!row || !row.type || !row.label) return;
            appendLaStoredRoutingRow(
                row.type,
                row.label,
                row.submit_value || row.label,
                row.payload || { source: row.source || 'address' },
                { skipPersist: true }
            );
            syncLaRoutingToFormFields(row.type, row.submit_value || row.label, row.payload || { source: row.source || 'address' });
        });
        syncLaRoutingHiddenInput();
        showLaStoredRoutingEmptyIfNeeded();
        return true;
    }

    function syncLaStopLocationsHidden() {
        var container = document.getElementById('la-stop-locations-hidden');
        var list = document.getElementById('la-stored-routing-list');
        if (!container || !list) return;
        container.innerHTML = '';
        list.querySelectorAll('.la-routing-stored-row[data-routing-type="stop"], .la-routing-stored-row[data-routing-type="wait"]').forEach(function (row) {
            var input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'stop_locations[]';
            input.value = row.getAttribute('data-submit-value') || '';
            container.appendChild(input);
        });
    }

    function syncLaRoutingToFormFields(type, submitValue, payload) {
        if (type === 'pickup') {
            var pu = document.getElementById('pickup_location');
            if (pu) pu.value = submitValue;
            if (payload && payload.source === 'airport') {
                var isAirport = document.getElementById('is_airport');
                if (isAirport) isAirport.value = '1';
                var nfl = document.getElementById('no_flight_info');
                if (nfl) nfl.value = '1';
                syncLaAirportBackendFields(payload);
            }
        } else if (type === 'dropoff') {
            var dof = document.getElementById('dropoff_location');
            if (dof) dof.value = submitValue;
        }
        syncLaStopLocationsHidden();
    }

    function syncLaAirportBackendFields(data) {
        if (!data) return;
        var flightNum = document.getElementById('flight_number');
        if (flightNum && data.flightNumber !== undefined) flightNum.value = data.flightNumber || '';
        var meet = document.getElementById('meet-option');
        if (meet && data.meetOption !== undefined) meet.value = data.meetOption || '';
        if (typeof syncLaPickupFlightDetails === 'function') syncLaPickupFlightDetails();
        else {
            var hidden = document.getElementById('pickup-flight-details');
            if (hidden) {
                if (data.airlineCode && data.airlineName) hidden.value = data.airlineCode + ' - ' + data.airlineName;
                else if (data.airlineName) hidden.value = data.airlineName;
                else hidden.value = '';
            }
        }
    }

    function laAddressFormHasData(data) {
        data = data || getLaAddressFormData();
        return !!(data.street1 || data.locationName || data.city);
    }

    function laAirportFormHasData(data) {
        data = data || getLaAirportFormData();
        return !!(data.airportCode || data.airportName || data.airlineCode || data.airlineName || data.flightNumber);
    }

    function laFboFormHasData(data) {
        data = data || getLaFboFormData();
        return !!(data.locationName || data.street1 || data.city || data.tailNumber);
    }

    function normalizeLaState(state) {
        var s = (state || '').trim();
        if (!s) return '';
        var upper = s.toUpperCase();
        if (upper === 'TX' || upper === 'TEXAS') return 'Texas';
        return s;
    }

    function applyLaFbo(fbo) {
        fbo = fbo || {};
        var set = function (id, value) {
            var el = document.getElementById(id);
            if (el) el.value = value || '';
        };
        set('la-fbo-name', fbo.name || '');
        set('la-fbo-street1', fbo.street1 || '');
        set('la-fbo-street2', fbo.street2 || '');
        set('la-fbo-city', fbo.city || '');
        set('la-fbo-state', normalizeLaState(fbo.state || ''));
        set('la-fbo-zip', fbo.zip || '');
        set('la-fbo-country', fbo.country || 'United States');
        set('la-addr-phone', fbo.phone || '');
        set('la-addr-notes', fbo.notes || '');
        var stored = document.getElementById('la-stored-fbo');
        if (stored) stored.value = fbo.id ? String(fbo.id) : '';
    }

    function clearLaFboForm() {
        ['la-fbo-name', 'la-fbo-tail', 'la-fbo-street1', 'la-fbo-street2', 'la-fbo-city', 'la-fbo-zip', 'la-addr-notes', 'la-addr-phone'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        var state = document.getElementById('la-fbo-state');
        if (state) state.value = '';
        var country = document.getElementById('la-fbo-country');
        if (country) country.value = 'United States';
        var stored = document.getElementById('la-stored-fbo');
        if (stored) stored.value = '';
    }

    function fillLaFboForm(data) {
        if (!data) return;
        applyLaFbo({
            id: data.storedFboId || '',
            name: data.locationName || '',
            street1: data.street1 || '',
            street2: data.street2 || '',
            city: data.city || '',
            state: data.state || '',
            zip: data.zip || '',
            country: data.country || 'United States',
            phone: data.phone || '',
            notes: data.notes || ''
        });
        var tail = document.getElementById('la-fbo-tail');
        if (tail) tail.value = data.tailNumber || '';
    }

    function initLaFbos() {
        var storedEl = document.getElementById('la-stored-fbo');
        if (!storedEl) return;
        storedEl.addEventListener('change', function () {
            var opt = storedEl.options[storedEl.selectedIndex];
            if (!opt || !opt.value) {
                clearLaFboForm();
                return;
            }
            applyLaFbo({
                id: opt.value,
                name: opt.getAttribute('data-name') || '',
                street1: opt.getAttribute('data-street1') || '',
                street2: opt.getAttribute('data-street2') || '',
                city: opt.getAttribute('data-city') || '',
                state: opt.getAttribute('data-state') || '',
                zip: opt.getAttribute('data-zip') || '',
                phone: opt.getAttribute('data-phone') || '',
                notes: opt.getAttribute('data-notes') || '',
                country: opt.getAttribute('data-country') || 'United States'
            });
        });
    }

    function hasLaStoredRoutingType(type) {
        var list = document.getElementById('la-stored-routing-list');
        return !!(list && list.querySelector('.la-routing-stored-row[data-routing-type="' + type + '"]'));
    }

    function clearLaAddressForm() {
        ['la-addr-location-name', 'la-addr-street1', 'la-addr-street2', 'la-addr-city', 'la-addr-zip', 'la-addr-notes', 'la-addr-phone', 'la-addr-time-in'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        var state = document.getElementById('la-addr-state');
        if (state) state.value = '';
        var country = document.getElementById('la-addr-country');
        if (country) country.value = 'United States';
    }

    function clearLaAirportForm(keepFlightBackend) {
        ['la-airport-code', 'la-airport-name', 'la-airline-code', 'la-airline-name',
            'la-airport-arr-dep', 'la-airport-terminal', 'la-airport-eta-etd',
            'la-airport-phone', 'la-airport-notes', 'la-airport-time-in'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.value = '';
        });
        var instr = document.getElementById('la-airport-instructions');
        if (instr) instr.value = '';
        var storedAirport = document.getElementById('la-stored-airport');
        if (storedAirport) storedAirport.value = '';
        var storedAirline = document.getElementById('la-stored-airline');
        if (storedAirline) storedAirline.value = '';
        if (!keepFlightBackend) {
            var flightNum = document.getElementById('flight_number');
            if (flightNum) flightNum.value = '';
            var meet = document.getElementById('meet-option');
            if (meet) meet.value = '';
            var hidden = document.getElementById('pickup-flight-details');
            if (hidden) hidden.value = '';
        }
    }

    function fillLaAddressForm(data) {
        if (!data) return;
        var set = function (id, value) {
            var el = document.getElementById(id);
            if (el) el.value = value || '';
        };
        set('la-addr-location-name', data.locationName);
        set('la-addr-street1', data.street1);
        set('la-addr-street2', data.street2);
        set('la-addr-city', data.city);
        set('la-addr-state', data.state);
        set('la-addr-zip', data.zip);
        set('la-addr-country', data.country || 'United States');
        set('la-addr-notes', data.notes);
        set('la-addr-phone', data.phone);
        set('la-addr-time-in', data.timeIn);
    }

    function fillLaAirportForm(data) {
        if (!data) return;
        var set = function (id, value) {
            var el = document.getElementById(id);
            if (el) el.value = value || '';
        };
        set('la-airport-code', data.airportCode);
        set('la-airport-name', data.airportName);
        set('la-airline-code', data.airlineCode);
        set('la-airline-name', data.airlineName);
        set('flight_number', data.flightNumber);
        set('la-airport-arr-dep', data.arrDep);
        set('la-airport-terminal', data.terminal);
        set('la-airport-instructions', data.instructions);
        set('la-airport-eta-etd', data.etaEtd);
        set('meet-option', data.meetOption);
        set('la-airport-phone', data.phone);
        set('la-airport-notes', data.notes);
        set('la-airport-time-in', data.timeIn);
        if (data.pickupDate) set('pickup_date', data.pickupDate);
        if (data.pickupTime) set('pickup_time', data.pickupTime);
        var storedAirport = document.getElementById('la-stored-airport');
        if (storedAirport && data.storedAirportId) storedAirport.value = data.storedAirportId;
        var storedAirline = document.getElementById('la-stored-airline');
        if (storedAirline && data.storedAirlineId) storedAirline.value = data.storedAirlineId;
        syncLaAirportBackendFields(data);
        if (typeof window.syncLaRightFields === 'function') window.syncLaRightFields();
    }

    function editLaStoredRoutingRow(row) {
        if (!row) return;
        var payload = {};
        try { payload = JSON.parse(row.getAttribute('data-payload') || '{}'); } catch (e) { payload = {}; }
        var type = row.getAttribute('data-routing-type') || 'pickup';
        var typeRadio = document.querySelector('input[name="la_routing_type"][value="' + type + '"]');
        if (typeRadio) typeRadio.checked = true;

        if (payload.source === 'airport') {
            switchAddrTab('airport');
            fillLaAirportForm(payload);
        } else if (payload.source === 'fbo') {
            switchAddrTab('fbo');
            fillLaFboForm(payload);
        } else {
            switchAddrTab('address');
            fillLaAddressForm(payload);
        }

        // Remove from list while editing so CREATE can re-add / update
        row.remove();
        syncLaStopLocationsHidden();
        showLaStoredRoutingEmptyIfNeeded();
        syncLaRoutingPersistence();
    }

    function deleteLaStoredRoutingRow(row) {
        if (!row) return;
        var type = row.getAttribute('data-routing-type');
        var source = row.getAttribute('data-source');
        row.remove();
        if (type === 'pickup') {
            var pu = document.getElementById('pickup_location');
            if (pu) pu.value = '';
            if (source === 'airport') {
                var isAirport = document.getElementById('is_airport');
                if (isAirport) isAirport.value = '0';
            }
        } else if (type === 'dropoff') {
            var dof = document.getElementById('dropoff_location');
            if (dof) dof.value = '';
        }
        syncLaStopLocationsHidden();
        showLaStoredRoutingEmptyIfNeeded();
        syncLaRoutingPersistence();
    }

    function addLaRoutingEntry(type, options) {
        options = options || {};
        var panel = getActiveAddrPanel();
        var data, label, submitValue;

        if (panel === 'airport') {
            data = getLaAirportFormData();
            if (!laAirportFormHasData(data)) {
                if (!options.silentEmpty) alert('Please enter airport details before adding routing.');
                return false;
            }
            label = buildLaRoutingLabel(data);
            submitValue = buildLaRoutingSubmitValue(data);
        } else if (panel === 'fbo') {
            data = getLaFboFormData();
            if (!laFboFormHasData(data)) {
                if (!options.silentEmpty) alert('Please select or enter an FBO before adding routing.');
                return false;
            }
            label = buildLaRoutingLabel(data);
            submitValue = buildLaRoutingSubmitValue(data);
        } else if (panel === 'address') {
            data = getLaAddressFormData();
            label = buildLaRoutingLabel(data);
            submitValue = buildLaRoutingSubmitValue(data);
            if (!submitValue) {
                if (!options.silentEmpty) alert('Please enter an address before adding routing.');
                return false;
            }
        } else {
            if (!options.silentEmpty) alert('Use Address, Airport, or FBO tab to add routing.');
            return false;
        }

        if ((type === 'pickup' || type === 'dropoff') && hasLaStoredRoutingType(type)) {
            return false;
        }

        appendLaStoredRoutingRow(type, label, submitValue, data);
        syncLaRoutingToFormFields(type, submitValue, data);
        if (panel === 'airport') clearLaAirportForm(type === 'pickup');
        else if (panel === 'fbo') clearLaFboForm();
        else clearLaAddressForm();
        return true;
    }

    function prefillLaAddressFormForRoutingType(type) {
        var source = null;
        if (type === 'pickup') source = document.getElementById('pickup_location');
        else if (type === 'dropoff') source = document.getElementById('dropoff_location');
        if (!source || !source.value.trim()) return;

        var street1 = document.getElementById('la-addr-street1');
        if (street1 && !street1.value.trim()) {
            street1.value = source.value.trim();
        }
    }

    function seedLaStoredRoutingFromForm() {
        var pu = document.getElementById('pickup_location');
        var dof = document.getElementById('dropoff_location');
        if (pu && pu.value.trim()) {
            appendLaStoredRoutingRow('pickup', pu.value.trim(), pu.value.trim(), {
                source: 'address',
                street1: pu.value.trim()
            });
        }
        if (dof && dof.value.trim()) {
            appendLaStoredRoutingRow('dropoff', dof.value.trim(), dof.value.trim(), {
                source: 'address',
                street1: dof.value.trim()
            });
        }
        syncLaStopLocationsHidden();
    }

    function initLaRoutingCreate() {
        var btn = document.getElementById('la-btn-create-routing');
        if (!btn || btn.getAttribute('data-la-routing-bound') === '1') return;
        btn.setAttribute('data-la-routing-bound', '1');

        btn.addEventListener('click', function () {
            var typeEl = document.querySelector('input[name="la_routing_type"]:checked');
            var type = typeEl ? typeEl.value : 'pickup';
            addLaRoutingEntry(type, { silentEmpty: false });
        });

        document.querySelectorAll('input[name="la_routing_type"]').forEach(function (radio) {
            radio.addEventListener('click', function () {
                var panel = getActiveAddrPanel();
                if (panel === 'airport') {
                    if (!laAirportFormHasData()) return;
                } else if (panel === 'fbo') {
                    if (!laFboFormHasData()) return;
                } else if (panel === 'address') {
                    if (!laAddressFormHasData()) return;
                } else {
                    return;
                }
                addLaRoutingEntry(radio.value, { silentEmpty: true });
            });
        });

        if (Array.isArray(laRoutingDraft) && laRoutingDraft.length) {
            hydrateLaStoredRoutingFromDraft(laRoutingDraft);
        } else {
            seedLaStoredRoutingFromForm();
            syncLaRoutingHiddenInput();
        }
    }

    function toggleServiceUi() {
        var hourly = serviceType() === 'hourlyHire';
        var wrapHours = document.getElementById('wrap-hours');
        var dropoff = document.getElementById('dropoff_location');
        if (wrapHours) wrapHours.classList.toggle('la-hidden-functional', !hourly);
        if (dropoff) {
            if (hourly) dropoff.removeAttribute('required');
            else dropoff.setAttribute('required', 'required');
        }
        if (hourly) {
            var rs = document.getElementById('return_service');
            var cb = document.getElementById('return_service_cb');
            if (rs) rs.value = '0';
            if (cb) cb.checked = false;
            var pickupRadio = document.querySelector('input[name="la_routing_type"][value="pickup"]');
            if (pickupRadio) pickupRadio.checked = true;
        }
        syncRoutingPanes();
    }

    function syncBookerHidden() {
        var bf = document.getElementById('booker_first_name');
        var bl = document.getElementById('booker_last_name');
        var hidden = document.getElementById('booking_for_someone_else');
        if (!hidden) return;
        var hasBooker = (bf && bf.value.trim()) || (bl && bl.value.trim());
        hidden.value = hasBooker ? '1' : '0';
    }

    function syncReturnHidden() {
        var cb = document.getElementById('return_service_cb');
        var hidden = document.getElementById('return_service');
        if (cb && hidden) hidden.value = cb.checked ? '1' : '0';
    }

    function getLaPrimaryChildSeatType() {
        var reqUi = document.getElementById('la-child-seat-required-ui');
        if (!reqUi) return null;
        var val = (reqUi.value || '').trim();
        return val === '' ? null : val;
    }

    function getLaPrimaryChildSeatQty() {
        var type = getLaPrimaryChildSeatType();
        if (!type) return 0;
        var qtyUi = document.getElementById('la-child-seat-count-ui');
        var q = qtyUi ? parseInt(qtyUi.value, 10) : 0;
        return isNaN(q) || q < 1 ? 0 : q;
    }

    function getLaChildSeatFeeUsd() {
        var totalEl = document.getElementById('la-price-child-seat-total');
        if (totalEl) {
            var fromRow = parseFloat(totalEl.value);
            if (!isNaN(fromRow)) return fromRow;
        }
        var fee = 0;
        var primaryQty = getLaPrimaryChildSeatQty();
        if (primaryQty > 0) fee += CHILD_SEAT_PRICE_PER_SEAT_USD * primaryQty;
        laChildSeatLines.forEach(function (line) {
            fee += CHILD_SEAT_PRICE_PER_SEAT_USD * (line.qty || 0);
        });
        return Math.round(fee * 100) / 100;
    }

    function getLaChildSeatTotalQty() {
        return getLaPrimaryChildSeatQty() + laChildSeatLines.reduce(function (sum, line) {
            return sum + (line.qty || 0);
        }, 0);
    }

    function syncLaChildSeatHiddenFields() {
        var reqHidden = document.getElementById('child_seat_required');
        var typeHidden = document.getElementById('child_seat_type');
        var qtyHidden = document.getElementById('child_seat_quantity');
        var qtyUi = document.getElementById('la-child-seat-count-ui');
        var totalSeats = document.getElementById('la-total-seats');
        var totalQty = getLaChildSeatTotalQty();
        var primaryType = getLaPrimaryChildSeatType();
        var lastLine = laChildSeatLines.length ? laChildSeatLines[laChildSeatLines.length - 1] : null;

        if (reqHidden) reqHidden.value = totalQty > 0 ? '1' : '0';
        if (typeHidden) typeHidden.value = primaryType || (lastLine ? lastLine.type : '');
        if (qtyHidden) qtyHidden.value = totalQty > 0 ? String(totalQty) : '';
        if (qtyUi && primaryType && parseInt(qtyUi.value, 10) < 1) qtyUi.value = '1';
        if (totalSeats) totalSeats.textContent = String(totalQty);
    }

    function handleLaChildSeatRequiredChange() {
        var reqUi = document.getElementById('la-child-seat-required-ui');
        var typeUi = document.getElementById('la-child-seat-type-ui');
        var qtyUi = document.getElementById('la-child-seat-count-ui');
        var type = reqUi ? reqUi.value : '';

        if (typeUi && type) typeUi.value = type;
        if (qtyUi) {
            qtyUi.disabled = !type;
            if (!type) {
                if (laChildSeatLines.length === 0) qtyUi.value = '0';
            } else if (parseInt(qtyUi.value, 10) < 1) {
                qtyUi.value = '1';
            }
        }

        syncLaChildSeatHiddenFields();
        updateLaChildSeatPricingRow();
        syncLaGrandTotals();
    }

    function recalcLaPricingPanel(panelKey) {
        var panel = document.querySelector('[data-price-panel="' + (panelKey || 'primary') + '"]');
        if (!panel) return 0;

        var rows = panel.querySelectorAll('.la-price-line');
        var runningSubtotal = 0;
        var grandSum = 0;

        rows.forEach(function (row) {
            var qtyEl = row.querySelector('.la-price-qty');
            var rateEl = row.querySelector('.la-price-rate');
            var pctEl = row.querySelector('.la-price-pct');
            var totalEl = row.querySelector('.la-price-total');
            var qty = parseFloat(qtyEl && qtyEl.value) || 0;
            var rate = parseFloat(rateEl && rateEl.value) || 0;
            var pct = parseFloat(pctEl && pctEl.value) || 0;
            var lineTotal = 0;

            if (pct > 0) {
                lineTotal = runningSubtotal * pct / 100;
            } else {
                lineTotal = qty * rate;
            }

            if (row.classList.contains('la-discount')) {
                lineTotal = -Math.abs(lineTotal);
            }

            if (totalEl) totalEl.value = Math.abs(lineTotal).toFixed(2);
            grandSum += lineTotal;
            if (!row.classList.contains('la-discount')) {
                runningSubtotal += lineTotal;
            }
        });

        return Math.round(grandSum * 100) / 100;
    }

    function sumLaActivePricingPanel() {
        var activePanel = document.querySelector('.la-price-panel.active[data-price-panel]');
        if (!activePanel) {
            return recalcLaPricingPanel('primary');
        }
        var key = activePanel.getAttribute('data-price-panel') || 'primary';
        return recalcLaPricingPanel(key);
    }

    function initLaPricingListeners() {
        document.querySelectorAll('.la-price-panel .la-price-qty, .la-price-panel .la-price-rate, .la-price-panel .la-price-pct').forEach(function (input) {
            input.addEventListener('input', function () {
                sumLaActivePricingPanel();
                syncLaGrandTotals();
            });
        });
    }

    function updateLaChildSeatPricingRow() {
        var qtyEl = document.getElementById('la-price-child-seat-qty');
        var rateEl = document.getElementById('la-price-child-seat-rate');
        var totalQty = getLaChildSeatTotalQty();

        if (qtyEl) qtyEl.value = String(totalQty);
        if (rateEl) rateEl.value = CHILD_SEAT_PRICE_PER_SEAT_USD.toFixed(2);
        recalcLaPricingPanel('primary');
    }

    function syncLaGrandTotals() {
        var custom = document.getElementById('custom_total_price');
        var due = document.getElementById('la-total-due');
        var payments = document.getElementById('la-payments-total');
        var faresSum = sumLaActivePricingPanel();
        var paymentsVal = payments ? parseFloat(payments.value) || 0 : 0;

        laTripBaseAmount = faresSum - getLaChildSeatFeeUsd();

        if (custom) custom.value = faresSum > 0 ? faresSum.toFixed(2) : '0.00';
        if (due) due.value = Math.max(0, faresSum - paymentsVal).toFixed(2);
        var payDisplay = document.getElementById('la-payment-total-display');
        if (payDisplay) payDisplay.textContent = '$' + (faresSum > 0 ? faresSum.toFixed(2) : '0.00');
    }

    function renderLaChildSeatList() {
        var list = document.getElementById('la-child-seat-added-list');
        if (!list) return;
        list.innerHTML = '';
        laChildSeatLines.forEach(function (line, idx) {
            var li = document.createElement('li');
            var fee = (CHILD_SEAT_PRICE_PER_SEAT_USD * line.qty).toFixed(2);
            li.textContent = line.label + ' × ' + line.qty + ' ($' + fee + ') ';
            var remove = document.createElement('a');
            remove.href = '#';
            remove.textContent = 'remove';
            remove.style.marginLeft = '4px';
            remove.addEventListener('click', function (e) {
                e.preventDefault();
                laChildSeatLines.splice(idx, 1);
                renderLaChildSeatList();
                syncLaChildSeatHiddenFields();
                updateLaChildSeatPricingRow();
                syncLaGrandTotals();
            });
            li.appendChild(remove);
            list.appendChild(li);
        });
    }

    function prepareLaChildSeatForSubmit() {
        var custom = document.getElementById('custom_total_price');
        var childFee = getLaChildSeatFeeUsd();
        if (!custom || childFee <= 0) return;
        var grand = parseFloat(custom.value);
        if (!isNaN(grand) && grand > childFee) {
            custom.value = (grand - childFee).toFixed(2);
        } else if (!isNaN(grand) && grand <= childFee) {
            custom.value = '';
        }
    }

    function syncTotalDue() {
        var custom = document.getElementById('custom_total_price');
        var due = document.getElementById('la-total-due');
        var payments = document.getElementById('la-payments-total');
        var grand = custom ? parseFloat(custom.value) || 0 : 0;
        var paymentsVal = payments ? parseFloat(payments.value) || 0 : 0;
        if (due) due.value = Math.max(0, grand - paymentsVal).toFixed(2);
        var payDisplay = document.getElementById('la-payment-total-display');
        if (payDisplay) payDisplay.textContent = '$' + grand.toFixed(2);
    }

    function getLaNoteSectionValue(id) {
        var el = document.getElementById(id);
        return el ? (el.value || '').trim() : '';
    }

    var LA_NOTES_STORAGE_KEY = 'dallas_bcls_la_reservation_notes';

    function collectLaNotesDraft() {
        var addTs = document.getElementById('la-note-add-ts');
        var hideCust = document.getElementById('la-note-hide-customer');
        return {
            trip: document.getElementById('la-trip-notes') ? document.getElementById('la-trip-notes').value : '',
            dispatch: document.getElementById('la-dispatch-notes') ? document.getElementById('la-dispatch-notes').value : '',
            partner: document.getElementById('la-partner-notes') ? document.getElementById('la-partner-notes').value : '',
            billto: document.getElementById('la-billto-notes') ? document.getElementById('la-billto-notes').value : '',
            addr: document.getElementById('la-addr-notes') ? document.getElementById('la-addr-notes').value : '',
            airport: document.getElementById('la-airport-notes') ? document.getElementById('la-airport-notes').value : '',
            addTs: !!(addTs && addTs.checked),
            hideCustomer: !!(hideCust && hideCust.checked)
        };
    }

    function applyLaNotesDraft(draft) {
        if (!draft || typeof draft !== 'object') return;
        var set = function (id, value) {
            var el = document.getElementById(id);
            if (el && value != null) el.value = value;
        };
        set('la-trip-notes', draft.trip || '');
        set('la-dispatch-notes', draft.dispatch || '');
        set('la-partner-notes', draft.partner || '');
        set('la-billto-notes', draft.billto || '');
        set('la-addr-notes', draft.addr || '');
        set('la-airport-notes', draft.airport || '');
        var addTs = document.getElementById('la-note-add-ts');
        var hideCust = document.getElementById('la-note-hide-customer');
        if (addTs) addTs.checked = !!draft.addTs;
        if (hideCust) hideCust.checked = !!draft.hideCustomer;
    }

    function saveLaNotesDraft() {
        try {
            localStorage.setItem(LA_NOTES_STORAGE_KEY, JSON.stringify(collectLaNotesDraft()));
        } catch (e) {}
        syncLaNotesForSubmit();
        persistLaNotesToDraftBooking();
    }

    function persistLaNotesToDraftBooking() {
        if (!laDraftSaveUrl || !laDraftBookingId) return;
        clearTimeout(laNotesDraftTimer);
        laNotesDraftTimer = setTimeout(function () {
            var notes = collectLaNotesDraft();
            var pickupDate = document.getElementById('pickup_date');
            var pickupTime = document.getElementById('pickup_time');
            var vehicleId = document.getElementById('vehicle-id');
            var serviceOpt = document.getElementById('service_option');
            var pax = document.getElementById('pax_count');
            var luggage = document.getElementById('luggage_count');
            var flightDetails = document.getElementById('pickup-flight-details');
            var flightNumber = document.getElementById('flight_number');
            var meetOption = document.getElementById('meet-option');
            var pu = document.getElementById('pickup_location');
            var dof = document.getElementById('dropoff_location');
            var poRef = document.getElementById('po_client_ref');

            fetch(laDraftSaveUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': token,
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({
                    draft_booking_id: laDraftBookingId,
                    note: buildLaCombinedNote(),
                    notes: {
                        trip: notes.trip || '',
                        dispatch: notes.dispatch || '',
                        partner: notes.partner || '',
                        billto: notes.billto || '',
                        addr: notes.addr || '',
                        airport: notes.airport || '',
                        add_ts: !!notes.addTs,
                        hide_customer: !!notes.hideCustomer
                    },
                    pickup_date: pickupDate && pickupDate.value ? pickupDate.value : null,
                    pickup_time: pickupTime && pickupTime.value ? pickupTime.value : null,
                    pickup_location: pu ? pu.value : null,
                    dropoff_location: dof ? dof.value : null,
                    po_client_ref: poRef ? poRef.value : null,
                    vehicle_id: vehicleId && vehicleId.value ? parseInt(vehicleId.value, 10) : null,
                    driver_id: (function () {
                        var d = document.getElementById('la-driver-id');
                        return d && d.value ? parseInt(d.value, 10) : null;
                    })(),
                    service_option: serviceOpt ? serviceOpt.value : null,
                    pax_count: pax && pax.value ? parseInt(pax.value, 10) : null,
                    luggage_count: luggage && luggage.value !== '' ? parseInt(luggage.value, 10) : null,
                    pickup_flight_details: flightDetails ? flightDetails.value : null,
                    flight_number: flightNumber ? flightNumber.value : null,
                    meet_option: meetOption ? meetOption.value : null
                })
            }).catch(function () { /* ignore */ });
        }, 350);
    }

    function loadLaNotesDraft() {
        try {
            var raw = localStorage.getItem(LA_NOTES_STORAGE_KEY);
            if (!raw) return null;
            return JSON.parse(raw);
        } catch (e) {
            return null;
        }
    }

    function clearLaNotesDraft() {
        try { localStorage.removeItem(LA_NOTES_STORAGE_KEY); } catch (e) {}
    }

    function buildLaCombinedNote() {
        var sections = [];
        var tripBody = getLaNoteSectionValue('la-trip-notes');
        var dispatch = getLaNoteSectionValue('la-dispatch-notes');
        var partner = getLaNoteSectionValue('la-partner-notes');
        var billto = getLaNoteSectionValue('la-billto-notes');
        var addr = getLaNoteSectionValue('la-addr-notes');
        var airport = getLaNoteSectionValue('la-airport-notes');

        if (tripBody) sections.push(tripBody);
        if (dispatch) sections.push('[Dispatch Notes]\n' + dispatch);
        if (partner) sections.push('[Partner Notes]\n' + partner);
        if (billto) sections.push('[Bill To & Pax Notes]\n' + billto);
        if (addr) sections.push('[Address Notes]\n' + addr);
        if (airport) sections.push('[Airport Notes]\n' + airport);

        var flags = [];
        var addTs = document.getElementById('la-note-add-ts');
        var hideCust = document.getElementById('la-note-hide-customer');
        if (addTs && addTs.checked) flags.push('Add to T/S');
        if (hideCust && hideCust.checked) flags.push('Hide From Customer');
        if (flags.length) sections.push('[Flags]\n' + flags.join(', '));

        return sections.join('\n\n').slice(0, 4000);
    }

    function parseLaCombinedNoteIntoFields(raw) {
        if (!raw) return;
        var tripEl = document.getElementById('la-trip-notes');
        var dispatchEl = document.getElementById('la-dispatch-notes');
        var partnerEl = document.getElementById('la-partner-notes');
        var billtoEl = document.getElementById('la-billto-notes');
        var addrEl = document.getElementById('la-addr-notes');
        var airportEl = document.getElementById('la-airport-notes');
        var hiddenNote = document.getElementById('note');

        function extract(label) {
            var re = new RegExp('\\[\\s*' + label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*\\]\\s*([\\s\\S]*?)(?=\\n\\s*\\[|$)', 'i');
            var m = raw.match(re);
            return m ? m[1].trim() : '';
        }

        var dispatch = extract('Dispatch Notes');
        var partner = extract('Partner Notes');
        var billto = extract('Bill To & Pax Notes');
        var addr = extract('Address Notes');
        var airport = extract('Airport Notes');
        var flags = extract('Flags');

        var tripBody = raw;
        ['Dispatch Notes', 'Partner Notes', 'Bill To & Pax Notes', 'Address Notes', 'Airport Notes', 'Flags'].forEach(function (label) {
            var re = new RegExp('\\n*\\[\\s*' + label.replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\s*\\][\\s\\S]*$', 'i');
            tripBody = tripBody.replace(re, '');
        });
        tripBody = tripBody.replace(/^\[Trip Notes\]\s*/i, '').trim();

        if (tripEl) tripEl.value = tripBody;
        if (dispatchEl) dispatchEl.value = dispatch || '';
        if (partnerEl) partnerEl.value = partner || '';
        if (billtoEl) billtoEl.value = billto || '';
        if (addrEl) addrEl.value = addr || '';
        if (airportEl) airportEl.value = airport || '';
        if (hiddenNote) hiddenNote.value = raw;

        if (flags) {
            var addTs = document.getElementById('la-note-add-ts');
            var hideCust = document.getElementById('la-note-hide-customer');
            if (addTs) addTs.checked = /Add to T\/S/i.test(flags);
            if (hideCust) hideCust.checked = /Hide From Customer/i.test(flags);
        }
    }

    function syncLaNotesForSubmit() {
        var hiddenNote = document.getElementById('note');
        if (!hiddenNote) return;
        hiddenNote.value = buildLaCombinedNote();
        return hiddenNote.value;
    }

    function flashLaNotesSaved(btn) {
        if (!btn) return;
        var original = btn.getAttribute('data-original-label') || btn.textContent;
        btn.setAttribute('data-original-label', original);
        btn.classList.add('saved');
        btn.textContent = 'SAVED';
        window.setTimeout(function () {
            btn.classList.remove('saved');
            btn.textContent = original;
        }, 1500);
    }

    function initLaNotesSave() {
        var hiddenNote = document.getElementById('note');
        var serverNote = hiddenNote ? (hiddenNote.value || '').trim() : '';
        var draft = loadLaNotesDraft();

        if (serverNote) {
            parseLaCombinedNoteIntoFields(serverNote);
            // Fill any empty secondary boxes from local draft
            if (draft) {
                var fillIfEmpty = function (id, value) {
                    var el = document.getElementById(id);
                    if (el && !(el.value || '').trim() && value) el.value = value;
                };
                fillIfEmpty('la-dispatch-notes', draft.dispatch);
                fillIfEmpty('la-partner-notes', draft.partner);
                fillIfEmpty('la-billto-notes', draft.billto);
                fillIfEmpty('la-addr-notes', draft.addr);
                fillIfEmpty('la-airport-notes', draft.airport);
            }
        } else if (draft) {
            applyLaNotesDraft(draft);
        }

        syncLaNotesForSubmit();

        var saveAndFlash = function (btn) {
            saveLaNotesDraft();
            flashLaNotesSaved(btn);
        };

        var tripBtn = document.getElementById('la-btn-save-trip-notes');
        if (tripBtn) {
            tripBtn.addEventListener('click', function () {
                saveAndFlash(tripBtn);
            });
        }

        var dispatchBtn = document.getElementById('la-btn-save-dispatch-notes');
        if (dispatchBtn) {
            dispatchBtn.addEventListener('click', function () {
                saveAndFlash(dispatchBtn);
            });
        }

        var noteIds = [
            'la-trip-notes', 'la-dispatch-notes', 'la-partner-notes',
            'la-billto-notes', 'la-addr-notes', 'la-airport-notes'
        ];
        var draftTimer = null;
        var scheduleDraftSave = function () {
            if (draftTimer) clearTimeout(draftTimer);
            draftTimer = setTimeout(function () {
                saveLaNotesDraft();
            }, 300);
        };
        noteIds.forEach(function (id) {
            var el = document.getElementById(id);
            if (!el) return;
            el.addEventListener('input', scheduleDraftSave);
            el.addEventListener('change', scheduleDraftSave);
        });
        ['la-note-add-ts', 'la-note-hide-customer'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', scheduleDraftSave);
        });
    }

    function initLaRightColumn() {
        function pad2(n) { return n < 10 ? '0' + n : String(n); }
        function formatDateTime(dateVal, timeVal) {
            if (!dateVal || !timeVal) return '';
            var parts = dateVal.split('-');
            if (parts.length !== 3) return dateVal + ' ' + timeVal;
            var d = new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10),
                parseInt(timeVal.split(':')[0] || '0', 10), parseInt(timeVal.split(':')[1] || '0', 10));
            if (isNaN(d.getTime())) return dateVal + ' ' + timeVal;
            var h = d.getHours();
            var ampm = h >= 12 ? 'PM' : 'AM';
            h = h % 12 || 12;
            return pad2(d.getMonth() + 1) + '/' + pad2(d.getDate()) + '/' + d.getFullYear()
                + ' ' + h + ':' + pad2(d.getMinutes()) + ' ' + ampm;
        }

        function syncRightDateTime() {
            var out = document.getElementById('la-right-datetime');
            var dateEl = document.getElementById('pickup_date');
            var timeEl = document.getElementById('pickup_time');
            if (out && dateEl && timeEl) {
                var formatted = formatDateTime(dateEl.value, timeEl.value);
                if (out.tagName === 'INPUT') out.value = formatted;
                else out.textContent = formatted || '—';
            }
        }

        function syncDuration() {
            var ui = document.getElementById('la-duration');
            var hidden = document.getElementById('select_hours');
            if (!ui || !hidden) return;
            hidden.value = ui.value || '1';
        }

        function syncChildSeatFields() {
            syncLaChildSeatHiddenFields();
        }

        var childSeatLabels = {
            forward_toddler: 'Forward facing (Toddler)',
            rear_infant: 'Rear facing (Infant)',
            booster: 'Booster Seat'
        };

        function addLaChildSeatLine() {
            var typeUi = document.getElementById('la-child-seat-type-ui');
            var qtyUi = document.getElementById('la-child-seat-add-qty');
            if (!typeUi || !qtyUi) return;

            var type = typeUi.value;
            var qty = parseInt(qtyUi.value, 10);
            if (!type || isNaN(qty) || qty < 1) {
                alert('Select child seat type and enter a valid count.');
                return;
            }

            laChildSeatLines.push({
                type: type,
                qty: qty,
                label: childSeatLabels[type] || type
            });

            renderLaChildSeatList();
            syncLaChildSeatHiddenFields();
            updateLaChildSeatPricingRow();
            syncLaGrandTotals();
            qtyUi.value = '1';
        }

        function initLaChildSeatFromForm() {
            var typeHidden = document.getElementById('child_seat_type');
            var qtyHidden = document.getElementById('child_seat_quantity');
            var reqUi = document.getElementById('la-child-seat-required-ui');
            if (!typeHidden || !qtyHidden) return;

            var type = typeHidden.value;
            var qty = parseInt(qtyHidden.value, 10);
            if (reqUi && type) reqUi.value = type;

            if (type && !isNaN(qty) && qty > 0) {
                var qtyUi = document.getElementById('la-child-seat-count-ui');
                if (qtyUi && laChildSeatLines.length === 0) qtyUi.value = String(qty);
                renderLaChildSeatList();
                updateLaChildSeatPricingRow();
            }
        }

        var toggleChildSeats = document.getElementById('la-toggle-child-seats');
        var childSeatPanel = document.getElementById('la-child-seat-panel');
        if (toggleChildSeats && childSeatPanel) {
            toggleChildSeats.addEventListener('click', function (e) {
                e.preventDefault();
                childSeatPanel.classList.toggle('open');
            });
        }

        var toggleAdditionalPassengers = document.getElementById('la-toggle-additional-passengers');
        var additionalPassengersPanel = document.getElementById('la-additional-passengers-panel');
        if (toggleAdditionalPassengers && additionalPassengersPanel) {
            toggleAdditionalPassengers.addEventListener('click', function (e) {
                e.preventDefault();
                additionalPassengersPanel.classList.toggle('open');
            });
        }

        var addChildSeatBtn = document.getElementById('la-child-seat-add-btn');
        if (addChildSeatBtn) addChildSeatBtn.addEventListener('click', addLaChildSeatLine);

        initLaChildSeatFromForm();

        ['pickup_date', 'pickup_time'].forEach(function (id) {
            var el = document.getElementById(id);
            if (el) el.addEventListener('change', syncRightDateTime);
            if (el) el.addEventListener('input', syncRightDateTime);
        });

        var duration = document.getElementById('la-duration');
        if (duration) duration.addEventListener('input', syncDuration);

        var childReq = document.getElementById('la-child-seat-required-ui');
        var childQty = document.getElementById('la-child-seat-count-ui');
        if (childReq) childReq.addEventListener('change', handleLaChildSeatRequiredChange);
        if (childQty) childQty.addEventListener('input', function () {
            syncLaChildSeatHiddenFields();
            updateLaChildSeatPricingRow();
            syncLaGrandTotals();
        });

        document.querySelectorAll('[data-la-price-tabs]').forEach(function (bar) {
            var tabs = bar.querySelectorAll('[data-price-tab]');
            var panels = bar.parentElement.querySelectorAll('[data-price-panel]');
            tabs.forEach(function (tab) {
                tab.addEventListener('click', function () {
                    var key = tab.getAttribute('data-price-tab');
                    tabs.forEach(function (t) { t.classList.remove('active'); });
                    panels.forEach(function (p) {
                        p.classList.toggle('active', p.getAttribute('data-price-panel') === key);
                    });
                    tab.classList.add('active');
                    sumLaActivePricingPanel();
                    syncLaGrandTotals();
                });
            });
        });

        initLaPricingListeners();

        document.querySelectorAll('[data-la-assign-tabs]').forEach(function (bar) {
            bar.querySelectorAll('[data-assign-tab]').forEach(function (tab) {
                tab.addEventListener('click', function () {
                    bar.querySelectorAll('[data-assign-tab]').forEach(function (t) { t.classList.remove('active'); });
                    tab.classList.add('active');
                });
            });
        });

        syncRightDateTime();
        syncDuration();
        syncChildSeatFields();
        recalcLaPricingPanel('primary');
        syncLaGrandTotals();

        window.syncLaRightFields = function () {
            syncRightDateTime();
            syncDuration();
            syncChildSeatFields();
        };
        window.prepareLaChildSeatForSubmit = prepareLaChildSeatForSubmit;
    }

    function initLaCompactSelect(rootId, onChange) {
        var root = document.getElementById(rootId);
        if (!root) return;
        var trigger = root.querySelector('.la-compact-select-trigger');
        var panel = root.querySelector('.la-compact-select-panel');
        var search = root.querySelector('.la-compact-select-search');
        var hidden = root.querySelector('input[type="hidden"]');
        var valueEl = root.querySelector('.la-compact-select-value');
        var options = Array.prototype.slice.call(root.querySelectorAll('.la-compact-select-option'));

        function close() { root.classList.remove('open'); }
        function open() {
            root.classList.add('open');
            if (search) { search.value = ''; filter(''); search.focus(); }
        }
        function filter(q) {
            var n = (q || '').toLowerCase();
            options.forEach(function (o) {
                var hay = (o.getAttribute('data-filter-text') || o.textContent).toLowerCase();
                o.style.display = n === '' || hay.indexOf(n) >= 0 ? '' : 'none';
            });
        }
        function select(opt) {
            options.forEach(function (o) { o.classList.remove('selected'); });
            opt.classList.add('selected');
            if (hidden) hidden.value = opt.getAttribute('data-value') || '';
            if (valueEl) valueEl.textContent = opt.getAttribute('data-label') || opt.textContent.trim();
            if (hidden) hidden.dispatchEvent(new Event('change', { bubbles: true }));
            close();
            if (typeof onChange === 'function') onChange();
        }

        trigger.addEventListener('click', function (e) {
            e.preventDefault();
            root.classList.contains('open') ? close() : open();
        });
        options.forEach(function (o) {
            o.addEventListener('click', function () { select(o); });
        });
        if (search) search.addEventListener('input', function () { filter(this.value); });
        document.addEventListener('click', function (e) {
            if (!root.contains(e.target)) close();
        });
    }

    function setAccountDetailFields(data) {
        var company = document.getElementById('account_company_name_view');
        var billing = document.getElementById('account_billing_name_view');
        if (company) company.value = data.companyName || '';
        if (billing) billing.value = data.billingName || '';
    }

    function syncAccountFromSelect() {
        var sel = document.getElementById('account_id');
        if (!sel || !sel.value) {
            setAccountDetailFields({});
            return;
        }
        fetch('{{ url('/accounts') }}/' + encodeURIComponent(sel.value) + '/edit-data', {
            headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
        }).then(function (r) { return r.ok ? r.json() : null; })
          .then(function (payload) {
              var a = payload && payload.account ? payload.account : null;
              setAccountDetailFields({
                  companyName: a ? a.company_name : '',
                  billingName: a && a.billing ? a.billing.name : ''
              });
          }).catch(function () { setAccountDetailFields({}); });
    }

    document.querySelectorAll('.la-tabs').forEach(function (tabBar) {
        tabBar.querySelectorAll('.la-tab').forEach(function (tab) {
            tab.addEventListener('click', function () {
                var col = tabBar.closest('.la-col');
                tabBar.querySelectorAll('.la-tab').forEach(function (t) { t.classList.remove('active'); });
                col.querySelectorAll('.la-tab-panel').forEach(function (p) { p.classList.remove('active'); });
                tab.classList.add('active');
                var panel = col.querySelector('[data-panel="' + tab.getAttribute('data-tab') + '"]');
                if (panel) panel.classList.add('active');
            });
        });
    });

    var serviceSel = document.getElementById('service_option');
    if (serviceSel) serviceSel.addEventListener('change', toggleServiceUi);

    ['booker_first_name', 'booker_last_name', 'booker_number', 'booker_email'].forEach(function (id) {
        var el = document.getElementById(id);
        if (el) el.addEventListener('input', syncBookerHidden);
    });

    var returnCb = document.getElementById('return_service_cb');
    if (returnCb) {
        returnCb.checked = document.getElementById('return_service').value === '1';
        returnCb.addEventListener('change', syncReturnHidden);
    }

    var customPrice = document.getElementById('custom_total_price');
    if (customPrice) customPrice.addEventListener('input', syncTotalDue);

    initLaCompactSelect('account-select', syncAccountFromSelect);
    initLaCompactSelect('vehicle-select', function () {
        var hid = document.getElementById('vehicle-id');
        var disp = document.getElementById('la-vehicle-display');
        if (hid && disp) {
            var root = document.getElementById('vehicle-select');
            var label = root ? root.querySelector('.la-compact-select-value') : null;
            disp.textContent = 'Vehicle: ' + (label ? label.textContent : '');
        }
        syncAssignCarFromVehicleId();
    });

    function syncDriverPreview() {
        var sel = document.getElementById('la-driver-id');
        var img = document.getElementById('la-driver-preview');
        if (!sel || !img) return;
        var opt = sel.options[sel.selectedIndex];
        var pic = opt ? (opt.getAttribute('data-picture') || '') : '';
        if (pic) {
            img.src = pic;
            img.classList.remove('is-empty');
            img.style.display = '';
        } else {
            img.removeAttribute('src');
            img.classList.add('is-empty');
            img.style.display = 'none';
        }
    }

    function syncAssignCarFromVehicleId() {
        var hid = document.getElementById('vehicle-id');
        var car = document.getElementById('la-assign-car');
        if (!hid || !car) return;
        var val = hid.value || '';
        if (car.value !== val) car.value = val;
    }

    function syncVehicleFromAssignCar() {
        var car = document.getElementById('la-assign-car');
        var hid = document.getElementById('vehicle-id');
        var root = document.getElementById('vehicle-select');
        if (!car || !hid) return;
        var val = car.value || '';
        hid.value = val;
        if (root) {
            var options = root.querySelectorAll('.la-compact-select-option');
            var valueEl = root.querySelector('.la-compact-select-value');
            var matched = null;
            options.forEach(function (o) {
                var isMatch = (o.getAttribute('data-value') || '') === val;
                o.classList.toggle('selected', isMatch);
                if (isMatch) matched = o;
            });
            if (valueEl) {
                if (matched) {
                    valueEl.textContent = matched.getAttribute('data-label') || matched.textContent;
                } else {
                    valueEl.textContent = '---- NOT ASSIGNED ----';
                }
            }
            var disp = document.getElementById('la-vehicle-display');
            if (disp && valueEl) disp.textContent = 'Vehicle: ' + valueEl.textContent;
        }
    }

    var driverSelect = document.getElementById('la-driver-id');
    if (driverSelect) {
        driverSelect.addEventListener('change', syncDriverPreview);
        syncDriverPreview();
    }
    var assignCar = document.getElementById('la-assign-car');
    if (assignCar) {
        assignCar.addEventListener('change', syncVehicleFromAssignCar);
        syncAssignCarFromVehicleId();
    }

    function initDateTimePickersOnClick() {
        document.querySelectorAll('input[type="date"], input[type="time"]').forEach(function (el) {
            if (el.getAttribute('data-picker-click') === '1' || el.disabled || el.readOnly) return;
            el.setAttribute('data-picker-click', '1');
            el.addEventListener('click', function () {
                if (typeof el.showPicker !== 'function') return;
                try {
                    el.showPicker();
                } catch (e) { /* browser may block if not user-activated / unsupported */ }
            });
            el.addEventListener('keydown', function (e) {
                if (e.key !== 'Enter' && e.key !== ' ') return;
                if (typeof el.showPicker !== 'function') return;
                try {
                    el.showPicker();
                } catch (err) {}
            });
        });
    }

    initRoutingRadios();
    initLaRoutingCreate();
    initAddrTypeTabs();
    initLaAirlines();
    initLaAirports();
    initLaFbos();
    initDateTimePickersOnClick();
    initLaRightColumn();
    initLaNotesSave();

    var accountHidden = document.getElementById('account_id');
    if (accountHidden) accountHidden.addEventListener('change', syncAccountFromSelect);

    toggleServiceUi();
    syncBookerHidden();
    syncReturnHidden();
    syncTotalDue();
    syncAccountFromSelect();

    if (reservationStripeEnabled && typeof Stripe !== 'undefined') {
        var stripe = Stripe(@json($stripePublishableKey ?? ''));
        var card = stripe.elements().create('card', { style: { base: { fontSize: '14px' } } });
        var cardEl = document.getElementById('reservation-card-element');
        if (cardEl) card.mount('#reservation-card-element');

        var payBtn = document.getElementById('btn-reservation-pay');
        if (payBtn) {
            payBtn.addEventListener('click', async function () {
                var errEl = document.getElementById('reservation-card-errors');
                var spinner = document.getElementById('btn-reservation-spinner');
                var btnText = document.getElementById('btn-reservation-text');
                if (errEl) errEl.textContent = '';
                var nameInput = document.getElementById('card-name-reservation');
                if (!nameInput || !nameInput.value.trim()) {
                    if (errEl) errEl.textContent = 'Enter name on card.';
                    return;
                }
                if (!document.getElementById('vehicle-id').value) {
                    alert('Select a vehicle.');
                    return;
                }
                syncBookerHidden();
                syncReturnHidden();
                syncLaPickupFlightDetails();
                if (typeof window.prepareLaChildSeatForSubmit === 'function') window.prepareLaChildSeatForSubmit();
                if (typeof window.syncLaRightFields === 'function') window.syncLaRightFields();
                if (typeof window.syncLaIntlPhoneValues === 'function') window.syncLaIntlPhoneValues();
                if (typeof syncLaStopLocationsHidden === 'function') syncLaStopLocationsHidden();
                if (typeof syncLaNotesForSubmit === 'function') syncLaNotesForSubmit();

                payBtn.disabled = true;
                if (spinner) spinner.classList.remove('d-none');
                if (btnText) btnText.style.opacity = '0.7';

                var pm = await stripe.createPaymentMethod({
                    type: 'card', card: card,
                    billing_details: { name: nameInput.value.trim() }
                });
                if (pm.error) {
                    if (errEl) errEl.textContent = pm.error.message;
                    payBtn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    if (btnText) btnText.style.opacity = '';
                    return;
                }

                var fd = new FormData(form);
                fd.set('payment_method_id', pm.paymentMethod.id);
                fd.delete('save_without_pay');

                try {
                    var res = await fetch(storeUrl, {
                        method: 'POST', body: fd,
                        headers: { 'Accept': 'application/json', 'X-Requested-With': 'XMLHttpRequest' }
                    });
                    var data = await res.json();
                    if (res.status === 422 && data.errors) {
                        alert(Object.values(data.errors).flat().join(' '));
                        return;
                    }
                    if (data.requires_action && data.payment_intent_client_secret) {
                        var conf = await stripe.confirmCardPayment(data.payment_intent_client_secret);
                        if (conf.error) { if (errEl) errEl.textContent = conf.error.message; return; }
                        var fin = await fetch(finalizeUrl, {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            body: JSON.stringify({ booking_id: String(data.booking_id) })
                        });
                        data = await fin.json();
                    }
                    if (data.success && data.redirect) {
                        if (typeof clearLaNotesDraft === 'function') clearLaNotesDraft();
                        window.location.href = data.redirect;
                    }
                    else alert(data.message || 'Payment failed.');
                } catch (e) {
                    alert(e.message || 'Request failed.');
                } finally {
                    payBtn.disabled = false;
                    if (spinner) spinner.classList.add('d-none');
                    if (btnText) btnText.style.opacity = '';
                }
            });
        }
    }

    form.addEventListener('submit', function () {
        if (typeof window.syncLaIntlPhoneValues === 'function') window.syncLaIntlPhoneValues();
        if (typeof window.syncLaRightFields === 'function') window.syncLaRightFields();
        if (typeof window.prepareLaChildSeatForSubmit === 'function') window.prepareLaChildSeatForSubmit();
        syncLaPickupFlightDetails();
        syncBookerHidden();
        syncReturnHidden();
        if (typeof syncLaStopLocationsHidden === 'function') syncLaStopLocationsHidden();
        if (typeof syncLaNotesForSubmit === 'function') syncLaNotesForSubmit();
        if (typeof saveLaNotesDraft === 'function') saveLaNotesDraft();
    });

    if (isEmbed) {
        document.body.classList.add('la-embed-mode');
    }
    @if(!empty($isEmbed) && request()->boolean('saved'))
    if (window.parent && window.parent !== window) {
        window.parent.postMessage({
            type: 'dispatch-edit-saved',
            bookingId: editingBookingId
        }, '*');
    }
    @endif
})();
</script>
@endpush
