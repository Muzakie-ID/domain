<?php require 'db.php'; 
// Fetch domains for the select dropdown
$domains = $pdo->query("SELECT * FROM domains ORDER BY name ASC")->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Subdomain Manager</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
</head>
<body class="bg-light">
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary mb-4">
        <div class="container">
            <a class="navbar-brand" href="index.php">Subdomain Manager</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <div class="navbar-nav">
                    <a class="nav-link active" href="index.php">Subdomains</a>
                    <a class="nav-link" href="domains.php">Domains</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="container">
        <div class="d-flex justify-content-between align-items-center mb-4">
            <h4>Daftar Subdomain & Port</h4>
            <div class="gap-2 d-flex">
                <button class="btn btn-success" data-bs-toggle="modal" data-bs-target="#nginxImportModal">
                    <i class="bi bi-upload"></i> Import Nginx
                </button>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#subdomainModal" onclick="resetForm()">
                    <i class="bi bi-plus-lg"></i> Tambah Subdomain
                </button>
            </div>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Subdomain</th>
                                <th>Port</th>
                                <th>Link</th>
                                <th>Keterangan</th>
                                <th class="text-end">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $sql = "SELECT s.*, d.name as domain_name 
                                    FROM subdomains s 
                                    JOIN domains d ON s.domain_id = d.id 
                                    ORDER BY s.port ASC";
                            $stmt = $pdo->query($sql);
                            while ($row = $stmt->fetch(PDO::FETCH_ASSOC)) {
                                $full_url = "http://" . $row['sub_name'] . "." . $row['domain_name'];
                                echo "<tr>";
                                echo "<td><span class='fw-bold'>{$row['sub_name']}</span>.<span class='text-muted'>{$row['domain_name']}</span></td>";
                                echo "<td><span class='badge bg-secondary'>{$row['port']}</span></td>";
                                echo "<td><a href='{$full_url}' target='_blank' class='btn btn-sm btn-outline-primary'><i class='bi bi-box-arrow-up-right'></i> Buka</a></td>";
                                echo "<td>" . ($row['description'] ? htmlspecialchars($row['description']) : '-') . "</td>";
                                echo "<td class='text-end'>
                                        <button class='btn btn-sm btn-warning me-1' onclick='editSubdomain(" . json_encode($row) . ")'><i class='bi bi-pencil'></i></button>
                                        <a href='actions.php?action=delete_subdomain&id={$row['id']}' class='btn btn-sm btn-danger' onclick='return confirm(\"Yakin hapus?\")'><i class='bi bi-trash'></i></a>
                                      </td>";
                                echo "</tr>";
                            }
                            ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    <!-- Modal -->
    <div class="modal fade" id="subdomainModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalTitle">Tambah Subdomain</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <form action="actions.php" method="POST">
                    <div class="modal-body">
                        <input type="hidden" name="action" value="save_subdomain">
                        <input type="hidden" name="id" id="sub_id">
                        
                        <div class="mb-3">
                            <label class="form-label">Domain Utama</label>
                            <select name="domain_id" id="domain_id" class="form-select" required>
                                <option value="">Pilih Domain...</option>
                                <?php foreach ($domains as $d): ?>
                                    <option value="<?= $d['id'] ?>"><?= $d['name'] ?></option>
                                <?php endforeach; ?>
                            </select>
                            <?php if(empty($domains)): ?>
                                <div class="form-text text-danger">Belum ada domain. <a href="domains.php">Tambah domain dulu</a>.</div>
                            <?php endif; ?>
                        </div>

                        <div class="row">
                            <div class="col-md-8 mb-3">
                                <label class="form-label">Subdomain</label>
                                <div class="input-group">
                                    <input type="text" name="sub_name" id="sub_name" class="form-control" placeholder="api" required>
                                    <span class="input-group-text" id="domainSuffix">.domain</span>
                                </div>
                            </div>
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Port</label>
                                <input type="text" name="port" id="port" class="form-control" placeholder="8080" required>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label class="form-label">Keterangan (Opsional)</label>
                            <textarea name="description" id="description" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                        <button type="submit" class="btn btn-primary">Simpan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Nginx Import -->
    <div class="modal fade" id="nginxImportModal" tabindex="-1">
        <div class="modal-dialog modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title">Import dari Nginx Config</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label class="form-label">Paste Nginx Config Block</label>
                        <textarea id="nginxConfig" class="form-control" rows="8" placeholder="server {&#10;    server_name aapanel.muzakie.my.id;&#10;    proxy_pass http://127.0.0.1:8887;&#10;}"></textarea>
                    </div>
                    <div id="parseResult" class="d-none">
                        <div class="alert alert-info">
                            <strong>Data Terdeteksi:</strong><br>
                            Domain: <span id="resultDomain" class="badge bg-primary"></span><br>
                            Subdomain: <span id="resultSub" class="badge bg-success"></span><br>
                            Port: <span id="resultPort" class="badge bg-warning"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button type="button" class="btn btn-primary" onclick="parseNginxConfig()">Parse & Import</button>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        const modal = new bootstrap.Modal(document.getElementById('subdomainModal'));
        const nginxModal = new bootstrap.Modal(document.getElementById('nginxImportModal'));

        function resetForm() {
            document.getElementById('modalTitle').innerText = 'Tambah Subdomain';
            document.getElementById('sub_id').value = '';
            document.getElementById('domain_id').value = '';
            document.getElementById('sub_name').value = '';
            document.getElementById('port').value = '';
            document.getElementById('description').value = '';
        }

        function editSubdomain(data) {
            document.getElementById('modalTitle').innerText = 'Edit Subdomain';
            document.getElementById('sub_id').value = data.id;
            document.getElementById('domain_id').value = data.domain_id;
            document.getElementById('sub_name').value = data.sub_name;
            document.getElementById('port').value = data.port;
            document.getElementById('description').value = data.description;
            
            modal.show();
        }

        function parseNginxConfig() {
            const nginxText = document.getElementById('nginxConfig').value;
            
            // Extract server_name (e.g., aapanel.muzakie.my.id)
            const serverNameMatch = nginxText.match(/server_name\s+([^;]+)/);
            const serverName = serverNameMatch ? serverNameMatch[1].trim() : '';
            
            // Extract proxy_pass (e.g., http://127.0.0.1:8887)
            const proxyPassMatch = nginxText.match(/proxy_pass\s+http:\/\/[^:]+:(\d+)/);
            const port = proxyPassMatch ? proxyPassMatch[1] : '';
            
            if (!serverName || !port) {
                alert('Tidak bisa parse config. Pastikan ada server_name dan proxy_pass dengan port.');
                return;
            }
            
            // Split domain dan subdomain
            const parts = serverName.split('.');
            if (parts.length < 2) {
                alert('Format server_name tidak valid. Contoh: aapanel.muzakie.my.id');
                return;
            }
            
            const subName = parts[0];
            const domain = parts.slice(1).join('.');
            
            // Show result
            document.getElementById('resultDomain').innerText = domain;
            document.getElementById('resultSub').innerText = subName;
            document.getElementById('resultPort').innerText = port;
            document.getElementById('parseResult').classList.remove('d-none');
            
            // Auto fill form after 1 second
            setTimeout(() => {
                resetForm();
                document.getElementById('sub_name').value = subName;
                document.getElementById('port').value = port;
                
                // Find and select domain
                const domainSelect = document.getElementById('domain_id');
                for (let i = 0; i < domainSelect.options.length; i++) {
                    if (domainSelect.options[i].text === domain) {
                        domainSelect.value = domainSelect.options[i].value;
                        break;
                    }
                }
                
                nginxModal.hide();
                modal.show();
            }, 1000);
        }

        document.getElementById('domain_id').addEventListener('change', function() {
            const selectedOption = this.options[this.selectedIndex];
            const suffix = selectedOption.text ? '.' + selectedOption.text : '.domain';
            document.getElementById('domainSuffix').innerText = suffix;
        });
    </script>
</body>
</html>