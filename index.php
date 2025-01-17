<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>
        * {
            box-sizing: border-box;
            font-family: Arial, sans-serif;
            margin: 0;
            padding: 0;
        }
        body {
            padding: 10px 10px
        }
        h2{
            margin-bottom:3px;
            font-size:1.3rem;
        }
        .standalone {
            margin-bottom: 20px;
            padding: 15px;
            border: 1px solid #e0e0e0;
            border-radius: 6px;
            background-color: #fafafa;
        }

        section{
            display: flex;
            flex-wrap: wrap;
            background-color:#fafafa;
            border-radius: 10px;
            padding: 5px 0;
            margin-top:5px;
        }

        form input[type="number"],
        form input[type="date"],
        form input[type="text"] {
            width: 100%;
            padding: 8px;
            margin-bottom: 5px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 0.9em;
        }

        form input[type="submit"] {
            font-weight:bold;
            font-size:1.5rem;
            width: 100%;
            padding: 8px;
            border: none;
            background-color: orange;
            color: white;
            font-size: 1em;
            cursor: pointer;
            border-radius: 4px;
            transition: background-color 0.1s ease;

            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        
        form input[type="submit"]:hover {
            background-color: tomato;
        }
        form input[type="submit"]:active {
            background-color: tomato;
            box-shadow: none;
        }
        form input[type="submit"]:active .delete{
            background-color:red;
            box-shadow: none;
        }
        .server_logs{
            font-size:0.9rem;
            width: fit-content;
            max-width: 500px;
            background: #ffffff;
            padding: 10px;
            border-radius: 8px;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.5);
        }
        summary{
            cursor:pointer;
        }
        .table{
            margin:0 auto;
        }
        table{
            border-collapse: collapse;
            font-size: 0.9em;
            font-family: sans-serif;
            /* min-width: 300px; */
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.25);
            border: 1px solid #e0e0e0;
            border-radius:5px;
        }
        thead tr {
            background-color: tomato;
            color: #ffffff;
            text-align: left;
        }

        th,td {
            padding: 6px 15px;
        }
        tbody tr {
            border-bottom: 1px solid #dddddd;
        }

        tbody tr:nth-of-type(even) {
            background-color: #f1f1f1;
        }
        tbody tr:hover {
            background-color: orange;
            /* font-weight: bold; */
            color: white;
        }
        .id{
            background-color:#ff9800;
        }
        tbody tr:last-of-type {
            border-bottom: 2px solid #009879;
        }

        tbody tr.active-row {
            font-weight: bold;
            color: #009879;
        }
        .card{
            background-color: #FFD580;
            width: content-fit;
            height: content-fit;
            border-radius:5px;
            padding: 5px;
            margin: 5px 0px;
        }
        .card:empty {
            display: none;
        }

        .container {
            all:unset;
            display: flex;
            flex-wrap: wrap;
            justify-content: center;
        }

        .form-item {
            flex-basis: 300px;
            margin: 10px;
            padding: 12px 20px;
            background-color: #fafafa;
            border: 1px solid #e0e0e0;
            border-radius: 8px;
        }
        .heading{
            color: tomato;
            margin:auto;
            display:inline;
            position:absolute;
            top:5px;
            left:32%;
        }
        .price{
            text-align:right;
        }
</style>
</head>
<body>

<details class="server_logs">
    <summary>Server Logs:</summary>
<p>
<?php
// Database connection
$host = 'localhost';
$user = 'root';
$password = '';
$conn = new mysqli($host, $user, $password);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Create database
$sql = "CREATE DATABASE IF NOT EXISTS inventory";
if ($conn->query($sql) === TRUE) {
    echo "Database created successfully/already exists<br>";
} else {
    echo "Error creating database: " . $conn->error . "<br>";
}

// Select database
$conn->select_db("inventory");

