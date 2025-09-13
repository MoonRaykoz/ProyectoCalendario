<?php
declare(strict_types=1);

require_once __DIR__ . '/../../includes/auth.php';
header('Content-Type: application/json; charset=utf-8');

// Sesión
if (session_status() === PHP_SESSION_NONE) { session_start(); }

$me = current_user();
if (!$me) { http_response_code(401); echo json_encode(['error'=>'No autorizado']); exit; }
$uid = (int)$me['id'];

// DB
global $mysqli;
if (!($mysqli instanceof mysqli)) {
  http_response_code(500);
  echo json_encode(['error'=>'DB no disponible']); exit;
}

$method = $_SERVER['REQUEST_METHOD'];
$action = $_GET['action'] ?? $_POST['action'] ?? 'list';

try {
  switch ($action) {
    case 'list': {
      $sql  = "SELECT id, usuario_id, titulo, descripcion, prioridad, 
                      DATE_FORMAT(fecha_vencimiento, '%Y-%m-%d %H:%i:%s') AS fecha_vencimiento,
                      tags, estado, orden, DATE_FORMAT(creado_en, '%Y-%m-%d %H:%i:%s') AS creado_en
               FROM tareas WHERE usuario_id = ?";
      $types = 'i'; $vals = [$uid];

      if (!empty($_GET['status']) && in_array($_GET['status'], ['pendiente','completada'], true)) {
        $sql .= " AND estado = ?"; $types .= 's'; $vals[] = $_GET['status'];
      }
      if (!empty($_GET['priority']) && in_array($_GET['priority'], ['alta','media','baja'], true)) {
        $sql .= " AND prioridad = ?"; $types .= 's'; $vals[] = $_GET['priority'];
      }
      if (!empty($_GET['search'])) {
        $sql .= " AND (titulo LIKE ? OR descripcion LIKE ? OR tags LIKE ?)";
        $like = '%'.$_GET['search'].'%'; $types .= 'sss'; array_push($vals, $like, $like, $like);
      }
      $sql .= " ORDER BY orden DESC, creado_en DESC";
      $stmt = $mysqli->prepare($sql); if (!$stmt) throw new Exception('Error al preparar consulta');
      $stmt->bind_param($types, ...$vals);
      $stmt->execute(); $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
      echo json_encode(['success'=>true, 'tasks'=>$rows], JSON_UNESCAPED_UNICODE);
      break;
    }

    case 'create': {
      if ($method !== 'POST') throw new Exception('Method not allowed');
      if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) throw new Exception('CSRF inválido');

      $titulo = trim($_POST['titulo'] ?? ''); if ($titulo==='') throw new Exception('Título requerido');
      $descripcion = trim($_POST['descripcion'] ?? ''); $descripcion = $descripcion === '' ? null : $descripcion;

      $prioridad = $_POST['prioridad'] ?? 'media';
      if (!in_array($prioridad, ['alta','media','baja'], true)) $prioridad = 'media';

      $fecha = null;
      if (!empty($_POST['fecha_vencimiento'])) {
        $ts = strtotime($_POST['fecha_vencimiento']); $fecha = $ts ? date('Y-m-d H:i:s', $ts) : null;
      }

      $tags = trim($_POST['tags'] ?? ''); $tags = $tags === '' ? null : $tags;
      $estado = (!empty($_POST['estado']) && $_POST['estado']==='completada') ? 'completada' : 'pendiente';

      $stmt = $mysqli->prepare("INSERT INTO tareas (usuario_id, titulo, descripcion, prioridad, fecha_vencimiento, tags, estado, creado_en)
                                VALUES (?,?,?,?,?,?,?, NOW())");
      if (!$stmt) throw new Exception('Error al preparar inserción');
      $stmt->bind_param('issssss', $uid, $titulo, $descripcion, $prioridad, $fecha, $tags, $estado);
      $stmt->execute(); $id = (int)$stmt->insert_id;

      $stmt = $mysqli->prepare("SELECT * FROM tareas WHERE id=?"); $stmt->bind_param('i', $id);
      $stmt->execute(); $task = $stmt->get_result()->fetch_assoc();
      echo json_encode(['success'=>true, 'task'=>$task], JSON_UNESCAPED_UNICODE);
      break;
    }

    case 'update': {
      if ($method !== 'POST') throw new Exception('Method not allowed');
      if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) throw new Exception('CSRF inválido');

      $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID inválido');

      $stmt = $mysqli->prepare("SELECT usuario_id FROM tareas WHERE id=?");
      $stmt->bind_param('i',$id); $stmt->execute();
      $own = $stmt->get_result()->fetch_assoc();
      if (!$own || (int)$own['usuario_id'] !== $uid) throw new Exception('No autorizado');

      $titulo = trim($_POST['titulo'] ?? ''); if ($titulo==='') throw new Exception('Título requerido');
      $descripcion = trim($_POST['descripcion'] ?? ''); $descripcion = $descripcion === '' ? null : $descripcion;
      $prioridad = $_POST['prioridad'] ?? 'media';
      if (!in_array($prioridad, ['alta','media','baja'], true)) $prioridad = 'media';
      $fecha = null;
      if (!empty($_POST['fecha_vencimiento'])) {
        $ts = strtotime($_POST['fecha_vencimiento']); $fecha = $ts ? date('Y-m-d H:i:s', $ts) : null;
      }
      $tags = trim($_POST['tags'] ?? ''); $tags = $tags === '' ? null : $tags;
      $estado = (!empty($_POST['estado']) && $_POST['estado']==='completada') ? 'completada' : 'pendiente';

      $stmt = $mysqli->prepare("UPDATE tareas SET titulo=?, descripcion=?, prioridad=?, fecha_vencimiento=?, tags=?, estado=? WHERE id=?");
      if (!$stmt) throw new Exception('Error al preparar actualización');
      $stmt->bind_param('ssssssi', $titulo, $descripcion, $prioridad, $fecha, $tags, $estado, $id);
      $stmt->execute();

      $stmt = $mysqli->prepare("SELECT * FROM tareas WHERE id=?"); $stmt->bind_param('i', $id);
      $stmt->execute(); $task = $stmt->get_result()->fetch_assoc();
      echo json_encode(['success'=>true, 'task'=>$task], JSON_UNESCAPED_UNICODE);
      break;
    }

    case 'delete': {
      if ($method !== 'POST') throw new Exception('Method not allowed');
      if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) throw new Exception('CSRF inválido');

      $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID inválido');
      $stmt = $mysqli->prepare("DELETE FROM tareas WHERE id=? AND usuario_id=?");
      if (!$stmt) throw new Exception('Error al preparar eliminación');
      $stmt->bind_param('ii', $id, $uid); $stmt->execute();
      echo json_encode(['success'=>true]);
      break;
    }

    case 'toggle': {
      if ($method !== 'POST') throw new Exception('Method not allowed');
      if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) throw new Exception('CSRF inválido');

      $id = (int)($_POST['id'] ?? 0); if ($id<=0) throw new Exception('ID inválido');

      $stmt = $mysqli->prepare("SELECT estado FROM tareas WHERE id=? AND usuario_id=?");
      $stmt->bind_param('ii', $id, $uid); $stmt->execute();
      $row = $stmt->get_result()->fetch_assoc(); if (!$row) throw new Exception('No encontrado');

      $new = ($row['estado']==='pendiente') ? 'completada' : 'pendiente';
      $stmt = $mysqli->prepare("UPDATE tareas SET estado=? WHERE id=? AND usuario_id=?");
      $stmt->bind_param('sii', $new, $id, $uid); $stmt->execute();
      echo json_encode(['success'=>true, 'estado'=>$new], JSON_UNESCAPED_UNICODE);
      break;
    }

    case 'reorder': {
      if ($method !== 'POST') throw new Exception('Method not allowed');
      if (!isset($_POST['_csrf']) || !hash_equals($_SESSION['_csrf'] ?? '', $_POST['_csrf'])) throw new Exception('CSRF inválido');

      $ids = $_POST['ids'] ?? ($_POST['ids'] ?? []); // admite 'ids[]'
      if (!is_array($ids)) { $ids = [ $ids ]; }
      $ids = array_map('intval', $ids);
      $order = count($ids);

      $stmt = $mysqli->prepare("UPDATE tareas SET orden=? WHERE id=? AND usuario_id=?");
      if (!$stmt) throw new Exception('Error al preparar ordenamiento');
      foreach ($ids as $tid) {
        if ($tid > 0) { $stmt->bind_param('iii', $order, $tid, $uid); $stmt->execute(); $order--; }
      }
      echo json_encode(['success'=>true]);
      break;
    }

    case 'export_csv': {
      $stmt = $mysqli->prepare("SELECT titulo, descripcion, estado, prioridad, 
                                       DATE_FORMAT(fecha_vencimiento, '%Y-%m-%d %H:%i:%s') AS fecha_vencimiento,
                                       tags, DATE_FORMAT(creado_en, '%Y-%m-%d %H:%i:%s') AS creado_en
                                FROM tareas WHERE usuario_id=? ORDER BY orden DESC, creado_en DESC");
      $stmt->bind_param('i', $uid); $stmt->execute();
      $res = $stmt->get_result();

      $filename = "tareas_export_" . date('Ymd_His') . ".csv";
      header('Content-Type: text/csv; charset=utf-8');
      header('Content-Disposition: attachment; filename='.$filename);
      $out = fopen('php://output', 'w');
      fputcsv($out, ['Título','Descripción','Estado','Prioridad','Fecha Vencimiento','Etiquetas','Creado En']);
      while ($r = $res->fetch_assoc()) {
        fputcsv($out, [$r['titulo'],$r['descripcion'],$r['estado'],$r['prioridad'],$r['fecha_vencimiento'],$r['tags'],$r['creado_en']]);
      }
      fclose($out); exit;
    }

    default:
      echo json_encode(['error'=>'Acción desconocida']);
  }
} catch (Throwable $e) {
  http_response_code(400);
  echo json_encode(['error'=>$e->getMessage()]);
}
