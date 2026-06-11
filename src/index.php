<?php
declare(strict_types=1);
require_once __DIR__ . '/config.php';

Auth::startSession();
if (Auth::needsSetup()) redirect('/auth/setup.php');
Auth::requireAuth();

$user = Auth::user();

// ── Handle POST actions ────────────────────────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'add_app') {
        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $link     = trim($_POST['link'] ?? '');
        $location = $_POST['location'] ?? 'apps';
        $cat      = ($location === 'home' && !empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
        if ($title && $link && !empty($_FILES['image']['name'])) {
            $imgName = uploadFile($_FILES['image'], LOGOS_DIR);
            if ($imgName) {
                Database::query(
                    "INSERT INTO apps (title,description,image,link,category_id,sort_order,location)
                     VALUES (?,?,?,?,?,(SELECT IFNULL(MAX(sort_order),0)+1 FROM apps WHERE IFNULL(category_id,0)=IFNULL(?,0)),?)",
                    [$title, $desc, $imgName, $link, $cat, $cat, $location]
                );
            }
        }
        redirect('/');
    }

    if ($action === 'edit_app') {
        $id       = (int)$_POST['id'];
        $title    = trim($_POST['title'] ?? '');
        $desc     = trim($_POST['description'] ?? '');
        $link     = trim($_POST['link'] ?? '');
        $location = $_POST['location'] ?? 'apps';
        $cat      = ($location === 'home' && !empty($_POST['category_id'])) ? (int)$_POST['category_id'] : null;
        if (!empty($_FILES['image']['name']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
            $old = Database::fetchOne("SELECT image FROM apps WHERE id=?", [$id]);
            if ($old) @unlink(LOGOS_DIR . '/' . $old['image']);
            $imgName = uploadFile($_FILES['image'], LOGOS_DIR);
            if ($imgName) {
                Database::query("UPDATE apps SET title=?,description=?,link=?,category_id=?,image=?,location=? WHERE id=?",
                    [$title, $desc, $link, $cat, $imgName, $location, $id]);
            }
        } else {
            Database::query("UPDATE apps SET title=?,description=?,link=?,category_id=?,location=? WHERE id=?",
                [$title, $desc, $link, $cat, $location, $id]);
        }
        redirect('/');
    }

    if ($action === 'delete_app') {
        $id  = (int)$_POST['id'];
        $app = Database::fetchOne("SELECT image FROM apps WHERE id=?", [$id]);
        if ($app) {
            @unlink(LOGOS_DIR . '/' . $app['image']);
            Database::query("DELETE FROM apps WHERE id=?", [$id]);
        }
        redirect('/');
    }

    if ($action === 'add_category') {
        $name = trim($_POST['cat_name'] ?? '');
        if ($name) {
            Database::query(
                "INSERT INTO categories (name,sort_order) VALUES (?,(SELECT IFNULL(MAX(sort_order),0)+1 FROM categories c2))",
                [$name]
            );
        }
        redirect('/');
    }

    if ($action === 'edit_category') {
        $id   = (int)$_POST['cat_id'];
        $name = trim($_POST['cat_name'] ?? '');
        if ($name) Database::query("UPDATE categories SET name=? WHERE id=?", [$name, $id]);
        redirect('/');
    }
}

// ── Fetch data ─────────────────────────────────────────────────────────────
$categories  = Database::fetchAll("SELECT * FROM categories ORDER BY sort_order");
$appsApps    = Database::fetchAll("SELECT * FROM apps WHERE location='apps' ORDER BY sort_order");
$termUrl     = getSetting('terminal_url', null, 'http://localhost:2222');
$weatherCity = getSetting('weather_city', null, 'Dhaka');

$pageTitle = 'Dashboard';
require_once __DIR__ . '/partials/header.php';
?>

<main>

<!-- ══════════ WIDGET / TABS (centred, constrained) ══════════ -->
<div class="page-wrap">
  <div class="widget-bar">
    <div id="time">00:00:00</div>
    <div id="date"></div>
    <div id="weather">
      <i class="fa-solid fa-temperature-half" id="weather-icon" style="color:var(--accent)"></i>
      <span id="weather-val">–</span>
    </div>
  </div>

  <div class="page-tabs" id="pageTabs">
    <button class="tab-btn active" onclick="showTab(0,this)">
      <i class="fa-solid fa-house"></i> Home
    </button>
    <button class="tab-btn" onclick="showTab(1,this)">
      <i class="fa-solid fa-grip"></i> Apps
    </button>
    <button class="tab-btn" onclick="showTab(2,this)">
      <i class="fa-solid fa-chart-column"></i> Stats
    </button>
    <button class="tab-btn" onclick="showTab(3,this)">
      <i class="fa-solid fa-terminal"></i> Terminal
    </button>
  </div>

  <hr class="section-divider">
