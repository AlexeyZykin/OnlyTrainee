<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Управление Яндекс.Диском</title>
    <link rel="stylesheet" href="/style.css">
</head>
<body>

    <main class="main-container">
        <div class="content-container">
            <?php if (isset($data["messages"]["error"])): ?>
                <div class="message-wrapper">
                    <div class="error-card">
                        <?= htmlspecialchars($data["messages"]["error"]) ?>
                    </div>
                </div>
            <?php endif; ?>

            <?php if (isset($data["messages"]["success"])): ?>
                <div class="message-wrapper">
                    <div class="success-card">
                        <?= htmlspecialchars($data["messages"]["success"]) ?>
                    </div>
                </div>
            <?php endif; ?>

            <div class="section">
                <h2>Загрузка файла</h2>
                <form class="form" method="post" action="/upload" enctype="multipart/form-data">
                    <input type="file" name="filename">
                    <input type="submit" value="Загрузить" />
                </form>
            </div>

            <div class="section">
                <h2>Список файлов диска</h2>
                <table>
                    <thead>
                    <tr>
                        <th>Имя</th>
                        <th>Тип</th>
                        <th>Действия</th>
                    </tr>
                    </thead>
                    <tbody>
                        <?php if (isset($data["files"]) && is_array($data["files"])): ?>
                            <?php foreach ($data["files"] as $arrFile): ?>
                                <?php if (!is_array($arrFile)) continue; ?>

                                <tr>
                                    <td>
                                        <?= htmlspecialchars($arrFile['name']) ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($arrFile['mime_type']) ?>
                                    </td>

                                    <td class="table-row-actions">
                                        <form method="get" action="/get">
                                            <input type="hidden" name="filename" value="<?= htmlspecialchars($arrFile['name']) ?>">
                                            <button type="submit">Открыть</button>
                                        </form>

                                        <form method="post" action="/delete">
                                            <input type="hidden" name="filename" value="<?= htmlspecialchars($arrFile['name']) ?>">
                                            <button type="submit">Удалить</button>
                                        </form>

                                        <div class="table-action-wrapper">
                                            <form method="post" action="/update" enctype="multipart/form-data">
                                                <input type="hidden" name="old_filename" value="<?= htmlspecialchars($arrFile['name']) ?>">
                                                <input type="file" name="new_filename">
                                                <button type="submit">Изменить</button>
                                            </form>
                                        </div>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

        </div>
    </main>

</body>
</html>