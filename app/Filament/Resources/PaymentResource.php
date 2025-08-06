<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Filament\Resources\PaymentResource\Widgets\PaymentSummary;
use App\Models\Payment;
use App\Models\Setting;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;
use Morilog\Jalali\Jalalian;

class PaymentResource extends Resource
{
    protected static ?string $model = Payment::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function getModelLabel(): string
    {
        return 'پرداخت ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'پرداختی ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Hidden::make('financial_year')->default(fn () => Setting::financialYear()),
                Forms\Components\Select::make('unit_id')
                    ->label('واحد')
                    ->options(function () {
                        return \App\Models\Unit::all()->mapWithKeys(fn($unit) => [
                            (string)$unit->id => 'واحد ' . $unit->number
                        ])->toArray();
                    })
                    ->searchable()
                    ->preload()
                    ->afterStateUpdated(function (callable $set) {
                        $set('user_id', null);
                    })
                    ->required(),
                Forms\Components\Select::make('user_id')
                    ->label('پرداخت کننده')
                    ->options(function (callable $get) {
                        $unitId = $get('unit_id');
                        $fy     = $get('financial_year') ?? Setting::financialYear();
                        $userId = $get('user_id');

                        $options = [];

                        if ($unitId) {
                            $options = \App\Models\Unit::find($unitId)
                                ?->eligibleUsersForFinancialYear($fy)
                                ?->toArray() ?? [];
                        }

                        if ($userId && ! isset($options[$userId])) {
                            $options[$userId] = \App\Models\User::find($userId)?->name ?? $userId;
                        }

                        if (! $unitId) return [];

                        return \App\Models\Unit::find($unitId)?->eligibleUsersForFinancialYear($fy)?->toArray() ?? [];
                    })
                    ->searchable()
                    ->preload()
                    ->reactive()
                    ->required(),
                Forms\Components\Select::make('payer_type')
                    ->label('پرداخت کننده')
                    ->options([
                        'owner' => 'مالک',
                        'tenant' => 'مستاجر',
                    ])
                    ->required(),
                Forms\Components\TextInput::make('amount')
                    ->label('مبلغ')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters([','])
                    ->required(),
                Forms\Components\DatePicker::make('paid_at')
                    ->label('تاریخ پرداخت')
                    ->jalali()
                    ->required(),
                Forms\Components\Textarea::make('description')
                    ->label('توضیحات')
                    ->nullable(),
                Forms\Components\Placeholder::make('remaining_amount')
                    ->hiddenOn('create')
                    ->label('مبلغ باقی مانده')
                    ->content(fn ($record) => number_format($record->remaining_amount) . ' ریال'),

            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('ID')->sortable()->label('شناسه'),
                Tables\Columns\TextColumn::make('financial_year')->label('سال مالی')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('unit.number')->label('شماره واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('user.name')->label('پرداخت کننده')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('amount')->label('مبلغ')->sortable()->money('IRR', true, 'fa'),
                Tables\Columns\TextColumn::make('paid_at')->label('تاریخ پرداخت')->jalaliDate()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('توضیحات')->limit(50)->searchable(),
            ])
            ->filters([
                //
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])->defaultSort('paid_at', 'desc');
    }

    public static function getRelations(): array
    {
        return [
            RelationManagers\UnitExpenseDetailsRelationManager::class,
            RelationManagers\DepositRelationManager::class,
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPayments::route('/'),
            'create' => Pages\CreatePayment::route('/create'),
            'edit' => Pages\EditPayment::route('/{record}/edit'),
        ];
    }
}