// Create tables
$sql_tables = [
    "CREATE TABLE IF NOT EXISTS categories (
        category_id INT AUTO_INCREMENT,
        category_name VARCHAR(255) NOT NULL UNIQUE,
        PRIMARY KEY (category_id)
    )",
    "CREATE TABLE IF NOT EXISTS products (
        product_id INT AUTO_INCREMENT,
        product_name VARCHAR(255) NOT NULL,
        price DECIMAL(10, 2) NOT NULL,
        stock_quantity INT NOT NULL,
        category_id INT,
        PRIMARY KEY (product_id),
        UNIQUE KEY (product_name),
        FOREIGN KEY (category_id) REFERENCES categories(category_id) ON DELETE CASCADE
    )",
    "CREATE TABLE IF NOT EXISTS orders (
        order_id INT AUTO_INCREMENT,
        product_id INT,
        order_quantity INT NOT NULL,
        order_date DATE NOT NULL,
        PRIMARY KEY (order_id),
        FOREIGN KEY (product_id) REFERENCES products(product_id) ON DELETE SET NULL
    )",
    "CREATE TABLE IF NOT EXISTS order_logs (
        log_id INT AUTO_INCREMENT,
        order_id INT,
        order_quantity INT NOT NULL,
        order_date DATE NOT NULL,
        PRIMARY KEY (log_id),
        FOREIGN KEY (order_id) REFERENCES orders(order_id) ON DELETE SET NULL
    )"
];

foreach ($sql_tables as $sql) {
    if ($conn->query($sql) === TRUE) {
        echo "Table created successfully/Table Already Exixsts<br>";
    } else {
        echo "Error creating table: " . $conn->error . "<br>";
    }
}

// Create trigger
$sql_trigger = "
CREATE TRIGGER IF NOT EXISTS after_order
AFTER INSERT ON orders
FOR EACH ROW
BEGIN
    INSERT INTO order_logs(order_id, order_quantity, order_date) 
    VALUES(NEW.order_id, NEW.order_quantity, NEW.order_date);
END;
";

if ($conn->multi_query($sql_trigger)) {
    echo "Trigger created successfully/Trigger Already Exists<br>";
} else {
    echo "Error creating trigger: " . $conn->error . "<br>";
}

// Create stored procedure
$sql_procedure = "
CREATE PROCEDURE IF NOT EXISTS get_product_details(IN prod_id INT)
BEGIN
    SELECT 
        p.product_name,
        c.category_name,
        COUNT(o.order_id) as total_orders
    FROM products p
    LEFT JOIN categories c ON p.category_id = c.category_id
    LEFT JOIN orders o ON p.product_id = o.product_id
    WHERE p.product_id = prod_id
    GROUP BY p.product_id;
END;
";

if ($conn->multi_query($sql_procedure)) {
    echo "Stored procedure created successfully/Stored procedure Already Exists<br>";
} else {
    echo "Error creating stored procedure: " . $conn->error . "<br>";
}

// Insert initial data if tables are empty
$sql = "SELECT COUNT(*) as count FROM categories";
$result = $conn->query($sql);
$row = $result->fetch_assoc();

if ($row['count'] == 0) {
    // Insert categories
    echo "Tables are empty, inserting dummy categories and products.<br>";
    $categories = ['Electronics', 'Stationery', 'Household'];
    $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
    
    foreach ($categories as $category) {
        $stmt->bind_param("s", $category);
        $stmt->execute();
    }
    
    // Insert products
    $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock_quantity, category_id) VALUES (?, ?, ?, ?)");
    
    $products = [
        // Electronics
        ['Smartphone', 499.99, 200, 1],
        ['Laptop', 899.99, 100, 1],
        ['Headphones', 79.99, 50, 1],
        ['Tablet', 299.99, 100, 1],
        ['Smartwatch', 199.99, 200, 1],
        // Stationery
        ['Notebook', 4.99, 100, 2],
        ['Pen Set', 9.99, 100, 2],
        ['Stapler', 6.99, 50, 2],
        ['Scissors', 3.99, 50, 2],
        ['Sticky Notes', 2.99, 200, 2],
        // Household
        ['Vacuum Cleaner', 149.99, 10, 3],
        ['Coffee Maker', 79.99, 30, 3],
        ['Toaster', 29.99, 50, 3],
        ['Blender', 49.99, 100, 3],
        ['Iron', 39.99, 200, 3]
    ];
    
    foreach ($products as $product) {
        $stmt->bind_param("sdii", $product[0], $product[1], $product[2], $product[3]);
        $stmt->execute();
    }
}
else{
    echo "Tables are not empty/Tables Already Exists<br>";
}
?>
</p></details>

