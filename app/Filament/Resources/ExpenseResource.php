<?php

namespace App\Filament\Resources;

use App\Filament\Resources\ExpenseResource\Pages;
use App\Filament\Resources\ExpenseResource\RelationManagers;
use App\Models\Expense;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use Mokhosh\FilamentJalali\Forms\Components\JalaliDatePicker;
use Illuminate\Support\Facades\Storage;
use Filament\Support\RawJs;
use Morilog\Jalali\Jalalian;

class ExpenseResource extends Resource
{
    protected static ?string $model = Expense::class;

    protected static ?string $navigationIcon = 'heroicon-o-currency-dollar';


    public static function getModelLabel(): string
    {
        return 'هزینه ';
    }

    public static function getPluralModelLabel(): string
    {
        return 'هزینه ها';
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
                Forms\Components\TextInput::make('title')
                    ->label('عنوان هزینه')
                    ->required()
                    ->maxLength(255),

                Forms\Components\TextInput::make('total_amount')
                    ->label('مبلغ کل')
                    ->mask(RawJs::make('$money($input)'))
                    ->stripCharacters([','])
                    ->required(),

                Forms\Components\Select::make('payer_type')
                    ->label('پرداخت کننده')
                    ->options([
                        'owner' => 'مالک',
                        'tenant' => 'مستاجر',
                    ])
                    ->required(),

                Forms\Components\DatePicker::make('date_from')
                    ->label('تاریخ ')
                    ->nullable()->jalali(),


                Forms\Components\Select::make('unit_ids')
                    ->label('واحدهای درگیر')
                    ->options(function () {
                        $units = \App\Models\Unit::pluck('owner_name', 'id')->toArray();
                        return ['all' => 'همه واحدها'] + $units;
                    })
                    ->multiple()
                    ->preload()
                    ->reactive()
                    ->afterStateUpdated(function (callable $set, $state) {
                        if (in_array('all', $state)) {
                            $allIds = \App\Models\Unit::pluck('id')->toArray();
                            $set('unit_ids', $allIds);
                        }
                    })
                    ->required(),

                Forms\Components\FileUpload::make('attachment')
                    ->label('فایل فاکتور')
                    ->directory('expenses/attachments')
                    ->disk('public')
                    ->nullable()
                    ->imageEditor()
                    ->acceptedFileTypes(['application/pdf', 'image/jpeg', 'image/png']),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')->label('شناسه')->sortable(),
                Tables\Columns\TextColumn::make('financial_year')->label('سال مالی')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('title')->label('عنوان')->sortable()->searchable(),
                Tables\Columns\TextColumn::make('total_amount')->label('مبلغ کل')->sortable()->money('IRR', true, 'fa'),
                Tables\Columns\TextColumn::make('payer_type')->label('پرداخت کننده')->sortable(),
                Tables\Columns\TextColumn::make('date_from')->label('تاریخ ')->jalaliDate()->sortable(),
                Tables\Columns\TextColumn::make('attachment')
                    ->label('فایل فاکتور')
                    ->state(function ($record) {
                        return $record->attachment ?? 'empty'; // اطمینان از اجرای تابع حتی در null
                    })
                    ->formatStateUsing(function ($state) {
                        if ($state == 'empty') {
                            return '<div class="text-center w-full">-</div>';
                        }

                        $url = Storage::disk('public')->url($state); // تولید لینک کامل
                        return "<a href='{$url}' target='_blank'>
                                    <span class='fi-ta-text-item-label text-sm leading-6 text-gray-950 dark:text-white'>
                                        مشاهده
                                    </span>
                                </a>";
                    })
                    ->html()
                    ->alignCenter()

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
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListExpenses::route('/'),
            'create' => Pages\CreateExpense::route('/create'),
            'edit' => Pages\EditExpense::route('/{record}/edit'),
        ];
    }
}