</div><!-- /.page-wrap -->

<!-- ══════════ FULL-WIDTH SLIDER ══════════ -->
<div class="slider-wrapper">
  <div class="slider" id="mainSlider">

    <!-- ── HOME SLIDE ── -->
    <section class="slide" id="slide-0">
      <div class="slide-inner">

        <form action="https://www.google.com/search" method="get" target="_blank" class="search-box">
          <input type="text" name="q" placeholder="Search Google…">
          <button type="submit"><i class="fa-solid fa-magnifying-glass"></i></button>
        </form>

        <div class="section-head">
          <i class="fa-solid fa-bookmark page-icon"></i>
          <h2>Bookmarks</h2>
          <button class="add-icon" onclick="openModal('addCatModal')" title="Add Category">
            <i class="fa-solid fa-folder-plus"></i>
          </button>
        </div>

        <div id="homeCategories">
        <?php foreach ($categories as $cat):
          $catApps = Database::fetchAll(
              "SELECT * FROM apps WHERE category_id=? AND location='home' ORDER BY sort_order",
              [$cat['id']]
          );
        ?>
        <div class="category-block" data-cat-id="<?= $cat['id'] ?>">
          <div class="category-row">
            <i class="fa-solid fa-grip-vertical drag-handle cat-drag"></i>
            <h2><?= e($cat['name']) ?></h2>
            <div class="cat-actions">
              <button class="cat-action-btn" onclick='openAddForCat(<?= $cat['id'] ?>)' title="Add App">
                <i class="fa-solid fa-plus"></i>
              </button>
              <button class="cat-action-btn" onclick='openEditCat(<?= $cat["id"] ?>, <?= json_encode($cat["name"]) ?>)' title="Edit">
                <i class="fa-solid fa-pen"></i>
              </button>
              <button class="cat-action-btn danger" onclick="deleteCat(<?= $cat['id'] ?>)" title="Delete">
                <i class="fa-solid fa-trash"></i>
              </button>
            </div>
          </div>

          <div class="cat-grid" data-cat-id="<?= $cat['id'] ?>">
            <?php foreach ($catApps as $i => $a): ?>
            <div class="app-card <?= $i >= 6 ? 'extra-card' : '' ?>" data-id="<?= $a['id'] ?>">
              <button class="card-menu-btn" onclick="toggleCardMenu(event,this)">
                <i class="fa-solid fa-ellipsis-vertical"></i>
              </button>
              <div class="card-actions">
                <button class="card-action-item" onclick='openEditApp(<?= json_encode($a) ?>)'>
                  <i class="fa-solid fa-pen"></i> Edit
                </button>
                <button class="card-action-item danger" onclick="deleteApp(<?= $a['id'] ?>)">
                  <i class="fa-solid fa-trash"></i> Delete
                </button>
              </div>
              <a href="<?= e($a['link']) ?>" target="_blank" class="card-link" style="text-decoration:none;color:inherit;">
                <div class="card-title"><?= e($a['title']) ?></div>
                <div class="card-logo">
                  <img src="/Logos/<?= e($a['image']) ?>" alt="<?= e($a['title']) ?>"
                       onerror="this.src='/assets/img/app-placeholder.php'">
                </div>
              </a>
            </div>
            <?php endforeach; ?>
          </div>

          <?php if (count($catApps) > 6): ?>
          <button class="btn btn-sm" style="margin:8px 0 0 4px;"
                  onclick="toggleExpand(this, this.closest('.category-block').querySelector('.cat-grid'))">
            Show all (<?= count($catApps) ?>)
          </button>
          <?php endif; ?>
        </div>
        <?php endforeach; ?>
        </div>

      </div>
    </section>

    <!-- ── APPS SLIDE ── -->
    <section class="slide" id="slide-1">
      <div class="slide-inner">
        <div class="section-head">
          <i class="fa-solid fa-grip page-icon"></i>
          <h2>Apps</h2>
          <button class="add-icon" onclick="openAddApp('apps')" title="Add App">
            <i class="fa-solid fa-plus"></i>
          </button>
        </div>

        <div class="apps-grid" id="appsGrid">
          <?php foreach ($appsApps as $a): ?>
          <div class="app-card" data-id="<?= $a['id'] ?>">
            <button class="card-menu-btn" onclick="toggleCardMenu(event,this)">
              <i class="fa-solid fa-ellipsis-vertical"></i>
            </button>
            <div class="card-actions">
              <button class="card-action-item" onclick='openEditApp(<?= json_encode($a) ?>)'>
                <i class="fa-solid fa-pen"></i> Edit
              </button>
              <button class="card-action-item danger" onclick="deleteApp(<?= $a['id'] ?>)">
                <i class="fa-solid fa-trash"></i> Delete
              </button>
            </div>
            <a href="<?= e($a['link']) ?>" target="_blank" style="text-decoration:none;color:inherit;">
              <div class="card-title"><?= e($a['title']) ?></div>
              <div class="card-logo">
                <img src="/Logos/<?= e($a['image']) ?>" alt="<?= e($a['title']) ?>"
                     onerror="this.src='/assets/img/app-placeholder.php'">
              </div>
            </a>
          </div>
          <?php endforeach; ?>
          <?php if (empty($appsApps)): ?>
          <div style="color:var(--muted);padding:20px;grid-column:1/-1;">
            No apps yet. Click <strong>+</strong> to add one.
          </div>
          <?php endif; ?>
        </div>
      </div>
    </section>

    <!-- ── STATS SLIDE ── -->
    <section class="slide" id="slide-2">
      <div class="slide-inner">
        <div class="section-head">
          <i class="fa-solid fa-chart-column page-icon"></i>
          <h2>System Stats</h2>
        </div>

        <div class="stats-grid">
          <div class="stat-card">
            <div class="stat-label">
              <span><i class="fa-solid fa-microchip stat-icon"></i> CPU</span>
              <span class="stat-value"><b id="cpu-val">–</b>% &nbsp;(<span id="temp-val">–</span>°C)</span>
            </div>
            <div class="progress-bg"><div id="cpu-bar" class="progress-fill" style="width:0"></div></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">
              <span><i class="fa-solid fa-memory stat-icon"></i> RAM</span>
              <span class="stat-value"><b id="ram-used">–</b> / <span id="ram-total">–</span> GB</span>
            </div>
            <div class="progress-bg"><div id="ram-bar" class="progress-fill" style="width:0"></div></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">
              <span><i class="fa-solid fa-hard-drive stat-icon"></i> System Disk</span>
              <span class="stat-value"><b id="ssd-used">–</b> / <span id="ssd-total">–</span> GB</span>
            </div>
            <div class="progress-bg"><div id="ssd-bar" class="progress-fill" style="width:0"></div></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">
              <span><i class="fa-solid fa-database stat-icon"></i> Main Storage</span>
              <span class="stat-value"><b id="storage-used">–</b> / <span id="storage-total">–</span> GB</span>
            </div>
            <div class="progress-bg"><div id="storage-bar" class="progress-fill" style="width:0"></div></div>
          </div>
          <div class="stat-card">
            <div class="stat-label">
              <span><i class="fa-solid fa-network-wired stat-icon"></i> Network</span>
            </div>
            <div class="net-row">
              <span><i class="fa-solid fa-arrow-down" style="color:var(--accent)"></i> Download</span>
              <strong><span id="net-down">–</span> Mbps</strong>
            </div>
            <div class="net-row" style="margin-top:6px">
              <span><i class="fa-solid fa-arrow-up" style="color:var(--accent)"></i> Upload</span>
              <strong><span id="net-up">–</span> Mbps</strong>
            </div>
          </div>
        </div>
      </div>
    </section>

    <!-- ── TERMINAL SLIDE ── -->
    <section class="slide" id="slide-3">
      <div class="slide-inner">
        <div class="section-head">
          <i class="fa-solid fa-terminal page-icon"></i>
          <h2>Terminal</h2>
          <a href="<?= e($termUrl) ?>" target="_blank" class="btn btn-sm" style="margin-left:auto;">
            <i class="fa-solid fa-arrow-up-right-from-square"></i> Open
          </a>
        </div>
        <div class="terminal-wrap">
          <iframe src="<?= e($termUrl) ?>" id="termIframe"></iframe>
        </div>
      </div>
    </section>

  </div><!-- .slider -->
