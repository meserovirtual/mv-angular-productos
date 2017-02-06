(function () {
    'use strict';

    angular.module('acCategoriasAdministracion', [])
        .component('acCategoriasAdministracion', acCategoriasAdministracion());

    function acCategoriasAdministracion() {
        return {
            bindings: {
                searchFunction: '&'
            },
            templateUrl: window.installPath + '/mv-angular-productos/mv-categorias-administracion.html',
            controller: AcCategoriasController
        }
    }

    AcCategoriasController.$inject = ['CategoryVars', 'CategoryService', "AcUtils"];
    /**
     * @param AcUsuarios
     * @constructor
     */
    function AcCategoriasController(CategoryVars, CategoryService, AcUtils) {
        var vm = this;

        vm.categorias = [];
        vm.categoria = {};
        vm.detailsOpen = false;
        vm.status = true;
        vm.update = false;

        vm.save = save;
        vm.cancel = cancel;
        vm.setData = setData;
        vm.loadCategorias = loadCategorias;
        vm.remove = remove;
        vm.omitirAcentos = omitirAcentos;
        //vm.cleanCategoria = cleanCategoria;

        var element = angular.element(document.getElementById('nombre'));

        element[0].addEventListener('focus', function () {
            element[0].classList.remove('error-input');
            element[0].removeEventListener('focus', removeFocus);
        });

        function removeFocus() { }

        loadCategorias();

        function loadCategorias() {
            CategoryService.get().then(function (data) {
                setData(data);
            });
        }

        function save() {
            if(vm.categoria.nombre === undefined || vm.categoria.nombre.length == 0) {
                element[0].classList.add('error-input');
                AcUtils.showMessage('error', 'El nombre de la categoria no puede ser vacio');
                return;
            } else if(vm.categoria.nombre.length > 70) {
                element[0].classList.add('error-input');
                AcUtils.showMessage('error', 'El nombre de la categoria no puede tener más de 100 caracteres');
                return;
            }

            if (vm.categoria.categoria_id == undefined) {
                vm.categoria.status = 1;
            } else {
                vm.categoria.status = vm.status ? 1 : 0;
            }
            CategoryService.save(vm.categoria).then(function (data) {
                //vm.detailsOpen = (data === undefined || data < 0) ? true : false;
                vm.detailsOpen = data.error;
                if(data.error) {
                    element[0].classList.add('error-input');
                    AcUtils.showMessage('error', data.message);
                }
                else {
                    vm.categoria = {};
                    loadCategorias();
                    element[0].classList.remove('error-input');
                    AcUtils.showMessage('success', data.message);
                }
            })
            .catch(function (data) {
                vm.categoria = {};
                vm.detailsOpen = true;
            });

        }

        function setData(data) {
            console.log('Cargo de nuevo categorias');
            vm.categorias = data;
            vm.paginas = CategoryVars.paginas;
        }

        function remove() {
            if(vm.categoria.categoria_id == undefined) {
                alert('Debe seleccionar una categoria');
            } else {
                var result = confirm('¿Esta seguro que desea eliminar la categoria seleccionada?');
                if(result) {
                    CategoryService.remove(vm.categoria.categoria_id, function(data){
                        vm.categoria = {};
                        vm.detailsOpen = false;
                        loadCategorias();
                        AcUtils.showMessage('success', 'La registro se borro satisfactoriamente');
                    });
                }
            }
        }

        /*
        function cleanCategoria() {
            vm.categoria = {};
            vm.status = false;
            vm.update = false;
            element[0].classList.remove('error-input');
        }
        */

        function omitirAcentos(texto) {
            return AcUtils.omitirAcentos(texto);
        }

        function cancel() {
            vm.categorias = [];
            vm.categoria={};
            vm.detailsOpen = false;
            vm.status = false;
            vm.update = false;
            element[0].classList.remove('error-input');
            CategoryVars.clearCache = true;
            loadCategorias();
        }



        // Implementación de la paginación
        vm.start = 0;
        vm.limit = CategoryVars.paginacion;
        vm.pagina = CategoryVars.pagina;
        vm.paginas = CategoryVars.paginas;

        function paginar(vars) {
            if (vars == {}) {
                return;
            }
            vm.start = vars.start;
            vm.pagina = vars.pagina;
        }

        vm.next = function () {
            paginar(AcUtils.next(CategoryVars));
        };
        vm.prev = function () {
            paginar(AcUtils.prev(CategoryVars));
        };
        vm.first = function () {
            paginar(AcUtils.first(CategoryVars));
        };
        vm.last = function () {
            paginar(AcUtils.last(CategoryVars));
        };

        vm.goToPagina = function () {
            paginar(AcUtils.goToPagina(vm.pagina, CategoryVars));
        }

    }


})();
