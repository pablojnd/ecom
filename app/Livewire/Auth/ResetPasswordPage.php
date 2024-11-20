<?php

namespace App\Livewire\Auth;

use App\Models\User;
use Livewire\Component;
use Illuminate\Support\Str;
use Livewire\Attributes\Url;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Password;
use Illuminate\Auth\Events\PasswordReset;

#[Title('Resetear Contraseña')]
class ResetPasswordPage extends Component
{
    public $token;
    public $email;
    public $password;
    public $password_confirmation;

    const PASSWORD_RESET_SUCCESS = 'Password reset successful. You can now log in.';
    const PASSWORD_RESET_ERROR = 'Error resetting password. Please try again.';

    /**
     * Reglas de validación para el formulario de reseteo de contraseña.
     */
    protected function rules()
    {
        return [
            'token' => 'required',
            'email' => 'required|email',
            'password' => 'required|min:8|confirmed',
        ];
    }

    public function mount($token)
    {
        $this->token = $token;
    }

    public function save()
    {
        $this->validate();

        $status = Password::reset(
            $this->credentials(),
            [$this, 'resetPassword']
        );

        return $status === Password::PASSWORD_RESET
            ? $this->notifyUser(self::PASSWORD_RESET_SUCCESS, 'success', '/login')
            : $this->notifyUser(self::PASSWORD_RESET_ERROR, 'error');
    }

    /**
     * Retorna las credenciales necesarias para el reset de contraseña.
     */
    private function credentials(): array
    {
        return [
            'email' => $this->email,
            'password' => $this->password,
            'password_confirmation' => $this->password_confirmation,
            'token' => $this->token,
        ];
    }

    /**
     * Callback para el reseteo de contraseña.
     */
    public function resetPassword(User $user, string $password): void
    {
        $user->forceFill([
            'password' => Hash::make($password),
        ])->setRememberToken(Str::random(60));

        $user->save();

        event(new PasswordReset($user));
    }

    /**
     * Notifica al usuario con un mensaje y opcionalmente redirige.
     */
    private function notifyUser(string $message, string $type = 'success', ?string $redirectUrl = null)
    {
        session()->flash($type, $message);

        if ($redirectUrl) {
            return redirect($redirectUrl);
        }
    }

    public function render()
    {
        return view('livewire.auth.reset-password-page');
    }
}
