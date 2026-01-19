@extends('admin.layout.app')

@section('content')
<div class="container-fluid">
    <div class="row mb-3 align-items-center">
        <div class="col">
            <h4 class="mb-0">Customize Invoice Template</h4>
        </div>
        <div class="col-auto">
            <select id="templateSelector" class="form-select" style="min-width:220px;">
                <option value="">Default Template</option>
                @foreach($templates as $t)
                    <option value="{{ $t->id }}" {{ (isset($template) && $template && $template->id === $t->id) ? 'selected' : '' }}>
                        {{ $t->name }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="row">
        <!-- Canvas area -->
        <div class="col-md-9">
            <div class="card">
                <div class="card-body text-center">
                    <div style="display:flex; justify-content:center;">
                        <canvas id="invoiceCanvas" style="border:1px solid #ddd;"></canvas>
                    </div>
                    <p class="mt-2 text-muted">Canvas represents an A4 page. Drag/resize text & logo.</p>
                </div>
            </div>
        </div>

        <!-- Controls -->
        <div class="col-md-3">
            <div class="card p-3">
                <label class="form-label">Template name</label>
                <input id="templateName" class="form-control mb-2" value="{{ $template->name ?? 'My Template' }}">

                <label class="form-label">Select element</label>
                <select id="elementSelect" class="form-select mb-2">
                    <option value="title">Title</option>
                    <option value="logo">Logo</option>
                    <option value="client-info">Client Info</option>
                    <option value="invoice-meta">Invoice Meta</option>
                    <option value="company-info">Company Info</option>
                    <option value="footer-text">Footer</option>
                </select>

                <div id="textControls" class="mb-2">
                    <label class="form-label">Font family</label>
                    <select id="fontFamily" class="form-select mb-1">
                        <option>Arial</option><option>Times New Roman</option><option>Verdana</option>
                    </select>

                    <label class="form-label">Font size</label>
                    <input id="fontSize" type="number" class="form-control mb-1" value="14">

                    <label class="form-label">Color</label>
                    <input id="fontColor" type="color" class="form-control form-control-color mb-2" value="#000000">

                    <div class="d-grid gap-2 mb-2">
                        <button id="boldBtn" class="btn btn-outline-secondary btn-sm">Bold</button>
                        <button id="italicBtn" class="btn btn-outline-secondary btn-sm">Italic</button>
                    </div>
                </div>

                <label class="form-label">Logo upload</label>
                <input id="logoUpload" type="file" accept="image/*" class="form-control mb-2">

                <div class="d-grid gap-2">
                    <button id="saveTemplate" class="btn btn-primary">Save Template</button>
                    <button id="applyPreview" class="btn btn-outline-secondary">Open Preview with Template</button>
                </div>
            </div>
        </div>
    </div>

    <textarea id="invoiceData" style="display:none;">@json($validated)</textarea>
</div>
@endsection

@section('scripts')
<!-- Fabric.js + Axios -->
<script src="https://cdnjs.cloudflare.com/ajax/libs/fabric.js/5.2.4/fabric.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/axios/dist/axios.min.js"></script>

<script>
(function(){
    // mm -> px for 96 DPI
    const mmToPx = mm => Math.round(mm * 3.7795275591);
    const canvasW = mmToPx(210);
    const canvasH = mmToPx(297);

    const canvas = new fabric.Canvas('invoiceCanvas', { selection: false, preserveObjectStacking: true });
    canvas.setWidth(canvasW);
    canvas.setHeight(canvasH);

    // scale canvas display (visual only) to fit container width
    const containerWidth = Math.min(window.innerWidth * 0.72, 900);
    const scale = containerWidth / canvasW;
    const canvasEl = document.getElementById('invoiceCanvas');
    canvasEl.style.width = canvasW * scale + 'px';
    canvasEl.style.height = canvasH * scale + 'px';

    const elements = {}; // key -> fabric object
    const invoiceData = JSON.parse(document.getElementById('invoiceData').value || '{}');

    // Helper: create text
    function createTextElement(key, text, left, top, opts = {}) {
        const tb = new fabric.Textbox(text, Object.assign({
            left, top, fontSize: opts.fontSize || 16, fontFamily: opts.fontFamily || 'Arial',
            fill: opts.fill || '#000', editable: false, hasControls: true
        }, opts));
        tb.set('elementKey', key);
        canvas.add(tb);
        elements[key] = tb;
        return tb;
    }

    // Helper: create logo
    function createLogoElement(key, url, left, top) {
        if (url) {
            fabric.Image.fromURL(url, function(img){
                img.set({ left, top, selectable: true, elementKey: key });
                // constrain default width
                if (img.width > 220) {
                    const s = 180 / img.width;
                    img.scale(s);
                }
                canvas.add(img);
                elements[key] = img;
            }, { crossOrigin: 'anonymous' });
        } else {
            const rect = new fabric.Rect({ left, top, width: 160, height: 60, rx: 6, fill: '#1e3a8a', elementKey: key });
            canvas.add(rect);
            elements[key] = rect;
        }
    }

    // Create base elements with reasonable positions
    createTextElement('title', invoiceData.title ?? 'TAX INVOICE', 40, 30, { fontSize: 36 });
    const clientText = [
        invoiceData.client_name ?? 'ABC Company',
        invoiceData.client_address ?? 'ABC Road Slough',
        invoiceData.client_city ?? 'United Kingdom',
        'T: ' + (invoiceData.client_phone ?? '07456764343'),
        'E: ' + (invoiceData.client_email ?? 'abc@company.co.uk'),
        'VAT No: ' + (invoiceData.client_vat ?? '15674537')
    ].join('\n');
    createTextElement('client-info', clientText, 40, 110, { fontSize: 12, width: 260 });

    const metaText = [
        'Invoice Date: ' + (invoiceData.Transaction_Date ?? ''),
        'Inv Due Date: ' + (invoiceData.Inv_Due_Date ?? ''),
        'Invoice No: ' + (invoiceData.invoice_no ?? ''),
        'Invoice Ref: ' + (invoiceData.invoice_ref ?? '')
    ].join('\n');
    createTextElement('invoice-meta', metaText, 340, 110, { fontSize: 12, width: 220 });

    const companyText = [
        'Energy Saviour Ltd',
        'First line of address',
        'Second line of address',
        'Town, County',
        'Postcode',
        'T: 07673767623',
        'E: office@energysaviourltd.co.uk',
        'VAT No: 157676554'
    ].join('\n');
    createTextElement('company-info', companyText, 560, 110, { fontSize: 12, width: 240 });

    createTextElement('footer-text', 'Company Registration No: 76767554   Registered Office: Unit 30, Business Village...', 40, canvasH - 90, { fontSize: 11, width: canvasW - 80 });

    @if(isset($template) && $template && $template->logo_path)
        createLogoElement('logo', '{{ Storage::url($template->logo_path) }}', canvasW - 220, 30);
    @else
        createLogoElement('logo', null, canvasW - 220, 30);
    @endif

    // Controls binding
    const elementSelect = document.getElementById('elementSelect');
    const fontFamily = document.getElementById('fontFamily');
    const fontSize = document.getElementById('fontSize');
    const fontColor = document.getElementById('fontColor');
    const boldBtn = document.getElementById('boldBtn');
    const italicBtn = document.getElementById('italicBtn');

    function refreshControlsForObj(obj) {
        if (!obj) return;
        if (obj.type === 'textbox' || obj.type === 'text') {
            fontFamily.value = obj.fontFamily || 'Arial';
            fontSize.value = obj.fontSize || 14;
            fontColor.value = rgbToHex(obj.fill || '#000000');
        }
    }

    elementSelect.addEventListener('change', function(){
        const key = this.value;
        const obj = elements[key];
        if (obj) {
            canvas.setActiveObject(obj);
            refreshControlsForObj(obj);
        }
    });

    canvas.on('selection:created', function(e){ refreshControlsForObj(e.target); if (e.target.elementKey) elementSelect.value = e.target.elementKey; });
    canvas.on('selection:updated', function(e){ refreshControlsForObj(e.target); if (e.target.elementKey) elementSelect.value = e.target.elementKey; });

    fontFamily.addEventListener('change', function(){ const o = canvas.getActiveObject(); if(!o) return; o.set('fontFamily', this.value); canvas.requestRenderAll(); });
    fontSize.addEventListener('change', function(){ const o = canvas.getActiveObject(); if(!o) return; o.set('fontSize', parseInt(this.value)||12); canvas.requestRenderAll(); });
    fontColor.addEventListener('change', function(){ const o = canvas.getActiveObject(); if(!o) return; o.set('fill', this.value); canvas.requestRenderAll(); });
    boldBtn.addEventListener('click', function(){ const o = canvas.getActiveObject(); if(!o) return; o.set('fontWeight', o.fontWeight==='bold'?'normal':'bold'); canvas.requestRenderAll(); });
    italicBtn.addEventListener('click', function(){ const o = canvas.getActiveObject(); if(!o) return; o.set('fontStyle', o.fontStyle==='italic'?'normal':'italic'); canvas.requestRenderAll(); });

    // Logo upload
    document.getElementById('logoUpload').addEventListener('change', function(e){
        const file = this.files[0];
        if (!file) return alert('Select an image.');
        const fd = new FormData();
        fd.append('logo', file);

        axios.post('{{ route("invoicetemplates.uploadLogo") }}', fd, { headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}', 'Content-Type': 'multipart/form-data'} })
            .then(res => {
                if (res.data.success) {
                    const url = res.data.url;
                    // remove existing logo object:
                    if (elements['logo']) { canvas.remove(elements['logo']); delete elements['logo']; }
                    createLogoElement('logo', url, canvasW - 220, 30);
                    alert('Logo uploaded.');
                }
            }).catch(err => {
                console.error(err); alert('Logo upload failed.');
            });
    });

    // Save template
    document.getElementById('saveTemplate').addEventListener('click', function(){
        const out = { elements: {} };
        let logo_path = null;

        for (const key in elements) {
            const obj = elements[key];
            if (!obj) continue;
            const left = Math.round(obj.left || 0);
            const top = Math.round(obj.top || 0);
            const width = Math.round((obj.width || (obj.getScaledWidth && obj.getScaledWidth())) || (obj.getScaledWidth && obj.getScaledWidth()) || 0);
            const height = Math.round((obj.height || (obj.getScaledHeight && obj.getScaledHeight())) || 0);

            if (obj.type === 'image') {
                // determine storage path if available
                let src = null;
                try { src = obj.getSrc(); } catch(e) { src = (obj._element ? obj._element.src : null); }
                if (src && src.includes('/storage/')) {
                    // store relative path (without /storage/)
                    const url = new URL(src, window.location.origin);
                    logo_path = url.pathname.replace('/storage/', '');
                } else {
                    // if not in storage, save full url as fallback
                    logo_path = src;
                }
                out.elements[key] = { position: { x: left, y: top }, size: { width, height }, styles: {} };
            } else {
                out.elements[key] = {
                    position: { x: left, y: top },
                    size: { width, height },
                    styles: {
                        'font-size': (obj.fontSize || 12) + 'px',
                        'font-family': obj.fontFamily || 'Arial',
                        'color': obj.fill || '#000000',
                        'font-weight': obj.fontWeight || 'normal',
                        'font-style': obj.fontStyle || 'normal'
                    }
                };
            }
        }

        const payload = {
            name: document.getElementById('templateName').value || 'My Template',
            template_data: JSON.stringify(out),
            logo_path: logo_path
        };

        axios.post('{{ route("invoicetemplates.save") }}', payload, { headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} })
            .then(res => {
                if (res.data.success) {
                    alert('Template saved.');
                    // add to selector
                    const sel = document.getElementById('templateSelector');
                    const opt = document.createElement('option'); opt.value = res.data.template_id; opt.text = payload.name; sel.appendChild(opt);
                } else alert('Save failed.');
            }).catch(err => { console.error(err); alert('Save failed.'); });
    });

    // Apply preview: call preview.ajax and open rendered HTML in new tab
    document.getElementById('applyPreview').addEventListener('click', function(){
        const templateId = document.getElementById('templateSelector').value || null;
        axios.post('{{ route("invoicetemplates.preview.ajax") }}', { template_id: templateId, invoice_data: invoiceData }, { headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'} })
            .then(resp => {
                if (resp.data.success) {
                    const w = window.open('', '_blank');
                    w.document.open();
                    w.document.write(resp.data.html);
                    w.document.close();
                } else {
                    alert('Preview failed.');
                }
            }).catch(err => { console.error(err); alert('Preview failed.'); });
    });

    // Helper to convert rgb to hex
    function rgbToHex(rgb) {
        if (!rgb) return '#000000';
        if (rgb[0] === '#') return rgb;
        const m = rgb.match(/\d+/g);
        if (!m) return '#000000';
        return '#' + ((1 << 24) + (parseInt(m[0]) << 16) + (parseInt(m[1]) << 8) + parseInt(m[2])).toString(16).slice(1);
    }

})();
</script>
@endsection
