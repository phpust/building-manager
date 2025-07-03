<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentResource\Pages;
use App\Filament\Resources\PaymentResource\RelationManagers;
use App\Models\Payment;
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
                Forms\Components\Select::make('financial_year')
                    ->label('سال مالی')
                    ->options(function () {
                        $currentYear = Jalalian::now()->getYear();
                        $years = [];
                        for ($i = $currentYear - 10; $i <= $currentYear + 1; $i++) {
                            $years[$i] = $i;
                        }
                        return $years;
                    })
                    ->default(Jalalian::now()->getYear())
                    ->required(),
                Forms\Components\Select::make('unit_id')
                    ->label('واحد')
                    ->searchable()
                    ->preload()
                    ->relationship('unit', 'owner_name')
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
                Tables\Columns\TextColumn::make('unit.owner_name')->label('مالک واحد')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('payer_type')->label('پرداخت کننده')->sortable(),
                Tables\Columns\TextColumn::make('amount')->label('مبلغ')->sortable()->money('IRR', true, 'fa'),
                Tables\Columns\TextColumn::make('paid_at')->label('تاریخ پرداخت')->jalaliDate()->sortable(),
                Tables\Columns\TextColumn::make('description')->label('توضیحات')->limit(50),
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
            ]);
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
