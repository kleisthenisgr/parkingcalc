<?php
/*
Plugin Name: Parking Cost Calculator
Description: Dynamically updates cost field value drop off and pick up times get altered.
Version: 3.0
Author: Alexios-Theocharis Koilias
*/
 
// Set default values for prices
$firstweekprice = 4;
$secondweekprice = 0;
$thirdweekprice = 2;
 
// Function to read prices from the file and update the variables
function update_prices_from_file() {
    global $firstweekprice, $secondweekprice, $thirdweekprice;
 
    // Read the file and extract prices
    $file_path = plugin_dir_path(__FILE__) . 'prices.txt';
    if (file_exists($file_path)) {
        $prices_data = file_get_contents($file_path);
        $matches = array();
        if (preg_match('/First Week Price: (\d+)/', $prices_data, $matches)) {
            $firstweekprice = intval($matches[1]);
        }
        if (preg_match('/Second Week Price: (\d+)/', $prices_data, $matches)) {
            $secondweekprice = intval($matches[1]);
        }
        if (preg_match('/Third Week Price: (\d+)/', $prices_data, $matches)) {
            $thirdweekprice = intval($matches[1]);
        }
    }
}
 
// Function to update the "textbox1920" field based on input fields
function updateTextbox1920() {
if (strpos($_SERVER['REQUEST_URI'], 'kratisi') !== false) {
    global $firstweekprice, $secondweekprice, $thirdweekprice;
    update_prices_from_file();
 
    echo '<script>
        function calculateAndUpdatePrice() {
            // Get values from input fields
            var date1String = document.getElementById("date1").value;
            var date2String = document.getElementById("date2").value;
            // Get the value of the dropdown menu with id "dropdown21"
            var dropdownValue = document.getElementById("dropdown21").value;
        
            // Calculate the additional price based on the dropdown value
            var additionalPrice = 0;
            if (dropdownValue === "1" || dropdownValue === "2") {
                additionalPrice = 5;
            } else if (dropdownValue === "3") {
                additionalPrice = 10;
            }
            
 
            // Parse the date strings into Date objects
            var date1Components = date1String.split("-");
            var date2Components = date2String.split("-");
 
            var date1 = new Date(date1Components[2], date1Components[1] - 1, date1Components[0]);
            var date2 = new Date(date2Components[2], date2Components[1] - 1, date2Components[0]);
 
            // Calculate the time difference in milliseconds
            var timeDifference = date2 - date1;
 
            // Calculate the number of days
            var daysDifference = Math.floor(timeDifference / (1000 * 60 * 60 * 24));
 
            // Calculate the price based on the number of days
            var price = 0;
            if (daysDifference <= 6 ) {
                price = (daysDifference + 1) * ' . $firstweekprice . ';
            } else if (daysDifference >= 7 && daysDifference <= 13) {
                price = 7 * ' . $firstweekprice . ' + (daysDifference - 7 + 1) * ' . $secondweekprice . ';
            } else if (daysDifference >=14  && daysDifference <= 29) {
                price = 7 * ' . $firstweekprice . ' + 7 * ' . $secondweekprice . ' + (daysDifference - 14 + 1 ) * ' . $thirdweekprice . ' ;
            } else if (daysDifference >= 30) {
                price = "Επικοινωνία";
                
                setTimeout(function() {
                  alert("Επικοινωνήστε μαζί μας για την τιμή.");
                }, 500);
            }
 
 
            // Update the "textbox1920" field
            if (date1String !== "" && date2String !== "") {
                document.getElementById("textbox1920").value = price+additionalPrice;
            } else {
                document.getElementById("textbox1920").value = "-";
            }
            textbox1920.readOnly = true;
        }
 
        // Attach an event listener to the specific input fields
        var date1Input = document.getElementById("date1");
        var date2Input = document.getElementById("date2");
 
        var dropdown21Input = document.getElementById("dropdown21");
        dropdown21Input.addEventListener("change", calculateAndUpdatePrice);
        
 
        date1Input.addEventListener("input", calculateAndUpdatePrice);
        date2Input.addEventListener("input", calculateAndUpdatePrice);
        
 
        // Initial calculation when the page loads
        calculateAndUpdatePrice();
    </script>';
}
}
 
function display_settings_page() {
    global $firstweekprice, $secondweekprice, $thirdweekprice;
    update_prices_from_file();
    if (isset($_POST['submit'])) {
        // Update prices from the form
        $firstweekprice = intval($_POST['firstweekprice']);
        $secondweekprice = intval($_POST['secondweekprice']);
        $thirdweekprice = intval($_POST['thirdweekprice']);
 
        // Save the updated prices to a file (you can modify this to suit your needs)
        $file_path = plugin_dir_path(__FILE__) . 'prices.txt';
        $prices_data = "First Week Price: $firstweekprice\nSecond Week Price: $secondweekprice\nThird Week Price: $thirdweekprice\n";
        file_put_contents($file_path, $prices_data);
 
        // Display a success message
        echo '<div class="updated"><p>Prices updated successfully.</p></div>';
    }
    echo '<style>
    .wrap h1 {
        margin-bottom: 20px;
    }
    .price-input {
        margin-bottom: 20px;
    }
    .label-input {
        margin-right: 20px; /* Adjust this value to control the space between the label and input */
    }
    </style>';
    // Display the form to update prices
    echo '<div class="wrap">
        <h1>Parking Cost Calculator Settings</h1>
        <form method="post">
            <label for="firstweekprice" class="label-input">First Week Price:&nbsp;&nbsp;&nbsp;&nbsp;&nbsp</label>
            <input type="number" name="firstweekprice" value="' . $firstweekprice . '" class="price-input" step="0.5"><br>
    
            <label for="secondweekprice" class="label-input">Second Week Price:</label>
            <input type="number" name="secondweekprice" value="' . $secondweekprice . '" class="price-input" step="0.5"><br>
    
            <label for="thirdweekprice" class="label-input">Third Week Price:&nbsp;&nbsp;&nbsp; </label>
            <input type="number" name="thirdweekprice" value="' . $thirdweekprice . '" class="price-input" step="0.5"><br>
    
            <input type="submit" name="submit" value="Save Prices">
        </form>
    </div>';
 
}
 
// Add a menu item to the WordPress admin menu
function add_plugin_menu_item() {
    add_menu_page(
        'Parking Cost Calculator Settings',
        'Parking Calculator',
        'manage_options',
        'parking_calculator_settings',
        'display_settings_page'
    );
}
 
add_action('admin_menu', 'add_plugin_menu_item');
add_action('wp_footer', 'updateTextbox1920');
