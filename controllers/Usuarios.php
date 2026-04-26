<?php
class Usuarios extends Controller
{
    private $id_usuario;
    public function __construct()
    {
        parent::__construct();
        session_start();
        if (empty($_SESSION['id_usuario'])) {
            header('Location: ' . BASE_URL);
            exit;
        }
        $this->id_usuario = $_SESSION['id_usuario'];
    }
    public function index()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Usuarios';
        $data['script'] = 'usuarios.js';
        $data['grupotrabajos'] = $this->model->getGrupoTrabajos(1);
        $this->views->getView('usuarios', 'index', $data);
    }
    public function listar()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->getUsuarios(1);
        for ($i = 0; $i < count($data); $i++) {

            if ($_SESSION['id_usuario'] != 1) {
                if ($data[$i]['id'] == 1) {
                    $data[$i]['acciones'] = '';
                } else {
                    $data[$i]['acciones'] = '<div>
                    <button class="btn btn-info" type="button" onclick="editarUsuario(' . $data[$i]['id'] . ')"> <i class="fa-solid fa-pen-to-square text-white"></i></button>
                    <button class="btn btn-danger" type="button" onclick="eliminarUsuario(' . $data[$i]['id'] . ')"> <i class="fas fa-trash"></i></button>
                </div>';
                }
            } else {
                $data[$i]['acciones'] = '<div>
                <button class="btn btn-info" type="button" onclick="editarUsuario(' . $data[$i]['id'] . ')"> <i class="fa-solid fa-pen-to-square text-white"></i></button>
                <button class="btn btn-danger" type="button" onclick="eliminarUsuario(' . $data[$i]['id'] . ')"> <i class="fas fa-trash"></i></button>
                </div>';
            }



            if ($data[$i]['rol'] == 1) {
                $data[$i]['rol'] = '<span class="badge bg-success">Administrador</span>';
            } else if ($data[$i]['rol'] == 2) {
                $data[$i]['rol'] = '<span class="badge bg-info">Secretario</span>';
            }else{
                $data[$i]['rol'] = '<span class="badge bg-warning">Tecnico</span>';

            }

            if ($data[$i]['descripcion'] == '' || $data[$i]['descripcion'] == null) {
                $data[$i]['grupotrabajo'] = '<span class="badge bg-info">Personal Administrativo</span>';

            } else {
                $data[$i]['grupotrabajo'] = '<span class="badge bg-success">'.$data[$i]['descripcion'].'</span>';

            }
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function listarInactivos()
    {
        $data = $this->model->getUsuarios(0);
        for ($i = 0; $i < count($data); $i++) {
            if ($data[$i]['rol'] == 1) {
                $data[$i]['rol'] = '<span class="badge bg-success">Administrador</span>';
            } else {
                $data[$i]['rol'] = '<span class="badge bg-info">Vendedor</span>';
            }

            $data[$i]['acciones'] = '<div> <button class="btn btn-success" type="button" onclick="restaurarUsuario(' . $data[$i]['id'] . ')"> <i class="fas fa-check-circle"></i></button>
            </div>';
        }
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }
    public function registrar()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_POST)) {
            if (empty($_POST['nombres'])) {
                $res = array('msg' => 'EL NOMBRE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['apellidos'])) {
                $res = array('msg' => 'EL APELLIDO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['correo'])) {
                $res = array('msg' => 'EL CORREO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['telefono'])) {
                $res = array('msg' => 'EL TELEFONO ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['direccion'])) {
                $res = array('msg' => 'LA DIRECCION ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['clave'])) {
                $res = array('msg' => 'LA CLAVE ES REQUERIDO', 'type' => 'warning');
            } else if (empty($_POST['rol'])) {
                $res = array('msg' => 'EL ROL ES REQUERIDO', 'type' => 'warning');
            } else {
                $nombres = strClean($_POST['nombres']);
                $apellidos = strClean($_POST['apellidos']);
                $correo = strClean($_POST['correo']);
                $telefono = strClean($_POST['telefono']);
                $direccion = strClean($_POST['direccion']);
                $clave = strClean($_POST['clave']);
                $hash = password_hash($clave, PASSWORD_DEFAULT);
                $rol = strClean($_POST['rol']);
                $grupotrabajo = strClean($_POST['grupotrabajo']);
                $id = strClean($_POST['id']);

                if ($id == '') {
                    //verificar datos si existen
                    $verificarCorreo = $this->model->getValidar('correo', $correo, 'registrar', 0);
                    if (empty($verificarCorreo)) {
                        $verificarTel = $this->model->getValidar('telefono', $telefono, 'registrar', 0);

                        if (empty($verificarTel)) {
                            $data = $this->model->registrar(
                                $nombres,
                                $apellidos,
                                $correo,
                                $telefono,
                                $direccion,
                                $hash,
                                $rol,
                                $grupotrabajo
                            );
                            if ($data > 0) {
                                $res = array('msg' => 'USUARIO REGISTRADO EXITOSAMENTE', 'type' => 'success');
                            } else {
                                $res = array('msg' => 'ERROR AL REGISTRAR', 'type' => 'error');
                            }
                        } else {
                            $res = array('msg' => 'EL TELEFONO DEBE SER UNICO', 'type' => 'warning');
                        }
                    } else {
                        $res = array('msg' => 'EL CORREO ELECTRONICO DEBE SER UNICO', 'type' => 'warning');
                    }
                } else {
                    //verificar datos si existen
                    $verificarCorreo = $this->model->getValidar('correo', $correo, 'modificar', $id);
                    if (empty($verificarCorreo)) {
                        $verificarTel = $this->model->getValidar('telefono', $telefono, 'modificar', $id);
                        if (empty($verificarTel)) {
                            $data = $this->model->actualizar(
                                $nombres,
                                $apellidos,
                                $correo,
                                $telefono,
                                $direccion,
                                $hash,
                                $rol,
                                $grupotrabajo,
                                $id
                            );
                            if ($data > 0) {
                                $res = array('msg' => 'USUARIO ACTUALIZADO EXITOSAMENTE', 'type' => 'success');
                            } else {
                                $res = array('msg' => 'ERROR AL ACTUALIZADO', 'type' => 'error');
                            }
                        } else {
                            $res = array('msg' => 'EL TELEFONO DEBE SER UNICO', 'type' => 'warning');
                        }
                    } else {
                        $res = array('msg' => 'EL CORREO ELECTRONICO DEBE SER UNICO', 'type' => 'warning');
                    }
                }
            }
        } else {
            $res = array('msg' => 'ERROR AL REGISTAR', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);

        die();
    }

    public function eliminar($id)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_GET)) {
            if (is_numeric(($id))) {
                $data = $this->model->eliminar(0, $id);
                if ($data == 1) {
                    $res = array('msg' => 'USUARIO ELIMINADO EXITOSAMENTE', 'type' => 'success');
                } else {
                    $res = array('msg' => 'ERROR AL ELIMINAR USUARIO', 'type' => 'error');
                }
            } else {
                $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function restaurar($id)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        if (isset($_GET)) {
            if (is_numeric(($id))) {
                $data = $this->model->eliminar(1, $id);
                if ($data == 1) {
                    $res = array('msg' => 'USUARIO RESTAURADO EXITOSAMENTE', 'type' => 'success');
                } else {
                    $res = array('msg' => 'ERROR AL RESTAURADO USUARIO', 'type' => 'error');
                }
            } else {
                $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
            }
        } else {
            $res = array('msg' => 'ERROR DESCONOCIDO', 'type' => 'error');
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function editar($id)
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data = $this->model->editar($id);
//print_r($data);
        echo json_encode($data, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function inactivos()
    {
        if ($_SESSION['rol'] == 2) {
            header('Location: ' . BASE_URL . 'admin/permisos');
            exit;
        }
        $data['title'] = 'Usuarios Inactivos';
        $data['script'] = 'usuarios-inactivos.js';
        $this->views->getView('usuarios', 'inactivos', $data);
    }

    //perfil
    public function profile()
    {
        $data['title'] = 'Datos del usuario';
        $data['script'] = 'profile.js';
        $data['usuario'] = $this->model->editar($this->id_usuario);
        $this->views->getView('usuarios', 'perfil', $data);
    }

    public function modificarDatos()
    {

        $nombre = strClean($_POST['nombrePerfil']);
        $apellidos = strClean($_POST['apellidoPerfil']);
        $correo = strClean($_POST['correoPerfil']);
        $telefono = strClean($_POST['telefonoPerfil']);
        $direccion = strClean($_POST['direccionPerfil']);
        $claveNueva = strClean($_POST['claveNueva']);
        $claveActual = strClean($_POST['claveActual']);

        $foto = $_FILES['fotoPerfil'];
        $name = $foto['name'];
        $tmp = $foto['tmp_name'];

        $verificarPerfil = $this->model->editar($this->id_usuario);
        $destino = $verificarPerfil['perfil'];

        if (!empty($name)) {
            if (file_exists($destino)) {
                unlink($destino);
            }
            $perfil = date('YmdHis') . $correo . '.jpg';
            $destino = 'assets/images/perfil/' . $perfil;
        }

        if (empty($nombre)) {
            $res = array('msg' => 'EL NOMBRE ES REQUERIDO', 'type' => 'warning');
        } else if (empty($apellidos)) {
            $res = array('msg' => 'EL APELLIDO ES REQUERIDO', 'type' => 'warning');
        } else if (empty($direccion)) {
            $res = array('msg' => 'LA DIRECCIÓN ES REQUERIDO', 'type' => 'warning');
        } else {
            $verificarClave = $this->model->editar($this->id_usuario);
            if (empty($claveNueva)) {
                $hash =  $verificarClave['clave'];
                //$verificarCorreo = $this->model->getValidar('correo', $correo, 'actualizar', $this->id_usuario);
                //if (empty($verificarCorreo)) {
                //  $verificarTel = $this->model->getValidar('telefono', $telefono, 'actualizar', $this->id_usuario);
                //  if (empty($verificarTel)) {
                $data = $this->model->modificarDatos($nombre, $apellidos, $correo, $telefono, $direccion, $hash, $destino, $this->id_usuario);
                if ($data == 1) {
                    if (!empty($name)) {
                        move_uploaded_file($tmp, $destino);
                    }
                    $res = array('msg' => 'DATOS ACTUALIZADO EXITOSAMENTE', 'type' => 'success', 'clave' => false);
                } else {
                    $res = array('msg' => 'ERROR AL MODIFICAR', 'type' => 'error');
                }
                /*   } else {
                        $res = array('msg' => 'EL TELEFONO DEBE SER UNICO', 'type' => 'warning');
                    }
                } else {
                    $res = array('msg' => 'EL CORREO DEBE SER UNICO', 'type' => 'warning');
                }*/
            } else {
                if (password_verify($claveActual, $verificarClave['clave'])) {
                    //  $verificarCorreo = $this->model->getValidar('correo', $correo, 'actualizar', $this->id_usuario);
                    // if (empty($verificarCorreo)) {
                    //  $verificarTel = $this->model->getValidar('telefono', $telefono, 'actualizar', $this->id_usuario);
                    //  if (empty($verificarTel)) {
                    $hash = password_hash($claveNueva, PASSWORD_DEFAULT);
                    $data = $this->model->modificarDatos($nombre, $apellidos, $correo, $telefono, $direccion, $hash, $destino, $this->id_usuario);
                    if ($data == 1) {
                        if (!empty($name)) {
                            move_uploaded_file($tmp, $destino);
                        }
                        $res = array('msg' => 'DATOS ACTUALIZADO EXITOSAMENTE', 'type' => 'success', 'clave' => true);
                    } else {
                        $res = array('msg' => 'ERROR AL MODIFICAR', 'type' => 'error');
                    }
                    /* } else {
                            $res = array('msg' => 'EL TELEFONO DEBE SER UNICO', 'type' => 'warning');
                        }
                    } else {
                        $res = array('msg' => 'EL CORREO DEBE SER UNICO', 'type' => 'warning');
                    }*/
                } else {
                    $res = array('msg' => 'CONTRASEÑA ACTUAL INCORRECTA', 'type' => 'warning');
                }
            }
        }
        echo json_encode($res, JSON_UNESCAPED_UNICODE);
        die();
    }

    public function salir()
    {
        $evento = 'Cierre de Sesión';
        $ip = $_SERVER['REMOTE_ADDR'];
        $detalle = $_SERVER['HTTP_USER_AGENT'];
        $acceso = $this->model->registrarAcceso($evento, $ip, $detalle);
        if ($acceso > 0) {
            session_destroy();
            header('Location: ' . BASE_URL);
        }
    }
}