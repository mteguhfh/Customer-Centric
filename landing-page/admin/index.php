<?php
session_start();

$password_hash = '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi'; // password: centricadmin

$data_file = __DIR__ . '/../data/content.json';

function read_content() {
    global $data_file;
    if (!file_exists($data_file)) return ['hero' => [], 'testimonials' => [], 'faq' => []];
    return json_decode(file_get_contents($data_file), true) ?: ['hero' => [], 'testimonials' => [], 'faq' => []];
}

function write_content($data) {
    global $data_file;
    return file_put_contents($data_file, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE));
}

// Ambil nilai konten per bahasa (backward-compat untuk format lama / flat string)
function tr($x, $lang) {
    if (is_array($x)) return isset($x[$lang]) ? $x[$lang] : ($x['id'] ?? '');
    return is_string($x) ? $x : '';
}

$error = '';
$success = '';

// Handle login
if (isset($_POST['login'])) {
    if (password_verify($_POST['password'], $password_hash)) {
        $_SESSION['logged_in'] = true;
    } else {
        $error = 'Password salah!';
    }
}

// Handle logout
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ?');
    exit;
}

// Handle save
if (isset($_POST['save']) && isset($_SESSION['logged_in'])) {
    $data = read_content();

    // Update hero
    $data['hero'] = [
        'headline' => [
            'id' => $_POST['hero_headline_id'] ?? '',
            'en' => $_POST['hero_headline_en'] ?? '',
            'ko' => $_POST['hero_headline_ko'] ?? '',
        ],
        'subheadline' => [
            'id' => $_POST['hero_subheadline_id'] ?? '',
            'en' => $_POST['hero_subheadline_en'] ?? '',
            'ko' => $_POST['hero_subheadline_ko'] ?? '',
        ],
    ];

    // Update testimonials
    $data['testimonials'] = [];
    if (isset($_POST['testimonial_quote_id'])) {
        foreach ($_POST['testimonial_quote_id'] as $i => $quote_id) {
            $quote_en = $_POST['testimonial_quote_en'][$i] ?? '';
            $quote_ko = $_POST['testimonial_quote_ko'][$i] ?? '';
            if (trim($quote_id) || trim($quote_en) || trim($quote_ko)) {
                $data['testimonials'][] = [
                    'id' => intval($_POST['testimonial_id'][$i] ?? 0),
                    'quote' => [
                        'id' => $quote_id,
                        'en' => $quote_en,
                        'ko' => $quote_ko,
                    ],
                    'name' => $_POST['testimonial_name'][$i] ?? '',
                    'role' => $_POST['testimonial_role'][$i] ?? '',
                    'company' => $_POST['testimonial_company'][$i] ?? '',
                    'rating' => intval($_POST['testimonial_rating'][$i] ?? 5)
                ];
            }
        }
    }

    // Update FAQ
    $data['faq'] = [];
    if (isset($_POST['faq_question_id'])) {
        foreach ($_POST['faq_question_id'] as $i => $question_id) {
            $question_en = $_POST['faq_question_en'][$i] ?? '';
            $question_ko = $_POST['faq_question_ko'][$i] ?? '';
            $answer_id = $_POST['faq_answer_id'][$i] ?? '';
            $answer_en = $_POST['faq_answer_en'][$i] ?? '';
            $answer_ko = $_POST['faq_answer_ko'][$i] ?? '';
            if (trim($question_id) || trim($question_en) || trim($question_ko) || trim($answer_id) || trim($answer_en) || trim($answer_ko)) {
                $data['faq'][] = [
                    'id' => intval($_POST['faq_id'][$i] ?? 0),
                    'question' => [
                        'id' => $question_id,
                        'en' => $question_en,
                        'ko' => $question_ko,
                    ],
                    'answer' => [
                        'id' => $answer_id,
                        'en' => $answer_en,
                        'ko' => $answer_ko,
                    ]
                ];
            }
        }
    }

    if (write_content($data)) {
        $success = 'Konten berhasil diperbarui!';
    } else {
        $error = 'Gagal menyimpan konten. Periksa izin file.';
    }
}

