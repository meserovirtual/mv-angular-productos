(function () {
    'use strict';

    var scripts = document.getElementsByTagName("script");
    var currentScriptPath = scripts[scripts.length - 1].src;

    if (currentScriptPath.length == 0) {
        currentScriptPath = window.installPath + '/mv-angular-productos/includes/mv-productos.php';
    }

    angular.module('mvProductos', [])
        .factory('ProductService', ProductService)
        .service('ProductVars', ProductVars)
        .factory('CategoryService', CategoryService)
        .service('CategoryVars', CategoryVars)
        .factory('CartService', CartService)
        .service('CartVars', CartVars)
        .factory('ProductTypeService', ProductTypeService)
        .service('ProductTypeVars', ProductTypeVars)
    ;


    ProductService.$inject = ['$http', 'ProductVars', '$cacheFactory', 'MvUtils', '$q', '$location', 'MvUtilsGlobals', 'ErrorHandler'];
    function ProductService($http, ProductVars, $cacheFactory, MvUtils, $q, $location, MvUtilsGlobals, ErrorHandler) {
        //Variables
        var service = {};

        var url = currentScriptPath.replace('mv-productos.js', '/includes/mv-productos.php');

        //Function declarations
        service.get = get;
        service.getByParams = getByParams;
        service.getMasVendidos = getMasVendidos;
        service.getByCategoria = getByCategoria;

        service.create = create;
        service.createPrecioPorHorario = createPrecioPorHorario;

        service.update = update;
        service.updatePrecioPorHorario = updatePrecioPorHorario;

        service.remove = remove;
        service.save = save;
        service.savePrecioPorHorario = savePrecioPorHorario;

        return service;

        //Functions

        /**
         * Función que determina si es un update o un create
         * @param producto
         * @returns {*}
         */
        function save(producto) {

            var deferred = $q.defer();

            if (producto.producto_id != undefined) {
                deferred.resolve(update(producto));
            } else {
                deferred.resolve(create(producto));
            }
            return deferred.promise;
        }

        /**
         * Función que determina si es un update o un create
         * @param producto
         * @returns {*}
         */
        function savePrecioPorHorario(producto) {
            var deferred = $q.defer();

            if (producto.precios[0].precio_id != undefined) {
                deferred.resolve(updatePrecioPorHorario(producto));
            } else {
                deferred.resolve(createPrecioPorHorario(producto));
            }
            return deferred.promise;
        }

        /**
         * @description Obtiene todos los productos
         * @param callback
         * @returns {*}
         */
        function get() {
            MvUtilsGlobals.startWaiting();
            var urlGet = url + '?function=getProductos';
            var $httpDefaultCache = $cacheFactory.get('$http');
            var cachedData = [];


            // Verifica si existe el cache de productos
            if ($httpDefaultCache.get(urlGet) != undefined) {
                if (ProductVars.clearCache) {
                    $httpDefaultCache.remove(urlGet);
                }
                else {
                    var deferred = $q.defer();
                    cachedData = $httpDefaultCache.get(urlGet);
                    deferred.resolve(cachedData);
                    MvUtilsGlobals.stopWaiting();
                    return deferred.promise;
                }
            }


            return $http.get(urlGet, {cache: true})
                .then(function (response) {

                        for (var i = 0; i < response.data.length; i++) {
                            if(response.data[i].precios !== undefined && response.data[i].precios.length > 0) {
                                for(var j=0; j < response.data[i].precios.length; j++){
                                    response.data[i].precios[j].precio = parseFloat(response.data[i].precios[j].precio);
                                }
                                //response.data[i].precios[0].precio = parseFloat(response.data[i].precios[0].precio);
                                //response.data[i].precios[1].precio = parseFloat(response.data[i].precios[1].precio);
                                //response.data[i].precios[2].precio = parseFloat(response.data[i].precios[2].precio);
                                response.data[i].precios.sort(function (a, b) {
                                    // Turn your strings into dates, and then subtract them
                                    // to get a value that is either negative, positive, or zero.
                                    return a.precio_tipo_id - b.precio_tipo_id;
                                });
                            }
                            response.data[i].pto_repo = parseFloat(response.data[i].pto_repo);
                            response.data[i].iva = parseFloat(response.data[i].iva);
                            response.data[i].status = '' + response.data[i].status;
                            response.data[i].en_oferta = '' + response.data[i].en_oferta;
                            response.data[i].en_slider = '' + response.data[i].en_slider;
                            response.data[i].destacado = '' + response.data[i].destacado;
                            response.data[i].producto_tipo = '' + response.data[i].producto_tipo;

                        }

                        $httpDefaultCache.put(urlGet, response.data);
                        ProductVars.clearCache = false;
                        ProductVars.paginas = (response.data.length % ProductVars.paginacion == 0) ? parseInt(response.data.length / ProductVars.paginacion) : parseInt(response.data.length / ProductVars.paginacion) + 1;
                        MvUtilsGlobals.stopWaiting();
                        return response.data;
                    }
                )
                .catch(function (response) {
                    ProductVars.clearCache = true;
                    MvUtilsGlobals.stopWaiting();
                    ErrorHandler(response);
                })
        }


        /**
         * @description Retorna la lista filtrada de productos
         * @param params -> String, separado por comas (,) que contiene la lista de par�metros de b�squeda, por ej: nombre, sku
         * @param values
         * @param exact_match
         */
        function getByParams(params, values, exact_match) {
            return get().then(function (data) {
                return MvUtils.getByParams(params, values, exact_match, data);
            }).then(function (data) {
                return data;
            });
        }

        /**
         * @description Retorna los primero 8 productos mas vendidos
         * @param callback
         */
        function getMasVendidos(callback) {
            get(function (data) {
                var response = data.sort(function (a, b) {
                    return b.vendidos - a.vendidos;
                });

                callback(response.slice(0, 8));
            });
        }

        /**
         * @description Retorna un listado de productos filtrando por la categoria
         * @param categoria_id
         * @param callback
         */
        function getByCategoria(categoria_id, callback) {
            var productos = [];
            get(function (data) {
                if (data == undefined || data.length == 0)
                    return callback(productos);

                data.forEach(function (producto) {
                    if (producto === undefined || producto.categorias === undefined || producto.categorias.length == 0)
                        return callback(productos);

                    if (categoria_id == producto.categorias[0].categoria_id)
                        productos.push(producto);
                });
                return callback(productos);
            });
        }

        /** @name: remove
         * @param producto_id
         * @param callback
         * @description: Elimina el producto seleccionado.
         */
        function remove(producto_id, callback) {
            return $http.post(url,
                {function: 'removeProducto', 'producto_id': producto_id})
                .success(function (data) {
                    //console.log(data);
                    if (data !== 'false') {
                        ProductVars.clearCache = true;
                        callback(data);
                    }
                })
                .error(function (data) {
                    callback(data);
                })
        }

        /**
         * @description: Crea un producto.
         * @param producto
         * @returns {*}
         */
        function create(producto) {

            return $http.post(url,
                {
                    'function': 'createProducto',
                    'producto': JSON.stringify(producto)
                })
                .then(function (response) {
                    ProductVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductVars.clearCache = true;
                    ErrorHandler(response)
                });
        }

        /**
         *
         * @param producto
         * @returns {*}
         */
        function createPrecioPorHorario(producto) {
            return $http.post(url,
                {
                    'function': 'createPrecioPorHorario',
                    'producto': JSON.stringify(producto)
                })
                .then(function (response) {
                    ProductVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductVars.clearCache = true;
                    ErrorHandler(response)
                });
        }

        /** @name: update
         * @param producto
         * @description: Realiza update al producto.
         */
        function update(producto) {
            return $http.post(url,
                {
                    'function': 'updateProducto',
                    'producto': JSON.stringify(producto)
                })
                .then(function (response) {
                    ProductVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductVars.clearCache = true;
                    ErrorHandler(response.data)
                });
        }

        function updatePrecioPorHorario(producto) {
            return $http.post(url,
                {
                    'function': 'updatePrecioPorHorario',
                    'producto': JSON.stringify(producto)
                })
                .then(function (response) {
                    ProductVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductVars.clearCache = true;
                    ErrorHandler(response.data)
                });
        }

    }

    ProductVars.$inject = [];
    /**
     * @description Almacena variables temporales de productos
     * @constructor
     */
    function ProductVars() {
        // Cantidad de p�ginas total del recordset
        this.paginas = 1;
        // P�gina seleccionada
        this.pagina = 1;
        // Cantidad de registros por p�gina
        this.paginacion = 10;
        // Registro inicial, no es p�gina, es el registro
        this.start = 0;


        // Indica si se debe limpiar el cach� la pr�xima vez que se solicite un get
        this.clearCache = true;

    }


    CategoryService.$inject = ['$http', 'CategoryVars', '$cacheFactory', 'MvUtils', 'MvUtilsGlobals', 'ErrorHandler', '$q'];
    function CategoryService($http, CategoryVars, $cacheFactory, MvUtils, MvUtilsGlobals, ErrorHandler, $q) {
        //Variables
        var service = {};

        var url = currentScriptPath.replace('mv-productos.js', '/includes/mv-productos.php');

        //Function declarations
        service.get = get;
        service.getByParams = getByParams;

        service.create = create;
        service.update = update;
        service.remove = remove;
        service.save = save;

        service.goToPagina = goToPagina;
        service.next = next;
        service.prev = prev;

        return service;

        //Functions
        /**
         * @description Obtiene todos los categorias
         * @param callback
         * @returns {*}
         */
        function get() {
            var urlGet = url + '?function=getCategorias';
            var $httpDefaultCache = $cacheFactory.get('$http');
            var cachedData = [];
            MvUtilsGlobals.startWaiting();


            // Verifica si existe el cache de categorias
            if ($httpDefaultCache.get(urlGet) != undefined) {
                if (CategoryVars.clearCache) {
                    $httpDefaultCache.remove(urlGet);
                }
                else {
                    var deferred = $q.defer();
                    cachedData = $httpDefaultCache.get(urlGet);
                    deferred.resolve(cachedData);
                    MvUtilsGlobals.stopWaiting();
                    return deferred.promise;
                }
            }


            return $http.get(urlGet, {cache: true})
                .then(function (response) {
                    $httpDefaultCache.put(urlGet, response.data);
                    CategoryVars.clearCache = false;
                    CategoryVars.paginas = (response.data.length % CategoryVars.paginacion == 0) ? parseInt(response.data.length / CategoryVars.paginacion) : parseInt(response.data.length / CategoryVars.paginacion) + 1;
                    MvUtilsGlobals.stopWaiting();
                    return response.data;
                })
                .catch(function (response) {
                    CategoryVars.clearCache = true;
                    MvUtilsGlobals.stopWaiting();
                    ErrorHandler(response);
                })
        }


        /**
         * @description Retorna la lista filtrada de categorias
         * @param param -> String, separado por comas (,) que contiene la lista de par�metros de b�squeda, por ej: nombre, sku
         * @param value
         * @param callback
         */
        function getByParams(params, values, exact_match, callback) {
            get(function (data) {

                MvUtils.getByParams(params, values, exact_match, data, callback);
            })
        }

        /** @name: remove
         * @param categoria_id
         * @param callback
         * @description: Elimina el categoria seleccionado.
         */
        function remove(categoria_id, callback) {
            return $http.post(url,
                {
                    'function': 'removeCategoria',
                    'categoria_id': categoria_id
                })
                .success(function (data) {
                    //console.log(data);
                    if (data !== 'false') {
                        CategoryVars.clearCache = true;
                        callback(data);
                    }
                })
                .error(function (data) {
                    callback(data);
                })
        }

        function save(categoria) {
            var deferred = $q.defer();

            if (categoria.categoria_id != undefined) {
                deferred.resolve(update(categoria));
            } else {
                deferred.resolve(create(categoria));
            }
            return deferred.promise;
        }

        /**
         * @description: Crea un categoria.
         * @param categoria
         * @returns {*}
         */
        function create(categoria) {
            return $http.post(url,
                {
                    'function': 'createCategoria',
                    'categoria': JSON.stringify(categoria)
                })
                .then(function (response) {
                    CategoryVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    CategoryVars.clearCache = true;
                    ErrorHandler(response);
                });
        }


        /** @name: update
         * @param categoria
         * @description: Realiza update al categoria.
         */
        function update(categoria) {
            return $http.post(url,
                {
                    'function': 'updateCategoria',
                    'categoria': JSON.stringify(categoria)
                })
                .then(function (response) {
                    CategoryVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    CategoryVars.clearCache = true;
                    ErrorHandler(response);
                });
        }

        /**
         * Para el uso de la p�ginaci�n, definir en el controlador las siguientes variables:
         *
         vm.start = 0;
         vm.pagina = CategoryVars.pagina;
         CategoryVars.paginacion = 5; Cantidad de registros por p�gina
         vm.end = CategoryVars.paginacion;


         En el HTML, en el ng-repeat agregar el siguiente filtro: limitTo:appCtrl.end:appCtrl.start;

         Agregar un bot�n de next:
         <button ng-click="appCtrl.next()">next</button>

         Agregar un bot�n de prev:
         <button ng-click="appCtrl.prev()">prev</button>

         Agregar un input para la p�gina:
         <input type="text" ng-keyup="appCtrl.goToPagina()" ng-model="appCtrl.pagina">

         */


        /**
         * @description: Ir a p�gina
         * @param pagina
         * @returns {*}
         * uso: agregar un m�todo
         vm.goToPagina = function () {
                vm.start= CategoryService.goToPagina(vm.pagina).start;
            };
         */
        function goToPagina(pagina) {

            if (isNaN(pagina) || pagina < 1) {
                CategoryVars.pagina = 1;
                return CategoryVars;
            }

            if (pagina > CategoryVars.paginas) {
                CategoryVars.pagina = CategoryVars.paginas;
                return CategoryVars;
            }

            CategoryVars.pagina = pagina - 1;
            CategoryVars.start = CategoryVars.pagina * CategoryVars.paginacion;
            return CategoryVars;

        }

        /**
         * @name next
         * @description Ir a pr�xima p�gina
         * @returns {*}
         * uso agregar un metodo
         vm.next = function () {
                vm.start = CategoryService.next().start;
                vm.pagina = CategoryVars.pagina;
            };
         */
        function next() {

            if (CategoryVars.pagina + 1 > CategoryVars.paginas) {
                return CategoryVars;
            }
            CategoryVars.start = (CategoryVars.pagina * CategoryVars.paginacion);
            CategoryVars.pagina = CategoryVars.pagina + 1;
            //CategoryVars.end = CategoryVars.start + CategoryVars.paginacion;
            return CategoryVars;
        }

        /**
         * @name previous
         * @description Ir a p�gina anterior
         * @returns {*}
         * uso, agregar un m�todo
         vm.prev = function () {
                vm.start= CategoryService.prev().start;
                vm.pagina = CategoryVars.pagina;
            };
         */
        function prev() {


            if (CategoryVars.pagina - 2 < 0) {
                return CategoryVars;
            }

            //CategoryVars.end = CategoryVars.start;
            CategoryVars.start = (CategoryVars.pagina - 2 ) * CategoryVars.paginacion;
            CategoryVars.pagina = CategoryVars.pagina - 1;
            return CategoryVars;
        }


    }

    CategoryVars.$inject = [];
    /**
     * @description Almacena variables temporales de categorias
     * @constructor
     */
    function CategoryVars() {
        // Cantidad de p�ginas total del recordset
        this.paginas = 1;
        // P�gina seleccionada
        this.pagina = 1;
        // Cantidad de registros por p�gina
        this.paginacion = 10;
        // Registro inicial, no es p�gina, es el registro
        this.start = 0;


        // Indica si se debe limpiar el cach� la pr�xima vez que se solicite un get
        this.clearCache = true;

    }


    CartService.$inject = ['$http', 'CartVars', '$cacheFactory', 'MvUtils'];
    function CartService($http, CartVars, $cacheFactory, MvUtils) {
        //Variables
        var service = {};


        var url = currentScriptPath.replace('mv-productos.js', '/includes/mv-productos.php');

        //Function declarations
        service.get = get;
        service.getByParams = getByParams;

        service.create = create; // El carrito se crea si no hay un carrito en estado 0 que se pueda usar. Siempre primero se trae en el controlador, se verifica si existe uno en Iniciado, si no existe se crea.

        service.update = update;

        service.remove = remove;


        service.addToCart = addToCart;
        service.updateProductInCart = updateProductInCart;
        service.removeFromCart = removeFromCart;
        service.reloadLastCart = reloadLastCart; // Invoca a getByParam con status 0, si existe cargalo como carrito.
        service.checkOut = checkOut; // Es solo invocar a update con el estado cambiado.

        service.goToPagina = goToPagina;
        service.next = next;
        service.prev = prev;

        return service;

        //Functions
        /**
         * @descripcion Agrega un producto al carrito. El producto es un extracto del producto total (cantidad, precio, producto_id, foto[0].url)
         * @param carrito_id
         * @param producto
         * @param callback
         */
        function addToCart(carrito_id, producto, callback) {
            /*
             var carrito_detalle = {
             carrito_id: carrito_id,
             producto_id: producto.producto_id,
             cantidad: producto.cantidad,
             en_oferta: producto.en_oferta,
             precio_unitario: producto.precio_unitario
             };
             */
            return $http.post(url,
                {
                    'function': 'createCarritoDetalle',
                    'carrito_id': carrito_id,
                    'carrito_detalle': JSON.stringify(producto)
                })
                .success(function (data) {


                    // Agrega un detalle al carrito y le avisa a todo el sistema para que se refresque
                    if (data != -1) {
                        //carrito_detalle.carrito_detalle_id = data;
                        //CartVars.carrito.push(carrito_detalle);
                        for (var i = 0; i < data.length; i++) {
                            CartVars.carrito.push(data[i]);
                        }
                        CartVars.broadcast();
                    }

                    CartVars.clearCache = true;
                    callback(data);
                })
                .error(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                });
        }

        /**
         * Modifica el detalle de un carrito
         * @param carrito_detalle
         * @param callback
         * @returns {*}
         */
        function updateProductInCart(carrito_detalle, callback) {
            return $http.post(url,
                {
                    'function': 'updateCarritoDetalle',
                    'carrito_detalle': JSON.stringify(carrito_detalle)
                })
                .success(function (data) {

                    // Agrega un detalle al carrito y le avisa a todo el sistema para que se refresque

                    if (data != -1) {
                        var index = 0;
                        for (var i = 0; i < CartVars.carrito.length; i++) {
                            if (CartVars.carrito[i].carrito_detalle_id == carrito_detalle.carrito_detalle_id) {

                                index = i;
                            }
                        }

                        CartVars.carrito[index] = {};
                        CartVars.carrito[index] = carrito_detalle;
                        CartVars.broadcast();
                    }


                    CartVars.clearCache = true;
                    callback(data);
                })
                .error(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                });
        }


        /**
         * Remueve un item del carrito
         * @param carrito_detalle_id
         * @param callback
         * @returns {*}
         */
        function removeFromCart(carrito_detalle_id, callback) {
            return $http.post(url,
                {
                    'function': 'removeCarritoDetalle',
                    'carrito_detalle_id': JSON.stringify(carrito_detalle_id)
                })
                .success(function (data) {

                    if (data != -1) {
                        //var index = 0;
                        for (var i = 0; i < CartVars.carrito.length; i++) {
                            for (var j = 0; j < carrito_detalle_id.length; j++) {
                                if (CartVars.carrito[i].carrito_detalle_id == carrito_detalle_id[j]) {
                                    //index = i;
                                    CartVars.carrito.splice(i, 1);
                                }
                            }
                        }

                        //CartVars.carrito.splice(index, 1);
                        CartVars.broadcast();
                    }

                    CartVars.clearCache = true;
                    callback(data);
                })
                .error(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                });
        }

        /**
         * Retorna el �ltimo carrito en estado Iniciado para el usuario seleccionado.
         * @param usuario_id
         * @param callback
         */
        function reloadLastCart(usuario_id, callback) {

            get(usuario_id, function (data) {
                MvUtils.getByParams('status', "0", "true", data, callback);
            });

        }

        /**
         * Cambia el estado a Pedido
         * @param carrito_id
         * @param callback
         */
        function checkOut(carrito_id, callback) {
            update({carrito_id: carrito_id, status: 1}, function (data) {
                callback(data);
            })
        }


        /**
         * @description Obtiene todos los carritos
         * @param usuario_id, en caso traer todos los carritos, debe ser -1; Est� as� para que si el m�dulo est� en la web, nunca llegue al cliente la lista completa de pedidos;
         * @param callback
         * @returns {*}
         */
        function get(usuario_id, callback) {
            var urlGet = url + '?function=getCarritos&usuario_id=' + usuario_id;
            var $httpDefaultCache = $cacheFactory.get('$http');
            var cachedData = [];


            // Verifica si existe el cache de Carritos
            if ($httpDefaultCache.get(urlGet) != undefined) {
                if (CartVars.clearCache) {
                    $httpDefaultCache.remove(urlGet);
                }
                else {
                    cachedData = $httpDefaultCache.get(urlGet);
                    callback(cachedData);
                    return;
                }
            }


            return $http.get(urlGet, {cache: true})
                .success(function (data) {
                    $httpDefaultCache.put(urlGet + usuario_id, data);
                    CartVars.clearCache = false;
                    CartVars.paginas = (data.length % CartVars.paginacion == 0) ? parseInt(data.length / CartVars.paginacion) : parseInt(data.length / CartVars.paginacion) + 1;
                    callback(data);
                })
                .error(function (data) {
                    callback(data);
                    CartVars.clearCache = false;
                })
        }


        /**
         * @description Retorna la lista filtrada de Carritos
         * @param params -> String, separado por comas (,) que contiene la lista de par�metros de b�squeda, por ej: nombre, sku
         * @param values
         * @param exact_match
         * @param usuario_id
         * @param callback
         */
        function getByParams(params, values, exact_match, usuario_id, callback) {
            get(usuario_id, function (data) {
                MvUtils.getByParams(params, values, exact_match, data, callback);
            })
        }


        /** @name: remove
         * @param carrito_id
         * @param callback
         * @description: Elimina el carrito seleccionado.
         */
        function remove(carrito_id, callback) {
            return $http.post(url,
                {function: 'removeCarrito', 'carrito_id': carrito_id})
                .success(function (data) {
                    //console.log(data);
                    if (data !== 'false') {
                        CartVars.clearCache = true;
                        callback(data);
                    }
                })
                .error(function (data) {
                    callback(data);
                })
        }

        /**
         * @description: Crea un carrito.
         * @param carrito
         * @param callback
         * @returns {*}
         */
        function create(carrito, callback) {

            return $http.post(url,
                {
                    'function': 'createCarrito',
                    'carrito': JSON.stringify(carrito)
                })
                .success(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                })
                .error(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                });
        }


        /** @name: update
         * @param carrito
         * @param callback
         * @description: Realiza update al carrito.
         */
        function update(carrito, callback) {
            return $http.post(url,
                {
                    'function': 'updateCarrito',
                    'carrito': JSON.stringify(carrito)
                })
                .success(function (data) {
                    CartVars.clearCache = true;
                    callback(data);
                })
                .error(function (data) {
                    callback(data);
                });
        }

        /**
         * Para el uso de la p�ginaci�n, definir en el controlador las siguientes variables:
         *
         vm.start = 0;
         vm.pagina = CartVars.pagina;
         CartVars.paginacion = 5; Cantidad de registros por p�gina
         vm.end = CartVars.paginacion;


         En el HTML, en el ng-repeat agregar el siguiente filtro: limitTo:appCtrl.end:appCtrl.start;

         Agregar un bot�n de next:
         <button ng-click="appCtrl.next()">next</button>

         Agregar un bot�n de prev:
         <button ng-click="appCtrl.prev()">prev</button>

         Agregar un input para la p�gina:
         <input type="text" ng-keyup="appCtrl.goToPagina()" ng-model="appCtrl.pagina">

         */


        /**
         * @description: Ir a p�gina
         * @param pagina
         * @returns {*}
         * uso: agregar un m�todo
         vm.goToPagina = function () {
                vm.start= CartService.goToPagina(vm.pagina).start;
            };
         */
        function goToPagina(pagina) {

            if (isNaN(pagina) || pagina < 1) {
                CartVars.pagina = 1;
                return CartVars;
            }

            if (pagina > CartVars.paginas) {
                CartVars.pagina = CartVars.paginas;
                return CartVars;
            }

            CartVars.pagina = pagina - 1;
            CartVars.start = CartVars.pagina * CartVars.paginacion;
            return CartVars;

        }

        /**
         * @name next
         * @description Ir a pr�xima p�gina
         * @returns {*}
         * uso agregar un metodo
         vm.next = function () {
                vm.start = CartService.next().start;
                vm.pagina = CartVars.pagina;
            };
         */
        function next() {

            if (CartVars.pagina + 1 > CartVars.paginas) {
                return CartVars;
            }
            CartVars.start = (CartVars.pagina * CartVars.paginacion);
            CartVars.pagina = CartVars.pagina + 1;
            //CartVars.end = CartVars.start + CartVars.paginacion;
            return CartVars;
        }

        /**
         * @name previous
         * @description Ir a p�gina anterior
         * @returns {*}
         * uso, agregar un m�todo
         vm.prev = function () {
                vm.start= CartService.prev().start;
                vm.pagina = CartVars.pagina;
            };
         */
        function prev() {


            if (CartVars.pagina - 2 < 0) {
                return CartVars;
            }

            //CartVars.end = CartVars.start;
            CartVars.start = (CartVars.pagina - 2 ) * CartVars.paginacion;
            CartVars.pagina = CartVars.pagina - 1;
            return CartVars;
        }


    }

    CartVars.$inject = ['$rootScope'];
    /**
     * @description Almacena variables temporales de Carritos
     * @param $rootScope
     * @constructor
     */
    function CartVars($rootScope) {
        // Cantidad de p�ginas total del recordset
        this.paginas = 1;
        // P�gina seleccionada
        this.pagina = 1;
        // Cantidad de registros por p�gina
        this.paginacion = 10;
        // Registro inicial, no es p�gina, es el registro
        this.start = 0;

        // Carrito Temporal
        this.carrito = [];
        // Total de productos
        this.carrito_cantidad_productos = function () {
            var cantidad = 0;
            for (var i = 0; i < this.carrito.length; i++) {
                cantidad = cantidad + this.carrito[i].cantidad;
            }
            return cantidad;
        };
        // Total precio
        this.carrito_total = function () {
            var precio = 0.0;
            for (var i = 0; i < this.carrito.length; i++) {
                precio = precio + (this.carrito[i].cantidad * this.carrito[i].precio_unitario);
            }
            return precio;
        };


        // Indica si se debe limpiar el cach� la pr�xima vez que se solicite un get
        this.clearCache = true;

        // Transmite el aviso de actualizaci�n
        this.broadcast = function () {
            $rootScope.$broadcast("CartVars")
        };

        // Recibe aviso de actualizaci�n
        this.listen = function (callback) {
            $rootScope.$on("CartVars", callback)
        };

    }




    ProductTypeService.$inject = ['$http', 'ProductTypeVars', '$cacheFactory', 'MvUtils', 'MvUtilsGlobals', 'ErrorHandler', '$q'];
    function ProductTypeService($http, ProductTypeVars, $cacheFactory, MvUtils, MvUtilsGlobals, ErrorHandler, $q) {
        //Variables
        var service = {};

        var url = currentScriptPath.replace('mv-productos.js', '/includes/mv-productos.php');

        //Function declarations
        service.get = get;

        service.create = create;
        service.update = update;
        service.remove = remove;
        service.save = save;

        service.goToPagina = goToPagina;
        service.next = next;
        service.prev = prev;

        return service;

        //Functions
        /**
         * @description Obtiene todos los tipos de productos
         * @param callback
         * @returns {*}
         */
        function get() {
            var urlGet = url + '?function=getProductosTipos';
            var $httpDefaultCache = $cacheFactory.get('$http');
            var cachedData = [];
            MvUtilsGlobals.startWaiting();

            // Verifica si existe el cache de categorias
            if ($httpDefaultCache.get(urlGet) != undefined) {
                if (ProductTypeVars.clearCache) {
                    $httpDefaultCache.remove(urlGet);
                }
                else {
                    var deferred = $q.defer();
                    cachedData = $httpDefaultCache.get(urlGet);
                    deferred.resolve(cachedData);
                    MvUtilsGlobals.stopWaiting();
                    return deferred.promise;
                }
            }

            return $http.get(urlGet, {cache: true})
                .then(function (response) {
                    $httpDefaultCache.put(urlGet, response.data);
                    ProductTypeVars.clearCache = false;
                    ProductTypeVars.paginas = (response.data.length % ProductTypeVars.paginacion == 0) ? parseInt(response.data.length / ProductTypeVars.paginacion) : parseInt(response.data.length / ProductTypeVars.paginacion) + 1;
                    MvUtilsGlobals.stopWaiting();
                    return response.data;
                })
                .catch(function (response) {
                    ProductTypeVars.clearCache = true;
                    MvUtilsGlobals.stopWaiting();
                    ErrorHandler(response);
                })
        }


        /** @name: remove
         * @param producto_tipo_id
         * @param callback
         * @description: Elimina el tipo de producto seleccionado
         */
        function remove(producto_tipo_id, callback) {
            return $http.post(url,
                {
                    'function': 'removeProductoTipo',
                    'producto_tipo_id': producto_tipo_id
                })
                .success(function (data) {
                    if (data !== 'false') {
                        ProductTypeVars.clearCache = true;
                        callback(data);
                    }
                })
                .error(function (data) {
                    callback(data);
                })
        }

        function save(productoTipo) {
            var deferred = $q.defer();

            if (productoTipo.producto_tipo_id != undefined) {
                deferred.resolve(update(productoTipo));
            } else {
                deferred.resolve(create(productoTipo));
            }
            return deferred.promise;
        }

        /**
         * @description: Crea un tipo de producto.
         * @param productoTipo
         * @returns {*}
         */
        function create(productoTipo) {
            return $http.post(url,
                {
                    'function': 'createProductoTipo',
                    'productoTipo': JSON.stringify(productoTipo)
                })
                .then(function (response) {
                    ProductTypeVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductTypeVars.clearCache = true;
                    ErrorHandler(response);
                });
        }


        /** @name: update
         * @param productoTipo
         * @description: Realiza update al tipo de producto.
         */
        function update(productoTipo) {
            return $http.post(url,
                {
                    'function': 'updateProductoTipo',
                    'productoTipo': JSON.stringify(productoTipo)
                })
                .then(function (response) {
                    ProductTypeVars.clearCache = true;
                    return response.data;
                })
                .catch(function (response) {
                    ProductTypeVars.clearCache = true;
                    ErrorHandler(response);
                });
        }

        /**
         * Para el uso de la paginacion, definir en el controlador las siguientes variables:
         *
         vm.start = 0;
         vm.pagina = ProductTypeVars.pagina;
         ProductTypeVars.paginacion = 5; Cantidad de registros por pagina
         vm.end = ProductTypeVars.paginacion;


         En el HTML, en el ng-repeat agregar el siguiente filtro: limitTo:appCtrl.end:appCtrl.start;

         Agregar un bot�n de next:
         <button ng-click="appCtrl.next()">next</button>

         Agregar un bot�n de prev:
         <button ng-click="appCtrl.prev()">prev</button>

         Agregar un input para la p�gina:
         <input type="text" ng-keyup="appCtrl.goToPagina()" ng-model="appCtrl.pagina">

         */


        /**
         * @description: Ir a p�gina
         * @param pagina
         * @returns {*}
         * uso: agregar un m�todo
         vm.goToPagina = function () {
                vm.start= ProductTypeService.goToPagina(vm.pagina).start;
            };
         */
        function goToPagina(pagina) {

            if (isNaN(pagina) || pagina < 1) {
                ProductTypeVars.pagina = 1;
                return ProductTypeVars;
            }

            if (pagina > ProductTypeVars.paginas) {
                ProductTypeVars.pagina = ProductTypeVars.paginas;
                return ProductTypeVars;
            }

            ProductTypeVars.pagina = pagina - 1;
            ProductTypeVars.start = ProductTypeVars.pagina * ProductTypeVars.paginacion;
            return ProductTypeVars;

        }

        /**
         * @name next
         * @description Ir a pr�xima p�gina
         * @returns {*}
         * uso agregar un metodo
         vm.next = function () {
                vm.start = CategoryService.next().start;
                vm.pagina = CategoryVars.pagina;
            };
         */
        function next() {

            if (ProductTypeVars.pagina + 1 > ProductTypeVars.paginas) {
                return ProductTypeVars;
            }
            ProductTypeVars.start = (ProductTypeVars.pagina * ProductTypeVars.paginacion);
            ProductTypeVars.pagina = ProductTypeVars.pagina + 1;
            //CategoryVars.end = CategoryVars.start + CategoryVars.paginacion;
            return ProductTypeVars;
        }

        /**
         * @name previous
         * @description Ir a p�gina anterior
         * @returns {*}
         * uso, agregar un m�todo
         vm.prev = function () {
                vm.start= CategoryService.prev().start;
                vm.pagina = CategoryVars.pagina;
            };
         */
        function prev() {


            if (ProductTypeVars.pagina - 2 < 0) {
                return ProductTypeVars;
            }

            //CategoryVars.end = CategoryVars.start;
            ProductTypeVars.start = (ProductTypeVars.pagina - 2 ) * ProductTypeVars.paginacion;
            ProductTypeVars.pagina = ProductTypeVars.pagina - 1;
            return ProductTypeVars;
        }


    }

    ProductTypeVars.$inject = [];
    /**
     * @description Almacena variables temporales de tipos de productos
     * @constructor
     */
    function ProductTypeVars() {
        // Cantidad de paginas total del recordset
        this.paginas = 1;
        // Pagina seleccionada
        this.pagina = 1;
        // Cantidad de registros por pagina
        this.paginacion = 10;
        // Registro inicial, no es pagina, es el registro
        this.start = 0;


        // Indica si se debe limpiar el cache la proxima vez que se solicite un get
        this.clearCache = true;

    }


})();