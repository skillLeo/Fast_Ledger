{{-- Employee Details Form (Preview Mode) --}}
<div id="employee-details-form" style="display: none;">
    {{-- Set preview mode --}}
    @php $isPreview = true; @endphp

    <form id="employee-preview-form" data-employee-id="" data-update-url="">
        @csrf
        @method('PUT')
        {{-- Tab Navigation --}}
        @include('admin.file_opening_book._partials._employees._employee-tabs-nav')

        {{-- Tab Content --}}
        <div class="tab-content-wrapper">
            @include('admin.employees.partials.personal-tab', ['isPreview' => true])
            @include('admin.employees.partials.employment-tab', ['isPreview' => true])
            @include('admin.employees.partials.nic-tab', ['isPreview' => true])
            @include('admin.employees.partials.hmrc-tab', ['isPreview' => true])
            @include('admin.employees.partials.contacts-tab', ['isPreview' => true])
            @include('admin.employees.partials.terms-tab', ['isPreview' => true])
            @include('admin.employees.partials.payment-tab', ['isPreview' => true])
        </div>
    </form>
</div>
