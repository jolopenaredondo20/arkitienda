<?php
// Start session and connect to DB
session_start();
$tenantId = $_SESSION['tenant_id'] ?? null;

$servername = "localhost";
$username = "root";
$password = "";
$dbname = "rental";
$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Mark as read when message is viewed via AJAX
if (isset($_POST['mark_read_id'])) {
    $id = intval($_POST['mark_read_id']);
    $stmt = $conn->prepare("UPDATE notifications SET status='read' WHERE id = ? AND tenant_id = ?");
    $stmt->bind_param("ii", $id, $tenantId);
    $stmt->execute();
    exit;
}

$notifications = [];
if ($tenantId) {
    $stmt = $conn->prepare("SELECT id, title, message, created_at, status 
                            FROM notifications 
                            WHERE tenant_id = ? 
                            ORDER BY created_at DESC");
    $stmt->bind_param("i", $tenantId);
    $stmt->execute();
    $result = $stmt->get_result();
} else {
    $result = $conn->query("SELECT id, title, message, created_at, status 
                            FROM notifications 
                            ORDER BY created_at DESC");
}

while ($row = $result->fetch_assoc()) {
    $notifications[] = $row;
}
$conn->close();
?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
<meta name="viewport" content="width=device-width, initial-scale=1" />
<title>Tenant Notifications</title>
<link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet" />
<style>
  /* Reset & base */
  * {
    box-sizing: border-box;
  }
  body {
    margin: 0; 
    background: #fafafa;
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen,
      Ubuntu, Cantarell, 'Open Sans', 'Helvetica Neue', sans-serif;
    color: #2c3e50;
    -webkit-font-smoothing: antialiased;
    -moz-osx-font-smoothing: grayscale;
  }
  .container {
    max-width: 700px;
    margin: 40px auto;
    background: white;
    border-radius: 12px;
    box-shadow: 0 8px 24px rgb(0 0 0 / 0.1);
    overflow: hidden;
    padding: 0;
  }
  header {
    background: #2563eb;
    padding: 24px 32px;
    color: white;
    font-weight: 600;
    font-size: 1.5rem;
    text-align: center;
    user-select: none;
  }

  .notif {
    padding: 20px 28px;
    border-bottom: 1px solid #e1e8f7;
    cursor: default;
    transition: background-color 0.3s ease;
    position: relative;
  }
  .notif:hover {
    background-color: #f5f9ff;
  }
  .notif.unread {
    background-color: #e0efff;
    font-weight: 600;
  }
  .notif h3 {
    margin: 0 0 6px 0;
    font-size: 1.125rem;
    color: #1e40af;
    line-height: 1.3;
  }
  .notif small {
    color: #64748b;
    font-size: 0.875rem;
    display: block;
    margin-bottom: 12px;
  }
  .view-btn {
    border: none;
    background: #2563eb;
    color: white;
    font-weight: 600;
    font-size: 0.875rem;
    padding: 8px 16px;
    border-radius: 6px;
    cursor: pointer;
    user-select: none;
    transition: background-color 0.2s ease;
    display: inline-block;
  }
  .view-btn:hover {
    background-color: #1d4ed8;
  }
  .read-indicator {
    position: absolute;
    right: 28px;
    top: 50%;
    transform: translateY(-50%);
    font-size: 0.9rem;
    color: #22c55e;
    user-select: none;
  }
  .no-notif {
    padding: 60px 20px;
    text-align: center;
    color: #94a3b8;
    font-size: 1rem;
    font-style: italic;
  }

  /* Modal styles */
  .modal {
    display: none;
    position: fixed;
    z-index: 1000;
    inset: 0;
    background-color: rgba(0,0,0,0.35);
    backdrop-filter: blur(2px);
    -webkit-backdrop-filter: blur(2px);
    align-items: center;
    justify-content: center;
  }
  .modal.active {
    display: flex;
  }
  .modal-content {
    background: white;
    max-width: 480px;
    width: 90%;
    padding: 28px 32px;
    border-radius: 14px;
    box-shadow: 0 16px 40px rgb(0 0 0 / 0.1);
    position: relative;
    color: #334155;
    user-select: text;
  }
  .modal-content h3 {
    margin-top: 0;
    font-weight: 700;
    font-size: 1.375rem;
    margin-bottom: 14px;
  }
  .modal-content p {
    font-size: 1rem;
    line-height: 1.5;
    white-space: pre-line;
  }
  .close {
    position: absolute;
    top: 18px;
    right: 18px;
    background: transparent;
    border: none;
    font-size: 1.5rem;
    font-weight: 700;
    color: #94a3b8;
    cursor: pointer;
    user-select: none;
    transition: color 0.2s ease;
  }
  .close:hover {
    color: #2563eb;
  }

  @media (max-width: 480px) {
    .notif {
      padding: 16px 20px;
    }
    .modal-content {
      padding: 24px 20px;
    }
  }
</style>
</head>
<body>
  <div class="container" role="main">
    <header>📢 Notifications</header>

    <?php if (count($notifications) > 0): ?>
      <?php foreach ($notifications as $notif): ?>
        <article class="notif <?= $notif['status'] === 'unread' ? 'unread' : '' ?>" tabindex="0" aria-live="polite">
          <h3><?= htmlspecialchars($notif['title']) ?></h3>
          <small>🕒 <?= date('F j, Y g:i A', strtotime($notif['created_at'])) ?></small>
          <button class="view-btn" 
              data-id="<?= $notif['id'] ?>" 
              data-title="<?= htmlspecialchars($notif['title']) ?>" 
              data-message="<?= htmlspecialchars($notif['message']) ?>" 
              aria-label="View notification: <?= htmlspecialchars($notif['title']) ?>">
            View
          </button>
          <?php if ($notif['status'] !== 'unread'): ?>
            <span class="read-indicator" aria-label="Read">&#10003;</span>
          <?php endif; ?>
        </article>
      <?php endforeach; ?>
    <?php else: ?>
      <p class="no-notif">No notifications yet.</p>
    <?php endif; ?>
  </div>

  <!-- Modal -->
  <div id="messageModal" class="modal" role="dialog" aria-modal="true" aria-labelledby="modalTitle" aria-describedby="modalMessage">
    <div class="modal-content">
      <button class="close" aria-label="Close modal">&times;</button>
      <h3 id="modalTitle"></h3>
      <p id="modalMessage"></p>
    </div>
  </div>

  <script>
    const modal = document.getElementById('messageModal');
    const modalTitle = document.getElementById('modalTitle');
    const modalMessage = document.getElementById('modalMessage');
    const closeBtn = modal.querySelector('.close');

    document.querySelectorAll('.view-btn').forEach(btn => {
      btn.addEventListener('click', () => {
        const id = btn.getAttribute('data-id');
        modalTitle.textContent = btn.getAttribute('data-title');
        modalMessage.textContent = btn.getAttribute('data-message');
        modal.classList.add('active');

        // Mark as read via AJAX
        fetch('', {
          method: 'POST',
          headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
          body: 'mark_read_id=' + encodeURIComponent(id)
        });

        // Remove unread highlight and button, add read checkmark
        const notif = btn.closest('.notif');
        if (notif.classList.contains('unread')) {
          notif.classList.remove('unread');
          btn.remove();

          const checkmark = document.createElement('span');
          checkmark.className = 'read-indicator';
          checkmark.setAttribute('aria-label', 'Read');
          checkmark.textContent = '✔';
          notif.appendChild(checkmark);
        }
      });
    });

    closeBtn.onclick = () => modal.classList.remove('active');
    window.onclick = e => {
      if (e.target === modal) {
        modal.classList.remove('active');
      }
    };
    window.addEventListener('keydown', e => {
      if (e.key === 'Escape' && modal.classList.contains('active')) {
        modal.classList.remove('active');
      }
    });
  </script>
</body>
</html>
