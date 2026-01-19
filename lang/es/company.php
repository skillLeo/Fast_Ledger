<?php

return [

    // ============================================
    // SALUDOS
    // ============================================
    'good_morning' => 'Buenos Días',
    'good_afternoon' => 'Buenas Tardes',
    'good_evening' => 'Buenas Noches',
    'user' => 'Usuario',

    // ============================================
    // NAVEGACIÓN Y MENÚ
    // ============================================
    'dashboard' => 'Panel de Control',
    'invoices' => 'ventas',
    'Purchasing' => 'compras',
    'customers' => 'Clientes',
    'products' => 'Productos',
    'users' => 'Usuarios',
    'settings' => 'Configuración',
    'reports' => 'Informes',
    'logout' => 'Cerrar Sesión',
    'profile' => 'Perfil',
    'help' => 'Ayuda',
    'notifications' => 'Notificaciones',
    'company_module' => 'Módulo de Empresa',
    'companies' => 'Empresas',
    'all_modules' => 'Todos los Módulos',
    'audit' => 'Registro de Auditoría',
    'more' => 'Más',
    'invoice_design' => 'Diseño de Factura',

    // ============================================
    // SELECTOR DE EMPRESA
    // ============================================
    'working_in' => 'Trabajando en:',
    'switch_company' => 'Cambiar Empresa',
    'select_company' => 'Seleccione una empresa para cambiar el espacio de trabajo actual',
    'search_companies' => 'Buscar empresas...',
    'view_all' => 'Ver Todos',
    'create' => 'Crear',
    'company_id' => 'ID de Empresa:',
    'current' => 'Actual',



    // Add these keys in the FACTURAS section
    'purchasing_management' => 'Compras',
    'issued_purchases' => 'Compras',
    'draft_purchases' => 'Borradores de Compras',
    'create_purchase' => 'Crear Compra',
    'no_issued_purchases_found' => 'No se encontraron compras emitidas',
    'no_draft_purchases_found' => 'No se encontraron borradores de compras',

    // ============================================
    // FACTURAS
    // ============================================
    'invoice_management' => 'ventas',
    'invoices_management' => 'ventas',
    'create_invoice' => 'Crear Nueva Factura',
    'create_new_invoice' => 'Crear Nueva Factura',
    'issued_invoices' => 'Facturas',
    'draft_invoices' => 'Borradores de Facturas',
    'invoice_no' => 'Nº de Factura',
    'invoice_ref' => 'Ref. de Factura',
    'invoice_date' => 'Fecha de Factura',
    'invoice_reference' => 'Referencia de Factura',
    'customer' => 'Cliente',
    'date' => 'Fecha',
    'due_date' => 'Fecha de Vencimiento',
    'total' => 'Total',
    'paid' => 'Pagado',
    'balance' => 'Balance',
    'status' => 'Estado',
    'documents' => 'Documentos',
    'actions' => 'Acciones',
    'view' => 'Ver',
    'view_invoice' => 'Ver Factura',
    'edit' => 'Editar',
    'edit_draft' => 'Editar Borrador',
    'delete' => 'Eliminar',
    'download_pdf' => 'Descargar PDF',
    'no_invoices_found' => 'No se encontraron facturas',
    'no_drafts_found' => 'No se encontraron borradores',
    'no_issued_found' => 'No se encontraron facturas emitidas',
    'view_document' => 'Ver documento(s)',
    'no_docs' => 'Sin docs',
    'click_to_change_status' => 'Haga clic para cambiar el estado',
    'read_only' => 'SOLO LECTURA',

    // Tipos de Compra/Venta
    'purchase' => 'Compra',
    'purchase_credit' => 'Crédito de Compra',
    'sales_invoice' => 'Factura de Venta',
    'sales_credit' => 'Crédito de Venta',
    'journal' => 'Diario',

    // ============================================
    // MODAL DE ESTADO DE FACTURA
    // ============================================
    'change_invoice_status' => 'Cambiar Estado de Factura',
    'loading_invoice_details' => 'Cargando detalles de factura...',
    'invoice_summary' => 'Resumen de Factura',
    'current_status' => 'Estado Actual:',
    'new_status' => 'Nuevo Estado',
    'select_status' => 'Seleccionar Estado',
    'payment_amount' => 'Monto del Pago',
    'current_balance' => 'Balance Actual:',
    'mark_fully_paid' => 'Esto marcará la factura como Totalmente Pagada. El balance será £0.00',
    'invoice_overdue' => 'Esta factura está vencida (La fecha de vencimiento ha pasado)',
    'update_status' => 'Actualizar Estado',
    'updating' => 'Actualizando...',
    'status_updated_successfully' => '¡Estado actualizado exitosamente!',
    'failed_to_update_status' => 'Error al actualizar el estado',

    // ============================================
    // MODAL DE DOCUMENTOS
    // ============================================
    'invoice_documents' => 'Documentos de Factura',
    'loading_documents' => 'Cargando documentos...',
    'total_documents' => 'Total de Documentos:',
    'document_name' => 'Nombre del Documento',
    'size' => 'Tamaño',
    'uploaded' => 'Subido:',
    'type' => 'Tipo',
    'view_download' => 'Ver/Descargar',
    'no_documents_attached' => 'No hay documentos adjuntos a esta factura',
    'failed_to_load_documents' => 'Error al cargar documentos',

    // ============================================
    // REGISTRO DE ACTIVIDAD
    // ============================================
    'activity_history' => 'Historial de Actividad',
    'view_activity_log' => 'Ver Registro de Actividad',
    'hide' => 'Ocultar',
    'activity_log' => 'Registro de Actividad',
    'track_all_changes' => 'Seguimiento de todos los cambios y acciones realizadas en esta factura',
    'loading_activity_log' => 'Cargando registro de actividad...',
    'no_activity_log_yet' => 'Aún no hay registro de actividad',
    'changes' => 'Cambios',
    'just_now' => 'Justo ahora',
    'minutes_ago' => 'hace :count min(s)',
    'hours_ago' => 'hace :count hora(s)',
    'days_ago' => 'hace :count día(s)',
    'invoice_activity_history' => 'Historial de Actividad de Facturas',
    'refresh' => 'Actualizar',
    'export' => 'Exportar',

    // Acciones de Actividad
    'created' => 'Creado',
    'edited' => 'Editado',
    'issued' => 'Emitido',
    'status_updated' => 'Estado Actualizado',
    'sent' => 'Enviado',
    'cancelled' => 'Cancelado',
    'viewed' => 'Visto',
    'payment_recorded' => 'Pago Registrado',

    // Índice de Registro de Actividad
    'total_activities' => 'Total de Actividades',
    'unique_invoices' => 'Facturas Únicas',
    'active_users' => 'Usuarios Activos',
    'today_activities' => 'Actividades de Hoy',
    'action' => 'Acción',
    'ip_address' => 'Dirección IP',
    'browser' => 'Navegador',
    'date_time' => 'Fecha/Hora',
    'no_activity_logs_found' => 'No se encontraron registros de actividad',
    'activity_details' => 'Detalles de Actividad',
    'notes' => 'Notas',
    'no_changes' => 'Sin cambios',
    'system' => 'Sistema',

    // Filtros
    'filters' => 'Filtros',
    'all_statuses' => 'Todos los Estados',
    'all_users' => 'Todos los Usuarios',
    'date_from' => 'Fecha Desde',
    'date_to' => 'Fecha Hasta',
    'filter' => 'Filtrar',
    'clear_filters' => 'Limpiar Filtros',
    'apply_filters' => 'Aplicar Filtros',

    // ============================================
    // PRODUCTOS
    // ============================================
    'product_management' => 'Gestión de Productos',
    'add_product' => 'Agregar Nuevo Producto',
    'all_products' => 'Todos los Productos',
    'purchase_products' => 'Productos de Compra',
    'sales_products' => 'Productos de Venta',
    'item_code' => 'Código de Artículo',
    'name' => 'Nombre',
    'category' => 'Categoría',
    'description' => 'Descripción',
    'unit_amount' => 'Precio Unitario',
    'vat_rate' => 'Tasa de IVA',
    'vat_amount' => 'Monto de IVA',
    'net_amount' => 'Monto Neto',
    'image' => 'Imagen',
    'search_placeholder' => 'Buscar por código, nombre o descripción...',
    'no_products_found' => 'No se encontraron productos',
    'showing_count' => 'Mostrando :count productos',
    'product_details' => 'Detalles del Producto',
    'product_name' => 'Nombre del Producto',
    'purchase_label' => 'Compra',
    'sales_label' => 'Venta',
    'status_label' => 'Estado',
    'confirm_delete_title' => 'Confirmar Eliminación',
    'confirm_delete_product_message' => '¿Está seguro de que desea eliminar este producto?',
    'product_label' => 'Producto',
    'delete_product_button' => 'Eliminar Producto',
    'deleting' => 'Eliminando...',
    'unknown_error' => 'Error desconocido',
    'product_image' => 'Imagen del Producto',
    'no_image_available' => 'No hay imagen disponible',
    'view_full_size' => 'Ver Tamaño Completo',
    'download' => 'Descargar',
    'file_information' => 'Información del Archivo',
    'file_name' => 'Nombre del Archivo:',
    'file_path' => 'Ruta del Archivo:',
    'product_information' => 'Información del Producto',
    'display_name' => 'Nombre para Mostrar',
    'financial_details' => 'Detalles Financieros',
    'ledger_account' => 'Cuenta Contable',
    'ledger_ref' => 'Ref. Contable',
    'account_reference' => 'Referencia de Cuenta',
    'account_ref' => 'Ref. de Cuenta',
    'system_information' => 'Información del Sistema',
    'product_id' => 'ID del Producto:',
    'created_at' => 'Creado:',
    'last_updated' => 'Última Actualización:',
    'edit_product' => 'Editar Producto',
    'delete_product' => 'Eliminar Producto',
    'no_items_found' => 'No se encontraron artículos',

    // ============================================
    // CLIENTES
    // ============================================
    'customer_management' => 'Gestión de Clientes',
    'add_customer' => 'Agregar Nuevo Cliente',
    'company_name' => 'Nombre de la Empresa',
    'customer_name' => 'Nombre del Cliente',
    'email' => 'Correo Electrónico',
    'phone' => 'Teléfono',
    'mobile' => 'Móvil',
    'address' => 'Dirección',
    'town' => 'Ciudad',
    'post_code' => 'Código Postal',
    'vat_registration_no' => 'Nº de Registro de IVA',
    'tax_id' => 'NIF/CIF',
    'no_customers_found' => 'No se encontraron clientes',

    // ============================================
    // BOTONES Y ACCIONES COMUNES
    // ============================================
    'back' => 'Volver',
    'save' => 'Guardar',
    'cancel' => 'Cancelar',
    'search' => 'Buscar',
    'confirm' => 'Confirmar',
    'close' => 'Cerrar',
    'yes' => 'Sí',
    'no' => 'No',
    'loading' => 'Cargando...',
    'success' => 'Éxito',
    'error' => 'Error',
    'new' => 'Nuevo',
    'add' => 'Agregar',
    'update' => 'Actualizar',
    'remove' => 'Eliminar',
    'import' => 'Importar',
    'print' => 'Imprimir',

    // ============================================
    // CONFIRMACIONES Y ADVERTENCIAS
    // ============================================
    'confirm_delete' => '¿Está seguro de que desea eliminar esto?',
    'delete_warning' => 'Esta acción no se puede deshacer.',
    'confirm_delete_product' => '¿Está seguro de que desea eliminar este producto?',
    'confirm_delete_invoice' => '¿Eliminar este borrador?',
    'confirm_delete_customer' => '¿Está seguro de que desea eliminar este cliente?',

    // ============================================
    // ESTADOS
    // ============================================
    'draft' => 'Borrador',
    'paid_status' => 'Pagado',
    'partially_paid' => 'Parcialmente Pagado',
    'overdue' => 'Vencido',
    'active' => 'Activo',
    'inactive' => 'Inactivo',
    'pending' => 'Pendiente',
    'approved' => 'Aprobado',
    'rejected' => 'Rechazado',

    // ============================================
    // RESUMEN DE FACTURA
    // ============================================
    'net_amount' => 'Monto Neto',
    'total_vat' => 'IVA Total',
    'total_amount' => 'Monto Total',
    'summary' => 'Resumen',

    // ============================================
    // NOTAS
    // ============================================
    'no_notes_available' => 'No hay notas disponibles',

    // ============================================
    // MENSAJES
    // ============================================
    'select_language' => 'Seleccionar Idioma',
    'language_changed' => 'Idioma cambiado exitosamente',
    'operation_successful' => 'Operación completada exitosamente',
    'operation_failed' => 'La operación falló',
    'no_data' => 'No hay datos disponibles',
    'invalid_input' => 'Entrada inválida proporcionada',
    'failed_to_load_invoice_data' => 'Error al cargar datos de factura',
    'pdf_download_initiated' => 'Descarga de PDF iniciada',

    // ============================================
    // PAGINACIÓN
    // ============================================
    'showing' => 'Mostrando',
    'to' => 'a',
    'of' => 'de',
    'results' => 'resultados',
    'activities' => 'actividades',
    'per_page' => 'por página',
    'previous' => 'Anterior',
    'next' => 'Siguiente',

    // ============================================
    // VARIOS
    // ============================================
    'n_a' => 'N/A',
    'unknown' => 'Desconocido',
    'unknown_device' => 'Dispositivo desconocido',
    'required' => 'requerido',
    'optional' => 'opcional',
    // ============================================
    // CLAVES ADICIONALES DE FACTURAS
    // ============================================
    'back_to_invoices' => 'Volver a Facturas',
    'back_to_products' => 'Volver a Productos',

    // Acciones de Ver/Editar Factura
    'edit_invoice' => 'Editar Factura',
    'delete_invoice' => 'Eliminar Factura',

    // Mensajes de Estado Adicionales
    'select_a_status' => 'Por favor seleccione un estado',
    'enter_payment_amount' => 'Por favor ingrese un monto de pago válido',
    'payment_exceeds_balance' => 'El monto del pago no puede exceder el balance',
    'invoice_not_overdue_yet' => 'Esta factura aún no está vencida. La fecha de vencimiento no ha pasado.',

    // Documentos
    'file_type' => 'Tipo de Archivo',
    'no_document_name' => 'Sin Nombre de Documento',

    // Formatos de Tiempo del Registro de Actividad
    'minutes_short' => 'min',
    'hours_short' => 'hora',
    'days_short' => 'día',

    // Descarga de PDF
    'downloading_pdf' => 'Descargando PDF...',
    'using_template' => 'Usando plantilla ID:',
    'pdf_generation_failed' => 'Error al generar PDF',

    // Mensajes de JavaScript
    'failed_to_load_data' => 'Error al cargar datos',
    'unknown_error_occurred' => 'Ocurrió un error desconocido',

    // Estados Vacíos
    'no_data_available' => 'No hay datos disponibles',
    'empty_list' => 'La lista está vacía',
    // Add these BEFORE the closing ];

    // ============================================
    // MÓDULO DE CLIENTES
    // ============================================
    'customers' => 'Clientes',
    'customer' => 'Cliente',
    'add_new_customer' => 'Agregar Nuevo Cliente',
    'add_first_customer' => 'Agregar Primer Cliente',
    'create_new_customer' => 'Crear Nuevo Cliente',
    'edit_customer' => 'Editar Cliente',
    'customer_details' => 'Detalles del Cliente',
    'all_customers' => 'Todos los Clientes',
    'no_customers_yet' => 'Aún No Hay Clientes',
    'start_by_adding_first' => 'Comience agregando su primer cliente',
    'search_customers' => 'Buscar clientes...',
    'showing_entries' => 'Mostrando :from a :to de :total entradas',

    // Campos del Formulario de Cliente
    'customer_type' => 'Tipo de Cliente',
    'individual' => 'Individual',
    'company_type' => 'Empresa',
    'legal_name' => 'Nombre Legal',
    'company_name' => 'Nombre de la Empresa',
    'contact_person' => 'Persona de Contacto',
    'contact_person_name' => 'Nombre de Persona de Contacto',

    // Información de Identidad
    'identity_information' => 'Información de Identidad',
    'tax_identification_type' => 'Tipo de Identificación Fiscal',
    'tax_id_type' => 'Tipo de ID Fiscal',
    'tax_id_number' => 'Número de ID Fiscal',
    'select_tax_id_type' => 'Seleccionar tipo de ID fiscal',
    'enter_tax_id_number' => 'Ingrese número de ID fiscal',

    // Tipos de ID Fiscal
    'nif' => 'NIF (ID Fiscal Español)',
    'cif' => 'CIF (ID Fiscal de Empresa)',
    'nie' => 'NIE (ID de Extranjero)',
    'eu_vat' => 'Número de IVA de la UE',
    'nif_number' => 'Número NIF',
    'cif_number' => 'Número CIF',
    'nie_number' => 'Número NIE',
    'eu_vat_number' => 'Número de IVA UE',

    // Información de Dirección
    'address_information' => 'Información de Dirección',
    'street_address' => 'Dirección',
    'city' => 'Ciudad',
    'postal_code' => 'Código Postal',
    'province' => 'Provincia',
    'country' => 'País',
    'enter_street_address' => 'Ingrese dirección',
    'enter_city' => 'Ingrese ciudad',
    'enter_postal_code' => 'Ingrese código postal',
    'enter_province' => 'Ingrese provincia',
    'enter_country' => 'Ingrese país',

    // Información de Contacto
    'contact_information' => 'Información de Contacto',
    'email' => 'Correo Electrónico',
    'phone' => 'Teléfono',
    'enter_email_address' => 'Ingrese correo electrónico',
    'enter_phone_number' => 'Ingrese número de teléfono',
    'enter_contact_person_name' => 'Ingrese nombre de contacto',
    'contact' => 'Contacto',

    // Configuración Fiscal
    'tax_configuration' => 'Configuración Fiscal',
    'tax_config' => 'Config. Fiscal',
    'tax_type' => 'Tipo de Impuesto',
    'no_tax' => 'Sin Impuesto',
    'enable_vat' => 'Habilitar IVA',
    'enable_irpf' => 'Habilitar IRPF (Impuesto sobre la Renta)',
    'vat_rate' => 'Tasa de IVA',
    'irpf_rate' => 'Tasa de IRPF',
    'select_vat_rate' => 'Seleccionar tasa de IVA',
    'select_irpf_rate' => 'Seleccionar tasa de IRPF',
    'has_vat' => 'Tiene IVA',
    'has_irpf' => 'Tiene IRPF',

    // Tasas de IVA
    'standard_21' => 'Estándar (21%)',
    'reduced_10' => 'Reducido (10%)',
    'super_reduced_4' => 'Superreducido (4%)',
    'exempt_0' => 'Exento (0%)',
    'intra_eu' => 'Intra-UE (Inversión del Sujeto Pasivo)',
    'export' => 'Exportación (Fuera de la UE)',

    // Tasas de IRPF
    'irpf_7' => '7%',
    'irpf_15' => '15%',

    // Configuración de Pago
    'payment_settings' => 'Configuración de Pago',
    'payment_method' => 'Método de Pago',
    'preferred_payment_method' => 'Método de Pago Preferido',
    'bank_transfer' => 'Transferencia Bancaria',
    'cash' => 'Efectivo',
    'bank_details' => 'Detalles Bancarios',
    'bank_details_optional' => 'Detalles Bancarios (Opcional)',
    'iban' => 'IBAN',
    'bank_name' => 'Nombre del Banco',
    'enter_iban_number' => 'Ingrese número IBAN',
    'enter_bank_name' => 'Ingrese nombre del banco',
    'payment' => 'Pago',

    // Acciones y Mensajes
    'create_customer' => 'Crear Cliente',
    'update_customer' => 'Actualizar Cliente',
    'delete_customer' => 'Eliminar Cliente',
    'view_customer' => 'Ver Cliente',
    'customer_created_successfully' => '¡Cliente creado exitosamente!',
    'customer_updated_successfully' => '¡Cliente actualizado exitosamente!',
    'customer_deleted_successfully' => '¡Cliente eliminado exitosamente!',
    'confirm_delete_customer' => '¿Está seguro de que desea eliminar este cliente? Esta acción no se puede deshacer.',

    // Encabezados de Tabla
    'customer_name' => 'Nombre del Cliente',
    'type' => 'Tipo',
    'tax_id' => 'ID Fiscal',

    // Varios
    'optional' => 'Opcional',
    'required' => 'Requerido',
    'enter_company_name' => 'Ingrese nombre de empresa',
    'enter_legal_name' => 'Ingrese nombre legal',
    // Add these keys BEFORE the closing ];

    // ============================================
    // MÓDULO DE EMPRESAS
    // ============================================
    'all_companies' => 'Todas las Empresas',
    'add_new_company' => 'Agregar Nueva Empresa',
    'create_company' => 'Crear Empresa',
    'edit_company' => 'Editar Empresa',
    'company_details' => 'Detalles de la Empresa',
    'no_companies_found' => 'No se Encontraron Empresas',
    'create_first_company' => 'Crea tu primera empresa para comenzar.',
    'back_to_companies' => 'Volver a Empresas',
    'your_role' => 'Tu Rol:',
    'profile_completion' => 'Finalización del Perfil',
    'complete' => 'Completo',
    'complete_profile_message' => 'Complete su perfil para desbloquear todas las funciones',
    'trade_name' => 'Nombre Comercial',

    // Información Básica
    'basic_information' => 'Información Básica',
    'tax_residence' => 'Residencia Fiscal',
    'currency' => 'Moneda',
    'company_type' => 'Tipo de Empresa',
    'tipo_empresa' => 'Tipo de Empresa',
    'tax_regime' => 'Régimen Fiscal',
    'tax_id_label' => 'ID Fiscal:',
    'country_label' => 'País:',
    'tax_residence_label' => 'Residencia Fiscal:',
    'currency_label' => 'Moneda:',

    // Configuración de Facturas
    'invoice_settings' => 'Configuración de Facturas',
    'invoice_prefix' => 'Prefijo de Factura',
    'invoice_prefix_label' => 'Prefijo de Factura:',
    'next_invoice_number' => 'Siguiente Número de Factura',
    'next_invoice_number_label' => 'Siguiente Número de Factura:',
    'sample_invoice_number' => 'Número de Factura de Ejemplo',
    'sample_invoice_number_label' => 'Número de Factura de Ejemplo:',
    'invoice_prefix_help' => 'Se utilizará para números de factura (ej., INV-00001)',

    // Estadísticas de Empresa
    'company_statistics' => 'Estadísticas de Empresa',
    'created_on' => 'Creado el',
    'created_on_label' => 'Creado el:',
    'last_modified' => 'Última Modificación',
    'last_modified_label' => 'Última Modificación:',
    'created_by' => 'Creado Por',
    'created_by_label' => 'Creado Por:',

    // Usuarios de Empresa
    'company_users' => 'Usuarios de Empresa',
    'manage_users' => 'Gestionar Usuarios',
    'user' => 'Usuario',
    'role' => 'Rol',
    'joined' => 'Se Unió',
    'no_users_found' => 'No se encontraron usuarios',

    // Actividad Reciente
    'recent_activity' => 'Actividad Reciente',
    'action' => 'Acción',
    'description' => 'Descripción',
    'no_activity_yet' => 'Aún no hay actividad',

    // Campos de Formulario
    'company_name' => 'Nombre de la Empresa',
    'enter_company_name' => 'Ingrese nombre de empresa',
    'trade_name_optional' => 'Nombre Comercial (Opcional)',
    'enter_trade_name' => 'Ingrese nombre comercial',
    'country_cannot_change' => 'El país no se puede cambiar después de crear la empresa',
    'company_type_cannot_change' => 'El tipo de empresa no se puede cambiar',
    'tax_id_cannot_change' => 'El ID fiscal no se puede cambiar después de crear la empresa',
    'tax_id_nif_cif_vat' => 'ID Fiscal / NIF / CIF / Número de IVA',
    'country_tax_residence' => 'País de Residencia Fiscal',
    'select_country' => 'Seleccionar País',
    'regimen_fiscal' => 'Régimen Fiscal',
    'seleccionar_regimen' => 'Seleccionar Régimen',

    // Información de Dirección
    'state_province_region' => 'Estado / Provincia / Región',
    'enter_state' => 'Ingrese estado',
    'postal_zip_code' => 'Código Postal / ZIP',

    // Detalles Adicionales
    'additional_details_optional' => 'Detalles Adicionales (Opcional)',
    'phone_number' => 'Número de Teléfono',
    'phone_label' => 'Teléfono:',
    'email_address' => 'Dirección de Correo Electrónico',
    'email_label' => 'Correo:',
    'website' => 'Sitio Web',
    'website_label' => 'Sitio Web:',
    'address_label' => 'Dirección:',
    'company_logo' => 'Logo de Empresa',
    'current_logo' => 'Logo Actual',
    'logo_upload_help' => 'Tamaño máximo: 2MB. Aceptados: JPG, PNG, GIF. Dejar vacío para mantener el logo actual.',

    // Acciones
    'update_company' => 'Actualizar Empresa',
    'search_companies' => 'Buscar empresas:',

    // Encabezados de Tabla
    'profile' => 'Perfil',
    'created' => 'Creado',

    // Varios
    'owner' => 'Propietario',
    'admin' => 'Administrador',
    'accountant' => 'Contador',
    'viewer' => 'Visualizador',


    // PLANTILLAS DE FACTURA
    'tax_invoice' => 'Factura Fiscal',
    'create_new_template' => 'Crear Nueva Plantilla',
    'edit_template' => 'Editar Plantilla',
    'delete_template' => 'Eliminar Plantilla',
    'default_template' => 'Plantilla Predeterminada',
    'customize_template' => 'Personalizar',
    'no_templates_yet' => 'Aún no hay plantillas',
    'create_first_template' => 'Crea tu primera plantilla de factura',
    'current_logo' => 'Logo Actual',
    'are_you_sure_delete_template' => '¿Estás seguro de que deseas eliminar',
    'action_cannot_undone' => 'Esta acción no se puede deshacer',
    'template_deleted_successfully' => 'Plantilla eliminada con éxito',
    'failed_delete_template' => 'Error al eliminar plantilla',

    // Vista Previa de Plantilla
    'template_selector_label' => 'Cargar Plantilla:',
    'default_layout' => 'Diseño Predeterminado',
    'load_template' => 'Cargar',
    'customize_button' => 'Personalizar',
    'download_button' => 'Descargar',
    'download_pdf' => 'Descargar PDF',
    'download_custom_pdf' => 'Descargar con Plantilla Personalizada',
    'template_applied_successfully' => '¡Plantilla aplicada con éxito!',
    'failed_load_template' => 'Error al cargar plantilla',
    'loading_template' => 'Cargando plantilla...',

    // Contenido de Factura
    'invoice_to' => 'Facturar a:',
    'invoice_date' => 'Fecha de Factura:',
    'inv_due_date' => 'Fecha de Vencimiento:',
    'invoice_no' => 'No. de Factura:',
    'invoice_ref' => 'Ref. de Factura:',
    'description' => 'Descripción',
    'qty' => 'Cant',
    'unit_price' => 'Precio Unitario',
    'vat' => 'IVA',
    'total_amount' => 'Monto Total',
    'image' => 'Imagen',
    'additional_notes' => 'Notas Adicionales',
    'net' => 'NETO',
    'total' => 'TOTAL',
    'company_registration_no' => 'No. de Registro de Empresa',
    'registered_office' => 'Oficina Registrada',
    'electronic_payment_to' => 'Por favor realice el pago electrónico a',
    'payment_name' => 'Nombre:',
    'sort_code' => 'Código de Sucursal:',
    'account_no' => 'No. de Cuenta:',
    'payment_ref' => 'Ref. de Pago:',
    'product_image' => 'Imagen del Producto',
    'click_view_full_size' => 'Clic para ver tamaño completo',
    'no_image' => 'Sin Imagen',
    'no_items_found' => 'No se encontraron artículos',

    // Personalización de Plantilla
    'live_preview' => 'Vista Previa en Vivo',
    'drag_mode' => 'Modo Arrastrar',
    'styling_controls' => 'Controles de Estilo',
    'template_name' => 'Nombre de Plantilla',
    'set_as_default' => 'Establecer como predeterminada',
    'element_positioning' => 'Posicionamiento de Elementos',
    'selected_element' => 'Seleccionado:',
    'none' => 'Ninguno',
    'left_px' => 'Izquierda (px)',
    'top_px' => 'Superior (px)',
    'reset_all_positions' => 'Restablecer Todas las Posiciones',
    'recalculate_table_spacing' => 'Recalcular Espaciado de Tabla',
    'logo_colors' => 'Colores del Logo',
    'primary' => 'Primario',
    'secondary' => 'Secundario',
    'quick_themes' => 'Temas Rápidos',
    'theme_default' => 'Predeterminado',
    'theme_blue' => 'Azul',
    'theme_green' => 'Verde',
    'theme_purple' => 'Púrpura',
    'theme_red' => 'Rojo',
    'theme_dark' => 'Oscuro',
    'typography' => 'Tipografía',
    'title_font' => 'Fuente del Título',
    'body_font' => 'Fuente del Cuerpo',
    'font_size' => 'Tamaño de Fuente',
    'font_size_extra_small' => 'Extra Pequeño (9px)',
    'font_size_small' => 'Pequeño (10px)',
    'font_size_medium' => 'Mediano (11px)',
    'font_size_large' => 'Grande (12px)',
    'font_size_extra_large' => 'Extra Grande (13px)',
    'table_customization' => 'Personalización de Tabla',
    'header_background_color' => 'Color de Fondo del Encabezado',
    'header_text_color' => 'Color de Texto del Encabezado',
    'border_color' => 'Color del Borde',
    'row_height' => 'Altura de Fila',
    'row_height_compact' => 'Compacta (8px)',
    'row_height_default' => 'Predeterminada (12px)',
    'row_height_comfortable' => 'Cómoda (16px)',
    'row_height_spacious' => 'Espaciosa (20px)',
    'column_width_preset' => 'Preajuste de Ancho de Columna',
    'column_auto' => 'Automático',
    'column_equal' => 'Igual',
    'column_balanced' => 'Equilibrado',
    'notes_table_customization' => 'Personalización de Tabla de Notas',
    'use_items_table_styling' => 'Usar mismo estilo que Tabla de Artículos',
    'logo_management' => 'Gestión de Logo',
    'png_recommended' => 'PNG recomendado (máx. 2MB)',
    'custom_logo_active' => 'Logo personalizado activo',
    'layout_presets' => 'Diseños Preestablecidos',
    'standard_layout' => 'Diseño Estándar',
    'centered_layout' => 'Diseño Centrado',
    'modern_layout' => 'Diseño Moderno',
    'save_template' => 'Guardar Plantilla',
    'preview_full_page' => 'Vista Previa de Página Completa',
    'reset_everything' => 'Restablecer Todo',

    // Notificaciones
    'saving' => 'Guardando...',
    'template_saved_successfully' => '¡Plantilla guardada con éxito!',
    'position_updated' => '¡Posición actualizada!',
    'drag_mode_enabled' => '¡Modo arrastrar habilitado! Haz clic y arrastra elementos.',
    'drag_mode_disabled' => 'Modo arrastrar deshabilitado',
    'primary_color_updated' => '¡Color primario actualizado!',
    'secondary_color_updated' => '¡Color secundario actualizado!',
    'theme_applied' => '¡Tema aplicado!',
    'title_font_updated' => '¡Fuente del título actualizada!',
    'body_font_updated' => '¡Fuente del cuerpo actualizada!',
    'font_size_updated' => '¡Tamaño de fuente actualizado!',
    'table_header_color_updated' => '¡Color del encabezado de tabla actualizado!',
    'header_text_color_updated' => '¡Color del texto del encabezado actualizado!',
    'border_color_updated' => '¡Color del borde actualizado!',
    'row_height_updated' => '¡Altura de fila actualizada!',
    'table_font_size_updated' => '¡Tamaño de fuente de tabla actualizado!',
    'notes_header_color_updated' => '¡Color del encabezado de tabla de notas actualizado!',
    'notes_header_text_color_updated' => '¡Color del texto del encabezado de notas actualizado!',
    'notes_border_color_updated' => '¡Color del borde de notas actualizado!',
    'notes_row_height_updated' => '¡Altura de fila de notas actualizada!',
    'notes_font_size_updated' => '¡Tamaño de fuente de notas actualizado!',
    'notes_using_items_styling' => 'Tabla de notas ahora usa estilo de tabla de artículos',
    'notes_styling_independent' => 'El estilo de tabla de notas ahora es independiente',
    'logo_uploaded_successfully' => '¡Logo cargado con éxito!',
    'column_widths_updated' => '¡Anchos de columna actualizados!',
    'layout_preset_applied' => '¡Diseño preestablecido aplicado!',
    'positions_reset' => 'Posiciones restablecidas a predeterminadas',
    'table_spacing_recalculated' => '¡Espaciado de tabla recalculado!',
    'element_aligned' => 'Elemento alineado',
    'upload_failed' => 'Error en la carga',
    'save_failed' => 'Error al guardar',
    'select_element_first' => 'Por favor selecciona un elemento primero',
    'confirm_reset_positions' => '¿Restablecer todas las posiciones de elementos al diseño predeterminado?',
    'confirm_reset_all' => '¿Restablecer todas las personalizaciones? Esto no se puede deshacer.',
    'file_too_large' => 'El archivo debe ser menor a 2MB',
    'click_element_position' => 'Haz clic en cualquier elemento para ver controles de posición',
    'drag_tip' => 'Consejo: Habilita el "Modo Arrastrar" para reposicionar elementos. Haz clic y arrastra cualquier sección para moverla. Haz clic en un elemento para ver controles de posición.',
    'auto_spacing_tip' => 'Espaciado Automático: Las secciones de pago y totales se posicionan automáticamente 20px debajo de la tabla. Usa "Recalcular Espaciado de Tabla" si parecen desalineadas.',

    'phone_short' => 'T',
    'email_short' => 'E',
    'website_short' => 'W',
    'vat_no' => 'No. IVA',
    'logo' => 'Logo',
    'product' => 'Producto',
    'my_custom_template' => 'Mi Plantilla Personalizada',
    'my_template' => 'Mi Plantilla',
    'customer_name' => 'Nombre del Cliente',
    'client_name' => 'Nombre del Cliente',
    'your_business' => 'Tu Empresa',
    'tip' => 'Consejo',
    'auto_spacing' => 'Espaciado Automático',
    'deleting' => 'Eliminando',
    'error' => 'Error',
    'unknown_error' => 'Error desconocido',
    'select_template_first' => 'Por favor selecciona una plantilla primero',

    'matter' => 'Asunto',
    'reg_no' => 'No. Reg',
    'no_image' => 'Sin Imagen',
];
