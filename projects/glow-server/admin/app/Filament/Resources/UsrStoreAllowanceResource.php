<?php

namespace App\Filament\Resources;

use App\Constants\NavigationGroups;
use App\Filament\Authorizable;
use App\Filament\Resources\UsrStoreAllowanceResource\Pages;
use App\Models\Usr\UsrStoreAllowance;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use WonderPlanet\Domain\Currency\Constants\CurrencyConstants;

class UsrStoreAllowanceResource extends Resource
{
    use Authorizable;

    protected static ?string $model = UsrStoreAllowance::class;

    protected static ?string $navigationIcon = 'heroicon-o-document-text';

    protected static ?string $navigationGroup = NavigationGroups::CS->value;
    protected static ?string $modelLabel = '商品購入事前許可の手動登録';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('usr_user_id')
                    ->required()
                    ->label('ユーザーID')
                    ->hint('ユーザーIDを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\Select::make('os_platform')
                    ->options([
                        CurrencyConstants::OS_PLATFORM_IOS => CurrencyConstants::OS_PLATFORM_IOS,
                        CurrencyConstants::OS_PLATFORM_ANDROID => CurrencyConstants::OS_PLATFORM_ANDROID
                    ])
                    ->required()
                    ->label('OSプラットフォーム')
                    ->hint('OSプラットフォームを選択')
                    ->placeholder('OSプラットフォームを選択')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\Select::make('billing_platform')
                    ->options([
                        CurrencyConstants::PLATFORM_APPSTORE => CurrencyConstants::PLATFORM_APPSTORE,
                        CurrencyConstants::PLATFORM_GOOGLEPLAY => CurrencyConstants::PLATFORM_GOOGLEPLAY
                    ])
                    ->required()
                    ->label('課金プラットフォーム')
                    ->hint('課金プラットフォームを選択')
                    ->placeholder('課金プラットフォームを選択')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('device_id')
                    ->label('デバイスID')
                    ->hint('デバイスIDを入力(空欄可)')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('product_id')
                    ->required()
                    ->label('プロダクトID')
                    ->hint('プロダクトIDを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('mst_store_product_id')
                    ->required()
                    ->label('mst_store_product_id')
                    ->hint('mst_store_product_idを入力')
                    ->columnSpanFull()
                    ->reactive(),
                Forms\Components\TextInput::make('product_sub_id')
                    ->required()
                    ->label('product_sub_id')
                    ->hint('product_sub_idを入力')
                    ->columnSpanFull()
                    ->reactive()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('usr_user_id')
                    ->label('ユーザID')
                    ->alignCenter()
                    ->searchable(),
                Tables\Columns\TextColumn::make('os_platform')
                    ->label('OSプラットフォーム')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('billing_platform')
                    ->label('課金プラットフォーム')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('device_id')
                    ->label('デバイスID')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('product_id')
                    ->label('プロダクトID')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('mst_store_product_id')
                    ->label('mst_store_product_id')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('product_sub_id')
                    ->label('product_sub_id')
                    ->alignCenter(),
                Tables\Columns\TextColumn::make('created_at')
                    ->label('追加日')
                    ->alignCenter()
                    ->sortable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->label('更新日')
                    ->alignCenter()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                //
            ])
            ->bulkActions([
                Tables\Actions\DeleteBulkAction::make(),
            ])
            ->searchPlaceholder('ユーザーIDで検索');
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
            'index' => Pages\ListUsrStoreAllowances::route('/'),
            'create' => Pages\CreateUsrStoreAllowance::route('/create'),
        ];
    }
}
