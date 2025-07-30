
$(document).ready(function () {
    // ✅ Save selected section to localStorage and show it
    $(".menu-item").click(function () {
        $(".menu-item").removeClass("active");
        $(this).addClass("active");

        var section = $(this).data("section"); // e.g., 'sales'
        $(".content-section").removeClass("active");
        $("#" + section).addClass("active");

        // 🔒 Save selected section to localStorage
        localStorage.setItem("activeSection", section);
    });

    // ✅ On page load, restore last active section from localStorage
    $(document).ready(function () {
        var lastSection = localStorage.getItem("activeSection");

        if (lastSection && $("#" + lastSection).length > 0) {
            // Remove any previously set active sections
            $(".menu-item").removeClass("active");
            $(".content-section").removeClass("active");

            // Add active to stored section
            $(".menu-item[data-section='" + lastSection + "']").addClass("active");
            $("#" + lastSection).addClass("active");
        } else {
            // Default fallback: make first content-section active
            $(".menu-item").first().addClass("active");
            $(".content-section").first().addClass("active");
        }
    });
    
    // // Search bar functionality
        // Search for Products Section
        const productSearch = document.getElementById("search");
        productSearch.addEventListener("input", function () {
            filterProducts("products", this.value.toLowerCase(), ".product-card");
        });

    
        // Search for Inventory Section
        const inventorySearch = document.getElementById("search-inventory-product");
        inventorySearch.addEventListener("input", function () {
            filterProducts("inventory", this.value.toLowerCase(), "tbody tr", function (row) {
                const cell = row.querySelector("td:first-child");
                return cell ? cell.textContent : "";
            });
        });

    
    
        function filterProducts(sectionId, searchText, itemSelector, getTextCallback = null) {
            const section = document.getElementById(sectionId);
            const items = section.querySelectorAll(itemSelector);
        
            items.forEach(item => {
                let name;
                if (getTextCallback && typeof getTextCallback === "function") {
                    name = getTextCallback(item).toLowerCase();
                } else {
                    name = item.textContent.toLowerCase();
                }
        
                item.style.display = name.includes(searchText) ? "" : "none";
            });
        }


        document.getElementById("resetInventorySearch").addEventListener("click", function () {
            // Clear the input field
            const input = document.getElementById("search-inventory-product");
            input.value = "";
        
            // Re-show all rows
            filterProducts("inventory", "", "tbody tr", function (row) {
                const cell = row.querySelector("td:first-child");
                return cell ? cell.textContent : "";
            });
        });
        

        // Search for sales & refund-history section
        // Grab the search input element
        document.querySelectorAll('.content-section input[name="search"]').forEach(input => {
            input.addEventListener('input', function () {
                const filter = this.value.toLowerCase().trim();
                const section = this.closest('.content-section');
                const rows = section.querySelectorAll('table tbody tr');
        
                rows.forEach(row => {
                    // Determine which column contains the customer name depending on the section
                    // Sales table: customer name is 3rd column (index 2)
                    // Refund history: customer name is 2nd column (index 1)
                    let customerName = '';
        
                    if (section.id === 'sales') {
                        customerName = row.cells[2]?.textContent.toLowerCase() || '';
                    } else if (section.id === 'refund-history') {
                        customerName = row.cells[1]?.textContent.toLowerCase() || '';
                    }
        
                    row.style.display = customerName.includes(filter) ? '' : 'none';
                });
            });
        });
                  
        
        
    
    

    //for restock start 
    // Function to redirect to Inventory Page when Restock button is clicked
        // Function to scroll to Inventory Section
        window.goToInventory = function () {
            const inventorySection = document.getElementById("inventory");
            if (inventorySection) {
                inventorySection.scrollIntoView({ behavior: "smooth" });
            }
        };

 /* restock end of code  */
 
//Inventory
    // Delete stock functionality
    let deleteButtons = document.querySelectorAll(".delete-btn");
    deleteButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            if (confirm("Are you sure you want to delete this product stock?")) {
                this.closest("tr").remove();
            }
        });
    });

    // Add stock functionality
    let addStockButtons = document.querySelectorAll(".add-stock-btn");
    addStockButtons.forEach(function (button) {
        button.addEventListener("click", function () {
            let row = this.closest("tr");
            let stockCell = row.querySelector(".stock-quantity");
            let statusCell = row.querySelector(".status");

            let additionalStock = prompt("Enter the quantity to add:", "0");
            if (additionalStock !== null && !isNaN(additionalStock) && additionalStock.trim() !== "") {
                let newStock = parseInt(stockCell.textContent) + parseInt(additionalStock);
                stockCell.textContent = newStock;

                // Update stock status
                if (newStock > 10) {
                    statusCell.textContent = "In Stock";
                    statusCell.classList.remove("low-stock");
                    statusCell.classList.add("in-stock");
                }
            }
        });
    });

