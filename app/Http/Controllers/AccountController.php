<?php

namespace App\Http\Controllers;

use App\Models\Account;
use DataTables;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\View\View;

class AccountController extends Controller
{
    public function index(): View
    {
        return view('pages.accounts.index');
    }

    /**
     * Server-side data for the Accounts list (DataTables: search, sort, paginate).
     */
    public function data(Request $request)
    {
        $query = Account::query()
            ->with('billingContact')
            ->orderByDesc('id');

        return DataTables::of($query)
            ->addColumn('billing_name', function (Account $a) {
                return $a->billingContact?->name ?: '—';
            })
            ->editColumn('phone', function (Account $a) {
                return Account::formatUsPhone($a->phone) ?? '—';
            })
            ->editColumn('address', function (Account $a) {
                return $a->addressPreview(20);
            })
            ->editColumn('email', function (Account $a) {
                return '<a href="mailto:' . e($a->email) . '">' . e($a->email) . '</a>';
            })
            ->editColumn('company_number', function (Account $a) {
                return '<span class="acc-mono-dt">' . e($a->company_number ?? '—') . '</span>';
            })
            ->addColumn('action', function (Account $a) {
                $id = (int) $a->id;

                return '<div class="table-actions acc-actions text-right" style="white-space:nowrap;">'
                    . '<a href="#" class="acc-view" data-id="' . $id . '" title="View">'
                    . '<i class="ik ik-eye f-16"></i></a> '
                    . '<a href="#" class="acc-edit" data-id="' . $id . '" title="Edit">'
                    . '<i class="ik ik-edit-2 f-16 text-primary"></i></a> '
                    . '<a href="#" class="acc-delete text-danger" data-id="' . $id . '" title="Delete">'
                    . '<i class="ik ik-trash-2 f-16"></i></a>'
                    . '</div>';
            })
            ->rawColumns(['action', 'email', 'company_number'])
            ->make(true);
    }

    public function forEdit(Account $account): JsonResponse
    {
        $account->load('billingContact');
        $bc = $account->billingContact;

        return response()->json([
            'account' => [
                'id' => $account->id,
                'company_number' => $account->company_number,
                'company_name' => $account->company_name,
                'email' => $account->email,
                'address' => $account->address,
                'phone' => Account::formatUsPhone($account->phone),
                'phone_digits' => $account->phone,
                'created_at' => $account->created_at?->toIso8601String(),
                'updated_at' => $account->updated_at?->toIso8601String(),
                'billing' => $bc ? [
                    'name' => $bc->name,
                    'email' => $bc->email,
                    'phone' => Account::formatUsPhone($bc->phone),
                    'phone_digits' => $bc->phone,
                ] : null,
            ],
        ]);
    }

    public function store(Request $request): JsonResponse
    {
        $validator = $this->validateAccountPayload($request, true);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }
        $d = $validator->validated();

        try {
            $account = DB::transaction(function () use ($d) {
                $acc = new Account;
                $acc->company_name = $d['company_name'];
                $acc->email = $d['email'];
                $acc->address = $d['address'];
                $acc->phone = Account::normalizeUsPhoneToDigits($d['phone']);
                $acc->company_number = Account::generateUniqueCompanyNumber();
                $acc->save();

                $acc->billingContact()->create([
                    'name' => $d['billing_name'],
                    'email' => $d['billing_email'],
                    'phone' => Account::normalizeUsPhoneToDigits($d['billing_phone']),
                ]);

                return $acc->fresh(['billingContact']);
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not create account.'], 500);
        }

        return response()->json([
            'message' => 'Account created successfully.',
            'account' => [
                'id' => $account->id,
                'company_number' => $account->company_number,
            ],
        ], 201);
    }

    public function update(Request $request, Account $account): JsonResponse
    {
        $validator = $this->validateAccountPayload($request, false);
        if ($validator->fails()) {
            return response()->json(['message' => 'Validation failed.', 'errors' => $validator->errors()], 422);
        }
        $d = $validator->validated();

        try {
            DB::transaction(function () use ($d, $account) {
                $account->update([
                    'company_name' => $d['company_name'],
                    'email' => $d['email'],
                    'address' => $d['address'],
                    'phone' => Account::normalizeUsPhoneToDigits($d['phone']),
                ]);

                if ($account->billingContact) {
                    $account->billingContact->update([
                        'name' => $d['billing_name'],
                        'email' => $d['billing_email'],
                        'phone' => Account::normalizeUsPhoneToDigits($d['billing_phone']),
                    ]);
                } else {
                    $account->billingContact()->create([
                        'name' => $d['billing_name'],
                        'email' => $d['billing_email'],
                        'phone' => Account::normalizeUsPhoneToDigits($d['billing_phone']),
                    ]);
                }
            });
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not update account.'], 500);
        }

        return response()->json(['message' => 'Account updated successfully.']);
    }

    public function destroy(Account $account): JsonResponse
    {
        try {
            $account->delete();
        } catch (\Throwable $e) {
            report($e);

            return response()->json(['message' => 'Could not delete account.'], 500);
        }

        return response()->json(['message' => 'Account deleted successfully.']);
    }

    private function validateAccountPayload(Request $request, bool $isCreate): \Illuminate\Validation\Validator
    {
        $phoneRule = ['required', 'string', function ($attribute, $value, $fail) {
            $d = Account::normalizeUsPhoneToDigits($value);
            if (strlen($d) !== 10) {
                $fail('The ' . $attribute . ' must be a valid 10-digit US phone number.');
            }
        }];

        return Validator::make($request->all(), [
            'company_name' => 'required|string|max:255',
            'email' => 'required|email|max:255',
            'address' => 'required|string|max:2000',
            'phone' => $phoneRule,
            'billing_name' => 'required|string|max:255',
            'billing_email' => 'required|email|max:255',
            'billing_phone' => $phoneRule,
        ]);
    }
}
