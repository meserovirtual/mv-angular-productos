<?php
session_start();


// Token

if (file_exists('../../../includes/MyDBi.php')) {
    require_once '../../../includes/MyDBi.php';
    require_once '../../../includes/utils.php';
} else {
    require_once 'MyDBi.php';
}


class Productos extends Main
{
    private static $instance;

    public static function init($decoded)
    {
        self::$instance = new Main(get_class(), $decoded['function']);
        try {
            call_user_func(get_class() . '::' . $decoded['function'], $decoded);
        } catch (Exception $e) {

            $file = 'error.log';
            $current = file_get_contents($file);
            $current .= date('Y-m-d H:i:s') . ": " . $e . "\n";
            file_put_contents($file, $current);

            header('HTTP/1.0 500 Internal Server Error');
            echo $e;
        }
    }

    /* @name: get
     * @param
     * @description: Obtiene todos los usuario con sus direcciones.
     * todo: Sacar dirección y crear sus propias clases dentro de este mismo módulo.
     */
    function getProductos()
    {
        $db = self::$instance->db;
        $results = $db->rawQuery('SELECT
    p.producto_id,
    p.nombre nombreProducto,
    p.descripcion,
    p.pto_repo,
    p.sku,
    p.status,
    p.vendidos,
    p.destacado,
    p.producto_tipo_id,
    p.en_slider,
    p.en_oferta,
    p.iva,
    c.categoria_id,
    c.nombre nombreCategoria,
    c.parent_id,
    c.status,
    ps.producto_kit_id,
    ps.producto_id productoKit,
    ps.producto_cantidad,
    (select nombre from productos where producto_id = ps.producto_id) nombreKit,
    pr.precio_id,
    pr.precio_tipo_id,
    pr.precio,
    ph.horario_id,
    ph.hora_desde,
    ph.hora_hasta,
    f.producto_foto_id,
    f.main,
    f.nombre nombreFoto,
    u.usuario_id,
    u.nombre nombreUsuario,
    u.apellido
FROM
    productos p
        LEFT JOIN
    productos_categorias pc ON p.producto_id = pc.producto_id
        LEFT JOIN
    categorias c ON c.categoria_id = pc.categoria_id
        LEFT JOIN
    precios pr ON p.producto_id = pr.producto_id
        LEFT JOIN
    precios_horario ph ON pr.precio_id = ph.precio_id
        LEFT JOIN
    productos_fotos f ON p.producto_id = f.producto_id
        LEFT JOIN
    productos_kits ps ON p.producto_id = ps.parent_id
        LEFT JOIN
    productos_proveedores pro ON pro.producto_id = p.producto_id
        LEFT JOIN
    usuarios u ON u.usuario_id = pro.proveedor_id
GROUP BY p.producto_id , p.nombre , p.descripcion , p.pto_repo , p.sku , p.status ,
p.vendidos , p.destacado , p.producto_tipo_id , p.en_slider , p.en_oferta , c.categoria_id ,
c.nombre , c.parent_id , ps.producto_kit_id , ps.producto_id , ps.producto_cantidad , pr.precio_id , pr.precio_tipo_id ,
pr.precio, ph.horario_id, ph.hora_desde, ph.hora_hasta, f.producto_foto_id, f.main, f.nombre, u.usuario_id, u.nombre, u.apellido;');

        $final = array();
        foreach ($results as $row) {

            if (!isset($final[$row["producto_id"]])) {
                $final[$row["producto_id"]] = array(
                    'producto_id' => $row["producto_id"],
                    'nombre' => $row["nombreProducto"],
                    'descripcion' => $row["descripcion"],
                    'pto_repo' => $row["pto_repo"],
                    'sku' => $row["sku"],
                    'status' => $row["status"],
                    'vendidos' => $row["vendidos"],
                    'destacado' => $row["destacado"],
                    'producto_tipo_id' => $row["producto_tipo_id"],
                    'en_slider' => $row["en_slider"],
                    'en_oferta' => $row["en_oferta"],
                    'iva' => $row["iva"],
                    'categorias' => array(),
                    'precios' => array(),
                    'fotos' => array(),
                    'kits' => array(),
                    'proveedores' => array()
                );
            }
            $have_cat = false;
            if ($row["categoria_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['categorias']) > 0) {
                    foreach ($final[$row['producto_id']]['categorias'] as $cat) {
                        if ($cat['categoria_id'] == $row["categoria_id"]) {
                            $have_cat = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['categorias'][] = array(
                        'categoria_id' => $row['categoria_id'],
                        'nombre' => $row['nombreCategoria'],
                        'parent_id' => $row['parent_id'],
                        'status' => $row['status']
                    );

                    $have_cat = true;
                }

                if (!$have_cat) {
                    array_push($final[$row['producto_id']]['categorias'], array(
                        'categoria_id' => $row['categoria_id'],
                        'nombre' => $row['nombreCategoria'],
                        'parent_id' => $row['parent_id'],
                        'status' => $row['status']
                    ));
                }
            }


            $have_pre = false;
            if ($row["precio_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['precios']) > 0) {
                    foreach ($final[$row['producto_id']]['precios'] as $cat) {
                        if ($cat['precio_id'] == $row["precio_id"]) {
                            $have_pre = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['precios'][] = array(
                        'precio_id' => $row['precio_id'],
                        'precio_tipo_id' => $row['precio_tipo_id'],
                        'precio' => $row['precio'],
                        'horario_id' => $row['horario_id'],
                        'hora_desde' => $row['hora_desde'],
                        'hora_hasta' => $row['hora_hasta']
                    );

                    $have_pre = true;
                }

                if (!$have_pre) {
                    array_push($final[$row['producto_id']]['precios'], array(
                        'precio_id' => $row['precio_id'],
                        'precio_tipo_id' => $row['precio_tipo_id'],
                        'precio' => $row['precio'],
                        'horario_id' => $row['horario_id'],
                        'hora_desde' => $row['hora_desde'],
                        'hora_hasta' => $row['hora_hasta']
                    ));
                }
            }


            $have_fot = false;
            if ($row["producto_foto_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['fotos']) > 0) {
                    foreach ($final[$row['producto_id']]['fotos'] as $cat) {
                        if ($cat['producto_foto_id'] == $row["producto_foto_id"]) {
                            $have_fot = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['fotos'][] = array(
                        'producto_foto_id' => $row['producto_foto_id'],
                        'nombre' => $row['nombreFoto'],
                        'main' => $row['main']
                    );

                    $have_fot = true;
                }

                if (!$have_fot) {
                    array_push($final[$row['producto_id']]['fotos'], array(
                        'producto_foto_id' => $row['producto_foto_id'],
                        'nombre' => $row['nombreFoto'],
                        'main' => $row['main']
                    ));
                }
            }

            $have_kit = false;
            if ($row["producto_kit_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['kits']) > 0) {
                    foreach ($final[$row['producto_id']]['kits'] as $cat) {
                        if ($cat['producto_kit_id'] == $row["producto_kit_id"]) {
                            $have_kit = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['kits'][] = array(
                        'producto_kit_id' => $row['producto_kit_id'],
                        'nombre' => $row['nombreKit'],
                        'producto_id' => $row['productoKit'],
                        'producto_cantidad' => $row['producto_cantidad']
                    );

                    $have_kit = true;
                }

                if (!$have_kit) {
                    array_push($final[$row['producto_id']]['kits'], array(
                        'producto_kit_id' => $row['producto_kit_id'],
                        'nombre' => $row['nombreKit'],
                        'producto_id' => $row['productoKit'],
                        'producto_cantidad' => $row['producto_cantidad']
                    ));
                }
            }


            $have_pro = false;
            if ($row["usuario_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['proveedores']) > 0) {
                    foreach ($final[$row['producto_id']]['proveedores'] as $cat) {
                        if ($cat['usuario_id'] == $row["usuario_id"]) {
                            $have_pro = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['proveedores'][] = array(
                        'usuario_id' => $row['usuario_id'],
                        'nombre' => $row['nombreUsuario'],
                        'apellido' => $row['apellido']
                    );

                    $have_pro = true;
                }

                if (!$have_pro) {
                    array_push($final[$row['producto_id']]['proveedores'], array(
                        'usuario_id' => $row['usuario_id'],
                        'nombre' => $row['nombreUsuario'],
                        'apellido' => $row['apellido']
                    ));
                }
            }
        }
        echo json_encode(array_values($final));
    }


    /* @name: get
     * @param
     * @description: Obtiene todos los usuario con sus direcciones.
     * todo: Sacar dirección y crear sus propias clases dentro de este mismo módulo.
     */
    function getProductosCliente()
    {
        $db = self::$instance->db;
        $results = $db->rawQuery('SELECT
    p.producto_id,
    p.nombre nombreProducto,
    p.descripcion,
    p.pto_repo,
    p.sku,
    p.status,
    p.vendidos,
    p.destacado,
    p.producto_tipo_id,
    p.en_slider,
    p.en_oferta,
    p.iva,
    c.categoria_id,
    c.nombre nombreCategoria,
    c.parent_id,
    c.status,
    ps.producto_kit_id,
    ps.producto_id productoKit,
    ps.producto_cantidad,
    (select nombre from productos where producto_id = ps.producto_id) nombreKit,
    ps.opcional,
    ps.precio,
    pr.precio_id,
    pr.precio_tipo_id,
    pr.precio,
    ph.horario_id,
    ph.hora_desde,
    ph.hora_hasta,
    f.producto_foto_id,
    f.main,
    f.nombre nombreFoto,
    u.usuario_id,
    u.nombre nombreUsuario,
    u.apellido
FROM
    productos p
        LEFT JOIN
    productos_categorias pc ON p.producto_id = pc.producto_id
        LEFT JOIN
    categorias c ON c.categoria_id = pc.categoria_id
        LEFT JOIN
    precios pr ON p.producto_id = pr.producto_id
        LEFT JOIN
    precios_horario ph ON pr.precio_id = ph.precio_id
        LEFT JOIN
    productos_fotos f ON p.producto_id = f.producto_id
        LEFT JOIN
    productos_kits ps ON p.producto_id = ps.parent_id
        LEFT JOIN
    productos_proveedores pro ON pro.producto_id = p.producto_id
        LEFT JOIN
    usuarios u ON u.usuario_id = pro.proveedor_id
WHERE ph.hora_desde < CAST("' . date('H:i') . '" AS time) AND ph.hora_hasta > CAST("' . date('H:i') . '" AS time)    
GROUP BY p.producto_id , p.nombre , p.descripcion , p.pto_repo , p.sku , p.status ,
p.vendidos , p.destacado , p.producto_tipo_id , p.en_slider , p.en_oferta , c.categoria_id ,
c.nombre , c.parent_id , ps.producto_kit_id , ps.producto_id , ps.producto_cantidad , pr.precio_id , pr.precio_tipo_id ,
pr.precio, ph.horario_id, ph.hora_desde, ph.hora_hasta, f.producto_foto_id, f.main, f.nombre, u.usuario_id, u.nombre, u.apellido;');




        $final = array();
        foreach ($results as $row) {

            if (!isset($final[$row["producto_id"]])) {
                $final[$row["producto_id"]] = array(
                    'producto_id' => $row["producto_id"],
                    'nombre' => $row["nombreProducto"],
                    'descripcion' => $row["descripcion"],
                    'pto_repo' => $row["pto_repo"],
                    'sku' => $row["sku"],
                    'status' => $row["status"],
                    'vendidos' => $row["vendidos"],
                    'destacado' => $row["destacado"],
                    'producto_tipo_id' => $row["producto_tipo_id"],
                    'en_slider' => $row["en_slider"],
                    'en_oferta' => $row["en_oferta"],
                    'iva' => $row["iva"],
                    'categorias' => array(),
                    'precios' => array(),
                    'fotos' => array(),
                    'kits' => array(),
                    'proveedores' => array()
                );
            }
            $have_cat = false;
            if ($row["categoria_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['categorias']) > 0) {
                    foreach ($final[$row['producto_id']]['categorias'] as $cat) {
                        if ($cat['categoria_id'] == $row["categoria_id"]) {
                            $have_cat = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['categorias'][] = array(
                        'categoria_id' => $row['categoria_id'],
                        'nombre' => $row['nombreCategoria'],
                        'parent_id' => $row['parent_id'],
                        'status' => $row['status']
                    );

                    $have_cat = true;
                }

                if (!$have_cat) {
                    array_push($final[$row['producto_id']]['categorias'], array(
                        'categoria_id' => $row['categoria_id'],
                        'nombre' => $row['nombreCategoria'],
                        'parent_id' => $row['parent_id'],
                        'status' => $row['status']
                    ));
                }
            }


            $have_pre = false;
            if ($row["precio_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['precios']) > 0) {
                    foreach ($final[$row['producto_id']]['precios'] as $cat) {
                        if ($cat['precio_id'] == $row["precio_id"]) {
                            $have_pre = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['precios'][] = array(
                        'precio_id' => $row['precio_id'],
                        'precio_tipo_id' => $row['precio_tipo_id'],
                        'precio' => $row['precio'],
                        'horario_id' => $row['horario_id'],
                        'hora_desde' => $row['hora_desde'],
                        'hora_hasta' => $row['hora_hasta']
                    );

                    $have_pre = true;
                }

                if (!$have_pre) {
                    array_push($final[$row['producto_id']]['precios'], array(
                        'precio_id' => $row['precio_id'],
                        'precio_tipo_id' => $row['precio_tipo_id'],
                        'precio' => $row['precio'],
                        'horario_id' => $row['horario_id'],
                        'hora_desde' => $row['hora_desde'],
                        'hora_hasta' => $row['hora_hasta']
                    ));
                }
            }


            $have_fot = false;
            if ($row["producto_foto_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['fotos']) > 0) {
                    foreach ($final[$row['producto_id']]['fotos'] as $cat) {
                        if ($cat['producto_foto_id'] == $row["producto_foto_id"]) {
                            $have_fot = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['fotos'][] = array(
                        'producto_foto_id' => $row['producto_foto_id'],
                        'nombre' => $row['nombreFoto'],
                        'main' => $row['main']
                    );

                    $have_fot = true;
                }

                if (!$have_fot) {
                    array_push($final[$row['producto_id']]['fotos'], array(
                        'producto_foto_id' => $row['producto_foto_id'],
                        'nombre' => $row['nombreFoto'],
                        'main' => $row['main']
                    ));
                }
            }

            $have_kit = false;
            if ($row["producto_kit_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['kits']) > 0) {
                    foreach ($final[$row['producto_id']]['kits'] as $cat) {
                        if ($cat['producto_kit_id'] == $row["producto_kit_id"]) {
                            $have_kit = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['kits'][] = array(
                        'producto_kit_id' => $row['producto_kit_id'],
                        'nombre' => $row['nombreKit'],
                        'producto_id' => $row['productoKit'],
                        'producto_cantidad' => $row['producto_cantidad'],
                        'opcional' => $row['opcional'],
                        'precio' => $row['precio']
                    );

                    $have_kit = true;
                }

                if (!$have_kit) {
                    array_push($final[$row['producto_id']]['kits'], array(
                        'producto_kit_id' => $row['producto_kit_id'],
                        'nombre' => $row['nombreKit'],
                        'producto_id' => $row['productoKit'],
                        'producto_cantidad' => $row['producto_cantidad'],
                        'opcional' => $row['opcional'],
                        'precio' => $row['precio']
                    ));
                }
            }


            $have_pro = false;
            if ($row["usuario_id"] !== null) {

                if (sizeof($final[$row['producto_id']]['proveedores']) > 0) {
                    foreach ($final[$row['producto_id']]['proveedores'] as $cat) {
                        if ($cat['usuario_id'] == $row["usuario_id"]) {
                            $have_pro = true;
                        }
                    }
                } else {
                    $final[$row['producto_id']]['proveedores'][] = array(
                        'usuario_id' => $row['usuario_id'],
                        'nombre' => $row['nombreUsuario'],
                        'apellido' => $row['apellido']
                    );

                    $have_pro = true;
                }

                if (!$have_pro) {
                    array_push($final[$row['producto_id']]['proveedores'], array(
                        'usuario_id' => $row['usuario_id'],
                        'nombre' => $row['nombreUsuario'],
                        'apellido' => $row['apellido']
                    ));
                }
            }
        }
        echo json_encode(array_values($final));
    }

    /**
     * @descr Obtiene las categorias
     */
    function getCategorias()
    {
        $db = self::$instance->db;
        $results = $db->rawQuery('SELECT c.*, (select count(producto_id) from productos_categorias p where p.categoria_id= c.categoria_id) total, d.nombre nombrePadre FROM categorias c LEFT JOIN categorias d ON c.parent_id = d.categoria_id;');
        echo json_encode($results);
    }

    /**
     *
     */
    function getProductosTipos()
    {
        $db = self::$instance->db;
        $results = $db->rawQuery('SELECT producto_tipo_id, nombre, disponible_para_venta, control_stock, compuesto, status FROM productos_tipo;');
        echo json_encode($results);
    }

    /**
     * @description Crea un producto, sus fotos, precios y le asigna las categorias
     * @param $product
     */
    function createProducto($params)
    {

        $db = self::$instance->db;
        $product_decoded = self::checkProducto(json_decode($params["producto"]));

        $SQL = 'Select producto_id from productos where nombre ="' . $product_decoded->nombre . '"';

        $result = $db->rawQuery($SQL);

        if ($db->count > 0) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'Existe un producto con ese nombre';
            return;
        }

        $db = self::$instance->db;
        $db->startTransaction();

        $data = array(
            'nombre' => $product_decoded->nombre,
            'descripcion' => $product_decoded->descripcion,
            'pto_repo' => $product_decoded->pto_repo,
            'sku' => $product_decoded->sku,
            'status' => $product_decoded->status,
            'vendidos' => $product_decoded->vendidos,
            'destacado' => $product_decoded->destacado,
            'en_slider' => $product_decoded->en_slider,
            'en_oferta' => $product_decoded->en_oferta,
            'producto_tipo_id' => $product_decoded->producto_tipo_id,
            'iva' => $product_decoded->iva
        );

        $result = $db->insert('productos', $data);
        if ($result > -1) {
            /*
            foreach ($product_decoded->precios as $precio) {
                if (!self::createPrecios($precio, $result, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }
            */
            foreach ($product_decoded->categorias as $categoria) {
                if (!self::createCategorias($categoria, $result, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }
            foreach ($product_decoded->fotos as $foto) {
                if (!self::createFotos($foto, $result, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }
            foreach ($product_decoded->proveedores as $proveedor) {
                if (!self::createProveedores($proveedor, $result, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }

            // Solo para cuando es kit
            if ($product_decoded->producto_tipo_id == 2) {
                foreach ($product_decoded->kits as $kit) {
                    if (!self::createKits($kit, $result, $db)) {
                        $db->rollback();
                        header('HTTP/1.0 500 Internal Server Error');
                        echo $db->getLastError();
                        return;
                    }
                }
            }


            $db->commit();
            header('HTTP/1.0 200 Ok');
            echo json_encode($result);
        } else {
            $db->rollback();
            header('HTTP/1.0 500 Internal Server Error');
            echo $db->getLastError();
        }
    }

    /**
     * @description Crea un precio para un producto determinado
     * @param $precio
     * @param $producto_id
     * @param $db
     * @return bool
     */
    function createPrecios($precio, $producto_id, $db)
    {
        $data = array(
            'precio_tipo_id' => $precio->precio_tipo_id,
            'producto_id' => $producto_id,
            'precio' => $precio->precio
        );
        $pre = $db->insert('precios', $data);
        return ($pre > -1) ? true : false;
    }

    /**
     * @description Creo el precio por hora
     * @param $precio_id
     * @param $horario
     * @param $db
     * @return bool
     */
    function createPrecioPorHorario($params)
    {
        $db = self::$instance->db;
        $product_decoded = self::checkProducto(json_decode($params["producto"]));
        $success = true;
        $db->startTransaction();

        foreach ($product_decoded->precios as $precio) {
            $precio_decoded = self::checkPrecios($precio);

            $data = array(
                'precio_tipo_id' => $precio_decoded->precio_tipo_id,
                'producto_id' => $product_decoded->producto_id,
                'precio' => $precio_decoded->precio
            );
            $precio_id = $db->insert('precios', $data);

            if ($precio_id > -1) {
                $horario_decoded = self::checkPrecioHorario($precio);

                $data = array(
                    'precio_id' => $precio_id,
                    'hora_desde' => $horario_decoded->hora_desde,
                    'hora_hasta' => $horario_decoded->hora_hasta
                );
                $horario_id = $db->insert('precios_horario', $data);

                if ($horario_id > -1) {
                    $db->commit();
                    header('HTTP/1.0 200 Ok');
                    //echo json_encode($horario_id);
                } else {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    $success = false;
                    echo $db->getLastError();
                }
            } else {
                $db->rollback();
                header('HTTP/1.0 500 Internal Server Error');
                $success = false;
                echo $db->getLastError();
            }
        }

        return $success;

    }

    /**
     * @description Crea la relación entre un producto y una categoría
     * @param $categoria
     * @param $producto_id
     * @param $db
     * @return bool
     */
    function createCategorias($categoria, $producto_id, $db)
    {
        $data = array(
            'categoria_id' => $categoria->categoria_id,
            'producto_id' => $producto_id
        );

        $cat = $db->insert('productos_categorias', $data);
        return ($cat > -1) ? true : false;
    }

    /**
     * @param $params
     */
    function createCategoria($params)
    {
        $db = self::$instance->db;
        $categoria_decoded = self::checkCategoria(json_decode($params["categoria"]));
        $error = false;
        $message = '';

        $SQL = 'Select categoria_id from categorias where nombre ="' . $categoria_decoded->nombre . '"';
        $db->rawQuery($SQL);

        if ($db->count > 0) {
            //header('HTTP/1.0 500 Internal Server Error');
            //echo 'Existe una categoria con ese nombre';
            //return;
            $message = 'Existe una categoria con ese nombre';
            $error = true;
        }

        if (!$error) {
            $db = self::$instance->db;
            $db->startTransaction();
            $categoria_decoded = self::checkCategoria(json_decode($params["categoria"]));

            $data = array(
                'nombre' => $categoria_decoded->nombre,
                'parent_id' => $categoria_decoded->parent_id,
                'status' => $categoria_decoded->status
            );

            $result = $db->insert('categorias', $data);
            if ($result > -1) {
                $db->commit();
                header('HTTP/1.0 200 Ok');
                //echo json_encode($result);
                $message = 'La operación se realizo satisfactoriamente';
                $error = false;
            } else {
                $db->rollback();
                //header('HTTP/1.0 500 Internal Server Error');
                //echo $db->getLastError();
                $db->getLastError();
                $message = 'Error guardando el dato';
                $error = true;
            }
        }

        echo json_encode(['error' => $error, 'message' => $message]);

    }

    /**
     * @description Crea una foto para un producto determinado, main == 1 significa que la foto es la principal
     * @param $foto
     * @param $producto_id
     * @param $db
     * @return bool
     */
    function createFotos($foto, $producto_id, $db)
    {
        $data = array(
            'main' => $foto->main,
            'nombre' => $foto->nombre,
            'producto_id' => $producto_id
        );

        $fot = $db->insert('productos_fotos', $data);
        return ($fot > -1) ? true : false;
    }

    /**
     * @description Crea una relación entre producto y proveedor
     * @param $proveedor_id
     * @param $producto_id
     * @param $db
     * @return bool
     */
    function createProveedores($proveedor, $producto_id, $db)
    {
        $data = array(
            'proveedor_id' => $proveedor->proveedor_id,
            'producto_id' => $producto_id
        );

        $pro = $db->insert('productos_proveedores', $data);
        return ($pro > -1 || $pro) ? true : false;
    }

    /**
     * @description Crea la agrupación de productos que representan al kit
     * @param $kit
     * @param $producto_id
     * @param $db
     * @return bool
     */
    function createKits($kit, $producto_id, $db)
    {
        $data = array(
            'producto_cantidad' => $kit->producto_cantidad,
            'producto_id' => $kit->producto_id,
            'parent_id' => $producto_id
        );

        $kit = $db->insert('productos_kits', $data);
        return ($kit > -1) ? true : false;
    }

    /**
     * @param $params
     */
    function createProductoTipo($params)
    {
        $db = self::$instance->db;
        $producto_tipo_decoded = self::checkProductoTipo(json_decode($params["productoTipo"]));
        $error = false;
        $message = '';

        $SQL = 'Select producto_tipo_id from productos_tipo where nombre ="' . $producto_tipo_decoded->nombre . '"';
        $db->rawQuery($SQL);

        if ($db->count > 0) {
            //header('HTTP/1.0 500 Internal Server Error');
            //echo 'Existe una categoria con ese nombre';
            //return;
            $message = 'Existe un Tipo de Producto con ese nombre';
            $error = true;
        }

        if (!$error) {
            $db = self::$instance->db;
            $db->startTransaction();
            $producto_tipo_decoded = self::checkProductoTipo(json_decode($params["productoTipo"]));

            $data = array(
                'nombre' => $producto_tipo_decoded->nombre,
                'disponible_para_venta' => $producto_tipo_decoded->disponible_para_venta,
                'control_stock' => $producto_tipo_decoded->control_stock,
                'compuesto' => $producto_tipo_decoded->compuesto,
                'status' => $producto_tipo_decoded->status,
            );

            $result = $db->insert('productos_tipo', $data);
            if ($result > -1) {
                $db->commit();
                header('HTTP/1.0 200 Ok');
                //echo json_encode($result);
                $message = 'La operación se realizo satisfactoriamente';
                $error = false;
            } else {
                $db->rollback();
                //header('HTTP/1.0 500 Internal Server Error');
                //echo $db->getLastError();
                $db->getLastError();
                $message = 'Error guardando el dato';
                $error = true;
            }
        }

        echo json_encode(['error' => $error, 'message' => $message]);

    }

    /**
     * @description Modifica un producto, sus fotos, precios y le asigna las categorias
     * @param $product
     */
    function updateProducto($params)
    {
        $db = self::$instance->db;
        $product_decoded = self::checkProducto(json_decode($params["producto"]));

        $SQL = 'Select producto_id from productos where nombre ="' . $product_decoded->nombre . '" AND producto_id != "' . $product_decoded->producto_id . '"';
        $db->rawQuery($SQL);

        if ($db->count > 0) {
            header('HTTP/1.0 500 Internal Server Error');
            echo 'Existe un producto con ese nombre';
            return;
            //$resultado = array('success'=>false, 'message'=>'');
            //return json_encode($resultado);
        }

        $db = self::$instance->db;
        $db->startTransaction();
        $product_decoded = self::checkProducto(json_decode($params["producto"]));


        $db->where('producto_id', $product_decoded->producto_id);
        $data = array(
            'nombre' => $product_decoded->nombre,
            'descripcion' => $product_decoded->descripcion,
            'pto_repo' => $product_decoded->pto_repo,
            'sku' => $product_decoded->sku,
            'status' => $product_decoded->status,
            'vendidos' => $product_decoded->vendidos,
            'destacado' => $product_decoded->destacado,
            'en_slider' => $product_decoded->en_slider,
            'en_oferta' => $product_decoded->en_oferta,
            'producto_tipo_id' => $product_decoded->producto_tipo_id,
            'iva' => $product_decoded->iva
        );

        $result = $db->update('productos', $data);

        /*
        foreach ($product_decoded->precios as $precio) {
            $db->where('precio_id', $precio->precio_id);
            $db->delete('precios_horario');
        }

        $db->where('producto_id', $product_decoded->producto_id);
        $db->delete('precios');
        */

        $db->where('producto_id', $product_decoded->producto_id);
        $db->delete('productos_fotos');

        $db->where('producto_id', $product_decoded->producto_id);
        $db->delete('productos_categorias');

        $db->where('parent_id', $product_decoded->producto_id);
        $db->delete('productos_kits');

        $db->where('producto_id', $product_decoded->producto_id);
        $db->delete('productos_proveedores');

        if ($result) {
            /*
            foreach ($product_decoded->precios as $precio) {
                if (!self::createPrecios($precio, $product_decoded->producto_id, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }
            */
            foreach ($product_decoded->categorias as $categoria) {
                if (!self::createCategorias($categoria, $product_decoded->producto_id, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }
            foreach ($product_decoded->fotos as $foto) {

                if (!self::createFotos($foto, $product_decoded->producto_id, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }

            foreach ($product_decoded->proveedores as $proveedor) {
                if (!self::createProveedores($proveedor, $product_decoded->producto_id, $db)) {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    echo $db->getLastError();
                    return;
                }
            }

            // Solo para cuando es kit
            if ($product_decoded->producto_tipo_id == 2) {
                foreach ($product_decoded->kits as $producto_kit) {
                    if (!self::createKits($producto_kit, $product_decoded->producto_id, $db)) {
                        $db->rollback();
                        echo json_encode(-1);
                        return;
                    }
                }
            }

            $db->commit();
            header('HTTP/1.0 200 Ok');
            echo json_encode($result);
        } else {
            $db->rollback();
            header('HTTP/1.0 500 Internal Server Error');
            echo $db->getLastError();
        }
    }

    /**
     * @param $params
     * @return bool
     */
    function updatePrecioPorHorario($params)
    {
        $db = self::$instance->db;
        $product_decoded = self::checkProducto(json_decode($params["producto"]));
        $success = true;
        $db->startTransaction();

        foreach ($product_decoded->precios as $precio) {
            $precio_decoded = self::checkPrecios($precio);

            $db->where('precio_id', $precio->precio_id);
            $data = array(
                'precio_tipo_id' => $precio_decoded->precio_tipo_id,
                'producto_id' => $product_decoded->producto_id,
                'precio' => $precio_decoded->precio
            );

            $result = $db->update('precios', $data);
            if ($result) {
                $horario_decoded = self::checkPrecioHorario($precio);

                $db->where('horario_id', $precio->horario_id);
                $data = array(
                    'precio_id' => $precio->precio_id,
                    'hora_desde' => $horario_decoded->hora_desde,
                    'hora_hasta' => $horario_decoded->hora_hasta
                );
                $result = $db->update('precios_horario', $data);

                if ($result) {
                    $db->commit();
                    header('HTTP/1.0 200 Ok');
                    //echo json_encode($horario_id);
                } else {
                    $db->rollback();
                    header('HTTP/1.0 500 Internal Server Error');
                    $success = false;
                    echo $db->getLastError();
                }
            } else {
                $db->rollback();
                header('HTTP/1.0 500 Internal Server Error');
                $success = false;
                echo $db->getLastError();
            }
        }

        return $success;

    }

    /**
     * @param $params
     */
    function updateCategoria($params)
    {
        $db = self::$instance->db;
        $categoria_decoded = self::checkCategoria(json_decode($params["categoria"]));
        $error = false;
        $message = '';

        $SQL = 'Select categoria_id from categorias where nombre ="' . $categoria_decoded->nombre . '" AND categoria_id != "' . $categoria_decoded->categoria_id . '"';
        $db->rawQuery($SQL);

        if ($db->count > 0) {
            //header('HTTP/1.0 500 Internal Server Error');
            //echo 'Existe una sucursal con ese nombre';
            //return;
            $message = 'Existe una categoria con ese nombre';
            $error = true;
        }

        if (!$error) {
            $db = self::$instance->db;
            $db->startTransaction();
            $categoria_decoded = self::checkCategoria(json_decode($params["categoria"]));

            $db->where('categoria_id', $categoria_decoded->categoria_id);
            $data = array(
                'nombre' => $categoria_decoded->nombre,
                'parent_id' => $categoria_decoded->parent_id,
                'status' => $categoria_decoded->status
            );

            $result = $db->update('categorias', $data);
            if ($result) {
                $db->commit();
                header('HTTP/1.0 200 Ok');
                //echo json_encode($result);
                $message = 'La operación se realizo satisfactoriamente';
                $error = false;
            } else {
                $db->rollback();
                //header('HTTP/1.0 500 Internal Server Error');
                //echo $db->getLastError();
                $db->getLastError();
                $message = 'Error guardando el dato';
                $error = true;
            }
        }

        echo json_encode(['error' => $error, 'message' => $message]);

    }

    /**
     * @param $params
     */
    function updateProductoTipo($params)
    {
        $db = self::$instance->db;
        $producto_tipo_decoded = self::checkProductoTipo(json_decode($params["productoTipo"]));
        $error = false;
        $message = '';

        $SQL = 'Select producto_tipo_id from productos_tipo where nombre ="' . $producto_tipo_decoded->nombre . '" AND producto_tipo_id != "' . $producto_tipo_decoded->producto_tipo_id . '"';
        $db->rawQuery($SQL);

        if ($db->count > 0) {
            //header('HTTP/1.0 500 Internal Server Error');
            //echo 'Existe una sucursal con ese nombre';
            //return;
            $message = 'Existe un Tipo de Producto con ese nombre';
            $error = true;
        }

        if (!$error) {
            $db = self::$instance->db;
            $db->startTransaction();
            $producto_tipo_decoded = self::checkProductoTipo(json_decode($params["productoTipo"]));

            $db->where('producto_tipo_id', $producto_tipo_decoded->producto_tipo_id);
            $data = array(
                'nombre' => $producto_tipo_decoded->nombre,
                'disponible_para_venta' => $producto_tipo_decoded->disponible_para_venta,
                'control_stock' => $producto_tipo_decoded->control_stock,
                'compuesto' => $producto_tipo_decoded->compuesto,
                'status' => $producto_tipo_decoded->status
            );

            $result = $db->update('productos_tipo', $data);
            if ($result) {
                $db->commit();
                header('HTTP/1.0 200 Ok');
                //echo json_encode($result);
                $message = 'La operación se realizo satisfactoriamente';
                $error = false;
            } else {
                $db->rollback();
                //header('HTTP/1.0 500 Internal Server Error');
                //echo $db->getLastError();
                $db->getLastError();
                $message = 'Error guardando el dato';
                $error = true;
            }
        }

        echo json_encode(['error' => $error, 'message' => $message]);

    }

    function removeProducto($params)
    {
        $db = self::$instance->db;
        $db->startTransaction();

        //borro las fotos
        $db->where("producto_id", $params["producto_id"]);
        $result = $db->delete('productos_fotos');

        //borro precios
        $db->where("producto_id", $params["producto_id"]);
        $result = $db->delete('precios');

        //borro los kit de un producto
        $db->where("parent_id", $params["producto_id"]);
        $result = $db->delete('productos_kits');

        //borro productos
        $db->where("producto_id", $params["producto_id"]);
        $result = $db->delete('productos');

        if ($result) {
            $db->commit();
            header('HTTP/1.0 200 Ok');
            echo json_encode($result);
        } else {
            $db->rollback();
            header('HTTP/1.0 500 Internal Server Error');
            echo $db->getLastError();
        }
    }


    /**
     * @param $params
     */
    function removeCategoria($params)
    {
        $db = self::$instance->db;

        $db->where("categoria_id", $params["categoria_id"]);
        $results = $db->delete('categorias');

        if ($results) {
            echo json_encode(1);
        } else {
            echo json_encode(-1);
        }
    }

    /**
     * @param $params
     */
    function removeProductoTipo($params)
    {
        $db = self::$instance->db;

        $db->where("producto_tipo_id", $params["producto_tipo_id"]);
        $results = $db->delete('productos_tipo');

        if ($results) {
            echo json_encode(1);
        } else {
            echo json_encode(-1);
        }
    }

    /**
     * @description Verifica todos los campos de producto para que existan
     * @param $producto
     * @return mixed
     */
    function checkProducto($producto)
    {


        $producto->nombre = (!array_key_exists("nombre", $producto)) ? '' : $producto->nombre;
        $producto->descripcion = (!array_key_exists("descripcion", $producto)) ? '' : $producto->descripcion;
        $producto->pto_repo = (!array_key_exists("pto_repo", $producto)) ? 0 : $producto->pto_repo;
        $producto->sku = (!array_key_exists("sku", $producto)) ? '' : $producto->sku;
        $producto->status = (!array_key_exists("status", $producto)) ? 1 : $producto->status;
        $producto->vendidos = (!array_key_exists("vendidos", $producto)) ? 0 : $producto->vendidos;
        $producto->destacado = (!array_key_exists("destacado", $producto)) ? 0 : $producto->destacado;
        $producto->en_slider = (!array_key_exists("en_slider", $producto)) ? 0 : $producto->en_slider;
        $producto->en_oferta = (!array_key_exists("en_oferta", $producto)) ? 0 : $producto->en_oferta;
        $producto->producto_tipo_id = (!array_key_exists("producto_tipo_id", $producto)) ? 0 : $producto->producto_tipo_id;
        $producto->iva = (!array_key_exists("iva", $producto)) ? 0.0 : $producto->iva;
        $producto->precios = (!array_key_exists("precios", $producto)) ? array() : self::checkPrecios($producto->precios);
        $producto->fotos = (!array_key_exists("fotos", $producto)) ? array() : self::checkFotos($producto->fotos);
        $producto->categorias = (!array_key_exists("categorias", $producto)) ? array() : self::checkCategorias($producto->categorias);
        $producto->proveedores = (!array_key_exists("proveedores", $producto)) ? array() : self::checkProductosProveedores($producto->proveedores);

        // Ejecuta la verificación solo si es kit
        if ($producto->producto_tipo_id == 2) {
            $producto->kits = (!array_key_exists("kits", $producto)) ? array() : self::checkProductosKit($producto->kits);
        }
        return $producto;
    }

    /**
     * @description Verifica todos los campos de Productos en un kit para que existan
     * @param $productos_kit
     * @return mixed
     */
    function checkProductosKit($productos_kit)
    {
        $productos_kit->producto_id = (!array_key_exists("producto_id", $productos_kit)) ? 0 : $productos_kit->producto_id;
        $productos_kit->parent_id = (!array_key_exists("parent_id", $productos_kit)) ? 0 : $productos_kit->parent_id;
        $productos_kit->producto_cantidad = (!array_key_exists("producto_cantidad", $productos_kit)) ? '' : $productos_kit->producto_cantidad;

        return $productos_kit;
    }

    /**
     * @description Verifica todos los campos de Proveedores y Productos existan
     * @param $productos_proveedores
     * @return mixed
     */
    function checkProductosProveedores($productos_proveedores)
    {
        foreach ($productos_proveedores as $producto_proveedor) {
            $producto_proveedor->producto_id = (!array_key_exists("producto_id", $producto_proveedor)) ? 0 : $producto_proveedor->producto_id;
            $producto_proveedor->proveedor_id = (!array_key_exists("proveedor_id", $producto_proveedor)) ? '' : $producto_proveedor->proveedor_id;
        }
        return $productos_proveedores;
    }

    /**
     * @description Verifica todos los campos de fotos para que existan
     * @param $fotos
     * @return mixed
     */
    function checkFotos($fotos)
    {
        foreach ($fotos as $foto) {
            $foto->producto_id = (!array_key_exists("producto_id", $foto)) ? 0 : $foto->producto_id;
            $foto->nombre = (!array_key_exists("nombre", $foto)) ? '' : $foto->nombre;
            $foto->main = (!array_key_exists("main", $foto)) ? 0 : $foto->main;
        }
        return $fotos;
    }

    /**
     * @description Verifica todos los campos de precios para que existan
     * @param $precios
     * @return mixed
     */
    function checkPrecios($precios)
    {
        foreach ($precios as $precio) {
            $precio->producto_id = (!array_key_exists("producto_id", $precio)) ? 0 : $precio->producto_id;
            $precio->precio_tipo_id = (!array_key_exists("precio_tipo_id", $precio)) ? 0 : $precio->precio_tipo_id;
            $precio->precio = (!array_key_exists("precio", $precio)) ? 0 : $precio->precio;
        }

        return $precios;
    }

    /**
     * @param $precio
     */
    function checkPrecioHorario($precio)
    {
        $precio->precio_id = (!array_key_exists("precio_id", $precio)) ? 0 : $precio->precio_id;
        $precio->hora_desde = (!array_key_exists("hora_desde", $precio)) ? '00:00:00' : $precio->hora_desde;
        $precio->hora_hasta = (!array_key_exists("hora_hasta", $precio)) ? '00:00:00' : $precio->hora_hasta;

        return $precio;
    }

    /**
     * @description Verifica todos los campos de categoria del producto para que existan
     * @param $categorias
     * @return mixed
     */
    function checkCategorias($categorias)
    {
        foreach ($categorias as $categoria) {
            $categoria->producto_id = (!array_key_exists("producto_id", $categoria)) ? 0 : $categoria->producto_id;
            $categoria->categoria_id = (!array_key_exists("categoria_id", $categoria)) ? 0 : $categoria->categoria_id;
        }

        return $categorias;
    }

    /**
     * @description Verifica todos los campos de categoria para que existan
     * @param $categoria
     * @return mixed
     */
    function checkCategoria($categoria)
    {
        $categoria->nombre = (!array_key_exists("nombre", $categoria)) ? '' : $categoria->nombre;
        $categoria->parent_id = (!array_key_exists("parent_id", $categoria)) ? -1 : $categoria->parent_id;
        $categoria->status = (!array_key_exists("status", $categoria)) ? 0 : $categoria->status;

        return $categoria;
    }

    /**
     * @description Verifica todos los campos de carrito para que existan
     * @param $carrito
     * @return mixed
     */
    function checkCarrito($carrito)
    {
        $now = new DateTime(null, new DateTimeZone('America / Argentina / Buenos_Aires'));

        $carrito->status = (!array_key_exists("status", $carrito)) ? 1 : $carrito->status;
        $carrito->total = (!array_key_exists("total", $carrito)) ? 0.0 : $carrito->total;
        $carrito->fecha = (!array_key_exists("fecha", $carrito)) ? $now->format('Y - m - d H:i:s') : $carrito->fecha;
        $carrito->usuario_id = (!array_key_exists("usuario_id", $carrito)) ? -1 : $carrito->usuario_id;
        $carrito->origen = (!array_key_exists("origen", $carrito)) ? -1 : $carrito->origen;
        $carrito->destino = (!array_key_exists("destino", $carrito)) ? -1 : $carrito->destino;
        $carrito->carrito_detalle = (!array_key_exists("carrito_detalle", $carrito)) ? array() : checkCarritoDetalle($carrito->carrito_detalle);

        return $carrito;
    }

    /**
     * @description Verifica todos los campos de detalle del carrito para que existan
     * @param $detalle
     * @return mixed
     */
    function checkCarritoDetalle($detalle)
    {
        $detalle->carrito_id = (!array_key_exists("carrito_id", $detalle)) ? 0 : $detalle->carrito_id;
        $detalle->producto_id = (!array_key_exists("producto_id", $detalle)) ? 0 : $detalle->producto_id;
        $detalle->cantidad = (!array_key_exists("cantidad", $detalle)) ? 0 : $detalle->cantidad;
        $detalle->en_oferta = (!array_key_exists("en_oferta", $detalle)) ? 0 : $detalle->en_oferta;
        $detalle->precio_unitario = (!array_key_exists("precio_unitario", $detalle)) ? 0 : $detalle->precio_unitario;

        return $detalle;
    }

    /**
     * @param $productoTipo
     */
    function checkProductoTipo($productoTipo)
    {
        $productoTipo->nombre = (!array_key_exists("nombre", $productoTipo)) ? '' : $productoTipo->nombre;
        $productoTipo->disponible_para_venta = (!array_key_exists("disponible_para_venta", $productoTipo)) ? 0 : $productoTipo->disponible_para_venta;
        $productoTipo->control_stock = (!array_key_exists("control_stock", $productoTipo)) ? 0 : $productoTipo->control_stock;
        $productoTipo->compuesto = (!array_key_exists("compuesto", $productoTipo)) ? 0 : $productoTipo->compuesto;
        $productoTipo->status = (!array_key_exists("status", $productoTipo)) ? 0 : $productoTipo->status;

        return $productoTipo;
    }

}


if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = file_get_contents("php://input");
    $decoded = json_decode($data);
    Productos::init(json_decode(json_encode($decoded), true));
} else {
    Productos::init($_GET);
}