//Start of cart functionality
let cart = []; // Initialize cart array

// Load cart from sessionStorage on page load
$(document).ready(function () {
    let savedCart = JSON.parse(sessionStorage.getItem('cart'));
    if (savedCart) {
        cart = savedCart;
        updateCart();
    }
});

// Function to update the cart and calculate subtotal
function updateCart() {
    let cartList = $("#cart-items");
    let cartTotal = 0;

    cartList.empty();

    // Loop through cart items and display them
    cart.forEach((item, index) => {
        let itemTotal = item.price * item.quantity;
        cartTotal += itemTotal;

        cartList.append(`
            <li class="cart-item" style="margin-bottom: 10px; display: flex; gap: 10px; align-items: center;">
                <img src="${item.image}" alt="${item.name}" style="width: 50px; height: 50px; object-fit: cover;">
                <div style="flex-grow: 1;">
                    <p><strong>${item.name}</strong></p>
                    <p>Rs. ${item.price} x ${item.quantity} = Rs. ${itemTotal}</p>
                    <div class="quantity-controls">
                        <button class="qty-btn decrease-qty" data-index="${index}">-</button>
                        <span>${item.quantity}</span>
                        <button class="qty-btn increase-qty" data-index="${index}">+</button>
                        <button class="btn btn-danger btn-sm remove-item" data-index="${index}">Remove</button>
                    </div>
                </div>
            </li>
        `);
    });

    // Update total
    $("#cart-total").text(cartTotal);

    // Store subtotal and cart in sessionStorage
    sessionStorage.setItem('subtotal', cartTotal);
    sessionStorage.setItem('cart', JSON.stringify(cart));
}

// Add to cart
$(document).on("click", ".add-to-cart", function () {
    let productCard = $(this).closest(".product-card");
    let name = productCard.find(".product-name").text().trim();
    let priceText = productCard.find(".product-price").text().trim();
    let price = parseFloat(priceText.replace(/[^0-9.]/g, '')) || 0;
    let image = productCard.find("img").attr("src");
    let stock = parseInt($(this).data("stock"));
    console.log(stock);

    if (stock <= 0) {
        alert("This product is currently out of stock!");
        return; // Stop execution
    }

    let existingItem = cart.find(item => item.name === name);

    if (existingItem) {
        if (existingItem.quantity < existingItem.stock) {
            existingItem.quantity += 1;
        } else {
            alert("Cannot add more. Product is Out of Stock.");
        }
    } else {
        cart.push({ name, price, image, quantity: 1, stock });
    }

    updateCart();
});


// Increase quantity
$(document).on("click", ".increase-qty", function () {
    let index = $(this).data("index");

    if (cart[index].quantity < cart[index].stock) {
        cart[index].quantity += 1;
    } else {
        alert("Cannot increase. Stock limit reached.");
    }

    updateCart();
});

// Decrease quantity
$(document).on("click", ".decrease-qty", function () {
    let index = $(this).data("index");
    if (cart[index].quantity > 1) {
        cart[index].quantity -= 1;
    } else {
        cart.splice(index, 1);
    }
    updateCart();
});

// Remove item
$(document).on("click", ".remove-item", function () {
    let index = $(this).data("index");
    cart.splice(index, 1);
    updateCart();
});

// Clear cart
$("#clear-cart").click(function () {
    cart = [];
    updateCart();
});
//en of cart functionality

