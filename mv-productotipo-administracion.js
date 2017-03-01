(function () {
    'use strict';

    angular.module('mvProductoTipoAdministracion', [])
        .component('mvProductoTipoAdministracion', mvProductoTipoAdministracion());

    function mvProductoTipoAdministracion() {
        return {
            bindings: {
                searchFunction: '&'
            },
            templateUrl: window.installPath + '/mv-angular-productos/mv-productotipo-administracion.html',
            controller: MvProductoTipoController
        }
    }

    MvProductoTipoController.$inject = ['ProductTypeVars', 'ProductTypeService', "MvUtils"];
    /**
     * @param AcUsuarios
     * @constructor
     */
    function MvProductoTipoController(ProductTypeVars, ProductTypeService, MvUtils) {
        var vm = this;

        vm.productosTipo = [];
        vm.productoTipo = {};
        vm.detailsOpen = false;
        vm.disponible_para_venta = false;
        vm.control_stock = false;
        vm.compuesto = false;
        vm.status = true;
        vm.update = false;

        vm.save = save;
        vm.cancel = cancel;
        vm.setData = setData;
        vm.loadProductosTipo = loadProductosTipo;
        vm.remove = remove;
        vm.setCheckBox = setCheckBox;

        var element = angular.element(document.getElementById('nombre'));

        element[0].addEventListener('focus', function () {
            element[0].classList.remove('error-input');
            element[0].removeEventListener('focus', removeFocus);
        });

        function removeFocus() { }

        loadProductosTipo();

        function loadProductosTipo() {
            ProductTypeService.get().then(function (data) {
                setData(data);
            });
        }

        function save() {
            if(vm.productoTipo.nombre === undefined || vm.productoTipo.nombre.length == 0) {
                element[0].classList.add('error-input');
                MvUtils.showMessage('error', 'El nombre del tipo de producto no puede ser vacio');
                return;
            } else if(vm.productoTipo.nombre.length > 50) {
                element[0].classList.add('error-input');
                MvUtils.showMessage('error', 'El nombre del tipo de producto no puede tener más de 50 caracteres');
                return;
            }

            if (vm.productoTipo.producto_tipo_id == undefined) {
                vm.productoTipo.status = 1;
            } else {
                vm.productoTipo.status = vm.status ? 1 : 0;
            }
            vm.productoTipo.disponible_para_venta = vm.disponible_para_venta ? 1 : 0;
            vm.productoTipo.control_stock = vm.control_stock ? 1 : 0;
            vm.productoTipo.compuesto = vm.compuesto ? 1 : 0;

            ProductTypeService.save(vm.productoTipo).then(function (data) {
                vm.detailsOpen = data.error;
                if(data.error) {
                    element[0].classList.add('error-input');
                    MvUtils.showMessage('error', data.message);
                }
                else {
                    vm.productoTipo = {};
                    loadProductosTipo();
                    element[0].classList.remove('error-input');
                    MvUtils.showMessage('success', data.message);
                }
            })
            .catch(function (data) {
                vm.productoTipo = {};
                vm.detailsOpen = true;
            });

        }

        function setData(data) {
            vm.productosTipo = data;
            vm.paginas = ProductTypeVars.paginas;
        }

        function remove() {
            if(vm.productoTipo.producto_tipo_id == undefined) {
                alert('Debe seleccionar un tipo de producto');
            } else {
                var result = confirm('¿Esta seguro que desea eliminar el registro seleccionada?');
                if(result) {
                    ProductTypeService.remove(vm.productoTipo.producto_tipo_id, function(data){
                        vm.productoTipo = {};
                        vm.detailsOpen = false;
                        loadProductosTipo();
                        MvUtils.showMessage('success', 'La registro se borro satisfactoriamente');
                    });
                }
            }
        }

        function cancel() {
            vm.productosTipo = [];
            vm.productoTipo={};
            vm.detailsOpen = false;
            vm.disponible_para_venta = false;
            vm.control_stock = false;
            vm.compuesto = false;
            vm.status = false;
            vm.update = false;
            element[0].classList.remove('error-input');
            ProductTypeVars.clearCache = true;
            loadProductosTipo();
        }

        function setCheckBox(productoTipo) {
            vm.disponible_para_venta = (productoTipo.disponible_para_venta == 1 ? true : false);
            vm.control_stock = (productoTipo.control_stock == 1 ? true : false);
            vm.compuesto = (productoTipo.compuesto == 1 ? true : false);
            vm.status = (productoTipo.status == 1 ? true : false);
        }


        // Implementación de la paginación
        vm.start = 0;
        vm.limit = ProductTypeVars.paginacion;
        vm.pagina = ProductTypeVars.pagina;
        vm.paginas = ProductTypeVars.paginas;

        function paginar(vars) {
            if (vars == {}) {
                return;
            }
            vm.start = vars.start;
            vm.pagina = vars.pagina;
        }

        vm.next = function () {
            paginar(MvUtils.next(ProductTypeVars));
        };
        vm.prev = function () {
            paginar(MvUtils.prev(ProductTypeVars));
        };
        vm.first = function () {
            paginar(MvUtils.first(ProductTypeVars));
        };
        vm.last = function () {
            paginar(MvUtils.last(ProductTypeVars));
        };

        vm.goToPagina = function () {
            paginar(MvUtils.goToPagina(vm.pagina, ProductTypeVars));
        }

    }


})();
