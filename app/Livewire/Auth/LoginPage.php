<?php

namespace App\Livewire\Auth;

use Livewire\Component;
use Livewire\Attributes\Title;
use Livewire\Attributes\Validate;

#[Title('Login')]
class LoginPage extends Component
{
    #[Validate('required|min:3')]
    public $email;

    #[Validate('required|min:3')]
    public $password;

    public function save()
    {
        $this->validate();

        if (!auth()->attempt(['email' => $this->email, 'password' => $this->password,])) {
            session()->flash('email', 'Invalid credentials');
            return;
        }

        return redirect()->intended();
    }

    public function render()
    {
        return view('livewire.auth.login-page');
    }
}
