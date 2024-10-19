<?php

namespace App\Filament\Resources;

use App\Filament\Resources\EmployeeResource\Pages;
use App\Filament\Resources\EmployeeResource\RelationManagers;
use App\Models\Division;
use App\Models\Employee;
use App\Models\User;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Forms\Get;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Container\Attributes\DB;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use NunoMaduro\Collision\Adapters\Phpunit\State;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Route;

class EmployeeResource extends Resource
{
    protected static ?string $model = Employee::class;

    protected static ?string $navigationIcon = 'heroicon-o-users';

    protected static ?string $navigationGroup = 'Employee Management';

    protected static ?int $navigationSort = 1;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Employee Information')
                ->description('Add the employee information here!')
                ->schema([
                    Forms\Components\TextInput::make('name')
                        ->label('Full Name')
                        ->default('Select Username First!')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('username')
                        ->label('Username')
                        ->options(User::query()->pluck('name', 'name'))
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                            $set('avatar', (string)User::query()->where('name', $state)->pluck('avatar')[0] ?? $state . " -> Invalid Plug") &&
                            $set('email', (string)User::query()->where('name', $state)->pluck('avatar')[0] ?? $state . " -> Invalid Plug") &&
                            $set('name', User::query()->where('name', $state)->pluck('fullname')[0] ?? $state . " -> Invalid Plug") &&
                            $set('date_of_birth', User::query()->where('name', $state)->pluck('date_of_birth')[0] ?? $state . " -> Invalid Plug"))
                        ->distinct()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->native(false)
                        ->searchable(),
                    Forms\Components\TextInput::make('email')
                        ->email()
                        ->default('Select Username First!')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\DatePicker::make('date_of_birth')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                ]),
                Forms\Components\Section::make('Division')
                ->description('Add the employee division information here!')
                ->schema([
                    Forms\Components\TextInput::make('division_id')
                        ->label('Division ID')
                        ->default('Select Division First!')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                    Forms\Components\Select::make('division_name')
                        ->label('Division Name')
                        ->options(Division::query()->pluck('name', 'name'))
                        ->preload()
                        ->required()
                        ->reactive()
                        ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                            $set('division_id', Division::query()->where('name', $state)?->pluck('id')[0] ?? $state . " -> Invalid Plug") &&
                            $set('division_category', Division::query()->where('name', $state)?->pluck('category')[0] ?? $state . " -> Invalid Plug"))
                        ->distinct()
                        ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                        ->native(false)
                        ->searchable(),
                    Forms\Components\TextInput::make('division_category')
                        ->label('Division Category')
                        ->default('Select Division First!')
                        ->disabled()
                        ->dehydrated()
                        ->required(),
                ]),
                Forms\Components\Section::make('Job Information')
                ->description('Add the employee job information here!')
                ->schema([
                    Forms\Components\DatePicker::make('date_hired')
                        ->label('Date Hired')
                        ->default(now())
                        ->readonly()
                        ->required(),
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Full Name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('username')
                    ->searchable(),
                Tables\Columns\TextColumn::make('email')
                    ->searchable(),
                Tables\Columns\TextColumn::make('division_name')
                    ->label('Division')
                    ->searchable(),
                Tables\Columns\TextColumn::make('date_of_birth')
                    ->label('Date of Birth')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('date_hired')
                    ->label('Date Hired')
                    ->date()
                    ->sortable(),
                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\ViewAction::make()
                    ->successRedirectUrl(env('APP_URL').'/admin/employees'),
                Tables\Actions\EditAction::make()
                    ->successRedirectUrl(env('APP_URL').'/admin/employees'),
                Tables\Actions\DeleteAction::make()
                    ->successRedirectUrl(env('APP_URL').'/admin/employees'),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListEmployees::route('/'),
            'create' => Pages\CreateEmployee::route('/create'),
            'view' => Pages\ViewEmployee::route('/{record}'),
            'edit' => Pages\EditEmployee::route('/{record}/edit'),
        ];
    }
}
