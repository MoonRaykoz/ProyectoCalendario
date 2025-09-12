<?php
// api/tasks.php
require_once __DIR__ . '/../includes/auth.php';
require_once __DIR__ . '/../config/conexion.php';
header('Content-Type: application/json');
if (!isLogged()) {
    http_response_code(401);
    echo json_encode(['error' => 'No autorizado']);
    exit;
}
$method = $_SERVER['REQUEST_METHOD'];
$action = $_REQUEST['action'] ?? 'list';
$uid = $_SESSION['user']['id'];
try {
    switch ($action) {
        case 'list':
            // filtros opcionales
            $params = [];
            $sql = "SELECT * FROM tareas WHERE usuario_id = :uid";
            $params[':uid'] = $uid;
            if (!empty($_GET['status']) && in_array($_GET['status'], ['pendiente','completada'])) {
                $sql .= " AND estado = :estado";
                $params[':estado'] = $_GET['status'];
            }
            if (!empty($_GET['priority']) && in_array($_GET['priority'], ['alta','media','baja'])) {
                $sql .= " AND prioridad = :pri";
                $params[':pri'] = $_GET['priority'];
            }
            if (!empty($_GET['search'])) {
                $sql .= " AND (titulo LIKE :s OR descripcion LIKE :s OR tags LIKE :s)";
                $params[':s'] = '%' . $_GET['search'] . '%';
            }
            $sql .= " ORDER BY orden DESC, creado_en DESC";
            $stmt = $pdo->prepare($sql);
            $stmt->execute($params);
            $rows = $stmt->fetchAll();
            echo json_encode(['success' => true, 'tasks' => $rows]);
            break;

        case 'create':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!verify_csrf($_POST['_csrf'] ?? '')) throw new Exception('CSRF inválido');
            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') throw new Exception('Título requerido');
            $descripcion = trim($_POST['descripcion'] ?? null);
            $prioridad = in_array($_POST['prioridad'] ?? 'media', ['alta','media','baja']) ? $_POST['prioridad'] : 'media';
            $fecha = !empty($_POST['fecha_vencimiento']) ? date('Y-m-d H:i:s', strtotime($_POST['fecha_vencimiento'])) : null;
            $tags = trim($_POST['tags'] ?? null);
            $estado = (!empty($_POST['estado']) && $_POST['estado'] === 'completada') ? 'completada' : 'pendiente';
            $stmt = $pdo->prepare("INSERT INTO tareas (usuario_id, titulo, descripcion, prioridad, fecha_vencimiento, tags, estado, creado_en) VALUES (:uid, :titulo, :desc, :prio, :fecha, :tags, :estado, NOW())");
            $stmt->execute([
                ':uid' => $uid,
                ':titulo' => $titulo,
                ':desc' => $descripcion,
                ':prio' => $prioridad,
                ':fecha' => $fecha,
                ':tags' => $tags,
                ':estado' => $estado
            ]);
            $id = $pdo->lastInsertId();
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
            $stmt->execute([$id]);
            $task = $stmt->fetch();
            echo json_encode(['success' => true, 'task' => $task]);
            break;

        case 'update':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!verify_csrf($_POST['_csrf'] ?? '')) throw new Exception('CSRF inválido');
            $id = intval($_POST['id'] ?? 0);
            if (!$id) throw new Exception('ID inválido');
            // comprobar que la tarea pertenece al usuario
            $stmt = $pdo->prepare("SELECT usuario_id FROM tareas WHERE id = ?");
            $stmt->execute([$id]);
            $row = $stmt->fetch();
            if (!$row || $row['usuario_id'] != $uid) throw new Exception('No autorizado');

            $titulo = trim($_POST['titulo'] ?? '');
            if ($titulo === '') throw new Exception('Título requerido');
            $descripcion = trim($_POST['descripcion'] ?? null);
            $prioridad = in_array($_POST['prioridad'] ?? 'media', ['alta','media','baja']) ? $_POST['prioridad'] : 'media';
            $fecha = !empty($_POST['fecha_vencimiento']) ? date('Y-m-d H:i:s', strtotime($_POST['fecha_vencimiento'])) : null;
            $tags = trim($_POST['tags'] ?? null);
            $estado = (!empty($_POST['estado']) && $_POST['estado'] === 'completada') ? 'completada' : 'pendiente';
            $stmt = $pdo->prepare("UPDATE tareas SET titulo=:titulo, descripcion=:desc, prioridad=:prio, fecha_vencimiento=:fecha, tags=:tags, estado=:estado WHERE id=:id");
            $stmt->execute([
                ':titulo'=>$titulo, ':desc'=>$descripcion, ':prio'=>$prioridad, ':fecha'=>$fecha, ':tags'=>$tags, ':estado'=>$estado, ':id'=>$id
            ]);
            $stmt = $pdo->prepare("SELECT * FROM tareas WHERE id = ?");
            $stmt->execute([$id]);
            echo json_encode(['success'=>true, 'task' => $stmt->fetch()]);
            break;

        case 'delete':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!verify_csrf($_POST['_csrf'] ?? '')) throw new Exception('CSRF inválido');
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("DELETE FROM tareas WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $uid]);
            echo json_encode(['success' => true]);
            break;

        case 'toggle':
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!verify_csrf($_POST['_csrf'] ?? '')) throw new Exception('CSRF inválido');
            $id = intval($_POST['id'] ?? 0);
            $stmt = $pdo->prepare("SELECT estado FROM tareas WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$id, $uid]);
            $row = $stmt->fetch();
            if (!$row) throw new Exception('No encontrado');
            $new = $row['estado'] === 'pendiente' ? 'completada' : 'pendiente';
            $stmt = $pdo->prepare("UPDATE tareas SET estado = ? WHERE id = ? AND usuario_id = ?");
            $stmt->execute([$new, $id, $uid]);
            echo json_encode(['success'=>true, 'estado' => $new]);
            break;

        case 'reorder':
            // recibe POST ids[] = [id1,id2,...] orden de mayor a menor (o lo que prefieras)
            if ($method !== 'POST') throw new Exception('Method not allowed');
            if (!verify_csrf($_POST['_csrf'] ?? '')) throw new Exception('CSRF inválido');
            $ids = $_POST['ids'] ?? [];
            if (!is_array($ids)) throw new Exception('Formato inválido');
            $order = count($ids);
            $stmt = $pdo->prepare("UPDATE tareas SET orden = :orden WHERE id = :id AND usuario_id = :uid");
            foreach ($ids as $id) {
                $stmt->execute([':orden' => $order, ':id' => intval($id), ':uid' => $uid]);
                $order--;
            }
            echo json_encode(['success'=>true]);
            break;

        case 'export_csv':
            // Exporta CSV para usuario (no JSON)
            $stmt = $pdo->prepare("SELECT titulo, descripcion, estado, prioridad, fecha_vencimiento, tags, creado_en FROM tareas WHERE usuario_id = :uid ORDER BY orden DESC, creado_en DESC");
            $stmt->execute([':uid' => $uid]);
            $rows = $stmt->fetchAll();
            $filename = "tareas_export_" . date('Ymd_His') . ".csv";
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename=' . $filename);
            $out = fopen('php://output', 'w');
            fputcsv($out, ['Título','Descripción','Estado','Prioridad','Fecha Vencimiento','Etiquetas','Creado En']);
            foreach ($rows as $r) {
                fputcsv($out, [$r['titulo'],$r['descripcion'],$r['estado'],$r['prioridad'],$r['fecha_vencimiento'],$r['tags'],$r['creado_en']]);
            }
            fclose($out);
            exit;
            break;

        default:
            echo json_encode(['error' => 'Acción desconocida']);
    }
} catch (Exception $e) {
    http_response_code(400);
    echo json_encode(['error' => $e->getMessage()]);
}
