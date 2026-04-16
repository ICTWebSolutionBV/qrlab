<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Spatie\LaravelPasskeys\Actions\GeneratePasskeyRegisterOptionsAction;
use Spatie\LaravelPasskeys\Actions\StorePasskeyAction;

class PasskeyController extends Controller
{
    public function registerOptions(Request $request)
    {
        $action = app(GeneratePasskeyRegisterOptionsAction::class);

        return $action->execute($request->user());
    }

    public function store(Request $request)
    {
        $request->validate([
            'passkey' => ['required', 'json'],
            'options' => ['required', 'json'],
            'name' => ['nullable', 'string', 'max:255'],
        ]);

        $action = app(StorePasskeyAction::class);

        try {
            $action->execute(
                $request->user(),
                $request->input('passkey'),
                $request->input('options'),
                $request->getHost(),
                ['name' => $request->input('name', 'Passkey ' . now()->format('Y-m-d H:i'))],
            );

            return back()->with('success', 'Passkey registered successfully.');
        } catch (\Throwable $e) {
            \Log::error('Passkey registration failed', [
                'message' => $e->getMessage(),
                'class' => get_class($e),
            ]);
            throw ValidationException::withMessages([
                'passkey' => 'Something went wrong registering the passkey. Please try again.',
            ]);
        }
    }

    public function destroy(string $passkey, Request $request)
    {
        $request->user()->passkeys()->where('id', $passkey)->delete();

        return back()->with('success', 'Passkey removed.');
    }
}
