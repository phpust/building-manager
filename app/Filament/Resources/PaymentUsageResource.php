<?php

namespace App\Filament\Resources;

use App\Filament\Resources\PaymentUsageResource\Pages;
use App\Filament\Resources\PaymentUsageResource\RelationManagers;
use App\Models\PaymentUsage;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Filament\Support\RawJs;

class PaymentUsageResource extends Resource
{
    protected static ?string $model = PaymentUsage::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    
    // protected static bool $shouldRegisterNavigation = false;

    public static function getModelLabel(): string
    {
        return 'محل مصرف پرداخت';
    }

    public static function getPluralModelLabel(): string
    {
        return 'محل مصرف پرداختی ها';
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Select::make('payment_id')
                    ->relationship('payment', 'id')
                    ->label('پرداخت')
                    ->required(),

                Forms\Components\Select::make('payable_type')
                    ->label('نوع مورد مصرف')
                    ->options([
                        'App\Models\Deposit' => 'ودیعه',
                        'App\Models\UnitExpenseDetail' => 'جزئیات هزینه واحد',
                    ])
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn (callable $set, $state) => $set('payable_id', null)),

                Forms\Components\Select::make('payable_id')
                    ->label('مورد مصرف')
                    ->required()
                    ->reactive()
                    ->afterStateUpdated(fn () => null)
                    ->options(function (callable $get) {
                        $type = $get('payable_type');

                        return match ($type) {
                            'App\Models\Deposit' => \App\Models\Deposit::pluck('id', 'id')->toArray(),
                            'App\Models\UnitExpenseDetail' => \App\Models\UnitExpenseDetail::pluck('id', 'id')->toArray(),
                            default => [],
                        };
                    }),

                Forms\Components\TextInput::make('amount_used')
                    ->label('مبلغ مصرف شده')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters([','])
                    ->required(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('payment.id')->label('پرداخت')->sortable(),
                Tables\Columns\TextColumn::make('payable_type')->label('نوع مورد مصرف')->sortable(),
                Tables\Columns\TextColumn::make('payable_id')->label('مورد مصرف')->sortable(),
                Tables\Columns\TextColumn::make('amount_used')->label('مبلغ مصرف شده')->sortable()->money('IRR', true, 'fa'),
                Tables\Columns\TextColumn::make('created_at')->label('تاریخ ثبت')->jalaliDate()->sortable(),
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
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListPaymentUsages::route('/'),
            'create' => Pages\CreatePaymentUsage::route('/create'),
            'edit' => Pages\EditPaymentUsage::route('/{record}/edit'),
        ];
    }
}
