<?php

namespace OpenSynergic\Plugins\Filament\Pages;

use ZipArchive;
use Filament\Tables;
use ZanySoft\Zip\Zip;
use Filament\Pages\Page;
use Filament\Pages\Actions\Action;
use Filament\Tables\Filters\Layout;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Str;
use Illuminate\Filesystem\Filesystem;
use Illuminate\Support\Facades\Storage;
use Filament\Forms\Components\FileUpload;
use Filament\Tables\Filters\TernaryFilter;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\Log;
use OpenSynergic\Plugins\Models\Plugin as PluginModel;
use OpenSynergic\Plugins\Facades\Plugin as FacadesPlugin;

class Plugins extends Page implements Tables\Contracts\HasTable
{
    use Tables\Concerns\InteractsWithTable;

    protected static ?string $navigationIcon = 'heroicon-o-view-grid-add';

    protected static string $view = 'plugins-manager::filament.pages.plugin';

    protected static ?string $navigationGroup = 'Plugins';

    public $form;

    protected function getTitle(): string
    {
        return __('plugins-manager::page.title');
    }


    protected static function getNavigationGroup(): ?string
    {
        return __('plugins-manager::page.navigationGroup');
    }


    protected static function getNavigationBadge(): ?string
    {
        return PluginModel::count();
    }

    protected function getTableQuery(): Builder
    {
        return PluginModel::query();
    }

    protected function getActions(): array | View | null
    {
        return [
            Action::make('upload')
                ->button()
                ->icon('heroicon-o-upload')
                ->label('Upload Plugin')
                ->action(function (array $data) {
                    $fileName = $data['attachment'];
                    $filePath = Storage::disk('local')->path($fileName);
                    try {
                        $zip = Zip::open($filePath);
                        if (!$zip->has('index.php', ZipArchive::FL_NODIR)) {
                            throw new \Exception(__('plugins-manager::page.exceptions.no_index_file'));
                        };

                        $zip->extract(config('plugins-manager.path'));
                        $zip->close();

                        // Delete the temporary directory and all the files inside it
                        Storage::disk('local')->delete($fileName);
                    } catch (\Exception $th) {
                        $this->notify('danger', $th->getMessage());
                        return;
                    }

                    $this->notify('success', __('plugins-manager::page.success.install'));
                    // reload page
                    return redirect(request()->header('Referer'));
                })
                ->outlined()
                ->form([
                    FileUpload::make('attachment')
                        ->disk('local')
                        ->disableLabel()
                        ->directory('plugin-tmp')
                        ->acceptedFileTypes(['application/zip'])
                ])
        ];
    }

    protected function getFormStatePath(): string
    {
        return 'form';
    }

    protected function getTableColumns(): array
    {
        return [
            Tables\Columns\TextColumn::make('name')
                ->extraAttributes(['class' => 'font-bold'])
                ->searchable()
                ->sortable(),
            Tables\Columns\TextColumn::make('description'),
            Tables\Columns\ViewColumn::make('enabled')
                ->view('plugins-manager::filament.tables.columns.switch-column')
                ->action(function (PluginModel $record) {

                    // init a plugin to check if there's no error on it
                    try {
                        $plugin = $record->getPlugin();

                        FacadesPlugin::init($plugin, false);

                        $record->enabled = !$record->enabled;
                    } catch (\Throwable $th) {
                        Log::error($th);
                        $this->notify('danger', __('plugins-manager::page.exceptions.enabled_plugin_failed'));
                        return;
                    }

                    $this->notify('success', __('plugins-manager::page.toggle_plugin', [
                        'pluginName' => $record->name,
                        'status' => $record->enabled ? 'enabled' : 'disabled'
                    ]));
                }),
        ];
    }

    protected function getTableFilters(): array
    {
        return [
            TernaryFilter::make('enabled')
        ];
    }

    // protected function getTableFiltersLayout(): ?string
    // {
    //     return Layout::AboveContent;
    // }

    protected function getTableActions(): array
    {
        return [
            Tables\Actions\Action::make('uninstall')
                ->label(__('Uninstall'))
                ->modalHeading(__('Uninstall Plugin'))
                ->button()
                ->icon('heroicon-o-trash')
                ->color('danger')
                ->requiresConfirmation()
                ->action(function (PluginModel $record) {
                    try {
                        if (!Str::startsWith($record->path, config('plugins-manager.path'))) {
                            throw new \Exception(__('plugins-manager::page.exceptions.path_not_correct'));
                        }

                        $fileSystem = app(Filesystem::class);
                        if (!$fileSystem->deleteDirectory($record->path)) {
                            throw new \Exception(__('plugins-manager::page.exceptions.plugin_not_uninstalled'));
                        };
                        $record->enabled = false;
                    } catch (\Throwable $th) {
                        $this->notify('danger', $th->getMessage());
                        return;
                    }

                    $this->notify('success', __('plugins-manager::page.success.uninstall'));
                    return redirect(request()->header('Referer'));
                })
        ];
    }
}
