@php
$state = $getState() ? true : false;
@endphp

<div class="px-4 py-3 filament-tables-text-column">
    <label class="inline-flex items-center space-x-3 rtl:space-x-reverse" for="data.enable">
        <span role="switch" @class([
            'relative inline-flex shrink-0 h-6 w-11 border-2 border-transparent rounded-full cursor-pointer transition-colors ease-in-out duration-200 focus:outline-none focus:border-primary-500 focus:ring-2 focus:ring-primary-500 disabled:opacity-70 disabled:cursor-not-allowed filament-forms-toggle-component border-gray-300',
            'bg-primary-600' => $state,
            'bg-gray-200  dark:bg-white/10 ' => !$state,
        ]) type="button">
            <span @class([
                'pointer-events-none relative inline-block h-5 w-5 rounded-full bg-white shadow transform ring-0 ease-in-out transition duration-200',
                'translate-x-5 rtl:-translate-x-5' => $state,
                'translate-x-0' => !$state,
            ])>
                <span aria-hidden="true" @class([
                    'absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-100 ease-in duration-200',
                    'opacity-0 ease-out duration-100' => $state,
                    'opacity-100 ease-in duration-200' => !$state,
                ])>
                </span>

                <span aria-hidden="true" @class([
                    'absolute inset-0 h-full w-full flex items-center justify-center transition-opacity opacity-0 ease-out duration-100',
                    'opacity-100 ease-in duration-200' => $state,
                    'opacity-0 ease-out duration-100' => !$state,
                ])>
                </span>
            </span>
        </span>
    </label>
</div>
