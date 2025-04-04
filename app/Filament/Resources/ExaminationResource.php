<?php

namespace App\Filament\Resources;

use App\Filament\Forms\Components\ServerFileSelect;
use App\Filament\Resources\ExaminationResource\Pages\CreateExamination;
use App\Filament\Resources\ExaminationResource\Pages\EditExamination;
use App\Filament\Resources\ExaminationResource\Pages\ListExaminations;
use App\Filament\Resources\ExaminationResource\Pages\ViewExamination;
use App\Models\Examination;
use App\Models\User;
use BezhanSalleh\FilamentShield\Contracts\HasShieldPermissions;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Auth;

class ExaminationResource extends Resource implements HasShieldPermissions
{
    protected static ?string $model = Examination::class;
    protected static ?string $navigationIcon = 'heroicon-o-document-magnifying-glass';
    protected static ?string $navigationLabel = 'Hasil Pemeriksaan';
    protected static ?string $modelLabel = 'Hasil Pemeriksaan';

    public static function getEloquentQuery(): Builder
    {
        if(Auth::user()->hasRole('pasien')) {
            return parent::getEloquentQuery()->where('pasien_id', Auth::user()->id);
        }
        return parent::getEloquentQuery();
    }

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Informasi Pemeriksaan')
                    ->columns(2)
                    ->schema([
                        Forms\Components\Select::make('pasien_id')
                            ->options(User::role('pasien')->pluck('name', 'id'))
                            ->label('Pasien')
                            ->searchable()
                            ->preload()
                            ->required(),

                        Forms\Components\DateTimePicker::make('examined_at')
                            ->label('Tanggal Pemeriksaan')
                            ->required()
                            ->default(now()),

                        ServerFileSelect::make('video_raw_path')
                            ->label('Video Asli')
                            ->required()
                            ->directory('videos/raw')
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/mpeg'])
                            ->enablePreview()
                            ->previewType('video')
                            ->helperText('Pilih file video pemeriksaan asli yang tersimpan di server'),

                        ServerFileSelect::make('video_ai_path')
                            ->label('Video Hasil Pemrosesan AI')
                            ->required()
                            ->directory('videos/ai')
                            ->acceptedFileTypes(['video/mp4', 'video/avi', 'video/mov', 'video/mpeg'])
                            ->enablePreview()
                            ->previewType('video')
                            ->helperText('Pilih file video hasil pemrosesan AI yang tersimpan di server'),
                    ]),

                Forms\Components\Section::make('Diagnosa')
                    ->schema([
                        Forms\Components\Textarea::make('diagnosa')
                            ->label('Hasil Diagnosa')
                            ->required()
                            ->rows(5)
                    ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('pasien.name')
                    ->label('Nama Pasien')
                    ->visible(fn() => Auth::user()->hasRole('dokter'))
                    ->searchable(),

                Tables\Columns\TextColumn::make('examined_at')
                    ->label('Tanggal Pemeriksaan')
                    ->dateTime()
                    ->sortable(),

                Tables\Columns\TextColumn::make('diagnosa')
                    ->label('Diagnosa')
                    ->limit(50)
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('pasien')
                    ->relationship('pasien', 'name')
                    ->label('Filter Pasien')
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
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
            'index' => ListExaminations::route('/'),
            'create' => CreateExamination::route('/create'),
            'view' => ViewExamination::route('/{record}'),
            'edit' => EditExamination::route('/{record}/edit'),
        ];
    }

    public static function getPermissionPrefixes(): array
    {
        return [
            'view',
            'view_any',
            'create',
            'update',
            'delete',
            'delete_any',
        ];
    }
}
