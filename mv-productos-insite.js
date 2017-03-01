(function () {
    'use strict';

    angular.module('mvProductosInsite', [])
        .component('mvProductosInsite', mvProductosInsite())
        .service('ProductoInsiteService', ProductoInsiteService);

    function mvProductosInsite() {
        return {
            bindings: {
                searchFunction: '&'
            },
            templateUrl: window.installPath + '/mv-angular-productos/mv-productos-insite.html',
            controller: MvProductosInsiteController
        }
    }

    MvProductosInsiteController.$inject = ["ProductVars", 'ProductService', "MvUtils", "CategoryService", "ProductoInsiteService", "$scope"];
    /**
     * @param AcProductos
     * @constructor
     */
    function MvProductosInsiteController(ProductVars, ProductService, MvUtils, CategoryService, ProductoInsiteService, $scope) {
        var vm = this;

        vm.productos = [];
        vm.producto = {};
        vm.categorias = [];
        vm.opcionales = {};

        vm.save = save;
        vm.cancel = cancel;
        vm.get = get;

        get();

        CategoryService.get().then(function(data){
            vm.categorias = data;
        });

        
        function save(){
            ProductoInsiteService.producto = vm.producto;
            ProductoInsiteService.broadcast();
            vm.detailsOpen = false;
            vm.producto = {};
        }

        function get(){
            ProductService.getProductosCliente().then(function (data) {
                vm.productos = data;
                console.log(data);
            });
        }

        function cancel() {
            vm.detailsOpen= false;
            vm.producto = {};
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


    ProductoInsiteService.$inject = ['$rootScope'];
    function ProductoInsiteService($rootScope){
        this.broadcast = function () {
            $rootScope.$broadcast("MvProductoInsiteService")
        };
        this.listen = function (callback) {
            $rootScope.$on("MvProductoInsiteService", callback)
        };

        this.producto = {};
    }


})();
