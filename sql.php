<?php
// Подключение к базе данных
$db_host = 'localhost';
$db_user = 'root';
$db_pass = '';
$db_name = 'school_management';

$conn = new mysqli($db_host, $db_user, $db_pass, $db_name);
if ($conn->connect_error) {
    die("Ошибка подключения: " . $conn->connect_error);
}

// Создание таблиц (если они не существуют)
$create_queries = [
    "CREATE TABLE IF NOT EXISTS groups (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",
    "CREATE TABLE IF NOT EXISTS students (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        group_id INT,
        FOREIGN KEY (group_id) REFERENCES groups(id) ON DELETE SET NULL
    ) ENGINE=InnoDB;",
    "CREATE TABLE IF NOT EXISTS teachers (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL
    ) ENGINE=InnoDB;",
    "CREATE TABLE IF NOT EXISTS courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        teacher_id INT,
        FOREIGN KEY (teacher_id) REFERENCES teachers(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;",
    "CREATE TABLE IF NOT EXISTS student_courses (
        id INT AUTO_INCREMENT PRIMARY KEY,
        student_id INT,
        course_id INT,
        FOREIGN KEY (student_id) REFERENCES students(id) ON DELETE CASCADE,
        FOREIGN KEY (course_id) REFERENCES courses(id) ON DELETE CASCADE
    ) ENGINE=InnoDB;"
];

foreach ($create_queries as $query) {
    if (!$conn->query($query)) {
        echo "Ошибка создания таблиц: " . $conn->error . "<br>";
    }
}

// Добавление студента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_student'])) {
    $name = $_POST['student_name'];
    $group_id = $_POST['group_id'] ?: NULL;
    $conn->query("INSERT INTO students (name, group_id) VALUES ('$name', '$group_id')");
}

// Добавление группы
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_group'])) {
    $group_name = $_POST['group_name'];
    $conn->query("INSERT INTO groups (name) VALUES ('$group_name')");
}

// Привязка студента к группе
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['assign_group'])) {
    $student_id = $_POST['student_id'];
    $group_id = $_POST['group_id'];
    $conn->query("UPDATE students SET group_id = '$group_id' WHERE id = '$student_id'");
}

// Регистрация студента на курс
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['register_course'])) {
    $student_id = $_POST['student_id'];
    $course_id = $_POST['course_id'];
    $conn->query("INSERT INTO student_courses (student_id, course_id) VALUES ('$student_id', '$course_id')");
}

// Удаление студента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_student'])) {
    $student_id = $_POST['student_id'];
    $conn->query("DELETE FROM students WHERE id = '$student_id'");
}

// Обновление имени студента
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_student'])) {
    $student_id = $_POST['student_id'];
    $new_name = $_POST['new_name'];
    $conn->query("UPDATE students SET name = '$new_name' WHERE id = '$student_id'");
}

// Формы для вывода данных
$students = $conn->query("SELECT * FROM students");
$groups = $conn->query("SELECT * FROM groups");
$courses = $conn->query("SELECT * FROM courses");
$teachers = $conn->query("SELECT * FROM teachers");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>School Management System</title>
</head>
<body>
    <h1>School Management System</h1>
    <nav>
        <a href="#add_student">Добавить студента</a> | 
        <a href="#add_group">Добавить группу</a> | 
        <a href="#assign_group">Привязать студента к группе</a> | 
        <a href="#register_course">Регистрация студента на курс</a> | 
        <a href="#students_list">Список студентов</a> | 
        <a href="#courses_list">Список курсов</a> | 
        <a href="#teachers_list">Список преподавателей</a>
    </nav>

    <!-- 1. Добавление студента -->
    <h2 id="add_student">Добавить нового студента</h2>
    <form method="POST">
        <label>Имя студента: <input type="text" name="student_name" required></label><br>
        <label>Группа: 
            <select name="group_id">
                <option value="">Не выбрано</option>
                <?php while ($group = $groups->fetch_assoc()): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="add_student">Добавить студента</button>
    </form>

    <!-- 2. Добавление группы -->
    <h2 id="add_group">Добавить новую группу</h2>
    <form method="POST">
        <label>Название группы: <input type="text" name="group_name" required></label><br>
        <button type="submit" name="add_group">Добавить группу</button>
    </form>

    <!-- 3. Привязка студента к группе -->
    <h2 id="assign_group">Привязать студента к группе</h2>
    <form method="POST">
        <label>Студент:
            <select name="student_id">
                <?php while ($student = $students->fetch_assoc()): ?>
                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Группа:
            <select name="group_id">
                <?php while ($group = $groups->fetch_assoc()): ?>
                    <option value="<?= $group['id'] ?>"><?= htmlspecialchars($group['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="assign_group">Привязать</button>
    </form>

    <!-- 4. Регистрация студента на курс -->
    <h2 id="register_course">Регистрация студента на курс</h2>
    <form method="POST">
        <label>Студент:
            <select name="student_id">
                <?php while ($student = $students->fetch_assoc()): ?>
                    <option value="<?= $student['id'] ?>"><?= htmlspecialchars($student['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <label>Курс:
            <select name="course_id">
                <?php while ($course = $courses->fetch_assoc()): ?>
                    <option value="<?= $course['id'] ?>"><?= htmlspecialchars($course['name']) ?></option>
                <?php endwhile; ?>
            </select>
        </label><br>
        <button type="submit" name="register_course">Зарегистрировать</button>
    </form>

    <!-- 5. Список студентов -->
    <h2 id="students_list">Список студентов</h2>
    <table border="1">
        <tr>
            <th>ID</th>
            <th>Имя</th>
            <th>Группа</th>
            <th>Удалить</th>
            <th>Обновить</th>
        </tr>
        <?php while ($student = $students->fetch_assoc()): ?>
            <tr>
                <td><?= $student['id'] ?></td>
                <td><?= htmlspecialchars($student['name']) ?></td>
                <td><?= htmlspecialchars($student['group_id'] ? $groups->fetch_assoc()['name'] : 'Нет группы') ?></td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                        <button type="submit" name="delete_student">Удалить</button>
                    </form>
                </td>
                <td>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="student_id" value="<?= $student['id'] ?>">
                        <label>Новое имя: <input type="text" name="new_name"></label>
                        <button type="submit" name="update_student">Обновить</button>
                    </form>
                </td>
            </tr>
        <?php endwhile; ?>
    </table>

</body>
</html>

<?php $conn->close(); ?>
