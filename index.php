<?php
header('Content-Type: text/html; charset=utf-8');

// Получаем данные из cookies
$errors = [];
$old_values = [];
$success_values = [];

if (isset($_COOKIE['form_errors'])) {
    $errors = json_decode($_COOKIE['form_errors'], true);
    // Удаляем cookie после использования
    setcookie('form_errors', '', time() - 3600, '/');
}

if (isset($_COOKIE['form_old_values'])) {
    $old_values = json_decode($_COOKIE['form_old_values'], true);
    // Удаляем cookie после использования
    setcookie('form_old_values', '', time() - 3600, '/');
}

if (isset($_COOKIE['form_success_values'])) {
    $success_values = json_decode($_COOKIE['form_success_values'], true);
}

// Приоритет значений: старые (при ошибке) > успешные > пустые
$values = array_merge($success_values, $old_values);
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <title>Форма</title>
</head>
<body>
    <div class="form-container">
        <h1>Форма</h1>
        
        <?php if (!empty($errors)): ?>
            <div class="error-messages">
                <h2>Ошибки при заполнении формы:</h2>
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?= htmlspecialchars($error) ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form action="form.php" method="post">
            <div class="form-group <?= isset($errors['fio']) ? 'has-error' : '' ?>">
                <label for="fio">ФИО:</label>
                <input type="text" id="fio" name="fio" value="<?= htmlspecialchars($values['fio'] ?? '') ?>" required>
                <?php if (isset($errors['fio'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['fio']) ?></div>
                <?php endif; ?>
            </div>            

            <div class="form-group <?= isset($errors['phone']) ? 'has-error' : '' ?>">
                <label for="phone">Телефон:</label>
                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($values['phone'] ?? '') ?>" required>
                <?php if (isset($errors['phone'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['phone']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['email']) ? 'has-error' : '' ?>">
                <label for="email">E-mail:</label>
                <input type="email" id="email" name="email" value="<?= htmlspecialchars($values['email'] ?? '') ?>" required>
                <?php if (isset($errors['email'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['email']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['dob']) ? 'has-error' : '' ?>">
                <label for="dob">Дата рождения:</label>
                <input type="date" id="dob" name="dob" value="<?= htmlspecialchars($values['dob'] ?? '') ?>" required>
                <?php if (isset($errors['dob'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['dob']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['gender']) ? 'has-error' : '' ?>">
                <label>Пол:</label>
                <label for="male">
                    <input type="radio" id="male" name="gender" value="male" <?= ($values['gender'] ?? '') === 'male' ? 'checked' : '' ?> required> Мужской
                </label>
                <label for="female">
                    <input type="radio" id="female" name="gender" value="female" <?= ($values['gender'] ?? '') === 'female' ? 'checked' : '' ?>> Женский
                </label>
                <?php if (isset($errors['gender'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['gender']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['language']) ? 'has-error' : '' ?>">
                <label for="language">Любимый язык программирования:</label>
                <select id="language" name="language[]" multiple required>
                    <?php
                    $validLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python', 
                                     'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                    $selectedLanguages = $values['language'] ?? [];
                    foreach ($validLanguages as $lang): ?>
                        <option value="<?= htmlspecialchars($lang) ?>" <?= in_array($lang, $selectedLanguages) ? 'selected' : '' ?>>
                            <?= htmlspecialchars($lang) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <?php if (isset($errors['language'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['language']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['bio']) ? 'has-error' : '' ?>">
                <label for="bio">Биография:</label>
                <textarea id="bio" name="bio" rows="5" required><?= htmlspecialchars($values['bio'] ?? '') ?></textarea>
                <?php if (isset($errors['bio'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['bio']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group <?= isset($errors['contract']) ? 'has-error' : '' ?>">
                <label for="contract">
                    <input type="checkbox" id="contract" name="contract" <?= ($values['contract'] ?? false) ? 'checked' : '' ?> required> С контрактом ознакомлен(а)
                </label>
                <?php if (isset($errors['contract'])): ?>
                    <div class="error"><?= htmlspecialchars($errors['contract']) ?></div>
                <?php endif; ?>
            </div>

            <div class="form-group">
                <button type="submit">Сохранить</button>
            </div>
        </form>
    </div>
</body>
</html>