// Proceed to payment
$("#checkout").click(function () {
    if (cart.length === 0) {
        alert("Your cart is empty!");
    } else {
        // Submit form using a hidden form and POST cart data
        const form = $('<form action="store_cart.php" method="POST"></form>');
        const input = $('<input type="hidden" name="cart">');
        input.val(JSON.stringify(cart));
        form.append(input);
        $('body').append(form);
        form.submit();
    }
});


      
// End of cart Functionality

// Function to calculate the total stock and total original price from the table
//  Script to calculate Inventory Info and Calculate Total items amount 
// Function to update the total stock and original price
function updateInventoryTotals() {
    let totalStock = 0;
    let totalOriginalPrice = 0;
    let totalResalePrice = 0;

    const rows = document.querySelectorAll('#inventoryTable tbody tr');

    rows.forEach((row) => {
        const originalPriceText = row.cells[1].textContent.trim(); // Original Price = column index 1
        const stockQuantityText = row.cells[5].textContent.trim(); // In Stock = column index 5
        const resalePriceText = row.cells[2].textContent.trim();

        const stockQuantity = parseInt(stockQuantityText.replace(/,/g, '').trim(), 10);
        const originalPrice = parseFloat(originalPriceText.replace(/,/g, '').trim());
        const resalePrice = parseFloat(resalePriceText.replace(/,/g, '').trim());


        if (!isNaN(stockQuantity) && !isNaN(originalPrice) && !isNaN(resalePrice)) {
            totalStock += stockQuantity;
            totalOriginalPrice += stockQuantity * originalPrice;
            totalResalePrice += stockQuantity * resalePrice;
        }
    });

    // Update the totals in the HTML
    document.getElementById('totalStock').textContent = totalStock;
    document.getElementById('totalOriginalPrice').textContent = totalOriginalPrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
    document.getElementById('totalResalePrice').textContent = totalResalePrice.toLocaleString(undefined, {
        minimumFractionDigits: 2,
        maximumFractionDigits: 2
    });
}

// Run on page load
updateInventoryTotals();



// Toggle Inventory Info when clicking "Toggle Inventory Info" button
document.getElementById('toggleInventory').addEventListener('click', function () {
    let infoDiv = document.getElementById('inventoryInfo');
    // Toggle display between 'none' and 'block'
    infoDiv.style.display = infoDiv.style.display === 'none' ? 'block' : 'none';
});

  // Refund Amount Calculator Start
  function calculateRefunds() {
        document.querySelectorAll('.product-item').forEach(item => {
            const select = item.querySelector('.product-name');
            const quantityInput = item.querySelector('.quantity');
            const refundInput = item.querySelector('.refunded-amount');

            const selectedOption = select.options[select.selectedIndex];
            const price = parseFloat(selectedOption.getAttribute('data-price') || 0);
            const quantity = parseInt(quantityInput.value || 0);

            const refund = price * quantity;
            refundInput.value = isNaN(refund) ? '' : refund.toFixed(2);
        });
    }

    // Attach event listeners
    document.addEventListener('input', function (e) {
        if (e.target.matches('.product-name') || e.target.matches('.quantity')) {
            calculateRefunds();
        }
    });

    // Add new product row
    document.getElementById('addProduct').addEventListener('click', function () {
        const section = document.getElementById('productSection');
        const firstItem = section.querySelector('.product-item');
        const newItem = firstItem.cloneNode(true);

        // Reset fields
        newItem.querySelector('.product-name').selectedIndex = 0;
        newItem.querySelector('.quantity').value = 1;
        newItem.querySelector('.refunded-amount').value = '';

        section.appendChild(newItem);
    });
    // Refund Amount Calculator End

// To encrypt the total stock and original price
// When the "Inventory" header is clicked
let inventoryUnlocked = false;

$('#toggleInventory').click(function() {
  // If not unlocked, prompt for password
  if (!inventoryUnlocked) {
    const pw = prompt('Enter password to view inventory totals:');
    if (pw === '1999') {
      inventoryUnlocked = true;
      $('#inventoryInfo').slideDown();
    } else {
      alert('Incorrect password!');
      $('#inventoryInfo').hide();
    }
  } else {
    // Already unlocked—so hide without prompting
    $('#inventoryInfo').slideUp(function() {
      // After it’s hidden, lock again
      inventoryUnlocked = false;
    });
  }
});

  
});