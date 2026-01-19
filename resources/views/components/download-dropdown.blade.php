@props(['pdfId' => 'downloadPDF', 'csvId' => 'downloadCSV'])

<div class="btn-group me-2">
    <button type="button" class="btn downloadcsv dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
        <i class="fas fa-download"></i> Download
    </button>
    <ul class="dropdown-menu">
        <li>
            <a class="dropdown-item" href="#" id="{{ $pdfId }}">
                <i class="fas fa-file-pdf"></i> Download PDF
            </a>
        </li>
        <li>
            <a class="dropdown-item" href="#" id="{{ $csvId }}">
                <i class="fas fa-file-csv"></i> Download CSV
            </a>
        </li>
    </ul>
</div>
