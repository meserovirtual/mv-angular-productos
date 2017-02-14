(function () {
    'use strict';

    angular.module('mvProductosInsite', [])
        .component('mvProductosInsite', mvProductosInsite());

    function mvProductosInsite() {
        return {
            bindings: {
                searchFunction: '&'
            },
            templateUrl: window.installPath + '/mv-angular-productos/mv-productos-insite.html',
            controller: MvProductosController
        }
    }

    MvProductosController.$inject = ["ProductVars", 'ProductService', "MvUtils", "CategoryService", "UserService", "$scope",
        "UploadVars", "UploadService", "ProductTypeService"];
    /**
     * @param AcProductos
     * @constructor
     */
    function MvProductosController(ProductVars, ProductService, MvUtils, CategoryService, UserService, $scope, UploadVars,
                                   UploadService, ProductTypeService) {
        var vm = this;

        vm.productos = [];
        vm.producto = {};
        vm.categorias = [];

        vm.save = save;
        vm.cancel = cancel;
        vm.get = get;


        CategoryService.get().then(function(data){
            vm.categorias = data;
        });

        
        function save(){

        }

        function get(){
            ProductService.get().then(function (data) {
                // setData(data);
                console.log(data);
            });
        }

        function cancel() {
        }


        // Implementación de la paginación
        vm.start = 0;
        vm.limit = ProductVars.paginacion;
        vm.pagina = ProductVars.pagina;
        vm.paginas = ProductVars.paginas;

        function paginar(vars) {
            if (vars == {}) {
                return;
            }
            vm.start = vars.start;
            vm.pagina = vars.pagina;
        }

        vm.next = function () {
            paginar(MvUtils.next(ProductVars));
        };
        vm.prev = function () {
            paginar(MvUtils.prev(ProductVars));
        };
        vm.first = function () {
            paginar(MvUtils.first(ProductVars));
        };
        vm.last = function () {
            paginar(MvUtils.last(ProductVars));
        };

        vm.goToPagina = function () {
            paginar(MvUtils.goToPagina(vm.pagina, ProductVars));
        }

    }


})();
