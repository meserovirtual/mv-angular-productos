(function () {
    'use strict';

    angular.module('mvProductosAdministracion', [])
        .component('mvProductosAdministracion', mvProductosAdministracion());

    function mvProductosAdministracion() {
        return {
            bindings: {
                searchFunction: '&'
            },
            templateUrl: window.installPath + '/mv-angular-productos/mv-productos-administracion.html',
            controller: MvProductosController
        }
    }

    MvProductosController.$inject = ["ProductVars", 'ProductService', "MvUtils", "CategoryService", "UserService", "$scope",
        "UploadVars", "UploadService", "ProductTypeService", 'MvUtilsGlobals', 'StockService', 'StockVars'];
    /**
     * @param AcProductos
     * @constructor
     */
    function MvProductosController(ProductVars, ProductService, MvUtils, CategoryService, UserService, $scope, UploadVars,
                                   UploadService, ProductTypeService, MvUtilsGlobals, StockService, StockVars) {
        var vm = this;

        vm.productos = [];
        vm.proveedores = [];
        vm.producto = {};
        vm.listProveedores = [];
        vm.producto_para_kit = {};
        vm.productos_en_kit = [];
        vm.categorias = [];
        vm.categoria = {};
        vm.productosTipo = [];
        vm.productoTipo = {};
        vm.detailsOpen = false;
        vm.priceOpen = false;
        vm.en_oferta = false;
        vm.en_slider = false;
        vm.destacado = false;
        vm.status = true;
        vm.update = false;
        vm.showProveedores = false;
        vm.showProductoCompuesto = false;
        vm.clear = true;
        StockVars.clearCache = true;
        vm.producto_para_kit = {};
        vm.productos_en_kit = [];


        vm.foto_01 = {nombre: 'no_image.png'};
        vm.foto_02 = {nombre: 'no_image.png'};
        vm.foto_03 = {nombre: 'no_image.png'};
        vm.foto_04 = {nombre: 'no_image.png'};


        vm.save = save;
        vm.cancel = cancel;
        vm.savePrecio = savePrecio;
        vm.setData = setData;
        vm.agregarKit = agregarKit;
        vm.quitarKit = quitarKit;
        vm.searchProductoKit = searchProductoKit;
        vm.updateFotoProyecto = updateFotoProyecto;
        vm.getTipoProducto = getTipoProducto;
        vm.getFotos = getFotos;
        vm.cleanProducto = cleanProducto;
        vm.removeFoto = removeFoto;
        vm.remove = remove;
        vm.loadProductos = loadProductos;
        vm.openContainer = openContainer;
        vm.openPrice = openPrice;
        vm.setChecked = setChecked;
        vm.getProveedores = getProveedores;
        vm.setCheckBox = setCheckBox;



        var element1 = angular.element(document.getElementById('nombre'));
        var element2 = angular.element(document.getElementById('precio_oferta'));
        var element3 = angular.element(document.getElementById('precio_oferta_desde'));
        var element4 = angular.element(document.getElementById('precio_oferta_hasta'));

        element1[0].addEventListener('focus', function () {
            element1[0].classList.remove('error-input');
            element1[0].removeEventListener('focus', removeFocus);
        });

        element2[0].addEventListener('focus', function () {
            element2[0].classList.remove('error-input');
            element2[0].removeEventListener('focus', removeFocus);
        });

        element3[0].addEventListener('focus', function () {
            element3[0].classList.remove('error-input');
            element3[0].removeEventListener('focus', removeFocus);
        });

        element4[0].addEventListener('focus', function () {
            element4[0].classList.remove('error-input');
            element4[0].removeEventListener('focus', removeFocus);
        });

        function removeFocus() { }

        document.getElementById('searchProducto').getElementsByTagName('input')[0].addEventListener('blur', function (event) {
            console.log('busco');
        });

        loadProductos();

        function loadProductos() {
            ProductService.get().then(function (data) {
                setData(data);
            });
        }

        function openContainer() {
            if(vm.detailsOpen == false && vm.priceOpen == false)
                return false;
            return true;
        }

        function openPrice() {
            if(vm.producto == undefined || vm.producto.producto_id == undefined) {
                MvUtils.showMessage('warning', 'Debe seleccionar un Producto');
            } else {
                vm.priceOpen=true;
                vm.detailsOpen = false;
            }
        }

        function setChecked(usuario_id) {
            if(usuario_id != undefined) {
                vm.checked = !vm.checked;
            }
        }

        function getTipoProducto(producto_tipo_id) {
            for(var i=0; i < vm.productosTipo.length; i++){
                if(vm.productosTipo[i].producto_tipo_id == producto_tipo_id){
                    return vm.productosTipo[i].nombre;
                }
            }
        }

        function getFotos(fotos) {
            vm.foto_01.nombre = fotos[0].nombre;
            vm.foto_02.nombre = fotos[1].nombre;
            vm.foto_03.nombre = fotos[2].nombre;
            vm.foto_04.nombre = fotos[3].nombre;
        }

        function getProveedores(proveedores) {
            for(var i=0; i < vm.proveedores.length; i++) {
                vm.proveedores[i].check = false;
            }

            for(var i=0; i < vm.proveedores.length; i++) {
                for(var j=0; j < proveedores.length; j++){
                    if(vm.proveedores[i].usuario_id == proveedores[j].usuario_id){
                        vm.proveedores[i].check = true;
                    }
                }
            }
        }

        function setCheckBox(producto) {
            for(var i=0; i < vm.categorias.length; i++){
                if(vm.categorias[i].categoria_id == producto.categorias[0].categoria_id){
                    vm.categoria = vm.categorias[i];
                    break;
                }
            }

            for(var i=0; i < vm.productosTipo.length; i++){
                if(vm.productosTipo[i].producto_tipo_id == producto.producto_tipo_id){
                    vm.productoTipo = vm.productosTipo[i];
                    break;
                }
            }

            vm.en_oferta = (producto.en_oferta == 1 ? true : false);
            vm.en_slider = (producto.en_slider == 1 ? true : false);
            vm.destacado = (producto.destacado == 1 ? true : false);
            vm.status = (producto.status == 1 ? true : false);
        }

        /**
         * Limpio producto, imagenes, proveedores
         */
        function cleanProducto() {
            vm.producto = {};
            vm.foto_01 = {nombre: 'no_image.png'};
            vm.foto_02 = {nombre: 'no_image.png'};
            vm.foto_03 = {nombre: 'no_image.png'};
            vm.foto_04 = {nombre: 'no_image.png'};

            for(var i=0; i < vm.proveedores.length; i++) {
                vm.proveedores[i].check = false;
            }

            vm.en_oferta = false;
            vm.en_slider = false;
            vm.destacado = false;
            vm.status = false;
            vm.update = false;
            vm.categoria = vm.categorias[0];
            vm.productoTipo = vm.productosTipo[0];
            UploadVars.uploadsList = [];
        }

        function updateFotoProyecto(filelist, id, sub_folder) {
            UploadService.addImages(filelist, id, sub_folder, function (data) {
                for (var i = 0; i < UploadVars.uploadsList.length; i++) {
                    if (UploadVars.uploadsList[i].id == 1) {
                        vm.foto_01.nombre = UploadVars.uploadsList[i].file.name;
                    }
                    if (UploadVars.uploadsList[i].id == 2) {
                        vm.foto_02.nombre = UploadVars.uploadsList[i].file.name;
                    }
                    if (UploadVars.uploadsList[i].id == 3) {
                        vm.foto_03.nombre = UploadVars.uploadsList[i].file.name;
                    }
                    if (UploadVars.uploadsList[i].id == 4) {
                        vm.foto_04.nombre = UploadVars.uploadsList[i].file.name;
                    }
                }
                $scope.$apply();
                //console.log(data);
            })
        }

        /*
         $scope.$watch('$ctrl.producto', function () {
         vm.listProveedores = [];
         if (vm.producto.proveedores == undefined) {
         return;
         }

         for (var i in vm.producto.proveedores) {
         vm.listProveedores[vm.producto.proveedores[i].usuario_id] = true;
         }
         });
         */

        function save() {
            if(vm.producto.nombre == undefined || vm.producto.nombre.length == 0){
                element1[0].classList.add('error-input');
                MvUtils.showMessage('error', 'El nombre es obligatorio');
                return;
            }

            vm.producto.proveedores = [];

            for(var i=0; i < vm.proveedores.length; i++) {
                if(vm.proveedores[i].check) {
                    vm.producto.proveedores.push({'proveedor_id': vm.proveedores[i].usuario_id});
                }
            }

            vm.producto.fotos = [];
            vm.producto.fotos[0] = vm.foto_01;
            vm.producto.fotos[1] = vm.foto_02;
            vm.producto.fotos[2] = vm.foto_03;
            vm.producto.fotos[3] = vm.foto_04;

            vm.producto.categorias = [];
            vm.producto.categorias.push(vm.categoria);
            vm.producto.producto_tipo_id = vm.productoTipo.producto_tipo_id;

            if (vm.producto.producto_id == undefined) {
                vm.producto.status = 1;
            } else {
                vm.producto.status = vm.status ? 1 : 0;
            }
            vm.producto.en_oferta = vm.en_oferta ? 1 : 0;
            vm.producto.en_slider = vm.en_slider ? 1 : 0;
            vm.producto.destacado = vm.destacado ? 1 : 0;

            console.log(vm.producto);

            ProductService.save(vm.producto).then(function (data) {
                console.log(data);
                vm.detailsOpen = (data === undefined || data < 0) ? true : false;
                if(data == undefined) {
                    element1[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'Error guardando el registro');
                } else {
                    //vm.detailsOpen = false;
                    cleanProducto();
                    loadProductos();
                    element1[0].classList.remove('error-input');
                    MvUtils.showMessage('success', 'La operación se realizó satisfactoriamente');
                }
            }).catch(function (data) {
                cleanProducto();
                MvUtils.showMessage('error', 'Error guardando el registro');
                //setData(data);
            });
        }

        function savePrecio() {
            if(vm.producto.precios.length == 0) {
                element2[0].classList.add('error-input');
                element3[0].classList.add('error-input');
                element4[0].classList.add('error-input');
                MvUtils.showMessage('error', 'Debe ingresar el primer precio y horario');
                return;
            }

            for(var i=0; i < vm.producto.precios.length; i++){
                if(vm.producto.precios[i].hora_desde == undefined || vm.producto.precios[i].hora_desde == '') {
                    element3[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'La hora desde es obligatoria');
                    return;
                } else if(vm.producto.precios[i].hora_desde != '' && !MvUtils.validaHora(vm.producto.precios[i].hora_desde)) {
                    element3[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'El formato de hora desde no es correcto');
                    return;
                } else {
                    element3[0].classList.remove('error-input');
                }

                if(vm.producto.precios[i].hora_hasta == undefined || vm.producto.precios[i].hora_hasta == '') {
                    element4[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'La hora hasta es obligatoria');
                    return;
                } else if(vm.producto.precios[i].hora_hasta != '' && !MvUtils.validaHora(vm.producto.precios[i].hora_hasta)) {
                    element4[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'El formato de hora hasta no es correcto');
                    return;
                } else {
                    element4[0].classList.remove('error-input');
                }
            }

            for(var i=0; i < vm.producto.precios.length; i++){
                if(vm.producto.precios[i].hora_desde >= vm.producto.precios[i].hora_hasta) {
                    element3[0].classList.add('error-input');
                    MvUtils.showMessage('error', 'La hora desde no puede ser mayor o igual que la hora hasta');
                    return;
                }
            }

            console.log(vm.producto);

            if(vm.producto.producto_id === undefined) {
                MvUtils.showMessage('error', 'Debe seleccionar un Producto');
            } else {
                ProductService.savePrecioPorHorario(vm.producto).then(function (data) {
                    if(data === undefined) {
                        element2[0].classList.add('error-input');
                        element3[0].classList.add('error-input');
                        element4[0].classList.add('error-input');
                        MvUtils.showMessage('error', 'Error actualizando el dato');
                    }
                    else {
                        vm.producto = {};
                        loadProductos();
                        element2[0].classList.remove('error-input');
                        element3[0].classList.remove('error-input');
                        element4[0].classList.remove('error-input');
                        MvUtils.showMessage('success', 'La operación se realizó satisfactoriamente');
                        vm.priceOpen = false;
                    }
                })
                    .catch(function (data) {
                        vm.producto = {};
                    });
            }
        }

        function setData(data) {
            vm.productos = data;
            vm.paginas = ProductVars.paginas;
        }

        CategoryService.get().then(function (data) {
            vm.categorias = data;
            vm.categoria = data[0];
        });

        ProductTypeService.get().then(function (data) {
            vm.productosTipo = data;
            vm.productoTipo = data[0];
        });

        UserService.get(2).then(function (data) {
            for(var i=0; i < data.length; i++) {
                var proveedor = {
                    usuario_id: data[i].usuario_id,
                    nombre: data[i].nombre,
                    check:false
                };
                vm.proveedores.push(proveedor);
            }
        });

        function searchProductoKit(callback) {
            ProductService.get().then(callback);
        }

        function agregarKit() {
            console.log(vm.producto.kits);
            if(vm.producto.kits == undefined)
                vm.producto.kits = [];

            vm.producto.kits.push({
                producto_cantidad: 1,
                producto_id: vm.producto_para_kit.producto_id,
                nombre: vm.producto_para_kit.nombre
            });
            vm.producto_para_kit = {};
        }


        function quitarKit(producto_kit) {
            for (var i = 0; i < vm.producto.kits.length; i++) {
                if (producto_kit.producto_id == vm.producto.kits[i].producto_id) {
                    vm.producto.kits.splice(i, 1);
                }
            }
        }

        function removeFoto(foto){
            if(foto == 1) {
                vm.foto_01.nombre = 'no_image.png';
            } else if(foto == 2) {
                vm.foto_02.nombre = 'no_image.png';
            } else if(foto == 3) {
                vm.foto_03.nombre = 'no_image.png';
            } else if(foto == 4) {
                vm.foto_04.nombre = 'no_image.png';
            }
        }

        function remove() {
            if(vm.producto.producto_id == undefined) {
                alert('Debe seleccionar un producto');
            } else {
                var result = confirm('¿Esta seguro que desea eliminar el producto seleccionado?');
                if(result) {
                    ProductService.remove(vm.producto.producto_id).then(function(data){
                        vm.producto = {};
                        cleanProducto();
                        loadProductos();
                    }).catch(function(data){
                        console.log(data);
                    });
                }
            }
        }

        function cancel() {
            vm.productos = [];
            vm.detailsOpen=false;
            vm.priceOpen=false;
            vm.en_oferta = false;
            vm.en_slider = false;
            vm.destacado = false;
            vm.status = false;
            vm.update = false;
            ProductVars.clearCache = true;
            cleanProducto();
            loadProductos();
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