<a href="/">

    <h1 class='heading'>
        Inventory Management System
    </h1>
</a>
    
<?php
// Handle form submissions
echo "<div class='card'>";
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    if (isset($_POST['place_order'])) {
        $product_id = $_POST['product_id'];
        $order_quantity = $_POST['order_quantity'];
        $order_date = $_POST['order_date'];
        
        // Check stock quantity
        $stmt = $conn->prepare("SELECT product_name, stock_quantity FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product = $result->fetch_assoc();
        
        if ($order_quantity > $product['stock_quantity']) {
            echo "Error placing order for " . $product['product_name'] . ":<br>";
            echo "Available Stock = " . $product['stock_quantity'] . "<br>";
            echo "Requested Order = " . $order_quantity . "<br>";
            echo "Quantity more needed = " . ($order_quantity - $product['stock_quantity']) . "<br>";
        } else {
            // Place order
            $stmt = $conn->prepare("INSERT INTO orders (product_id, order_quantity, order_date) VALUES (?, ?, ?)");
            $stmt->bind_param("iis", $product_id, $order_quantity, $order_date);
            $stmt->execute();
            
            // Update stock
            $new_quantity = $product['stock_quantity'] - $order_quantity;
            $stmt = $conn->prepare("UPDATE products SET stock_quantity = ? WHERE product_id = ?");
            $stmt->bind_param("ii", $new_quantity, $product_id);
            $stmt->execute();
            
            echo "Order for " . $product['product_name'] . " placed Successfully<br>";
            echo "Stock Quantity Remaining = " . $new_quantity . "<br>";
        }
    } elseif (isset($_POST['view_product'])) {
        $product_id = $_POST['view_product_id'];
        
        // Create a new connection for the stored procedure
        $proc_conn = new mysqli($host, $user, $password, "inventory");
        $stmt = $proc_conn->prepare("CALL get_product_details(?)");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $result = $stmt->get_result();
        $product_details = $result->fetch_assoc();
        
        if ($product_details) {
            
            echo "Product Name: " . $product_details['product_name'] . "<br>";
            echo "Category: " . $product_details['category_name'] . "<br>";
            echo "Total Orders: " . $product_details['total_orders'] . "<br>";
            
        } else {
            echo "Product not found.<br>";
        }
        
        // Close the procedure connection
        $stmt->close();
        $proc_conn->close();
    } elseif (isset($_POST['delete_category'])) {
        $category_id = $_POST['category_id'];
        
        // Check if the category exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_id = ?");
        $stmt->bind_param("i", $category_id);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();

        if ($count == 0) {
            echo "Error: Category with ID '$category_id' does not exist.<br>";
        } else {
            // Fetch products associated with the category
            $stmt = $conn->prepare("SELECT product_name FROM products WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            $stmt->execute();
            $result = $stmt->get_result();
            
            // Display products that will be deleted
            if ($result->num_rows > 0) {
                echo "The following products will be deleted:<br>";
                while ($row = $result->fetch_assoc()) {
                    echo "- " . $row['product_name'] . "<br>";
                }
            } else {
                echo "No products found for this category.<br>";
            }

            // Confirm deletion
            $stmt = $conn->prepare("DELETE FROM categories WHERE category_id = ?");
            $stmt->bind_param("i", $category_id);
            if($stmt->execute()) {
                echo "Category and associated products deleted successfully.<br>";
            } else {
                echo "Error deleting category: " . $conn->error . "<br>";
            }
        }
    } elseif (isset($_POST['add_category'])) {
         $category_name = $_POST['category_name'];

        // Check if the category already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM categories WHERE category_name = ?");
        $stmt->bind_param("s", $category_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            echo "Error: Category '$category_name' already exists.<br>";
        } else {
            $stmt = $conn->prepare("INSERT INTO categories (category_name) VALUES (?)");
            $stmt->bind_param("s", $category_name);
            $stmt->execute();
            echo "Category '$category_name' added successfully.<br>";
        }
    } elseif (isset($_POST['add_product'])) {
        $product_name = $_POST['product_name'];
        $price = $_POST['price'];
        $stock_quantity = $_POST['stock_quantity'];
        $category_id = $_POST['category_id'];
        
        // Check if the product already exists
        $stmt = $conn->prepare("SELECT COUNT(*) FROM products WHERE product_name = ?");
        $stmt->bind_param("s", $product_name);
        $stmt->execute();
        $stmt->bind_result($count);
        $stmt->fetch();
        $stmt->close();
        if ($count > 0) {
            echo "Error: Product '$product_name' already exists.<br>";
        } else {
            $stmt = $conn->prepare("INSERT INTO products (product_name, price, stock_quantity, category_id) VALUES (?, ?, ?, ?)");
            $stmt->bind_param("sdii", $product_name, $price, $stock_quantity, $category_id);
            $stmt->execute();
            echo "Product '$product_name' added successfully.<br>";
        }
    } elseif (isset($_POST['delete_product'])) {
        $product_id = $_POST['product_id'];
        
        // Check if the product exists
        $stmt = $conn->prepare("SELECT product_name FROM products WHERE product_id = ?");
        $stmt->bind_param("i", $product_id);
        $stmt->execute();
        $stmt->bind_result($product_name);
        $stmt->fetch();
        $stmt->close();
        if ($product_name === null) {
            echo "Error: Product with ID '$product_id' does not exist.<br>";
        } else {
            // Delete the product
            $stmt = $conn->prepare("DELETE FROM products WHERE product_id = ?");
            $stmt->bind_param("i", $product_id);
            if ($stmt->execute()) {
                echo "Product '$product_name' deleted successfully.<br>";
            } else {
                echo "Error deleting product: " . $conn->error . "<br>";
            }
        }
    }
}
echo "</div>";


// Display categories table
echo "<section>";
echo "<div class='table'>";
echo "<h2>📦 Categories</h2>";
echo "<table border='1'>
        <thead><tr>
            <th class='id'>ID</th>
            <th>Name</th>
            <th>🗑️ Delete</th> <!-- Added Action column -->
        </tr></thead>";
$result = $conn->query("SELECT * FROM categories");
echo "<tbody>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td class='id'>" . $row['category_id'] . "</td>
            <td>" . $row['category_name'] . "</td>
            <td style='margin:0 1px'>
                <form method='post' style='all:unset; display:inline;'>
                    <input type='hidden' name='delete_category' value='1'>
                    <input type='hidden' name='category_id' value='" . $row['category_id'] . "'>
                    <input style='padding:0;margin:0;' type='submit' value='❌' class='delete' title='Delete Category'>
                </form>
            </td>
          </tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";




// Display products table
echo "<div class='table'>";
echo "<h2>🏷️ Products</h2>";
echo "<table border='1'>
        <thead><tr>
            <th class='id'>ID</th>
            <th>Name</th>
            <th>💵 Price</th>
            <th>🔢 Stock</th>
            <th>📦 Category</th>
            <th>🗑️ Delete</th> <!-- Added Action column -->
        </tr></thead>";
$result = $conn->query("SELECT p.*, c.category_name FROM products p JOIN categories c ON p.category_id = c.category_id");
echo "<tbody>";
while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td class='id'>" . $row['product_id'] . "</td>
            <td>" . $row['product_name'] . "</td>
            <td class='price'>$ " . number_format($row['price'], 2) . "</td>
            <td>" . $row['stock_quantity'] . "</td>
            <td>" . $row['category_name'] . "</td>
            <td style='margin:0 1px'>
                <form method='post' style='all:unset; display:inline;'>
                    <input type='hidden' name='delete_product' value='1'>
                    <input type='hidden' name='product_id' value='" . $row['product_id'] . "'>
                    <input style='padding:0px;margin:0;' type='submit' value='❌' class='delete' title='Delete Product'>
                </form>
            </td>
          </tr>";
}
echo "</tbody>";
echo "</table>";
echo "</div>";

