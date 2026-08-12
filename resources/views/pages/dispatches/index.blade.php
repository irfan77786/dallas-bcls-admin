@extends('layouts.main')
@section('title', 'Dispatches')

@push('head')
<meta name="csrf-token" content="{{ csrf_token() }}">
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
<style>
    .main-content .container-fluid.disp-shell {
        max-width: none;
        width: 100%;
        margin: 0;
        padding: 0.35rem 0.5rem 0.75rem;
    }

    .disp-toolbar {
        display: flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.65rem 1rem;
        padding: 0.45rem 0.35rem 0.55rem;
        background: #f3f4f6;
        border: 1px solid #d1d5db;
        border-bottom: 0;
        font-size: 15px;
        color: #222;
    }

    .disp-date-input {
        width: 168px;
        height: 26px;
        border: 1px solid #9ca3af;
        padding: 0 6px;
        font-size: 15px;
        text-align: center;
        background: #fff;
        cursor: pointer;
    }

    .disp-date-native {
        display: none;
    }

    .disp-date-nav {
        display: inline-flex;
        align-items: center;
        gap: 2px;
        position: relative;
    }

    .disp-date-nav a,
    .disp-date-nav button.disp-date-arrow {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 26px;
        height: 26px;
        border: 1px solid #c45c14;
        background: #e67e22;
        color: #fff;
        text-decoration: none;
        cursor: pointer;
        padding: 0;
        line-height: 1;
    }

    .disp-date-nav a:hover,
    .disp-date-nav button.disp-date-arrow:hover {
        background: #d35400;
        color: #fff;
    }

    .disp-date-menu {
        position: absolute;
        top: 30px;
        left: 0;
        z-index: 1200;
        min-width: 160px;
        background: #fff;
        border: 1px solid #9ca3af;
        box-shadow: 0 4px 14px rgba(0,0,0,.18);
        font-size: 15px;
    }

    .disp-date-menu[hidden] { display: none !important; }

    .disp-date-menu button {
        display: flex;
        width: 100%;
        align-items: center;
        justify-content: space-between;
        border: 0;
        background: transparent;
        padding: 7px 12px;
        text-align: left;
        cursor: pointer;
        color: #111;
    }

    .disp-date-menu button:hover,
    .disp-date-menu button.is-active {
        background: #e5e7eb;
    }

    .disp-date-range-panel {
        position: absolute;
        top: 30px;
        left: 162px;
        z-index: 1201;
        background: #dcdcdc;
        border: 1px solid #9ca3af;
        box-shadow: 0 4px 14px rgba(0,0,0,.18);
        padding: 10px;
        min-width: 520px;
    }

    .disp-date-range-panel.is-specific {
        min-width: 260px;
    }

    .disp-date-range-panel.is-specific .disp-date-cals {
        grid-template-columns: 1fr;
    }

    .disp-date-range-panel.is-specific [data-cal-end-wrap] {
        display: none;
    }

    .disp-date-range-panel[hidden] { display: none !important; }

    .disp-date-cals {
        display: grid;
        grid-template-columns: 1fr 1fr;
        gap: 12px;
    }

    .disp-cal-title {
        font-weight: 500;
        font-size: 15px;
        margin-bottom: 6px;
    }

    .disp-cal-nav {
        display: flex;
        align-items: center;
        gap: 4px;
        margin-bottom: 6px;
    }

    .disp-cal-nav select {
        height: 24px;
        font-size: 14px;
        border: 1px solid #9ca3af;
        background: #fff;
    }

    .disp-cal-nav button {
        width: 24px;
        height: 24px;
        border: 1px solid #9ca3af;
        border-radius: 50%;
        background: #fff;
        cursor: pointer;
        line-height: 1;
    }

    .disp-cal-grid {
        width: 100%;
        border-collapse: collapse;
        background: #fff;
        font-size: 14px;
        table-layout: fixed;
    }

    .disp-cal-grid th {
        background: #f3f4f6;
        padding: 3px 0;
        font-weight: 500;
        text-align: center;
    }

    .disp-cal-grid td {
        padding: 0;
        text-align: center;
        border: 1px solid #eee;
    }

    .disp-cal-grid button {
        width: 100%;
        height: 26px;
        border: 0;
        background: transparent;
        cursor: pointer;
        padding: 0;
    }

    .disp-cal-grid button:hover { background: #e5e7eb; }
    .disp-cal-grid button.is-muted { color: #bbb; }
    .disp-cal-grid button.is-selected {
        background: #fff59d;
        outline: 2px solid #f4c430;
        outline-offset: -2px;
        font-weight: 500;
    }

    .disp-date-range-actions {
        display: flex;
        justify-content: flex-end;
        margin-top: 8px;
    }

    .disp-date-range-actions button {
        height: 26px;
        padding: 0 14px;
        border: 1px solid #c45c14;
        background: #e67e22;
        color: #fff;
        font-weight: 500;
        font-size: 15px;
        cursor: pointer;
    }

    .disp-include {
        display: inline-flex;
        flex-wrap: wrap;
        align-items: center;
        gap: 0.45rem 0.75rem;
    }

    .disp-include-label {
        font-weight: 500;
        margin-right: 0.15rem;
    }

    .disp-include label {
        display: inline-flex;
        align-items: center;
        gap: 3px;
        margin: 0;
        font-weight: 400;
        cursor: pointer;
        white-space: nowrap;
    }

    .disp-include input[type="checkbox"] {
        margin: 0;
        vertical-align: middle;
    }

    .disp-search {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        margin-left: auto;
    }

    .disp-search input[type="text"] {
        width: 180px;
        height: 26px;
        border: 1px solid #9ca3af;
        padding: 0 8px;
        font-size: 15px;
        background: #fff;
    }

    .disp-btn-go {
        height: 26px;
        padding: 0 12px;
        border: 1px solid #b91c1c;
        background: #dc2626;
        color: #fff;
        font-size: 14px;
        font-weight: 500;
        letter-spacing: 0.03em;
        cursor: pointer;
    }

    .disp-btn-go:hover {
        background: #b91c1c;
    }

    .disp-advanced {
        display: inline-flex;
        align-items: center;
        gap: 4px;
        color: #c45c14;
        text-decoration: none;
        font-size: 15px;
        white-space: nowrap;
        cursor: pointer;
        font-weight: 500;
    }

    .disp-advanced:hover {
        text-decoration: underline;
        color: #9a4510;
    }

    .disp-advanced .disp-adv-caret {
        font-size: 14px;
        transition: transform 0.15s ease;
    }

    .disp-advanced.is-open .disp-adv-caret {
        transform: rotate(180deg);
    }

    .disp-advanced-panel {
        display: none;
        border: 1px solid #d1d5db;
        border-top: 0;
        background: #fafafa;
        padding: 10px 12px 12px;
    }

    .disp-advanced-panel.is-open {
        display: block;
    }

    .disp-adv-divider {
        border: 0;
        border-top: 1px dashed #c4c4c4;
        margin: 0 0 10px;
    }

    .disp-adv-row {
        display: grid;
        gap: 8px 10px;
        margin-bottom: 10px;
    }

    .disp-adv-row-selects {
        grid-template-columns: repeat(6, minmax(120px, 1fr));
    }

    .disp-adv-row-fields {
        grid-template-columns: repeat(7, minmax(100px, 1fr)) auto auto;
        align-items: end;
    }

    .disp-adv-field label {
        display: block;
        font-size: 14px;
        font-weight: 500;
        color: #333;
        margin-bottom: 3px;
    }

    .disp-adv-field select,
    .disp-adv-field input[type="text"] {
        width: 100%;
        height: 28px;
        border: 1px solid #9ca3af;
        background: #fff;
        font-size: 15px;
        padding: 0 6px;
    }

    .disp-adv-field .select2-container {
        width: 100% !important;
        font-size: 15px;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple {
        min-height: 28px;
        border: 1px solid #9ca3af;
        border-radius: 0;
        background: #fff;
    }

    .disp-adv-field .select2-container--default.select2-container--focus .select2-selection--multiple {
        border-color: #e67e22;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple .select2-selection__choice {
        background-color: #e67e22;
        border: 1px solid #c45c14;
        color: #fff;
        border-radius: 2px;
        padding: 0 5px;
        margin-top: 3px;
        font-size: 14px;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple .select2-selection__choice__remove {
        color: #fff;
        margin-right: 3px;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple .select2-selection__choice__remove:hover {
        color: #fee2e2;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple .select2-selection__placeholder {
        color: #6b7280;
        margin-top: 3px;
        font-size: 15px;
    }

    .disp-adv-field .select2-container--default .select2-selection--multiple .select2-selection__rendered {
        padding: 0 4px;
    }

    .disp-adv-field .select2-dropdown {
        border-color: #9ca3af;
        font-size: 15px;
    }

    .disp-adv-actions {
        display: inline-flex;
        gap: 6px;
        align-items: end;
        padding-bottom: 1px;
    }

    .disp-btn-search {
        height: 28px;
        padding: 0 14px;
        border: 1px solid #c45c14;
        background: #e67e22;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
    }

    .disp-btn-search:hover {
        background: #d35400;
    }

    .disp-btn-clear {
        height: 28px;
        padding: 0 14px;
        border: 1px solid #6b7280;
        background: #9ca3af;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        text-decoration: none;
        display: inline-flex;
        align-items: center;
    }

    .disp-btn-clear:hover {
        background: #6b7280;
        color: #fff;
        text-decoration: none;
    }

    @media (max-width: 1200px) {
        .disp-adv-row-selects {
            grid-template-columns: repeat(3, minmax(140px, 1fr));
        }
        .disp-adv-row-fields {
            grid-template-columns: repeat(3, minmax(120px, 1fr));
        }
    }

    .disp-grid-wrap {
        border: 1px solid #9ca3af;
        overflow: auto;
        background: #fff;
        max-height: calc(100vh - 160px);
    }

    .disp-grid {
        width: 100%;
        border-collapse: collapse;
        table-layout: auto;
        font-family: Tahoma, "Segoe UI", Arial, sans-serif;
        font-size: 14px;
        white-space: nowrap;
    }

    .disp-grid thead th {
        position: sticky;
        top: 0;
        z-index: 2;
        background: #6b7280;
        color: #fff;
        font-weight: 500;
        font-size: 14px;
        padding: 5px 6px;
        border: 1px solid #4b5563;
        text-align: left;
        user-select: none;
    }

    .disp-grid thead th .sort {
        opacity: 0.75;
        margin-left: 2px;
        font-size: 12px;
    }

    .disp-grid tbody td {
        padding: 3px 6px;
        border: 1px solid #d1d5db;
        vertical-align: middle;
        color: #111;
        line-height: 1.25;
    }

    .disp-grid tbody tr.disp-data-row {
        cursor: pointer;
    }

    .disp-grid tbody tr.disp-data-row:hover td {
        filter: none;
        outline: 1px solid rgba(0, 0, 0, 0.12);
        outline-offset: -1px;
    }

    /* Status row colors — sampled from LA dispatch screenshot */
    .disp-row-done td { background: #c9c9c9 !important; }
    .disp-row-done:nth-child(even) td { background: #c4c4c4 !important; }

    .disp-row-noshow td { background: #d8c8e8 !important; }
    .disp-row-noshow:nth-child(even) td { background: #d0bce0 !important; }

    .disp-row-ontheway td { background: #99ff99 !important; }
    .disp-row-ontheway:nth-child(even) td { background: #99ff99 !important; }

    .disp-row-arrived td { background: #f8f992 !important; }
    .disp-row-arrived:nth-child(even) td { background: #f4f588 !important; }

    .disp-row-customer_in_car td,
    .disp-row-customer td { background: #99ff99 !important; }
    .disp-row-customer_in_car:nth-child(even) td,
    .disp-row-customer:nth-child(even) td { background: #99ff99 !important; }

    .disp-row-assigned td { background: #8becee !important; }
    .disp-row-assigned:nth-child(even) td { background: #7ee8ea !important; }

    .disp-row-offered td { background: #ffffff !important; }
    .disp-row-offered:nth-child(even) td { background: #ffffff !important; }

    .disp-row-unassigned td { background: #f5350f !important; }
    .disp-row-unassigned:nth-child(even) td { background: #e8300a !important; }

    .disp-row-cancelled td,
    .disp-row-cancel_by_affiliate td { background: #999999 !important; }
    .disp-row-cancelled:nth-child(even) td,
    .disp-row-cancel_by_affiliate:nth-child(even) td { background: #999999 !important; }

    .disp-row-late_cancel td { background: #ffffcc !important; }
    .disp-row-late_cancel:nth-child(even) td { background: #ffffcc !important; }

    .disp-row-quote td { background: #fff0c0 !important; }
    .disp-row-quote:nth-child(even) td { background: #ffe8a8 !important; }

    /* Only the location CELL turns yellow (not the whole row) when DFW/Dal is present */
    .disp-grid tbody tr td.disp-loc-hl,
    .disp-grid tbody tr:nth-child(even) td.disp-loc-hl,
    .disp-grid tbody tr.disp-data-row:hover td.disp-loc-hl {
        background: #fff467 !important;
        font-weight: 500;
        filter: none;
    }

    /* Keep text readable on bright unassigned red */
    .disp-row-unassigned td {
        color: #111;
    }

    .disp-link {
        color: #1d4ed8;
        text-decoration: underline;
        cursor: pointer;
    }

    .disp-link:hover {
        color: #1e40af;
    }

    .disp-expand {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 16px;
        height: 16px;
        color: #374151;
        cursor: pointer;
        border: 0;
        background: transparent;
        padding: 0;
    }

    .disp-icon-note {
        color: #ca8a04;
        font-size: 16px;
    }

    .disp-icon-phone {
        color: #374151;
        font-size: 14px;
        margin-right: 2px;
    }

    .disp-icon-check {
        color: #15803d;
        font-weight: 500;
    }

    .disp-empty {
        padding: 2rem;
        text-align: center;
        color: #6b7280;
        font-size: 16px;
        background: #fff;
        border: 1px solid #9ca3af;
        border-top: 0;
    }

    .disp-detail-row td {
        background: #fff !important;
        padding: 10px 12px;
        white-space: normal;
        font-size: 14px;
        color: #222;
        border-top: 0;
        filter: none !important;
    }

    .disp-detail-row[hidden] {
        display: none;
    }

    .disp-inline-form {
        display: flex;
        flex-wrap: wrap;
        align-items: flex-end;
        gap: 10px 14px;
    }

    .disp-inline-field {
        display: flex;
        flex-direction: column;
        gap: 3px;
        min-width: 110px;
    }

    .disp-inline-field label {
        font-weight: 500;
        font-size: 14px;
        color: #222;
        margin: 0;
    }

    .disp-inline-field input,
    .disp-inline-field select {
        height: 26px;
        border: 1px solid #9ca3af;
        background: #fff;
        font-size: 15px;
        padding: 0 6px;
        min-width: 110px;
    }

    .disp-inline-times {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .disp-inline-times .disp-inline-field {
        min-width: 120px;
    }

    .disp-inline-field-car {
        min-width: 140px;
        max-width: 160px;
        flex: 0 0 160px;
    }

    .disp-inline-field-car select {
        min-width: 0;
        width: 160px;
        max-width: 160px;
    }

    .disp-inline-field-driver {
        min-width: 220px;
        max-width: 280px;
        flex: 0 0 240px;
    }

    .disp-inline-field-driver select {
        min-width: 0;
        width: 240px;
        max-width: 280px;
    }

    .disp-driver-opt {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .disp-driver-opt-img,
    .disp-driver-opt-ph {
        width: 28px;
        height: 28px;
        border-radius: 50%;
        object-fit: cover;
        flex-shrink: 0;
        background: #e5e7eb;
        border: 1px solid #d1d5db;
    }

    .disp-driver-opt-ph {
        display: inline-block;
    }

    .disp-driver-cell-wrap {
        display: inline-flex;
        align-items: center;
        gap: 6px;
    }

    .disp-driver-cell-img {
        width: 22px;
        height: 22px;
        border-radius: 50%;
        object-fit: cover;
        vertical-align: middle;
        border: 1px solid #c4c4c4;
    }

    .select2-container--default .select2-results__option--highlighted .disp-driver-opt-ph {
        background: #fff;
    }

    .disp-inline-field-driver .select2-container {
        width: 240px !important;
        font-size: 14px;
    }

    .disp-inline-field-driver .select2-container--default .select2-selection--single {
        height: 26px;
        border: 1px solid #9ca3af;
        border-radius: 0;
        background: #fff;
    }

    .disp-inline-field-driver .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 24px;
        padding-left: 6px;
        color: #111;
    }

    .disp-inline-field-driver .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 24px;
    }

    .disp-inline-actions {
        display: inline-flex;
        gap: 6px;
        align-items: center;
        padding-bottom: 1px;
    }

    .disp-btn-ok {
        height: 26px;
        min-width: 72px;
        padding: 0 14px;
        border: 1px solid #1d4ed8;
        background: #2563eb;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 6px;
    }

    .disp-btn-ok:hover { background: #1d4ed8; }
    .disp-btn-ok:disabled {
        opacity: 0.85;
        cursor: wait;
    }
    .disp-btn-ok.is-loading {
        min-width: 118px;
        background: #1d4ed8;
    }
    .disp-btn-ok .disp-ok-spinner {
        display: none;
        width: 12px;
        height: 12px;
        border: 2px solid rgba(255,255,255,0.35);
        border-top-color: #fff;
        border-radius: 50%;
        animation: dispOkSpin 0.7s linear infinite;
    }
    .disp-btn-ok.is-loading .disp-ok-spinner { display: inline-block; }
    .disp-btn-ok.is-loading .disp-ok-label { /* keep text */ }
    @keyframes dispOkSpin {
        to { transform: rotate(360deg); }
    }

    .disp-inline-msg.is-success {
        color: #15803d;
        font-weight: 600;
    }

    .disp-btn-close {
        height: 26px;
        padding: 0 14px;
        border: 1px solid #6b7280;
        background: #9ca3af;
        color: #fff;
        font-size: 15px;
        font-weight: 500;
        cursor: pointer;
    }

    .disp-btn-close:hover { background: #6b7280; }

    .disp-inline-msg {
        width: 100%;
        font-size: 14px;
        color: #15803d;
        display: none;
    }

    .disp-inline-msg.is-error { color: #b91c1c; }
    .disp-inline-msg.is-visible { display: block; }

    .disp-ctx-menu {
        position: fixed;
        z-index: 1080;
        min-width: 180px;
        background: #fff;
        border: 1px solid #9ca3af;
        box-shadow: 0 4px 16px rgba(0,0,0,.18);
        padding: 4px 0;
        font-family: Tahoma, "Segoe UI", Arial, sans-serif;
        font-size: 15px;
    }

    .disp-ctx-menu[hidden] {
        display: none !important;
    }

    .disp-ctx-menu button {
        display: block;
        width: 100%;
        text-align: left;
        border: 0;
        background: transparent;
        padding: 7px 14px;
        cursor: pointer;
        color: #111;
    }

    .disp-ctx-menu button:hover {
        background: #2563eb;
        color: #fff;
    }

    .disp-ctx-menu button.is-danger {
        color: #b91c1c;
    }

    .disp-ctx-menu button.is-danger:hover {
        background: #b91c1c;
        color: #fff;
    }

    #disp-edit-modal .modal-content {
        border-radius: 2px;
    }

    .disp-col-grid { width: 28px; text-align: center; }
    .disp-col-status { font-weight: 500; }
    .disp-col-mu { width: 42px; text-align: center; }
    .disp-col-rnd { width: 28px; text-align: center; }
    .disp-col-pax, .disp-col-lug { text-align: center; }
    .disp-col-total { font-weight: 500; white-space: nowrap; }

    .disp-pay-badge {
        display: inline-flex;
        align-items: center;
        padding: 1px 7px;
        border-radius: 10px;
        font-size: 13px;
        font-weight: 500;
        line-height: 1.4;
        white-space: nowrap;
        border: 1px solid transparent;
    }

    .disp-pay-paid {
        background: #dcfce7;
        color: #166534;
        border-color: #86efac;
    }

    .disp-pay-pending {
        background: #fef3c7;
        color: #92400e;
        border-color: #fcd34d;
    }

    .disp-pay-authorized {
        background: #dbeafe;
        color: #1e40af;
        border-color: #93c5fd;
    }

    .disp-pay-default {
        background: #f3f4f6;
        color: #374151;
        border-color: #d1d5db;
    }

    .disp-col-paylink {
        width: 34px;
        text-align: center;
        padding-left: 2px !important;
        padding-right: 2px !important;
    }

    .disp-paylink-btn {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 22px;
        height: 22px;
        color: #16a34a;
        text-decoration: none;
        border-radius: 3px;
        font-size: 14px;
        line-height: 1;
    }

    .disp-paylink-btn:hover {
        color: #15803d;
        background: rgba(22, 163, 74, 0.12);
    }

    .disp-paylink-btn.is-disabled {
        color: #9ca3af;
        opacity: 0.55;
        pointer-events: none;
        cursor: not-allowed;
    }

    .disp-tooltip {
        position: absolute;
        z-index: 50;
        background: #1f2937;
        color: #fff;
        padding: 6px 10px;
        font-size: 14px;
        border-radius: 2px;
        pointer-events: none;
        max-width: 260px;
        display: none;
        box-shadow: 0 2px 8px rgba(0,0,0,.25);
    }
</style>
@endpush

@section('content')
@php
    $qs = function (array $overrides = []) use ($dateValue, $dateFromValue, $dateToValue, $dateMode, $include, $search) {
        return array_merge([
            'date' => $dateValue,
            'date_from' => $dateFromValue,
            'date_to' => $dateToValue,
            'date_mode' => $dateMode,
            'new_live' => $include['new_live'] ? 1 : 0,
            'in_house' => $include['in_house'] ? 1 : 0,
            'farm_out' => $include['farm_out'] ? 1 : 0,
            'settled' => $include['settled'] ? 1 : 0,
            'farm_in' => $include['farm_in'] ? 1 : 0,
            'quotes' => $include['quotes'] ? 1 : 0,
            'q' => $search,
        ], $overrides);
    };
@endphp

<div class="container-fluid disp-shell">
    <form method="get" action="{{ route('dispatches.index') }}" id="disp-filter-form">
        <input type="hidden" name="adv" id="disp-adv-flag" value="{{ $advancedActive ? 1 : 0 }}">
        <input type="hidden" name="date_mode" id="disp-date-mode" value="{{ $dateMode }}">
        <input type="hidden" name="date" id="disp-date" value="{{ $dateValue }}">
        <input type="hidden" name="date_from" id="disp-date-from" value="{{ $dateFromValue }}">
        <input type="hidden" name="date_to" id="disp-date-to" value="{{ $dateToValue }}">
        <div class="disp-toolbar">
            <div class="disp-date-nav" id="disp-date-nav">
                <a href="{{ route('dispatches.index', $qs([
                    'date' => $prevDate,
                    'date_mode' => $prevDateMode,
                    'date_from' => $prevDateFrom,
                    'date_to' => $prevDateTo,
                ])) }}" title="Previous" aria-label="Previous">
                    <i class="bi bi-caret-left-fill"></i>
                </a>
                <input
                    type="text"
                    class="disp-date-input"
                    id="disp-date-display"
                    value="{{ $dateDisplay }}"
                    readonly
                    title="Pickup date filter"
                >
                <a href="{{ route('dispatches.index', $qs([
                    'date' => $nextDate,
                    'date_mode' => $nextDateMode,
                    'date_from' => $nextDateFrom,
                    'date_to' => $nextDateTo,
                ])) }}" title="Next" aria-label="Next">
                    <i class="bi bi-caret-right-fill"></i>
                </a>

                <div class="disp-date-menu" id="disp-date-menu" hidden>
                    <button type="button" data-date-preset="today">Today</button>
                    <button type="button" data-date-preset="tomorrow">Tomorrow</button>
                    <button type="button" data-date-preset="yesterday">Yesterday</button>
                    <button type="button" data-date-preset="week">This Week</button>
                    <button type="button" data-date-preset="month">This Month</button>
                    <button type="button" data-date-preset="specific">Specific Date <span>›</span></button>
                    <button type="button" data-date-preset="range" id="disp-date-range-trigger">Date Range <span>›</span></button>
                </div>

                <div class="disp-date-range-panel" id="disp-date-range-panel" hidden>
                    <div class="disp-date-cals">
                        <div data-cal-start-wrap>
                            <div class="disp-cal-title" id="disp-cal-start-title">Start date</div>
                            <div class="disp-cal-nav">
                                <button type="button" data-cal="start" data-dir="-1">&lt;</button>
                                <select data-cal-month="start"></select>
                                <select data-cal-year="start"></select>
                                <button type="button" data-cal="start" data-dir="1">&gt;</button>
                            </div>
                            <table class="disp-cal-grid" data-cal-grid="start">
                                <thead>
                                    <tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                        <div data-cal-end-wrap>
                            <div class="disp-cal-title">End date</div>
                            <div class="disp-cal-nav">
                                <button type="button" data-cal="end" data-dir="-1">&lt;</button>
                                <select data-cal-month="end"></select>
                                <select data-cal-year="end"></select>
                                <button type="button" data-cal="end" data-dir="1">&gt;</button>
                            </div>
                            <table class="disp-cal-grid" data-cal-grid="end">
                                <thead>
                                    <tr><th>Su</th><th>Mo</th><th>Tu</th><th>We</th><th>Th</th><th>Fr</th><th>Sa</th></tr>
                                </thead>
                                <tbody></tbody>
                            </table>
                        </div>
                    </div>
                    <div class="disp-date-range-actions">
                        <button type="button" id="disp-date-range-done">Done</button>
                    </div>
                </div>
            </div>

            <div class="disp-include">
                <span class="disp-include-label">Include:</span>
                <label><input type="checkbox" name="new_live" value="1" data-disp-type @checked($include['new_live'])> New Live</label>
                <label><input type="checkbox" name="in_house" value="1" data-disp-type @checked($include['in_house'])> In-House</label>
                <label><input type="checkbox" name="farm_out" value="1" data-disp-type @checked($include['farm_out'])> Farm-Out</label>
                <label><input type="checkbox" name="settled" value="1" data-disp-type @checked($include['settled'])> Settled</label>
                <label><input type="checkbox" name="farm_in" value="1" data-disp-type @checked($include['farm_in'])> Farm-In</label>
                <label><input type="checkbox" name="quotes" value="1" data-disp-type @checked($include['quotes'])> Quotes</label>
            </div>

            <div class="disp-search">
                <input type="text" name="q" value="{{ $search }}" placeholder="Quick Search Within Grid" autocomplete="off">
                <button type="submit" class="disp-btn-go">GO</button>
                <a href="#" class="disp-advanced{{ $advancedActive ? ' is-open' : '' }}" id="disp-advanced-toggle" title="Advanced Search">
                    Advanced Search <i class="bi bi-caret-down-fill disp-adv-caret"></i>
                </a>
            </div>
        </div>

        <div class="disp-advanced-panel{{ $advancedActive ? ' is-open' : '' }}" id="disp-advanced-panel">
            <hr class="disp-adv-divider">
            <div class="disp-adv-row disp-adv-row-selects">
                <div class="disp-adv-field">
                    <label>Status(es):</label>
                    <select name="statuses[]" class="disp-select2" multiple data-placeholder="Select options">
                        @foreach($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(in_array($value, $advanced['statuses'], true))>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="disp-adv-field">
                    <label>Driver(s):</label>
                    <select name="drivers[]" class="disp-select2" multiple data-placeholder="Select options">
                        @foreach(($drivers ?? []) as $driver)
                            <option value="{{ $driver->id }}" @selected(in_array((string) $driver->id, array_map('strval', (array) request('drivers', [])), true))>
                                {{ $driver->name }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="disp-adv-field">
                    <label>Car(s):</label>
                    <select name="cars[]" class="disp-select2" multiple data-placeholder="Select options">
                        @foreach($vehicles as $vehicle)
                            <option value="{{ $vehicle->id }}" @selected(in_array((string) $vehicle->id, array_map('strval', $advanced['cars']), true))>
                                {{ $vehicle->vehicle_name }}{{ $vehicle->vehicle_code ? ' ('.$vehicle->vehicle_code.')' : '' }}
                            </option>
                        @endforeach
                    </select>
                </div>
                <div class="disp-adv-field">
                    <label>Vehicle Type(s):</label>
                    <select name="vehicle_types[]" class="disp-select2" multiple data-placeholder="Select options">
                        @foreach($vehicleTypes as $code)
                            <option value="{{ $code }}" @selected(in_array($code, $advanced['vehicle_types'], true))>{{ $code }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="disp-adv-field">
                    <label>Affiliate(s):</label>
                    <select name="affiliates[]" class="disp-select2" multiple data-placeholder="Select options" disabled title="Affiliates not available yet">
                    </select>
                </div>
                <div class="disp-adv-field">
                    <label>Alias(es):</label>
                    <select name="aliases[]" class="disp-select2" multiple data-placeholder="Select options" disabled title="Aliases not available yet">
                    </select>
                </div>
            </div>

            <div class="disp-adv-row disp-adv-row-fields">
                <div class="disp-adv-field">
                    <label>Confirmation#</label>
                    <input type="text" name="confirmation" value="{{ $advanced['confirmation'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Account#</label>
                    <input type="text" name="account" value="{{ $advanced['account'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Billing Contact:</label>
                    <input type="text" name="billing_contact" value="{{ $advanced['billing_contact'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Pax Last Name:</label>
                    <input type="text" name="pax_last" value="{{ $advanced['pax_last'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Pax First Name:</label>
                    <input type="text" name="pax_first" value="{{ $advanced['pax_first'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Company Name:</label>
                    <input type="text" name="company_name" value="{{ $advanced['company_name'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-field">
                    <label>Client/Ref#</label>
                    <input type="text" name="client_ref" value="{{ $advanced['client_ref'] }}" autocomplete="off">
                </div>
                <div class="disp-adv-actions">
                    <button type="submit" class="disp-btn-search">Search</button>
                    <a href="{{ route('dispatches.index') }}" class="disp-btn-clear">Clear All</a>
                </div>
            </div>
        </div>
    </form>

    @if($bookings->isEmpty())
        <div class="disp-empty">No dispatches found for {{ $dateDisplay }}.</div>
    @else
        <div class="disp-grid-wrap">
            <table class="disp-grid" id="disp-grid">
                <thead>
                    <tr>
                        <th class="disp-col-grid">Grid</th>
                        <th>Svc Type <span class="sort">↕</span></th>
                        <th>Conf# <span class="sort">↕</span></th>
                        <th>PO/Client Ref# <span class="sort">↕</span></th>
                        <th>Status <span class="sort">↕</span></th>
                        <th class="disp-col-mu">Notes</th>
                        <th>PU Date <span class="sort">↕</span></th>
                        <th>PU Time <span class="sort">↕</span></th>
                        <th>PU Location <span class="sort">↕</span></th>
                        <th>DO Location <span class="sort">↕</span></th>
                        <th>Veh# <span class="sort">↕</span></th>
                        <th>Driver <span class="sort">↕</span></th>
                        <th>Passenger Name <span class="sort">↕</span></th>
                        <th class="disp-col-pax">Pax# <span class="sort">↕</span></th>
                        <th class="disp-col-lug">Lug# <span class="sort">↕</span></th>
                        <th>Passenger Pr Lvl <span class="sort">↕</span></th>
                        <th>Passenger Ph# <span class="sort">↕</span></th>
                        <th class="disp-col-rnd">Rnd</th>
                        <th>Total <span class="sort">↕</span></th>
                        <th>Payment <span class="sort">↕</span></th>
                        <th class="disp-col-paylink" title="Send payment link">Pay</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($bookings as $row)
                        @php
                            $rowClass = match ($row['status_key']) {
                                'done' => 'disp-row-done',
                                'noshow' => 'disp-row-noshow',
                                'ontheway' => 'disp-row-ontheway',
                                'arrived' => 'disp-row-arrived',
                                'customer_in_car', 'customer' => 'disp-row-customer_in_car',
                                'assigned' => 'disp-row-assigned',
                                'offered' => 'disp-row-offered',
                                'cancelled', 'cancel_by_affiliate' => 'disp-row-cancelled',
                                'late_cancel' => 'disp-row-late_cancel',
                                'quote' => 'disp-row-quote',
                                default => 'disp-row-unassigned',
                            };
                        @endphp
                        <tr
                            class="{{ $rowClass }} disp-data-row"
                            data-row-id="{{ $row['id'] }}"
                            data-edit-la-url="{{ $row['edit_la_url'] }}"
                            data-show-url="{{ $row['show_url'] }}"
                            data-destroy-url="{{ $row['destroy_url'] }}"
                            data-conf="{{ $row['conf'] }}"
                        >
                            <td class="disp-col-grid">
                                <button type="button" class="disp-expand" data-expand="{{ $row['id'] }}" aria-label="Expand">
                                    <i class="bi bi-caret-down-fill"></i>
                                </button>
                            </td>
                            <td>{{ $row['svc_type'] }}</td>
                            <td>
                                <a class="disp-link" href="{{ $row['show_url'] }}">{{ $row['conf'] }}</a>
                            </td>
                            <td>{{ $row['po_ref'] }}</td>
                            <td class="disp-col-status" data-status-cell>{{ $row['status_label'] }}</td>
                            <td class="disp-col-mu">
                                @if($row['has_note'])
                                    <i class="bi bi-file-earmark-text-fill disp-icon-note" title="{{ e($row['note']) }}"></i>
                                @endif
                            </td>
                            <td data-pu-date-cell>{{ $row['pu_date'] }}</td>
                            <td data-pu-time-cell>{{ $row['pu_time'] }}</td>
                            @php
                                $puLoc = (string) $row['pu_location'];
                                $doLoc = (string) $row['do_location'];
                                $puHighlight = $puLoc !== '' && preg_match('/\b(DFW|Dal)/i', $puLoc);
                                $doHighlight = $doLoc !== '' && preg_match('/\b(DFW|Dal)/i', $doLoc);
                            @endphp
                            <td class="{{ $puHighlight ? 'disp-loc-hl' : '' }}">{{ $puLoc }}</td>
                            <td class="{{ $doHighlight ? 'disp-loc-hl' : '' }}">{{ $doLoc }}</td>
                            <td data-veh-code-cell>{{ $row['veh_code'] }}</td>
                            <td data-driver-cell>
                                @if($row['driver'] !== '')
                                    <span class="disp-driver-cell-wrap">
                                        @if(!empty($row['driver_picture']))
                                            <img src="{{ $row['driver_picture'] }}" alt="" class="disp-driver-cell-img">
                                        @endif
                                        <a class="disp-link" href="#">{{ $row['driver'] }}</a>
                                    </span>
                                @endif
                            </td>
                            <td
                                class="disp-passenger"
                                data-phone="{{ e($row['passenger_phone']) }}"
                                title=""
                            >{{ $row['passenger_name'] }}</td>
                            <td class="disp-col-pax">{{ $row['pax'] }}</td>
                            <td class="disp-col-lug">{{ $row['lug'] }}</td>
                            <td>{{ $row['priority'] }}</td>
                            <td>
                                @if($row['passenger_phone'] !== '')
                                    <i class="bi bi-telephone-fill disp-icon-phone"></i>{{ $row['passenger_phone'] }}
                                @endif
                            </td>
                            <td class="disp-col-rnd">
                                @if($row['is_round_trip'])
                                    <span class="disp-icon-check">✓</span>
                                @endif
                            </td>
                            <td class="disp-col-total">${{ number_format((float) $row['total'], 2) }}</td>
                            <td>
                                @php
                                    $payClass = match (strtolower((string) $row['payment_status'])) {
                                        'paid' => 'disp-pay-paid',
                                        'pending' => 'disp-pay-pending',
                                        'authorized' => 'disp-pay-authorized',
                                        'draft' => 'disp-pay-default',
                                        default => 'disp-pay-default',
                                    };
                                @endphp
                                <span class="disp-pay-badge {{ $payClass }}">
                                    {{ $row['payment_status'] !== '' ? $row['payment_status'] : '—' }}
                                </span>
                            </td>
                            <td class="disp-col-paylink">
                                <a href="#"
                                   class="disp-paylink-btn js-send-payment-link {{ !empty($row['can_send_payment_link']) ? '' : 'is-disabled' }}"
                                   title="{{ !empty($row['can_send_payment_link']) ? 'Send payment link' : 'Payment link unavailable' }}"
                                   data-booking-id="{{ $row['id'] }}"
                                   data-public-id="{{ $row['conf'] }}"
                                   data-email="{{ $row['passenger_email'] ?? '' }}"
                                   data-customer-name="{{ $row['passenger_name'] ?? '' }}"
                                   data-amount="{{ number_format((float) $row['total'], 2, '.', '') }}"
                                   data-status="{{ $row['payment_status'] !== '' ? $row['payment_status'] : 'Unknown' }}"
                                   aria-label="Send payment link">
                                    <i class="bi bi-credit-card-fill"></i>
                                </a>
                            </td>
                        </tr>
                        <tr class="disp-detail-row" data-detail-for="{{ $row['id'] }}" hidden>
                            <td colspan="21">
                                <form
                                    class="disp-inline-form"
                                    data-inline-form="{{ $row['id'] }}"
                                    data-initial-driver-id="{{ $row['driver_id'] ?? '' }}"
                                    data-initial-trip-status="{{ $row['status_key'] ?? '' }}"
                                >
                                    <div class="disp-inline-field">
                                        <label>Pick-Up Date:</label>
                                        <input type="text" name="pickup_date" value="{{ $row['pu_date'] }}" autocomplete="off">
                                    </div>
                                    <div class="disp-inline-times">
                                        <div class="disp-inline-field">
                                            <label>Pick-Up Time:</label>
                                            <input type="text" name="pickup_time" value="{{ $row['pu_time'] }}" autocomplete="off">
                                        </div>
                                        <div class="disp-inline-field">
                                            <label>DO Time:</label>
                                            <input type="text" name="dropoff_time" value="{{ $row['do_time'] }}" autocomplete="off">
                                        </div>
                                        <div class="disp-inline-field">
                                            <label>Spot Time:</label>
                                            <input type="text" name="spot_time" value="{{ $row['spot_time'] }}" autocomplete="off">
                                        </div>
                                    </div>
                                    <div class="disp-inline-field">
                                        <label>Trip Status:</label>
                                        <select name="trip_status">
                                            @foreach($tripStatusOptions as $value => $label)
                                                <option value="{{ $value }}" @selected($row['status_key'] === $value)>{{ $label }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="disp-inline-field">
                                        <label>Vehicle Type:</label>
                                        <select name="vehicle_type_ui" data-vehicle-type>
                                            <option value="">—</option>
                                            @foreach($vehicleTypes as $code)
                                                <option value="{{ $code }}" @selected($row['veh_code'] === $code)>{{ $code }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="disp-inline-field disp-inline-field-driver">
                                        <label>Primary Driver:</label>
                                        <select name="driver_id" class="disp-driver-select" data-placeholder="— Unassigned —">
                                            <option value="">— Unassigned —</option>
                                            @foreach(($drivers ?? []) as $driver)
                                                <option
                                                    value="{{ $driver->id }}"
                                                    data-picture="{{ $driver->pictureUrl() ?: '' }}"
                                                    @selected((string) ($row['driver_id'] ?? '') === (string) $driver->id)
                                                >
                                                    {{ $driver->name }}@if($driver->plate_number) ({{ $driver->plate_number }})@endif
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="disp-inline-field disp-inline-field-car">
                                        <label>Primary Car:</label>
                                        <select name="vehicle_id" data-vehicle-id>
                                            <option value="">— Unassigned —</option>
                                            @foreach($vehicles as $vehicle)
                                                <option
                                                    value="{{ $vehicle->id }}"
                                                    data-code="{{ $vehicle->vehicle_code }}"
                                                    @selected((string) $row['vehicle_id'] === (string) $vehicle->id)
                                                >
                                                    {{ $vehicle->vehicle_code ? $vehicle->vehicle_code.' ('.$vehicle->vehicle_name.')' : $vehicle->vehicle_name }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="disp-inline-actions">
                                        <button type="submit" class="disp-btn-ok" aria-busy="false">
                                            <span class="disp-ok-spinner" aria-hidden="true"></span>
                                            <span class="disp-ok-label">OK</span>
                                        </button>
                                        <button type="button" class="disp-btn-close" data-close="{{ $row['id'] }}">Close</button>
                                    </div>
                                    <div class="disp-inline-msg" data-inline-msg></div>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif
</div>

<div class="disp-tooltip" id="disp-tooltip"></div>

<div class="disp-ctx-menu" id="disp-ctx-menu" hidden>
    <button type="button" data-action="quick-edit">Quick Edit</button>
    <button type="button" data-action="edit-reservation">Edit Reservation</button>
    <button type="button" data-action="view">View</button>
    <button type="button" class="is-danger" data-action="delete">Delete</button>
</div>

<div class="modal fade" id="disp-edit-modal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-centered" role="document" style="max-width:96vw;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h5 class="modal-title" style="font-size:18px; font-weight:500;">Edit Reservation <span id="disp-edit-modal-conf"></span></h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body p-0" style="height:82vh; background:#e8e8e8;">
                <iframe id="disp-edit-iframe" title="Edit Reservation" src="about:blank" style="width:100%;height:100%;border:0;background:#e8e8e8;"></iframe>
            </div>
        </div>
    </div>
</div>
@endsection

@push('script')
<script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<script>
(function () {
    var form = document.getElementById('disp-filter-form');
    var dateInput = document.getElementById('disp-date');
    var dateDisplay = document.getElementById('disp-date-display');
    var dateModeInput = document.getElementById('disp-date-mode');
    var dateFromInput = document.getElementById('disp-date-from');
    var dateToInput = document.getElementById('disp-date-to');
    var dateMenu = document.getElementById('disp-date-menu');
    var dateRangePanel = document.getElementById('disp-date-range-panel');
    var tooltip = document.getElementById('disp-tooltip');
    var advToggle = document.getElementById('disp-advanced-toggle');
    var advPanel = document.getElementById('disp-advanced-panel');
    var advFlag = document.getElementById('disp-adv-flag');

    var months = ['Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec'];
    var rangeState = {
        start: parseYmd((dateFromInput && dateFromInput.value) || (dateInput && dateInput.value)),
        end: parseYmd((dateToInput && dateToInput.value) || (dateInput && dateInput.value)),
        viewStart: null,
        viewEnd: null
    };
    rangeState.viewStart = new Date(rangeState.start.getFullYear(), rangeState.start.getMonth(), 1);
    rangeState.viewEnd = new Date(rangeState.end.getFullYear(), rangeState.end.getMonth(), 1);

    function parseYmd(value) {
        var parts = String(value || '').split('-');
        if (parts.length === 3) {
            return new Date(parseInt(parts[0], 10), parseInt(parts[1], 10) - 1, parseInt(parts[2], 10));
        }
        var d = new Date();
        return new Date(d.getFullYear(), d.getMonth(), d.getDate());
    }

    function toYmd(d) {
        var m = d.getMonth() + 1;
        var day = d.getDate();
        return d.getFullYear() + '-' + (m < 10 ? '0' + m : m) + '-' + (day < 10 ? '0' + day : day);
    }

    function sameDay(a, b) {
        return a && b && a.getFullYear() === b.getFullYear() && a.getMonth() === b.getMonth() && a.getDate() === b.getDate();
    }

    function fillMonthYearSelects(which) {
        var monthSel = document.querySelector('[data-cal-month="' + which + '"]');
        var yearSel = document.querySelector('[data-cal-year="' + which + '"]');
        if (!monthSel || !yearSel) return;
        var view = which === 'start' ? rangeState.viewStart : rangeState.viewEnd;
        if (!monthSel.options.length) {
            months.forEach(function (label, idx) {
                var opt = document.createElement('option');
                opt.value = String(idx);
                opt.textContent = label;
                monthSel.appendChild(opt);
            });
        }
        if (!yearSel.options.length) {
            var yNow = new Date().getFullYear();
            for (var y = yNow - 5; y <= yNow + 5; y++) {
                var optY = document.createElement('option');
                optY.value = String(y);
                optY.textContent = String(y);
                yearSel.appendChild(optY);
            }
        }
        monthSel.value = String(view.getMonth());
        yearSel.value = String(view.getFullYear());
    }

    function renderCalendar(which) {
        fillMonthYearSelects(which);
        var grid = document.querySelector('[data-cal-grid="' + which + '"] tbody');
        if (!grid) return;
        var view = which === 'start' ? rangeState.viewStart : rangeState.viewEnd;
        var selected = which === 'start' ? rangeState.start : rangeState.end;
        var first = new Date(view.getFullYear(), view.getMonth(), 1);
        var startPad = first.getDay();
        var daysInMonth = new Date(view.getFullYear(), view.getMonth() + 1, 0).getDate();
        var prevDays = new Date(view.getFullYear(), view.getMonth(), 0).getDate();
        grid.innerHTML = '';
        var dayNum = 1 - startPad;
        for (var r = 0; r < 6; r++) {
            var tr = document.createElement('tr');
            for (var c = 0; c < 7; c++, dayNum++) {
                var td = document.createElement('td');
                var btn = document.createElement('button');
                btn.type = 'button';
                var cellDate;
                if (dayNum < 1) {
                    cellDate = new Date(view.getFullYear(), view.getMonth() - 1, prevDays + dayNum);
                    btn.textContent = String(prevDays + dayNum);
                    btn.classList.add('is-muted');
                } else if (dayNum > daysInMonth) {
                    cellDate = new Date(view.getFullYear(), view.getMonth() + 1, dayNum - daysInMonth);
                    btn.textContent = String(dayNum - daysInMonth);
                    btn.classList.add('is-muted');
                } else {
                    cellDate = new Date(view.getFullYear(), view.getMonth(), dayNum);
                    btn.textContent = String(dayNum);
                }
                if (sameDay(cellDate, selected)) btn.classList.add('is-selected');
                btn.addEventListener('click', (function (picked, side) {
                    return function (e) {
                        e.preventDefault();
                        e.stopPropagation();
                        if (side === 'start') {
                            rangeState.start = picked;
                            rangeState.viewStart = new Date(picked.getFullYear(), picked.getMonth(), 1);
                            if (dateRangePanel && dateRangePanel.classList.contains('is-specific')) {
                                rangeState.end = new Date(picked.getTime());
                            } else if (rangeState.end < rangeState.start) {
                                rangeState.end = new Date(picked.getTime());
                            }
                        } else {
                            rangeState.end = picked;
                            rangeState.viewEnd = new Date(picked.getFullYear(), picked.getMonth(), 1);
                            if (rangeState.end < rangeState.start) rangeState.start = new Date(picked.getTime());
                        }
                        renderCalendar('start');
                        if (!(dateRangePanel && dateRangePanel.classList.contains('is-specific'))) {
                            renderCalendar('end');
                        }
                    };
                })(cellDate, which));
                td.appendChild(btn);
                tr.appendChild(td);
            }
            grid.appendChild(tr);
        }
    }

    function openDateMenu() {
        if (!dateMenu) return;
        dateMenu.hidden = false;
        if (dateRangePanel) dateRangePanel.hidden = true;
        dateMenu.querySelectorAll('[data-date-preset]').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-date-preset') === (dateModeInput && dateModeInput.value));
        });
    }

    function closeDateMenus() {
        if (dateMenu) dateMenu.hidden = true;
        if (dateRangePanel) dateRangePanel.hidden = true;
    }

    function openRangePanel(mode) {
        if (!dateRangePanel) return;
        if (dateMenu) dateMenu.hidden = false;
        dateRangePanel.hidden = false;
        var isSpecific = mode === 'specific';
        dateRangePanel.classList.toggle('is-specific', isSpecific);
        if (dateModeInput) dateModeInput.value = isSpecific ? 'specific' : 'range';

        var startTitle = document.getElementById('disp-cal-start-title');
        if (startTitle) startTitle.textContent = isSpecific ? 'Date' : 'Start date';

        dateMenu.querySelectorAll('[data-date-preset]').forEach(function (btn) {
            btn.classList.toggle('is-active', btn.getAttribute('data-date-preset') === mode);
        });

        // For specific date, keep end in sync with start selection.
        if (isSpecific) {
            rangeState.end = new Date(rangeState.start.getTime());
            rangeState.viewEnd = new Date(rangeState.viewStart.getTime());
        }

        renderCalendar('start');
        if (!isSpecific) renderCalendar('end');
    }

    function applyDatePreset(mode, extra) {
        if (!form) return;
        if (mode === 'all') mode = 'today';
        if (dateModeInput) dateModeInput.value = mode;
        if (extra) {
            if (extra.date && dateInput) dateInput.value = extra.date;
            if (extra.from && dateFromInput) dateFromInput.value = extra.from;
            if (extra.to && dateToInput) dateToInput.value = extra.to;
        }
        form.submit();
    }

    if (dateDisplay) {
        dateDisplay.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            if (dateMenu && !dateMenu.hidden) {
                closeDateMenus();
            } else {
                openDateMenu();
            }
        });
    }

    if (dateMenu) {
        dateMenu.querySelectorAll('[data-date-preset]').forEach(function (btn) {
            btn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                var mode = btn.getAttribute('data-date-preset');
                if (mode === 'range' || mode === 'specific') {
                    openRangePanel(mode);
                    return;
                }
                applyDatePreset(mode);
            });
        });
    }

    document.querySelectorAll('[data-cal][data-dir]').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var which = btn.getAttribute('data-cal');
            var dir = parseInt(btn.getAttribute('data-dir'), 10) || 0;
            if (which === 'start') {
                rangeState.viewStart = new Date(rangeState.viewStart.getFullYear(), rangeState.viewStart.getMonth() + dir, 1);
            } else {
                rangeState.viewEnd = new Date(rangeState.viewEnd.getFullYear(), rangeState.viewEnd.getMonth() + dir, 1);
            }
            renderCalendar(which);
        });
    });

    document.querySelectorAll('[data-cal-month], [data-cal-year]').forEach(function (sel) {
        sel.addEventListener('change', function (e) {
            e.stopPropagation();
            var which = sel.getAttribute('data-cal-month') || sel.getAttribute('data-cal-year');
            var monthSel = document.querySelector('[data-cal-month="' + which + '"]');
            var yearSel = document.querySelector('[data-cal-year="' + which + '"]');
            var view = new Date(parseInt(yearSel.value, 10), parseInt(monthSel.value, 10), 1);
            if (which === 'start') rangeState.viewStart = view;
            else rangeState.viewEnd = view;
            renderCalendar(which);
        });
        sel.addEventListener('click', function (e) { e.stopPropagation(); });
    });

    var rangeDone = document.getElementById('disp-date-range-done');
    if (rangeDone) {
        rangeDone.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var isSpecific = dateRangePanel && dateRangePanel.classList.contains('is-specific');
            var from = toYmd(rangeState.start);
            var to = isSpecific ? from : toYmd(rangeState.end);
            var mode = isSpecific ? 'specific' : (from === to ? 'specific' : 'range');
            applyDatePreset(mode, { date: from, from: from, to: to });
        });
    }

    document.addEventListener('click', function (e) {
        if (e.target.closest('#disp-date-nav')) return;
        // Keep calendar open while interacting; only outside clicks close it.
        closeDateMenus();
    }, true);

    function initDispSelect2() {
        if (!window.jQuery || typeof window.jQuery.fn.select2 !== 'function') return;
        var $ = window.jQuery;
        $('.disp-select2').each(function () {
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '100%',
                placeholder: $el.data('placeholder') || 'Select options',
                allowClear: true,
                closeOnSelect: false
            });
        });
    }

    function formatDriverSelectOption(opt) {
        if (!opt.id) {
            return opt.text;
        }
        var picture = '';
        if (opt.element) {
            picture = opt.element.getAttribute('data-picture') || '';
        }
        var $ = window.jQuery;
        var imgHtml = picture
            ? '<img src="' + picture + '" class="disp-driver-opt-img" alt="">'
            : '<span class="disp-driver-opt-ph"></span>';
        return $('<span class="disp-driver-opt">' + imgHtml + '<span>' + opt.text + '</span></span>');
    }

    function initDriverSelect2(scope) {
        if (!window.jQuery || typeof window.jQuery.fn.select2 !== 'function') return;
        var $ = window.jQuery;
        var $root = scope ? $(scope) : $(document);
        $root.find('.disp-driver-select').each(function () {
            var $el = $(this);
            if ($el.hasClass('select2-hidden-accessible')) {
                $el.select2('destroy');
            }
            $el.select2({
                width: '240px',
                placeholder: $el.data('placeholder') || '— Unassigned —',
                allowClear: true,
                templateResult: formatDriverSelectOption,
                templateSelection: formatDriverSelectOption,
                dropdownParent: $(document.body)
            });
        });
    }

    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function () {
            initDispSelect2();
            initDriverSelect2();
        });
    } else {
        initDispSelect2();
        initDriverSelect2();
    }
    // theme.js may also init selects after this stack; re-init once more shortly after.
    setTimeout(function () {
        initDispSelect2();
        initDriverSelect2();
    }, 50);

    if (advToggle && advPanel) {
        advToggle.addEventListener('click', function (e) {
            e.preventDefault();
            var open = !advPanel.classList.contains('is-open');
            advPanel.classList.toggle('is-open', open);
            advToggle.classList.toggle('is-open', open);
            if (advFlag) advFlag.value = open ? '1' : '0';
            if (open) {
                setTimeout(function () {
                    if (window.jQuery) {
                        window.jQuery('.disp-select2').select2('close');
                    }
                }, 0);
            }
        });
    }

    if (form) {
        var typeCbs = form.querySelectorAll('[data-disp-type]');

        typeCbs.forEach(function (cb) {
            cb.addEventListener('change', function () {
                form.submit();
            });
        });
    }

    function csrfToken() {
        var meta = document.querySelector('meta[name="csrf-token"]');
        if (meta) return meta.getAttribute('content');
        var input = document.querySelector('input[name="_token"]');
        return input ? input.value : '';
    }

    var ctxMenu = document.getElementById('disp-ctx-menu');
    var ctxRowId = null;
    var editModal = window.jQuery ? window.jQuery('#disp-edit-modal') : null;
    var editIframe = document.getElementById('disp-edit-iframe');
    var editModalConf = document.getElementById('disp-edit-modal-conf');

    function setRowOpen(id, open) {
        var detail = document.querySelector('.disp-detail-row[data-detail-for="' + id + '"]');
        var row = document.querySelector('.disp-data-row[data-row-id="' + id + '"]');
        if (!detail || !row) return;
        detail.toggleAttribute('hidden', !open);
        var icon = row.querySelector('.disp-expand i');
        if (icon) {
            icon.className = open ? 'bi bi-caret-up-fill' : 'bi bi-caret-down-fill';
        }
    }

    function closeAllRows(exceptId) {
        document.querySelectorAll('.disp-detail-row').forEach(function (detail) {
            var id = detail.getAttribute('data-detail-for');
            if (exceptId && String(id) === String(exceptId)) return;
            setRowOpen(id, false);
        });
    }

    function openQuickEdit(id) {
        closeAllRows(id);
        setRowOpen(id, true);
        var detail = document.querySelector('.disp-detail-row[data-detail-for="' + id + '"]');
        if (detail) {
            setTimeout(function () { initDriverSelect2(detail); }, 0);
        }
    }

    function hideCtxMenu() {
        if (!ctxMenu) return;
        ctxMenu.hidden = true;
        ctxRowId = null;
    }

    function showCtxMenu(x, y, row) {
        if (!ctxMenu || !row) return;
        ctxRowId = row.getAttribute('data-row-id');
        ctxMenu.hidden = false;
        var menuW = ctxMenu.offsetWidth || 180;
        var menuH = ctxMenu.offsetHeight || 140;
        var left = Math.min(x, window.innerWidth - menuW - 8);
        var top = Math.min(y, window.innerHeight - menuH - 8);
        ctxMenu.style.left = Math.max(8, left) + 'px';
        ctxMenu.style.top = Math.max(8, top) + 'px';
    }

    function openEditReservationModal(row) {
        if (!row) return;
        var url = row.getAttribute('data-edit-la-url');
        if (!url) return;
        var sep = url.indexOf('?') >= 0 ? '&' : '?';
        if (editModalConf) {
            editModalConf.textContent = row.getAttribute('data-conf') ? '#' + row.getAttribute('data-conf') : '';
        }
        if (editIframe) {
            editIframe.src = url + sep + 'embed=1';
        }
        if (editModal && editModal.modal) {
            editModal.modal('show');
        } else {
            window.open(url, '_blank');
        }
    }

    function closeEditReservationModal(reload) {
        if (editModal && editModal.modal) {
            editModal.modal('hide');
        }
        if (editIframe) editIframe.src = 'about:blank';
        if (reload) {
            window.location.reload();
        }
    }

    if (editModal && editModal.on) {
        editModal.on('hidden.bs.modal', function () {
            if (editIframe) editIframe.src = 'about:blank';
        });
    }

    window.addEventListener('message', function (event) {
        if (!event.data || event.data.type !== 'dispatch-edit-saved') return;
        closeEditReservationModal(true);
    });

    document.querySelectorAll('.disp-data-row').forEach(function (row) {
        row.addEventListener('contextmenu', function (e) {
            if (e.target.closest('a, input, select, textarea, button.disp-btn-ok, button.disp-btn-close, .disp-inline-form')) return;
            e.preventDefault();
            showCtxMenu(e.clientX, e.clientY, row);
        });

        row.addEventListener('dblclick', function (e) {
            if (e.target.closest('a, input, select, textarea, button, .disp-inline-form, .disp-ctx-menu')) return;
            e.preventDefault();
            hideCtxMenu();
            openQuickEdit(row.getAttribute('data-row-id'));
        });
    });

    document.querySelectorAll('.disp-expand').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var row = btn.closest('.disp-data-row');
            var rect = btn.getBoundingClientRect();
            showCtxMenu(rect.left, rect.bottom + 2, row);
        });
    });

    document.addEventListener('click', function (e) {
        if (ctxMenu && !ctxMenu.hidden && !e.target.closest('#disp-ctx-menu, .disp-expand')) {
            hideCtxMenu();
        }
    });

    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') hideCtxMenu();
    });

    if (ctxMenu) {
        ctxMenu.querySelectorAll('button[data-action]').forEach(function (btn) {
            btn.addEventListener('click', function () {
                var action = btn.getAttribute('data-action');
                var row = ctxRowId
                    ? document.querySelector('.disp-data-row[data-row-id="' + ctxRowId + '"]')
                    : null;
                var id = ctxRowId;
                hideCtxMenu();
                if (!row || !id) return;

                if (action === 'quick-edit') {
                    openQuickEdit(id);
                    return;
                }
                if (action === 'edit-reservation') {
                    openEditReservationModal(row);
                    return;
                }
                if (action === 'view') {
                    var showUrl = row.getAttribute('data-show-url');
                    if (showUrl) window.location.href = showUrl;
                    return;
                }
                if (action === 'delete') {
                    var conf = row.getAttribute('data-conf') || id;
                    var destroyUrl = row.getAttribute('data-destroy-url');
                    if (!destroyUrl) return;

                    Swal.fire({
                        title: 'Delete reservation?',
                        text: 'Delete reservation #' + conf + '? This cannot be undone.',
                        icon: 'warning',
                        showCancelButton: true,
                        confirmButtonText: 'Yes, delete',
                        cancelButtonText: 'Cancel',
                        confirmButtonColor: '#c75c5c'
                    }).then(function (result) {
                        if (!result.isConfirmed) return;

                        fetch(destroyUrl, {
                            method: 'DELETE',
                            headers: {
                                'Accept': 'application/json',
                                'X-CSRF-TOKEN': csrfToken(),
                                'X-Requested-With': 'XMLHttpRequest'
                            }
                        })
                        .then(function (res) {
                            return res.json().then(function (data) {
                                return { ok: res.ok, data: data };
                            });
                        })
                        .then(function (result) {
                            if (!result.ok) {
                                throw new Error((result.data && result.data.message) || 'Delete failed');
                            }
                            var detail = document.querySelector('.disp-detail-row[data-detail-for="' + id + '"]');
                            if (detail) detail.remove();
                            row.remove();
                            Swal.fire({
                                icon: 'success',
                                title: 'Deleted',
                                text: (result.data && result.data.message) || 'Reservation deleted.',
                                timer: 1500,
                                showConfirmButton: false
                            });
                        })
                        .catch(function (err) {
                            Swal.fire('Error', err.message || 'Delete failed', 'error');
                        });
                    });
                }
            });
        });
    }

    function statusRowClass(statusKey) {
        switch (statusKey) {
            case 'done': return 'disp-row-done';
            case 'noshow': return 'disp-row-noshow';
            case 'ontheway': return 'disp-row-ontheway';
            case 'arrived': return 'disp-row-arrived';
            case 'customer_in_car':
            case 'customer': return 'disp-row-customer_in_car';
            case 'assigned': return 'disp-row-assigned';
            case 'offered': return 'disp-row-offered';
            case 'cancelled':
            case 'cancel_by_affiliate': return 'disp-row-cancelled';
            case 'late_cancel': return 'disp-row-late_cancel';
            case 'quote': return 'disp-row-quote';
            default: return 'disp-row-unassigned';
        }
    }

    document.querySelectorAll('.disp-btn-close').forEach(function (btn) {
        btn.addEventListener('click', function (e) {
            e.preventDefault();
            e.stopPropagation();
            setRowOpen(btn.getAttribute('data-close'), false);
        });
    });

    document.querySelectorAll('[data-inline-form]').forEach(function (inlineForm) {
        var vehicleType = inlineForm.querySelector('[data-vehicle-type]');
        var vehicleId = inlineForm.querySelector('[data-vehicle-id]');

        if (vehicleType && vehicleId) {
            vehicleType.addEventListener('change', function () {
                var code = vehicleType.value;
                if (!code) return;
                var match = Array.prototype.find.call(vehicleId.options, function (opt) {
                    return opt.getAttribute('data-code') === code;
                });
                if (match) vehicleId.value = match.value;
            });
            vehicleId.addEventListener('change', function () {
                var selected = vehicleId.options[vehicleId.selectedIndex];
                var code = selected ? (selected.getAttribute('data-code') || '') : '';
                if (code && vehicleType) vehicleType.value = code;
            });
        }

        inlineForm.addEventListener('submit', function (e) {
            e.preventDefault();
            e.stopPropagation();
            var id = inlineForm.getAttribute('data-inline-form');
            var msg = inlineForm.querySelector('[data-inline-msg]');
            var btn = inlineForm.querySelector('.disp-btn-ok');
            var btnLabel = btn ? btn.querySelector('.disp-ok-label') : null;
            var closeBtn = inlineForm.querySelector('.disp-btn-close');
            var driverSelect = inlineForm.querySelector('[name="driver_id"]');
            var statusSelect = inlineForm.querySelector('[name="trip_status"]');
            var payload = {
                pickup_date: (inlineForm.querySelector('[name="pickup_date"]') || {}).value || '',
                pickup_time: (inlineForm.querySelector('[name="pickup_time"]') || {}).value || '',
                dropoff_time: (inlineForm.querySelector('[name="dropoff_time"]') || {}).value || '',
                spot_time: (inlineForm.querySelector('[name="spot_time"]') || {}).value || '',
                trip_status: (statusSelect || {}).value || '',
                vehicle_id: (inlineForm.querySelector('[name="vehicle_id"]') || {}).value || null,
                driver_id: (driverSelect || {}).value || null
            };
            if (payload.vehicle_id === '') payload.vehicle_id = null;
            if (payload.driver_id === '') payload.driver_id = null;

            var initialDriverId = String(inlineForm.getAttribute('data-initial-driver-id') || '');
            var initialStatus = String(inlineForm.getAttribute('data-initial-trip-status') || '');
            var nextDriverId = payload.driver_id != null ? String(payload.driver_id) : '';
            var nextStatus = String(payload.trip_status || '');
            var maySendDriverEmail = nextDriverId !== '' && nextDriverId !== initialDriverId;
            var maySendStatusEmail = nextStatus !== '' && nextStatus !== initialStatus;
            var maySendEmail = maySendDriverEmail || maySendStatusEmail;

            function setOkLoading(on, labelText) {
                if (!btn) return;
                btn.disabled = !!on;
                btn.classList.toggle('is-loading', !!on);
                btn.setAttribute('aria-busy', on ? 'true' : 'false');
                if (btnLabel) btnLabel.textContent = on ? (labelText || 'Saving…') : 'OK';
                if (closeBtn) closeBtn.disabled = !!on;
            }

            if (msg) {
                msg.classList.remove('is-visible', 'is-error', 'is-success');
                msg.textContent = '';
            }

            setOkLoading(true, maySendEmail ? 'Sending…' : 'Saving…');
            if (msg && maySendEmail) {
                msg.textContent = 'Please wait — saving and sending email…';
                msg.classList.add('is-visible');
            }

            fetch('/dispatches/' + id, {
                method: 'PATCH',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-TOKEN': csrfToken(),
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify(payload)
            })
            .then(function (res) { return res.json().then(function (data) { return { ok: res.ok, data: data }; }); })
            .then(function (result) {
                if (!result.ok || !result.data || !result.data.ok) {
                    throw new Error((result.data && result.data.message) || 'Save failed');
                }
                var rowData = result.data.row;
                var dataRow = document.querySelector('.disp-data-row[data-row-id="' + id + '"]');
                if (dataRow && rowData) {
                    dataRow.className = statusRowClass(rowData.status_key) + ' disp-data-row';
                    var statusCell = dataRow.querySelector('[data-status-cell]');
                    var puDateCell = dataRow.querySelector('[data-pu-date-cell]');
                    var puTimeCell = dataRow.querySelector('[data-pu-time-cell]');
                    var vehCell = dataRow.querySelector('[data-veh-code-cell]');
                    var carCell = dataRow.querySelector('[data-car-cell]');
                    var driverCell = dataRow.querySelector('[data-driver-cell]');
                    if (statusCell) statusCell.textContent = rowData.status_label || '';
                    if (puDateCell) puDateCell.textContent = rowData.pu_date || '';
                    if (puTimeCell) puTimeCell.textContent = rowData.pu_time || '';
                    if (vehCell) vehCell.textContent = rowData.veh_code || '';
                    if (carCell) {
                        carCell.innerHTML = rowData.car
                            ? '<a class="disp-link" href="#">' + rowData.car + '</a>'
                            : '';
                    }
                    if (driverCell) {
                        if (rowData.driver) {
                            var drvHtml = '<span class="disp-driver-cell-wrap">';
                            if (rowData.driver_picture) {
                                drvHtml += '<img src="' + rowData.driver_picture + '" alt="" class="disp-driver-cell-img">';
                            }
                            drvHtml += '<a class="disp-link" href="#">' + rowData.driver + '</a></span>';
                            driverCell.innerHTML = drvHtml;
                        } else {
                            driverCell.innerHTML = '';
                        }
                    }
                }

                // Keep form baselines in sync after a successful save.
                inlineForm.setAttribute('data-initial-driver-id', rowData && rowData.driver_id != null ? String(rowData.driver_id) : '');
                inlineForm.setAttribute('data-initial-trip-status', rowData && rowData.status_key ? String(rowData.status_key) : nextStatus);

                var driverEmailSent = !!result.data.driver_email_sent;

                if (driverEmailSent && typeof Swal !== 'undefined') {
                    var title = 'Successfully sent';
                    var text = 'Driver assignment email sent to passenger and admin.';
                    if (msg) {
                        msg.textContent = text;
                        msg.classList.add('is-visible', 'is-success');
                    }
                    return Swal.fire({
                        icon: 'success',
                        title: title,
                        text: text,
                        timer: 2200,
                        showConfirmButton: false
                    }).then(function () {
                        setRowOpen(id, false);
                    });
                }

                if (msg) {
                    msg.textContent = 'Saved successfully.';
                    msg.classList.add('is-visible', 'is-success');
                }
                setTimeout(function () { setRowOpen(id, false); }, 450);
            })
            .catch(function (err) {
                if (msg) {
                    msg.textContent = err.message || 'Save failed';
                    msg.classList.remove('is-success');
                    msg.classList.add('is-visible', 'is-error');
                }
                if (typeof Swal !== 'undefined') {
                    Swal.fire('Error', err.message || 'Save failed', 'error');
                }
            })
            .finally(function () {
                setOkLoading(false);
            });
        });
    });

    document.querySelectorAll('.disp-passenger').forEach(function (cell) {
        cell.addEventListener('mouseenter', function (e) {
            var phone = cell.getAttribute('data-phone') || '';
            if (!phone || !tooltip) return;
            tooltip.textContent = 'Passenger Contact Information: Home Phone : ' + phone;
            tooltip.style.display = 'block';
            tooltip.style.left = (e.pageX + 12) + 'px';
            tooltip.style.top = (e.pageY + 12) + 'px';
        });
        cell.addEventListener('mousemove', function (e) {
            if (!tooltip || tooltip.style.display === 'none') return;
            tooltip.style.left = (e.pageX + 12) + 'px';
            tooltip.style.top = (e.pageY + 12) + 'px';
        });
        cell.addEventListener('mouseleave', function () {
            if (tooltip) tooltip.style.display = 'none';
        });
    });
})();
</script>
@include('pages.bookings.partials.payment-link-modal')
@endpush
