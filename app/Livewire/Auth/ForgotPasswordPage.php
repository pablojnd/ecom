<?php

namespace App\Livewire\Auth;

use Illuminate\Support\Facades\Password;
use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Forgot Password')]
class ForgotPasswordPage extends Component
{
    public $email;

    const RESET_LINK_SENT_MESSAGE = 'Reset link sent to your email. Please check your inbox.';

    /**
     * Definición de reglas de validación.
     */
    protected function rules()
    {
        return [
            'email' => 'required|email|exists:users,email|max:255',
        ];
    }

    /**
     * Método para enviar el enlace de restablecimiento de contraseña.
     */
    public function save()
    {
        $this->validate();

        $status = Password::sendResetLink(['email' => $this->email]);

        if ($status === Password::RESET_LINK_SENT) {
            session()->flash('success', self::RESET_LINK_SENT_MESSAGE);
            $this->reset('email');
        } else {
            session()->flash('error', 'An error occurred. Please try again.');
        }
    }

    /**
     * Renderizar la vista.
     */
    public function render()
    {
        return view('livewire.auth.forgot-password-page');
    }
}