</div><!-- .slider-wrapper -->

</main>

<!-- ═══════════════════════════ MODALS ═══════════════════════════ -->

<!-- ADD APP MODAL -->
<div class="modal" id="addAppModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('addAppModal')">&times;</button>
    <h2 class="modal-title"><i class="fa-solid fa-plus"></i> Add App</h2>
    <form method="POST" enctype="multipart/form-data" id="addAppForm">
      <input type="hidden" name="action" value="add_app">
      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span></label>
        <input class="form-input" type="text" name="title" placeholder="App name" required>
      </div>
      <div class="form-group">
        <label class="form-label">Note / Description</label>
        <textarea class="form-textarea" name="description" placeholder="Optional description"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">URL <span class="req">*</span></label>
        <input class="form-input" type="url" name="link" placeholder="https://..." required>
      </div>
      <div class="form-group">
        <label class="form-label">Slide</label>
        <select class="form-select" name="location" id="addLocation" onchange="toggleCatField('addCat','addLocation')">
          <option value="home">Home (Bookmarks)</option>
          <option value="apps">Apps</option>
        </select>
      </div>
      <div class="form-group" id="addCat">
        <label class="form-label">Category <span class="req">*</span></label>
        <select class="form-select" name="category_id" id="addCatSelect">
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">Icon <span class="req">*</span></label>
        <input class="form-input" type="file" name="image" accept="image/*" required>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Add App</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT APP MODAL -->