// Add new item
if (isset($_POST['add_testimonial']) && isset($_SESSION['logged_in'])) {
    $data = read_content();
    $max_id = 0;
    foreach ($data['testimonials'] as $t) { if ($t['id'] > $max_id) $max_id = $t['id']; }
    $data['testimonials'][] = ['id' => $max_id + 1, 'quote' => ['id' => '', 'en' => '', 'ko' => ''], 'name' => '', 'role' => '', 'company' => '', 'rating' => 5];
    write_content($data);
    $success = 'Testimonial baru ditambahkan.';
}

if (isset($_POST['add_faq']) && isset($_SESSION['logged_in'])) {
    $data = read_content();
    $max_id = 0;
    foreach ($data['faq'] as $f) { if ($f['id'] > $max_id) $max_id = $f['id']; }
    $data['faq'][] = ['id' => $max_id + 1, 'question' => ['id' => '', 'en' => '', 'ko' => ''], 'answer' => ['id' => '', 'en' => '', 'ko' => '']];
    write_content($data);
    $success = 'FAQ baru ditambahkan.';
}

// Delete item
if (isset($_GET['delete_testimonial']) && isset($_SESSION['logged_in'])) {
    $data = read_content();
    $del_id = intval($_GET['delete_testimonial']);
    $data['testimonials'] = array_values(array_filter($data['testimonials'], fn($t) => $t['id'] !== $del_id));
    write_content($data);
    header('Location: ?');
    exit;
}

if (isset($_GET['delete_faq']) && isset($_SESSION['logged_in'])) {
    $data = read_content();
    $del_id = intval($_GET['delete_faq']);
    $data['faq'] = array_values(array_filter($data['faq'], fn($f) => $f['id'] !== $del_id));
    write_content($data);
    header('Location: ?');
    exit;
}

$content = read_content();
$is_logged_in = isset($_SESSION['logged_in']) && $_SESSION['logged_in'] === true;

if (!$is_logged_in):
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Centric CMS</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-slate-900 min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-sm">
        <div class="text-center mb-8">
            <img src="../logo/logo_CC_putih.png" alt="Centric" class="h-10 mx-auto brightness-0 invert opacity-90 mb-4">
            <h1 class="text-2xl font-bold text-white">CMS Admin</h1>
            <p class="text-slate-400 text-sm mt-1">Masuk untuk mengelola konten landing page</p>
        </div>
        <?php if ($error): ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 px-4 py-3 rounded-xl text-sm mb-4"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" class="bg-slate-800 rounded-2xl p-8 border border-slate-700">
            <div class="mb-5">
                <label class="block text-slate-300 text-sm font-medium mb-2">Password</label>
                <input type="password" name="password" required class="w-full px-4 py-3 bg-slate-700 border border-slate-600 rounded-xl text-white text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent" placeholder="Masukkan password admin">
            </div>
            <button type="submit" name="login" class="w-full px-5 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-all text-sm">Masuk</button>
        </form>
        <p class="text-center mt-6 text-slate-500 text-xs">Landing Page v1.0 &mdash; Centric Ecosystem</p>
    </div>