echo "<div class='table'>";

echo "<div>";
echo "<h2>🛒 Orders</h2>";
echo "<table border='1'>
        <thead><tr>
            <th class='id'>ID</th>
            <th>Product</th>
            <th>🔢 Quantity</th>
            <th>📅 Date</th>
        </thead></tr>";
$result = $conn->query("SELECT order_id, p.product_name, order_quantity, order_date FROM orders as o join products as p where o.product_id=p.product_id");
echo "<tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td class='id'>" . $row['order_id'] . "</td>
            <td>" . $row['product_name'] . "</td>
            <td>" . $row['order_quantity'] . "</td>
            <td>" . $row['order_date'] . "</td>
          </tr>";
}
echo "</tbody>";
echo "</table>";

echo "</div><br>";



// Display order logs table
echo "<div class='table'>";
echo "<h2>📝 Order Logs</h2>";
echo "<table border='1'>
        <thead><tr>
            <th class='id'>ID</th>
            <th>Order ID</th>
            <th>🔢 Quantity</th>
            <th>📅 Date</th>
        </thead></tr>";
$result = $conn->query("SELECT * FROM order_logs");
echo "<tbody>";

while ($row = $result->fetch_assoc()) {
    echo "<tr>
            <td class='id'>" . $row['log_id'] . "</td>
            <td>" . $row['order_id'] . "</td>
            <td>" . $row['order_quantity'] . "</td>
            <td>" . $row['order_date'] . "</td>
          </tr>";
}
echo "</tbody>";

