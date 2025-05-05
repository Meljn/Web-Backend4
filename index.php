<?php
header('Content-Type: text/html; charset=utf-8');

$errors = json_decode($_COOKIE['form_errors'] ?? '{}', true);
$values = json_decode($_COOKIE['form_values'] ?? '{}', true);

setcookie('form_errors', '', time() - 3600, '/');
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Форма</title>
    <link rel="stylesheet" href="style.css">
</head>
<body>
<div class="form-container">
    <h1>Форма</h1>

    <?php if (!empty($errors)): ?>
        <div class="error">
            <strong>Пожалуйста, исправьте ошибки:</strong>
            <ul>
                <?php foreach ($errors as $error): ?>
                    <li><?= htmlspecialchars($error) ?></li>
                <?php endforeach; ?>
            </ul>
        </div>
    <?php endif; ?>

    <form action="form.php" method="post">
        <div class="form-group">
            <label for="fio">ФИО:</label>
            <input type="text" id="fio" name="fio"
                   value="<?= htmlspecialchars($values['fio'] ?? '') ?>"
                   class="<?= isset($errors['fio']) ? 'input-error' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="phone">Телефон:</label>
            <input type="tel" id="phone" name="phone"
                   value="<?= htmlspecialchars($values['phone'] ?? '') ?>"
                   class="<?= isset($errors['phone']) ? 'input-error' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="email">E-mail:</label>
            <input type="email" id="email" name="email"
                   value="<?= htmlspecialchars($values['email'] ?? '') ?>"
                   class="<?= isset($errors['email']) ? 'input-error' : '' ?>" required>
        </div>

        <div class="form-group">
            <label for="dob">Дата рождения:</label>
            <input type="date" id="dob" name="dob"
                   value="<?= htmlspecialchars($values['dob'] ?? '') ?>"
                   class="<?= isset($errors['dob']) ? 'input-error' : '' ?>" required>
        </div>

        <div class="form-group">
            <label>Пол:</label>
            <label>
                <input type="radio" name="gender" value="male"
                    <?= (isset($values['gender']) && $values['gender'] === 'male') ? 'checked' : '' ?>>
                Мужской
            </label>
            <label>
                <input type="radio" name="gender" value="female"
                    <?= (isset($values['gender']) && $values['gender'] === 'female') ? 'checked' : '' ?>>
                Женский
            </label>
        </div>

        <div class="form-group">
            <label for="language">Любимый язык программирования:</label>
            <select id="language" name="language[]" multiple
                    class="<?= isset($errors['language']) ? 'input-error' : '' ?>" required>
                <?php
                $langs = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python',
                          'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
                $selected = $values['language'] ?? [];
                foreach ($langs as $lang): ?>
                    <option value="<?= $lang ?>" <?= in_array($lang, $selected) ? 'selected' : '' ?>>
                        <?= $lang ?>
                    </option>
                <?php endforeach; ?>
            </select>
        </div>

        <div class="form-group">
            <label for="bio">Биография:</label>
            <textarea id="bio" name="bio" rows="5"
                      class="<?= isset($errors['bio']) ? 'input-error' : '' ?>"
                      required><?= htmlspecialchars($values['bio'] ?? '') ?></textarea>
        </div>

        <div class="form-group">
            <label>
                <input type="checkbox" name="contract"
                       <?= isset($values['contract']) && $values['contract'] === 'on' ? 'checked' : '' ?>>
                С контрактом ознакомлен(а)
            </label>
        </div>

        <div class="form-group">
            <button type="submit">Сохранить</button>
        </div>
    </form>
</div>
</body>
</html>
