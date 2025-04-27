<?php
$db_name = "education_system";

// Подключение к БД
$conn = new mysqli("localhost", "root", "");
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Создание базы данных
if ($conn->query("CREATE DATABASE IF NOT EXISTS $db_name")) {
    echo "База данных '$db_name' создана.<br>";
} else {
    echo "Ошибка при создании базы данных: " . $conn->error . "<br>";
}

// Выбор базы данных
if (!$conn->select_db($db_name)) {
    die("Ошибка выбора базы данных: " . $conn->error);
}

// Создание таблиц
$queries = [
    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) NOT NULL UNIQUE
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        course_name VARCHAR(100) NOT NULL,
        instructor VARCHAR(100) NOT NULL
    ) ENGINE=InnoDB;",

    "CREATE TABLE IF NOT EXISTS registrations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT NOT NULL,
        course_id INT NOT NULL,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;"
];

foreach ($queries as $query) {
    if (!$conn->query($query)) {
        echo "Ошибка создания таблицы: " . $conn->error . "<br>";
    }
}

// Обработка форм

// Добавить студента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $stmt = $conn->prepare("INSERT INTO students (name, email) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['student_name'], $_POST['student_email']);
    $stmt->execute();
    $stmt->close();
}

// Добавить курс
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_course'])) {
    $stmt = $conn->prepare("INSERT INTO courses (course_name, instructor) VALUES (?, ?)");
    $stmt->bind_param("ss", $_POST['course_name'], $_POST['instructor_name']);
    $stmt->execute();
    $stmt->close();
}

// Зарегистрировать студента на курс
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_course'])) {
    $stmt = $conn->prepare("INSERT INTO registrations (student_id, course_id) VALUES (?, ?)");
    $stmt->bind_param("ii", $_POST['student_id'], $_POST['course_id']);
    $stmt->execute();
    $stmt->close();
}

// Получить студентов и курсы для форм
$students = $conn->query("SELECT id, name FROM students");
$courses = $conn->query("SELECT id, course_name FROM courses");

// Получить список регистраций
$registrations = $conn->query("
    SELECT students.name AS student_name, courses.course_name
    FROM registrations
    JOIN students ON registrations.student_id = students.id
    JOIN courses ON registrations.course_id = courses.id
");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Система управления студентами и курсами</title>
</head>
<body>
    <h1>Система управления студентами и курсами</h1>

    <h2>Добавить нового студента</h2>
    <form method="POST">
        <label>Имя: <input type="text" name="student_name" required></label><br>
        <label>Email: <input type="email" name="student_email" required></label><br>
        <button type="submit" name="add_student">Добавить студента</button>
    </form>

    <h2>Добавить новый курс</h2>
    <form method="POST">
        <label>Название курса: <input type="text" name="course_name" required></label><br>
        <label>Имя преподавателя: <input type="text" name="instructor_name" required></label><br>
        <button type="submit" name="add_course">Добавить курс</button>
    </form>

    <h2>Зарегистрировать студента на курс</h2>
    <form method="POST">
        <label>Студент:
            <select name="student_id" required>
                <?php while ($student = $students->fetch_assoc()): ?>
                    <option value="<?= $student['id'] ?>">
                        <?= htmlspecialchars($student['name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Курс:
            <select name="course_id" required>
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <option value="<?= $course['id'] ?>">
                        <?= htmlspecialchars($course['course_name']) ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="register_course">Зарегистрировать</button>
    </form>

    <h2>Список регистраций</h2>
    <table border="1">
        <thead>
            <tr>
                <th>Студент</th>
                <th>Курс</th>
            </tr>
        </thead>
        <tbody>
            <?php while ($reg = $registrations->fetch_assoc()): ?>
                <tr>
                    <td><?= htmlspecialchars($reg['student_name']) ?></td>
                    <td><?= htmlspecialchars($reg['course_name']) ?></td>
                </tr>
            <?php endwhile; ?>
        </tbody>
    </table>

</body>
</html>

<?php $conn->close(); ?>
