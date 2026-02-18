<?php
// Start session only if it's not already active
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}
require_once('../includes/db.php'); // Ensure this file initializes the MySQLi connection ($conn)

if ($_SESSION['role'] !== 'admin') {
    header('Location: ../index.php');
    exit();
} else {
    $msg = $error = '';
    $about_data = null;

    // Handle form submission for creating/updating about page content
    if (isset($_POST['submit'])) {
        $content = $_POST['content'];

        // Check if an entry already exists
        $check_sql = "SELECT * FROM about_page";
        $check_query = $conn->query($check_sql);
        $existing_entry = $check_query->fetch_assoc();

        if ($existing_entry) {
            // Update existing entry
            $sql = "UPDATE about_page SET content = ? WHERE id = ?";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("si", $content, $existing_entry['id']);
            if ($stmt->execute()) {
                $msg = "About page content updated successfully!";
            } else {
                $error = "Failed to update about page content.";
            }
            $stmt->close();
        } else {
            // Insert new entry
            $sql = "INSERT INTO about_page (content) VALUES (?)";
            $stmt = $conn->prepare($sql);
            $stmt->bind_param("s", $content);
            if ($stmt->execute()) {
                $msg = "About page content created successfully!";
            } else {
                $error = "Failed to create about page content.";
            }
            $stmt->close();
        }
    }

    // Fetch existing content
    $sql = "SELECT * FROM about_page";
    $query = $conn->query($sql);
    if ($query) {
        $about_data = $query->fetch_assoc();
    }
}
?>

<!doctype html>
<html lang="en" class="no-js">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1, minimum-scale=1, maximum-scale=1">
    <meta name="description" content="">
    <meta name="author" content="">
    <meta name="theme-color" content="#3e454c">

    <title>Alpha Investment | Admin About Page</title>

    <!-- Font awesome -->
    <link rel="stylesheet" href="css/font-awesome.min.css">
    <!-- Sandstone Bootstrap CSS -->
    <link rel="stylesheet" href="css/bootstrap.min.css">
    <!-- Bootstrap Datatables -->
    <link rel="stylesheet" href="css/dataTables.bootstrap.min.css">
    <!-- Bootstrap social button library -->
    <link rel="stylesheet" href="css/bootstrap-social.css">
    <!-- Bootstrap select -->
    <link rel="stylesheet" href="css/bootstrap-select.css">
    <!-- Bootstrap file input -->
    <link rel="stylesheet" href="css/fileinput.min.css">
    <!-- Awesome Bootstrap checkbox -->
    <link rel="stylesheet" href="css/awesome-bootstrap-checkbox.css">
    <!-- Admin Style -->
    <link rel="stylesheet" href="css/style.css">
    <style>
        .errorWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #dd3d36;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }

        .succWrap {
            padding: 10px;
            margin: 0 0 20px 0;
            background: #fff;
            border-left: 4px solid #5cb85c;
            -webkit-box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
            box-shadow: 0 1px 1px 0 rgba(0, 0, 0, .1);
        }
    </style>
</head>

<body>
    <?php include('../includes/menu.php'); ?>
    <div class="ts-main-content">
        <div class="content-wrapper">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-md-12">
                        <h2 class="page-title">About Page Content</h2>
                        <div class="row">
                            <div class="col-md-10">
                                <div class="panel panel-default">
                                    <div class="panel-heading">Form fields</div>
                                    <div class="panel-body">
                                        <form method="post" name="aboutForm" class="form-horizontal">
                                            <?php if ($error) { ?>
                                                <div class="errorWrap"><strong>ERROR</strong>:<?php echo htmlentities($error); ?> </div>
                                            <?php } else if ($msg) { ?>
                                                <div class="succWrap"><strong>SUCCESS</strong>:<?php echo htmlentities($msg); ?> </div>
                                            <?php } ?>

                                            <div class="form-group">
                                                <label class="col-sm-4 control-label">About Page Content</label>
                                                <div class="col-sm-8">
                                                    <textarea class="form-control" name="content" id="content" rows="10" required><?php echo htmlentities($about_data['content'] ?? ''); ?></textarea>
                                                </div>
                                            </div>

                                            <div class="hr-dashed"></div>

                                            <div class="form-group">
                                                <div class="col-sm-8 col-sm-offset-4">
                                                    <button class="btn btn-primary" name="submit" type="submit">Save</button>
                                                </div>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Loading Scripts -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap-select.min.js"></script>
    <script src="js/bootstrap.min.js"></script>
    <script src="js/jquery.dataTables.min.js"></script>
    <script src="js/dataTables.bootstrap.min.js"></script>
    <script src="js/Chart.min.js"></script>
    <script src="js/fileinput.js"></script>
    <script src="js/chartData.js"></script>
    <script src="js/main.js"></script>
</body>

</html>