<div class="modal" id="editAppModal">
  <div class="modal-box">
    <button class="modal-close" onclick="closeModal('editAppModal')">&times;</button>
    <h2 class="modal-title"><i class="fa-solid fa-pen"></i> Edit App</h2>
    <form method="POST" enctype="multipart/form-data">
      <input type="hidden" name="action" value="edit_app">
      <input type="hidden" name="id" id="editId">
      <div class="form-group">
        <label class="form-label">Title <span class="req">*</span></label>
        <input class="form-input" type="text" name="title" id="editTitle" required>
      </div>
      <div class="form-group">
        <label class="form-label">Note</label>
        <textarea class="form-textarea" name="description" id="editDesc"></textarea>
      </div>
      <div class="form-group">
        <label class="form-label">URL <span class="req">*</span></label>
        <input class="form-input" type="url" name="link" id="editLink" required>
      </div>
      <div class="form-group">
        <label class="form-label">Slide</label>
        <select class="form-select" name="location" id="editLocation" onchange="toggleCatField('editCat','editLocation')">
          <option value="home">Home (Bookmarks)</option>
          <option value="apps">Apps</option>
        </select>
      </div>
      <div class="form-group" id="editCat">
        <label class="form-label">Category</label>
        <select class="form-select" name="category_id" id="editCatSelect">
          <?php foreach ($categories as $c): ?>
          <option value="<?= $c['id'] ?>"><?= e($c['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>
      <div class="form-group">
        <label class="form-label">New Icon (leave blank to keep current)</label>
        <input class="form-input" type="file" name="image" accept="image/*">
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Save Changes</button>
      </div>
    </form>
  </div>
</div>

<!-- ADD CATEGORY MODAL -->
<div class="modal" id="addCatModal">
  <div class="modal-box" style="max-width:380px">
    <button class="modal-close" onclick="closeModal('addCatModal')">&times;</button>
    <h2 class="modal-title"><i class="fa-solid fa-folder-plus"></i> Add Category</h2>
    <form method="POST">
      <input type="hidden" name="action" value="add_category">
      <div class="form-group">
        <label class="form-label">Name <span class="req">*</span></label>
        <input class="form-input" type="text" name="cat_name" placeholder="Category name" required autofocus>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Add Category</button>
      </div>
    </form>
  </div>
</div>

<!-- EDIT CATEGORY MODAL -->
<div class="modal" id="editCatModal">
  <div class="modal-box" style="max-width:380px">
    <button class="modal-close" onclick="closeModal('editCatModal')">&times;</button>
    <h2 class="modal-title"><i class="fa-solid fa-pen"></i> Edit Category</h2>
    <form method="POST">
      <input type="hidden" name="action" value="edit_category">
      <input type="hidden" name="cat_id" id="editCatId">
      <div class="form-group">
        <label class="form-label">Name <span class="req">*</span></label>
        <input class="form-input" type="text" name="cat_name" id="editCatName" required>
      </div>
      <div class="form-actions">
        <button type="submit" class="btn btn-primary btn-full">Save</button>
      </div>
    </form>
  </div>
</div>

<!-- DELETE FORMS (hidden) -->
<form id="deleteAppForm"  method="POST" style="display:none">
  <input type="hidden" name="action" value="delete_app">
  <input type="hidden" name="id" id="deleteAppId">
</form>
<form id="deleteCatForm"  method="POST" action="/api/delete_category.php" style="display:none">
  <input type="hidden" name="id" id="deleteCatId">
</form>

<!-- TOAST CONTAINER -->
<div id="toast-container"></div>

<script src="/assets/js/app.js" defer></script>
<script>
const WEATHER_CITY = <?= json_encode($weatherCity) ?>;
</script>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
