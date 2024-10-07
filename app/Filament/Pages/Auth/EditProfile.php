<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use Dotenv\Exception\ValidationException;
use Filament\Forms\Form;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Component;
use Filament\Forms\Get;
use Filament\Pages\Auth\EditProfile as BaseAuth;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException as ValidationValidationException;

class EditProfile extends BaseAuth
{
    public function form(Form $form): Form
    {
        return $form
        ->schema([
            FileUpload::make('avatar')
            ->reorderable()
            ->appendFiles()
            ->avatar()
            ->disk('public')
            ->directory('avatar'),
            TextInput::make('fullname')
            ->label('Full Name')
            ->required()
            ->maxLength(255),
            $this->getNameFormComponent(),
            $this->getEmailFormComponent(),
            DateTimePicker::make('date_of_birth')
                ->label('Date of Birth')
                ->native(false)
                ->displayFormat('d/m/Y')
                ->minDate(now()->subYears(90))
                ->maxDate(now()->subYears(17))
                ->required(),
            $this->getPasswordFormComponent(),
            $this->getPasswordConfirmationFormComponent(),
        ]);
    }

    protected function getPasswordFormComponent(): Component
    {
        return parent::getPasswordFormComponent()
        ->revealable(false);
    }


    protected function getNameFormComponent(): Component
    {
        return parent::getNameFormComponent()
        ->label('Username');
    }

    protected function getEmailFormComponent(): Component
    {
        return parent::getEmailFormComponent()
        ->label('Email Address');
    }
}