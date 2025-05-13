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
    foreach ($_COOKIE as $name => $value) {
        if (strpos($name, 'error_') === 0 || strpos($name, 'value_') === 0) {
            setcookie($name, '', time() - 3600, '/');
        }
    }

    $errors = [];

    $fio = trim($_POST['fio'] ?? '');
    if (empty($fio)) {
        $errors['fio'] = 'Поле ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $fio)) {
        $errors['fio'] = 'ФИО должно содержать только буквы и пробелы';
    }

    $phone = trim($_POST['phone'] ?? '');
    if (empty($phone)) {
        $errors['phone'] = 'Поле Телефон обязательно для заполнения';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать 10-15 цифр, может начинаться с +';
    }

    $email = trim($_POST['email'] ?? '');
    if (empty($email)) {
        $errors['email'] = 'Поле Email обязательно для заполнения';
    } elseif (!preg_match('/^[a-zA-Z0-9._%+-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,}$/', $email)) {
        $errors['email'] = 'Введите корректный email (пример: user@example.com)';
    }

    $dob = $_POST['dob'] ?? '';
    if (empty($dob)) {
        $errors['dob'] = 'Поле Дата рождения обязательно для заполнения';
    } elseif (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $dob)) {
        $errors['dob'] = 'Введите дату в формате ГГГГ-ММ-ДД';
    }

    $gender = $_POST['gender'] ?? '';
    if (empty($gender)) {
        $errors['gender'] = 'Укажите ваш пол';
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Выбран недопустимый пол';
    }

    $languages = $_POST['language'] ?? [];
    if (empty($languages)) {
        $errors['language'] = 'Выберите хотя бы один язык программирования';
    }

    $bio = trim($_POST['bio'] ?? '');
    if (empty($bio)) {
        $errors['bio'] = 'Поле Биография обязательно для заполнения';
    }

    if (!isset($_POST['contract'])) {
        $errors['contract'] = 'Необходимо согласиться с условиями';
    }

    if (!empty($errors)) {
        foreach ($errors as $field => $error) {
            setcookie("error_$field", $error, time() + 3600, '/');
            if (isset($_POST[$field])) {
                $value = is_array($_POST[$field]) ? implode(',', $_POST[$field]) : $_POST[$field];
                setcookie("value_$field", $value, time() + 3600, '/');
            }
        }
        include 'index.php';
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
            ':contract' => 1
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

        foreach ($_COOKIE as $name => $value) {
            if (strpos($name, 'error_') === 0 || strpos($name, 'value_') === 0) {
                setcookie($name, '', time() - 3600, '/');
            }
        }
        foreach ($_POST as $field => $value) {
            if ($field !== 'contract') {
                $value = is_array($value) ? implode(',', $value) : $value;
                setcookie("success_$field", $value, time() + 60*60*24*365, '/');
            }
        }
        setcookie('success_contract', '1', time() + 60*60*24*365, '/');

        header('Location: index.php?success=1');
        exit;

    } catch (PDOException $e) {
        $pdo->rollBack();
        die("Ошибка при сохранении данных: " . $e->getMessage());
    }
} else {
    header('Location: index.php');
    exit;
}