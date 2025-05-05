<?php
header('Content-Type: text/html; charset=utf-8');

$db_host = 'localhost';
$db_user = 'u68532';
$db_pass = '9110579';
$db_name = 'u68532';

try {
    $pdo = new PDO("mysql:host=$db_host;dbname=$db_name;charset=utf8mb4", $db_user, $db_pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    die("Ошибка подключения к базе данных: " . $e->getMessage());
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $errors = [];

    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        $errors['fio'] = 'Поле ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $fio)) {
        $errors['fio'] = 'ФИО должно содержать только буквы и пробелы';
    } elseif (mb_strlen($fio, 'UTF-8') > 150) {
        $errors['fio'] = 'ФИО не должно превышать 150 символов';
    }

    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Поле Телефон обязательно для заполнения';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать от 10 до 15 цифр, может начинаться с +';
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Поле E-mail обязательно для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес';
    }

    $dob = $_POST['dob'] ?? '';
    if (empty($dob)) {
        $errors['dob'] = 'Поле Дата рождения обязательно для заполнения';
    } else {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
            $errors['dob'] = 'Введите корректную дату рождения';
        }
    }

    $gender = $_POST['gender'] ?? '';
    if (empty($gender)) {
        $errors['gender'] = 'Поле Пол обязательно для заполнения';
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Выбран недопустимый пол';
    }

    $languages = $_POST['language'] ?? [];
    if (empty($languages)) {
        $errors['language'] = 'Выберите хотя бы один язык программирования';
    } else {
        $validLanguages = ['Pascal', 'C', 'C++', 'JavaScript', 'PHP', 'Python',
                           'Java', 'Haskell', 'Clojure', 'Prolog', 'Scala', 'Go'];
        foreach ($languages as $lang) {
            if (!in_array($lang, $validLanguages)) {
                $errors['language'] = 'Выбран недопустимый язык программирования';
                break;
            }
        }
    }

    $bio = trim($_POST['bio'] ?? '');
    if (empty($bio)) {
        $errors['bio'] = 'Поле Биография обязательно для заполнения';
    }

    $contract = isset($_POST['contract']) && $_POST['contract'] === 'on';
    if (!$contract) {
        $errors['contract'] = 'Необходимо ознакомиться с контрактом';
    }

    if (!empty($errors)) {
        setcookie('form_errors', json_encode($errors), 0, '/');
        setcookie('form_values', json_encode($_POST), 0, '/');
        header('Location: index.php');
        exit;
    }

    try {
        $pdo->beginTransaction();

        $stmt = $pdo->prepare("INSERT INTO Application (FIO, Phone_number, Email, Birth_day, Gender, Biography, Contract_accepted) 
                               VALUES (:fio, :phone, :email, :dob, :gender, :bio, :contract)");
        $stmt->execute([
            ':fio' => $fio,
            ':phone' => $phone,
            ':email' => $email,
            ':dob' => $dob,
            ':gender' => $gender,
            ':bio' => $bio,
            ':contract' => $contract ? 1 : 0
        ]);

        $application_id = $pdo->lastInsertId();

        $stmt = $pdo->prepare("INSERT INTO Application_Languages (Application_ID, Language_ID) 
                               SELECT :app_id, Language_ID FROM Programming_Languages WHERE Name = :language");

        foreach ($languages as $language) {
            $stmt->execute([
                ':app_id' => $application_id,
                ':language' => $language
            ]);
        }

        $pdo->commit();

        $year = time() + 365 * 24 * 60 * 60;
        setcookie('form_values', json_encode($_POST), $year, '/');
        setcookie('form_errors', '', time() - 3600, '/');

        echo '<div class="success-container">';
        echo '<h2>Данные успешно сохранены!</h2>';
        echo '<p>Спасибо за заполнение формы.</p>';
        echo '<a href="index.php">Вернуться к форме</a>';
        echo '</div>';

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при сохранении данных: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}
