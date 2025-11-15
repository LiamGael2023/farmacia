/**
 * JavaScript Principal
 * Sistema de Farmacia
 */

(function() {
    'use strict';

    // Configuración global
    const App = {
        init: function() {
            this.setupDataTables();
            this.setupTooltips();
            this.setupAlerts();
            this.setupFormValidation();
        },

        // Configurar DataTables
        setupDataTables: function() {
            if (typeof $.fn.dataTable !== 'undefined') {
                $.extend(true, $.fn.dataTable.defaults, {
                    language: {
                        url: '//cdn.datatables.net/plug-ins/1.13.6/i18n/es-ES.json'
                    },
                    responsive: true,
                    pageLength: 25,
                    order: [[0, 'desc']],
                    dom: '<"row"<"col-sm-12 col-md-6"l><"col-sm-12 col-md-6"f>>rt<"row"<"col-sm-12 col-md-5"i><"col-sm-12 col-md-7"p>>'
                });
            }
        },

        // Configurar tooltips de Bootstrap
        setupTooltips: function() {
            const tooltipTriggerList = [].slice.call(
                document.querySelectorAll('[data-bs-toggle="tooltip"]')
            );
            tooltipTriggerList.map(function(tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        },

        // Configurar alertas automáticas
        setupAlerts: function() {
            // Auto-cerrar alertas después de 5 segundos
            setTimeout(function() {
                $('.alert').fadeOut('slow', function() {
                    $(this).remove();
                });
            }, 5000);
        },

        // Validación de formularios
        setupFormValidation: function() {
            // Validación HTML5
            const forms = document.querySelectorAll('.needs-validation');
            Array.from(forms).forEach(function(form) {
                form.addEventListener('submit', function(event) {
                    if (!form.checkValidity()) {
                        event.preventDefault();
                        event.stopPropagation();
                    }
                    form.classList.add('was-validated');
                }, false);
            });
        }
    };

    // Utilidades globales
    window.AppUtils = {
        // Formatear moneda
        formatCurrency: function(amount) {
            return 'S/ ' + parseFloat(amount).toFixed(2);
        },

        // Formatear fecha
        formatDate: function(date, format = 'dd/MM/yyyy') {
            if (!(date instanceof Date)) {
                date = new Date(date);
            }
            const day = String(date.getDate()).padStart(2, '0');
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const year = date.getFullYear();

            return format
                .replace('dd', day)
                .replace('MM', month)
                .replace('yyyy', year);
        },

        // Mostrar alerta
        showAlert: function(type, message, title = '') {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: type,
                    title: title || (type === 'success' ? 'Éxito' : 'Error'),
                    text: message,
                    confirmButtonColor: '#206bc4'
                });
            } else {
                alert(message);
            }
        },

        // Confirmar acción
        confirm: function(message, callback) {
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    title: '¿Está seguro?',
                    text: message,
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#d63939',
                    cancelButtonColor: '#6c757d',
                    confirmButtonText: 'Sí, continuar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed && typeof callback === 'function') {
                        callback();
                    }
                });
            } else {
                if (confirm(message) && typeof callback === 'function') {
                    callback();
                }
            }
        },

        // Validar DNI peruano
        validateDNI: function(dni) {
            return /^\d{8}$/.test(dni);
        },

        // Validar RUC peruano
        validateRUC: function(ruc) {
            return /^\d{11}$/.test(ruc);
        },

        // Validar email
        validateEmail: function(email) {
            const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            return re.test(email);
        },

        // Debounce function
        debounce: function(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        },

        // Loading state
        setLoading: function(element, loading = true) {
            if (loading) {
                element.classList.add('loading');
                element.disabled = true;
            } else {
                element.classList.remove('loading');
                element.disabled = false;
            }
        }
    };

    // Inicializar cuando el DOM esté listo
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            App.init();
        });
    } else {
        App.init();
    }

    // Prevenir doble submit de formularios
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.classList.contains('submitted')) {
            e.preventDefault();
            return false;
        }
        form.classList.add('submitted');

        // Remover la clase después de 3 segundos para permitir reintentos
        setTimeout(function() {
            form.classList.remove('submitted');
        }, 3000);
    });

})();
