$(document).on('ready', init())

function init() {

    var FORM = (function () {
        var events = function () {
            $('.btn_reset').on('click', function (e) {
                e.preventDefault()
                $('form input.form-control').val('')
                $('form select.form-control').val(0)
            })
        }
        return {
            init: function () {
                events()
                $('#d_date_range').daterangepicker({
                    format: 'DD/MM/YYYY',
                    locale: {
                        applyLabel: 'Cargar',
                        cancelLabel: 'Cancelar',
                        fromLabel: 'Desde',
                        toLabel: 'Hasta',
                        daysOfWeek: ['Do', 'Lu', 'Ma', 'Mi', 'Ju', 'Vi', 'Sa'],
                        monthNames: ['Enero', 'Febrero', 'Marzo', 'Abril', 'Mayo', 'Junio', 'Julio', 'Agosto', 'Setiembre', 'Octubre', 'Noviembre', 'Diciembre'],
                        firstDay: 1
                    }
                })
            }
        }
    }())

    FORM.init()
}