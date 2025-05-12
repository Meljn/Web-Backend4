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
    $old_values = [];
    
    // ФИО
    $fio = trim($_POST['fio'] ?? '');
    $old_values['fio'] = $fio;
    if (empty($fio)) {
        $errors['fio'] = 'Поле ФИО обязательно для заполнения';
    } elseif (!preg_match('/^[A-Za-zА-Яа-яЁё\s]+$/u', $fio)) {
        $errors['fio'] = 'ФИО должно содержать только буквы и пробелы. Допустимые символы: A-Z, a-z, А-Я, а-я, пробел.';
    } elseif (strlen($fio) > 150) {
        $errors['fio'] = 'ФИО не должно превышать 150 символов';
    }
    
    // Телефон
    $phone = trim($_POST['phone'] ?? '');
    $old_values['phone'] = $phone;
    if (empty($phone)) {
        $errors['phone'] = 'Поле Телефон обязательно для заполнения';
    } elseif (!preg_match('/^\+?[0-9]{10,15}$/', $phone)) {
        $errors['phone'] = 'Телефон должен содержать от 10 до 15 цифр, может начинаться с +. Допустимые символы: цифры 0-9 и знак + в начале.';
    }
    
    // Email
    $email = trim($_POST['email'] ?? '');
    $old_values['email'] = $email;
    if (empty($email)) {
        $errors['email'] = 'Поле E-mail обязательно для заполнения';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors['email'] = 'Введите корректный email адрес. Должен содержать @ и домен.';
    }
    
    // Дата рождения
    $dob = $_POST['dob'] ?? '';
    $old_values['dob'] = $dob;
    if (empty($dob)) {
        $errors['dob'] = 'Поле Дата рождения обязательно для заполнения';
    } else {
        $dobDate = DateTime::createFromFormat('Y-m-d', $dob);
        if (!$dobDate || $dobDate->format('Y-m-d') !== $dob) {
            $errors['dob'] = 'Введите корректную дату рождения в формате ГГГГ-ММ-ДД';
        }
    }
    
    // Пол
    $gender = $_POST['gender'] ?? '';
    $old_values['gender'] = $gender;
    if (empty($gender)) {
        $errors['gender'] = 'Поле Пол обязательно для заполнения';
    } elseif (!in_array($gender, ['male', 'female'])) {
        $errors['gender'] = 'Выбран недопустимый пол';
    }
    
    // Языки программирования
    $languages = $_POST['language'] ?? [];
    $old_values['language'] = $languages;
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
    
    // Биография
    $bio = trim($_POST['bio'] ?? '');
    $old_values['bio'] = $bio;
    if (empty($bio)) {
        $errors['bio'] = 'Поле Биография обязательно для заполнения';
    } elseif (!preg_match('/^[A-Za-zА-Яа-яЁё0-9\s.,!?-]+$/u', $bio)) {
        $errors['bio'] = 'Биография содержит недопустимые символы. Допустимы буквы, цифры, пробелы и знаки препинания.';
    }
    
    // Контракт
    $contract = isset($_POST['contract']) && $_POST['contract'] === 'on';
    $old_values['contract'] = $contract;
    if (!$contract) {
        $errors['contract'] = 'Необходимо ознакомиться с контрактом';
    }
    
    if (!empty($errors)) {
        // Сохраняем ошибки и старые значения в cookies на время сессии
        setcookie('form_errors', json_encode($errors), 0, '/');
        setcookie('form_old_values', json_encode($old_values), 0, '/');
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
        
        // Сохраняем успешные значения в cookies на год
        $success_values = [
            'fio' => $fio,
            'phone' => $phone,
            'email' => $email,
            'dob' => $dob,
            'gender' => $gender,
            'language' => $languages,
            'bio' => $bio,
            'contract' => $contract
        ];
        setcookie('form_success_values', json_encode($success_values), time() + 60*60*24*365, '/');
        
        // Удаляем ошибки и старые значения, если они были
        setcookie('form_errors', '', time() - 3600, '/');
        setcookie('form_old_values', '', time() - 3600, '/');
        
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
?>