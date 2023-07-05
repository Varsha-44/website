<!DOCTYPE html>
<html>
<head>
    <title>Project Details</title>
    <style>
        table {
            border-collapse: collapse;
            width: 100%;
        }
        
        th, td {
            border: 1px solid #ddd;
            padding: 8px;
        }
        
        th {
            background-color: #f2f2f2;
        }
        
        .search-container {
            margin-bottom: 20px;
        }
        
        .department-container,
        .year-container {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
    <?php
    // Database configuration
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "website";

    // Create a new PDO instance
    try {
        $conn = new PDO("mysql:host=$servername;dbname=$dbname", $username, $password);
        $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    } catch(PDOException $e) {
        die("Connection failed: " . $e->getMessage());
    }

    // Retrieve the search term from the query string
    $searchTerm = isset($_GET['search']) ? $_GET['search'] : '';

    // Retrieve the selected department and year values
    $selectedDepartment = isset($_GET['department']) ? $_GET['department'] : 'all';
    $selectedYear = isset($_GET['year']) ? $_GET['year'] : 'all';

    // Prepare the query with search and filter conditions
    $query = "SELECT * FROM project WHERE (name LIKE :searchTerm OR rollno LIKE :searchTerm OR email LIKE :searchTerm OR dept LIKE :searchTerm OR technology LIKE :searchTerm OR projectTitle LIKE :searchTerm)";
    $params = array(':searchTerm' => '%' . $searchTerm . '%');

    if ($selectedDepartment !== 'all') {
        $query .= " AND dept = :department";
        $params[':department'] = $selectedDepartment;
    }

    if ($selectedYear !== 'all') {
        $query .= " AND year = :year";
        $params[':year'] = $selectedYear;
    }

    $stmt = $conn->prepare($query);
    $stmt->execute($params);
    $data = $stmt->fetchAll();

    // Get the count of projects department-wise and year-wise
    $departmentCounts = array();
    $yearCounts = array();

    foreach ($data as $result) {
        if (isset($departmentCounts[$result['dept']][$result['year']])) {
            $departmentCounts[$result['dept']][$result['year']]++;
        } else {
            $departmentCounts[$result['dept']][$result['year']] = 1;
        }

        if (isset($yearCounts[$result['year']][$result['dept']])) {
            $yearCounts[$result['year']][$result['dept']]++;
        } else {
            $yearCounts[$result['year']][$result['dept']] = 1;
        }
    }

    // Retrieve all unique departments from the database
    $query = "SELECT DISTINCT dept FROM project";
    $stmt = $conn->prepare($query);
    $stmt->execute();
    $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);

    // Check if records are found
    $count = count($data);
    ?>

    <div class="search-container">
        <form method="GET" action="">
            <input type="text" name="search" placeholder="Search..." value="<?php echo htmlspecialchars($searchTerm); ?>">
            <input type="submit" value="Search">
            
            <input type="hidden" name="department" id="hiddenDepartment" value="<?php echo htmlspecialchars($selectedDepartment); ?>">
            <input type="hidden" name="year" id="hiddenYear" value="<?php echo htmlspecialchars($selectedYear); ?>">
        </form>
    </div>

    <div class="department-container">
        <label for="department">Select Department:</label>
        <select id="department" name="department" onchange="updateCount()">
            <option value="all" <?php if ($selectedDepartment === 'all') echo 'selected'; ?>>All Departments</option>
            <?php
            foreach ($departments as $department) {
                echo "<option value=\"$department\"";
                if ($selectedDepartment === $department) echo 'selected';
                echo ">$department</option>";
            }
            ?>
        </select>
        <div class="department-count" id="departmentCount">Total Projects: <?php echo $count; ?></div>
    </div>

    <div class="year-container">
        <label for="year">Select Year:</label>
        <select id="year" name="year" onchange="updateCount()">
            <option value="all" <?php if ($selectedYear === 'all') echo 'selected'; ?>>All Years</option>
            <?php
            foreach ($yearCounts as $year => $counts) {
                echo "<option value=\"$year\"";
                if ($selectedYear === $year) echo 'selected';
                echo ">$year</option>";
            }
            ?>
        </select>
        <div class="year-count" id="yearCount">Total Projects: <?php echo $count; ?></div>
    </div>

    <?php
    if ($count > 0) {
        // Group projects by user
        $groupedProjects = array();
        foreach ($data as $result) {
            $userId = $result['rollno'];
            if (!isset($groupedProjects[$userId])) {
                $groupedProjects[$userId] = array();
            }
            $groupedProjects[$userId][] = $result;
        }

        // Start the table
        echo "<table>";

        // Output table headers
        echo "<tr>";
        echo "<th>Name</th>";
        echo "<th>Roll No</th>";
        echo "<th>Email</th>";
        echo "<th>Department</th>";
        echo "<th>Technology</th>";
        echo "<th>Project Title</th>";
        echo "<th>Google Drive Link</th>";
        echo "<th>Uploaded Data</th>";
        echo "<th>Year</th>";
        echo "</tr>";

        // Loop through each user's projects
        foreach ($groupedProjects as $project) {
            $rowspan = count($project); // Calculate rowspan for the user's projects

            // Output table rows for the user's projects
            foreach ($project as $index => $result) {
                echo "<tr>";

                // Output user details in the first row only
                if ($index === 0) {
                    echo "<td rowspan=\"$rowspan\">" . $result['name'] . "</td>";
                    echo "<td rowspan=\"$rowspan\">" . $result['rollno'] . "</td>";
                    echo "<td rowspan=\"$rowspan\">" . $result['email'] . "</td>";
                    echo "<td rowspan=\"$rowspan\">" . $result['dept'] . "</td>";
                }

                // Output project details
                echo "<td>" . $result['technology'] . "</td>";
                echo "<td>" . $result['projectTitle'] . "</td>";
                echo "<td>" . $result['googleDriveLink'] . "</td>";
                echo "<td>" . $result['uploaded_Date'] . "</td>";
                echo "<td>" . $result['year'] . "</td>";

                echo "</tr>";
            }
        }

        echo "</table>";
    } else {
        echo "No records found.";
    }
    ?>

    <script>
        function updateCount() {
            var selectedDepartment = document.getElementById("department").value;
            var selectedYear = document.getElementById("year").value;
            var departmentCounts = <?php echo json_encode($departmentCounts); ?>;
            var yearCounts = <?php echo json_encode($yearCounts); ?>;
            var departmentCountElement = document.getElementById("departmentCount");
            var yearCountElement = document.getElementById("yearCount");
            var totalCount;

            if (selectedDepartment === "all" && selectedYear === "all") {
                totalCount = <?php echo $count; ?>;
            } else if (selectedDepartment !== "all" && selectedYear === "all") {
                totalCount = departmentCounts[selectedDepartment] ? Object.values(departmentCounts[selectedDepartment]).reduce((a, b) => a + b, 0) : 0;
            } else if (selectedDepartment === "all" && selectedYear !== "all") {
                totalCount = yearCounts[selectedYear] ? Object.values(yearCounts[selectedYear]).reduce((a, b) => a + b, 0) : 0;
            } else {
                totalCount = departmentCounts[selectedDepartment][selectedYear] ? departmentCounts[selectedDepartment][selectedYear] : 0;
            }

            departmentCountElement.textContent = "Total Projects: " + totalCount;
            yearCountElement.textContent = "Total Projects: " + totalCount;
            
            // Set the values of hidden fields
            document.getElementById("hiddenDepartment").value = selectedDepartment;
            document.getElementById("hiddenYear").value = selectedYear;

            // Submit the form
            document.getElementsByTagName("form")[0].submit();
        }
    </script>
</body>
</html>