</body>
</html>
<?php else: ?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CMS Admin - Centric Ecosystem</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <style>*{font-family:'Inter',sans-serif;}</style>
</head>
<body class="bg-slate-50 min-h-screen">
    <nav class="bg-white border-b border-slate-200 sticky top-0 z-50">
        <div class="max-w-6xl mx-auto px-4 sm:px-6">
            <div class="flex items-center justify-between h-16">
                <div class="flex items-center gap-3">
                    <img src="../logo/logo_CC_biru.png" alt="Centric" class="h-7 w-auto">
                    <span class="text-slate-400 text-sm">CMS</span>
                </div>
                <div class="flex items-center gap-4">
                    <a href="../" target="_blank" class="text-slate-500 hover:text-indigo-600 text-sm transition-colors">Lihat Halaman</a>
                    <a href="?logout=1" class="text-sm text-red-500 hover:text-red-600 transition-colors">Logout</a>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-6xl mx-auto px-4 sm:px-6 py-8">
        <?php if ($success): ?>
            <div class="bg-emerald-500/10 border border-emerald-500/20 text-emerald-700 px-5 py-3.5 rounded-xl text-sm mb-6 flex items-center gap-2">
                <svg class="w-5 h-5 shrink-0" fill="currentColor" viewBox="0 0 20 20"><path d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z"/></svg>
                <?= htmlspecialchars($success) ?>
            </div>
        <?php endif; ?>

        <form method="post">
            <!-- ===== HERO SECTION ===== -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 mb-6">
                <h2 class="text-lg font-bold text-slate-900 mb-1">Hero Section</h2>
                <p class="text-slate-500 text-sm mb-5">Ubah teks headline dan subheadline pada hero landing page.</p>
                <div class="space-y-4">
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Headline <span class="text-indigo-600 font-semibold">ID</span></label>
                            <textarea name="hero_headline_id" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['headline'] ?? '', 'id')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Headline <span class="text-emerald-600 font-semibold">EN</span></label>
                            <textarea name="hero_headline_en" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['headline'] ?? '', 'en')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Headline <span class="text-sky-600 font-semibold">KO</span></label>
                            <textarea name="hero_headline_ko" rows="2" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['headline'] ?? '', 'ko')) ?></textarea>
                        </div>
                    </div>
                    <p class="text-xs text-slate-400">HTML tags diperbolehkan. Gunakan <code class="text-indigo-600 bg-indigo-50 px-1 rounded">&lt;span class="..."&gt;</code> untuk gradient text.</p>
                    <div class="grid md:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Subheadline <span class="text-indigo-600 font-semibold">ID</span></label>
                            <textarea name="hero_subheadline_id" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['subheadline'] ?? '', 'id')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Subheadline <span class="text-emerald-600 font-semibold">EN</span></label>
                            <textarea name="hero_subheadline_en" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['subheadline'] ?? '', 'en')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-slate-700 mb-1.5">Subheadline <span class="text-sky-600 font-semibold">KO</span></label>
                            <textarea name="hero_subheadline_ko" rows="3" class="w-full px-4 py-3 border border-slate-300 rounded-xl text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($content['hero']['subheadline'] ?? '', 'ko')) ?></textarea>
                        </div>
                    </div>
                </div>
            </section>

            <!-- ===== TESTIMONIALS ===== -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 mb-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold text-slate-900">Testimonials</h2>
                    <button type="submit" name="add_testimonial" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-all">+ Tambah</button>
                </div>
                <p class="text-slate-500 text-sm mb-5">Kelola testimonial yang ditampilkan di landing page.</p>

                <?php foreach (($content['testimonials'] ?? []) as $i => $t): ?>
                <div class="border border-slate-200 rounded-xl p-5 mb-4 <?= $i > 0 ? 'mt-4' : '' ?>">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">Testimonial #<?= $i + 1 ?></span>
                        <a href="?delete_testimonial=<?= $t['id'] ?>" class="text-red-500 hover:text-red-600 text-xs font-medium" onclick="return confirm('Hapus testimonial ini?')">Hapus</a>
                    </div>
                    <input type="hidden" name="testimonial_id[]" value="<?= $t['id'] ?>">
                    <div class="grid sm:grid-cols-3 gap-4 mb-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Quote <span class="text-indigo-600 font-semibold">ID</span></label>
                            <textarea name="testimonial_quote_id[]" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($t['quote'] ?? '', 'id')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Quote <span class="text-emerald-600 font-semibold">EN</span></label>
                            <textarea name="testimonial_quote_en[]" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($t['quote'] ?? '', 'en')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Quote <span class="text-sky-600 font-semibold">KO</span></label>
                            <textarea name="testimonial_quote_ko[]" rows="2" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($t['quote'] ?? '', 'ko')) ?></textarea>
                        </div>
                    </div>
                    <div class="grid sm:grid-cols-2 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Nama</label>
                            <input type="text" name="testimonial_name[]" value="<?= htmlspecialchars($t['name'] ?? '') ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Rating (1-5)</label>
                            <input type="number" name="testimonial_rating[]" value="<?= $t['rating'] ?? 5 ?>" min="1" max="5" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Role / Jabatan</label>
                            <input type="text" name="testimonial_role[]" value="<?= htmlspecialchars($t['role']) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Perusahaan</label>
                            <input type="text" name="testimonial_company[]" value="<?= htmlspecialchars($t['company']) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($content['testimonials'])): ?>
                <p class="text-slate-400 text-sm text-center py-8">Belum ada testimonial. Klik "+ Tambah" untuk menambahkan.</p>
                <?php endif; ?>
            </section>

            <!-- ===== FAQ ===== -->
            <section class="bg-white rounded-2xl border border-slate-200 p-6 sm:p-8 mb-6">
                <div class="flex items-center justify-between mb-1">
                    <h2 class="text-lg font-bold text-slate-900">FAQ</h2>
                    <button type="submit" name="add_faq" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white text-sm font-medium rounded-xl transition-all">+ Tambah</button>
                </div>
                <p class="text-slate-500 text-sm mb-5">Kelola pertanyaan yang sering diajukan.</p>

                <?php foreach (($content['faq'] ?? []) as $i => $f): ?>
                <div class="border border-slate-200 rounded-xl p-5 mb-4">
                    <div class="flex items-center justify-between mb-3">
                        <span class="text-xs font-medium text-slate-400 uppercase tracking-wider">FAQ #<?= $i + 1 ?></span>
                        <a href="?delete_faq=<?= $f['id'] ?>" class="text-red-500 hover:text-red-600 text-xs font-medium" onclick="return confirm('Hapus FAQ ini?')">Hapus</a>
                    </div>
                    <input type="hidden" name="faq_id[]" value="<?= $f['id'] ?>">
                    <div class="grid sm:grid-cols-3 gap-4">
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pertanyaan <span class="text-indigo-600 font-semibold">ID</span></label>
                            <input type="text" name="faq_question_id[]" value="<?= htmlspecialchars(tr($f['question'] ?? '', 'id')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pertanyaan <span class="text-emerald-600 font-semibold">EN</span></label>
                            <input type="text" name="faq_question_en[]" value="<?= htmlspecialchars(tr($f['question'] ?? '', 'en')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Pertanyaan <span class="text-sky-600 font-semibold">KO</span></label>
                            <input type="text" name="faq_question_ko[]" value="<?= htmlspecialchars(tr($f['question'] ?? '', 'ko')) ?>" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent">
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Jawaban <span class="text-indigo-600 font-semibold">ID</span></label>
                            <textarea name="faq_answer_id[]" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($f['answer'] ?? '', 'id')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Jawaban <span class="text-emerald-600 font-semibold">EN</span></label>
                            <textarea name="faq_answer_en[]" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($f['answer'] ?? '', 'en')) ?></textarea>
                        </div>
                        <div>
                            <label class="block text-xs font-medium text-slate-600 mb-1">Jawaban <span class="text-sky-600 font-semibold">KO</span></label>
                            <textarea name="faq_answer_ko[]" rows="3" class="w-full px-3 py-2 border border-slate-300 rounded-lg text-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:border-transparent"><?= htmlspecialchars(tr($f['answer'] ?? '', 'ko')) ?></textarea>
                        </div>
                    </div>
                </div>
                <?php endforeach; ?>
                <?php if (empty($content['faq'])): ?>
                <p class="text-slate-400 text-sm text-center py-8">Belum ada FAQ. Klik "+ Tambah" untuk menambahkan.</p>
                <?php endif; ?>
            </section>

            <!-- ===== SAVE ===== -->
            <div class="sticky bottom-4 bg-white rounded-2xl border border-slate-200 p-4 shadow-lg flex items-center justify-between">
                <p class="text-sm text-slate-500">Semua perubahan akan langsung diterapkan di landing page.</p>
                <button type="submit" name="save" class="px-8 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-semibold rounded-xl transition-all text-sm">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</body>
</html>
<?php endif; ?>