echo "</table>";
echo "</div>";


echo "</div>";

echo "</section>";


?>


<style>
    label {
  margin:8px 0px;
  position:relative;
  display:inline-block;
}
  
span {
  padding:7px;
  pointer-events: none;
  position:absolute;
  left:0;
  top:0;
  transition: 0.1s;
  transition-timing-function: ease;
  transition-timing-function: cubic-bezier(0.25, 0.1, 0.25, 1);
  opacity:0.7;
}

input {
  padding:7px;
}

input:focus + span, input:not(:placeholder-shown) + span {
  opacity:1;
  transform: scale(0.9) translateY(-80%) translateX(-16px);
}

/* For IE Browsers*/
input:focus + span, input:not(:-ms-input-placeholder) + span {
  opacity:1;
  transform: scale(0.9) translateY(-80%) translateX(-15px);
}
</style>

<div class="container">
    <form method="post" class="form-item standalone">
        <h2>Place Order</h2>
        <input type="hidden" name="place_order" value="1">
        <label><input type="number" name="product_id" required placeholder=' '><span>Product ID</span></label>
        <label><input type="number" name="order_quantity" required placeholder=' '><span>Quantity</span></label>
        <label><input type="date" name="order_date" required placeholder=' '><span>Date</span></label>
        <input type="submit" value="Place Order">
    </form>

    <div class="form-item">
        <form method="post">
            <h2>View Product Details</h2>
            <input type="hidden" name="view_product" value="1">
            <label><input type="number" name="view_product_id" required placeholder=' '><span>Product ID</span></label>
            <input type="submit" value="View Details">
        </form>
        <hr style="margin:10px 0px">
        <form method="post">
            <h2>Delete Product</h2>
            <input type="hidden" name="delete_product" value="1">
            <label><input type="number" name="product_id" required placeholder=' '><span>Product ID</span></label>
            <input type="submit" value="Delete Product" class='delete'>
        </form>
    </div>
    
    <div class="form-item">
        <form method="post">
            <h2>Add Category</h2>
            <input type="hidden" name="add_category" value="1">
            <label><input type="text" name="category_name" required placeholder=' '><span>Category Name</span></label>
            <input type="submit" value="Add Category">
        </form>
        <hr style="margin:10px 0px">
        <form method="post">
            <h2>Delete Category</h2>
            <input type="hidden" name="delete_category" value="1">
            <label><input type="number" name="category_id" required placeholder=' '><span>Category ID</span></label>
            <input type="submit" value="Delete Category" class='delete'>
        </form>
        
    </div>

    <form method="post" class="form-item standalone">
        <h2>Add Product</h2>
        <input type="hidden" name="add_product" value="1">
        <label><input type="text" name="product_name" required placeholder=' '><span>Product Name</span></label>
        <label><input type="number" step="0.01" name="price" required placeholder=' '><span>Price</span></label>
        <label><input type="number" name="stock_quantity" required placeholder=' '><span>Stock Quantity</span></label>
        <label><input type="number" name="category_id" required placeholder=' '><span>Category ID</span></label>
        <input type="submit" value="Add Product">
    </form>
</div>


</body>
</html>