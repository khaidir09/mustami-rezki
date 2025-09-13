document.addEventListener("DOMContentLoaded", function () {
    let productSearchInput = document.getElementById("purchase_product_search");
    let productList = document.getElementById("purchase_product_list");
    let orderItemsTableBody = document.querySelector("tbody");

    productSearchInput.addEventListener("keyup", function () {
        let query = this.value;

        if (query.length > 1) {
            fetchPurchaseProducts(query);
        } else {
            productList.innerHTML = "";
        }
    });

    function fetchPurchaseProducts(query) {
        // warehouse_id bisa kosong, tetap kirim parameter (atau bisa dihilangkan jika backend support)
        let url = purchaseProductSearchUrl + "?query=" + query;
        fetch(url)
            .then((response) => response.json())
            .then((data) => {
                productList.innerHTML = "";
                if (data.length > 0) {
                    data.forEach((product) => {
                        let item = `<a href="#" class="list-group-item list-group-item-action product-item"
                            data-id="${product.id}"
                            data-code="${product.code}"
                            data-name="${product.name}"
                            data-cost="${product.modal}"
                            data-stock="${product.product_qty}">
                            <span class="mdi mdi-text-search"></span>
                            ${product.code} - ${product.name}
                            </a> `;
                        productList.innerHTML += item;
                        // console.log(item);
                    });

                    // add event listener for product selection
                    document
                        .querySelectorAll(".product-item")
                        .forEach((item) => {
                            item.addEventListener("click", function (e) {
                                e.preventDefault();
                                addProductToTable(this);
                            });
                        });
                } else {
                    productList.innerHTML =
                        '<p class="text-muted">Tidak ada produk ditemukan</p>';
                }
            });
    }

    ///// Add Product in to the table
    function addProductToTable(productElement) {
        let productId = productElement.getAttribute("data-id");
        let productCode = productElement.getAttribute("data-code");
        let productName = productElement.getAttribute("data-name");
        let netUnitCost = parseFloat(productElement.getAttribute("data-cost"));
        let stock = parseInt(productElement.getAttribute("data-stock"));

        // Check if product already exists in table
        if (document.querySelector(`tr[data-id="${productId}"]`)) {
            alert("Produk sudah ada dalam daftar pesanan.");
            return;
        }

        let row = `
      <tr data-id="${productId}">
          <td>
              ${productCode} - ${productName}
              <input type="hidden" name="products[${productId}][id]" value="${productId}">
              <input type="hidden" name="products[${productId}][name]" value="${productName}">
              <input type="hidden" name="products[${productId}][code]" value="${productCode}">
          </td>
          <td>${netUnitCost.toFixed(0)}
              <input type="hidden" name="products[${productId}][modal]" value="${netUnitCost}">
          </td>
          <td style="color:#ffc121">${stock}</td>
          <td>
              <div class="input-group">
                  <button class="btn btn-outline-secondary decrement-qty" type="button">−</button>
                  <input type="text" class="form-control text-center qty-input"
                      name="products[${productId}][quantity]" value="1" min="1" max="${stock}"
                      data-cost="${netUnitCost}" style="width: 30px;">
                  <button class="btn btn-outline-secondary increment-qty" type="button">+</button>
              </div>
          </td>
          <td class="subtotal">${netUnitCost.toFixed(0)}</td>
          <td><button class="btn btn-danger btn-sm remove-product"><span class="mdi mdi-delete-circle mdi-18px"></span></button></td>
      </tr>
  `;

        orderItemsTableBody.innerHTML += row;
        productList.innerHTML = "";
        productSearchInput.value = "";

        updateEvents();
        updateGrandTotal();
    }

    function updateEvents() {
        document.querySelectorAll(".qty-input").forEach((input) => {
            input.addEventListener("input", function () {
                let row = this.closest("tr");
                let qty = parseInt(row.querySelector(".qty-input").value) || 1;
                let unitCost =
                    parseFloat(
                        row
                            .querySelector(".qty-input")
                            .getAttribute("data-cost")
                    ) || 0;

                let subtotal = unitCost * qty;
                row.querySelector(".subtotal").textContent =
                    subtotal.toFixed(0);

                updateGrandTotal();
            });
        });

        // Increment quantity
        document.querySelectorAll(".increment-qty").forEach((button) => {
            button.addEventListener("click", function () {
                let input =
                    this.closest(".input-group").querySelector(".qty-input");
                let max = parseInt(input.getAttribute("max"));
                let value = parseInt(input.value);
                if (value < max) {
                    input.value = value + 1;
                    updateSubtotal(this.closest("tr"));
                }
            });
        });

        // Decrement quantity
        document.querySelectorAll(".decrement-qty").forEach((button) => {
            button.addEventListener("click", function () {
                let input =
                    this.closest(".input-group").querySelector(".qty-input");
                let min = parseInt(input.getAttribute("min"));
                let value = parseInt(input.value);
                if (value > min) {
                    input.value = value - 1;
                    updateSubtotal(this.closest("tr"));
                }
            });
        });

        // Remove product row
        document.querySelectorAll(".remove-product").forEach((button) => {
            button.addEventListener("click", function () {
                this.closest("tr").remove();
                updateGrandTotal();
            });
        });
    }

    function updateSubtotal(row) {
        let qty = parseFloat(row.querySelector(".qty-input").value);
        let netUnitCost = parseFloat(
            row.querySelector(".qty-input").dataset.cost
        );

        // Calculate subtotal after discount
        let subtotal = netUnitCost * qty;
        row.querySelector(".subtotal").innerText = subtotal.toFixed(0);

        // Update Grand Total
        updateGrandTotal();
    }

    // Grand total update function
    function updateGrandTotal() {
        let subtotalSum = 0;

        // Calculate subtotal sum
        document.querySelectorAll("td.subtotal").forEach(function (item) {
            let textValue = item.textContent;

            // 2. Bersihkan teks dari karakter non-numerik (seperti "Rp", spasi, dan titik ribuan)
            // dan pastikan menggunakan titik sebagai desimal jika ada
            let numericValue = parseFloat(textValue.replace(/[^0-9.-]+/g, ""));

            // 3. Tambahkan ke total jika valid
            if (!isNaN(numericValue)) {
                subtotalSum += numericValue;
            }
        });

        // Get discount and shipping values
        let shipping =
            parseFloat(document.getElementById("inputShipping").value) || 0;

        // Apply discount and add shipping cost
        grandTotal = subtotalSum + shipping;

        // Ensure grand total is not negative
        if (grandTotal < 0) {
            grandTotal = 0;
        }

        function formatRupiah(angka) {
            return new Intl.NumberFormat("id-ID", {
                style: "currency",
                currency: "IDR",
                minimumFractionDigits: 0,
            }).format(angka);
        }

        // Update Grand Total display
        document.getElementById("grandTotal").textContent =
            formatRupiah(grandTotal);

        document.querySelector("input[name='grand_total']").value =
            grandTotal.toFixed(0);

        updateDueAmount();
    }

    // Event listeners for discount and shipping input change
    document
        .getElementById("inputShipping")
        .addEventListener("input", updateGrandTotal);

    document
        .getElementById("inputShipping")
        .addEventListener("input", function () {
            document.getElementById("shippingDisplay").textContent =
                this.value || 0;
        });
});
