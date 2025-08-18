document.addEventListener("DOMContentLoaded", function () {
    let productSearchInput = document.getElementById("product_search");
    let productList = document.getElementById("product_list");
    let orderItemsTableBody = document.querySelector("tbody");

    productSearchInput.addEventListener("keyup", function () {
        let query = this.value;

        if (query.length > 1) {
            fetchProducts(query);
        } else {
            productList.innerHTML = "";
        }
    });

    function fetchProducts(query) {
        // warehouse_id bisa kosong, tetap kirim parameter (atau bisa dihilangkan jika backend support)
        let url = productSearchUrl + "?query=" + query;
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
                            data-cost="${product.price}"
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
              <button type="button" class="btn btn-primary btn-sm edit-discount-btn"
                  data-id="${productId}" 
                  data-name="${productName}" 
                  data-cost="${netUnitCost}"
                  data-bs-toggle="modal">
                  <span class="mdi mdi-book-edit "></span>
              </button>
              <input type="hidden" name="products[${productId}][id]" value="${productId}">
              <input type="hidden" name="products[${productId}][name]" value="${productName}">
              <input type="hidden" name="products[${productId}][code]" value="${productCode}">
          </td>
          <td>${netUnitCost.toFixed(0)}
              <input type="hidden" name="products[${productId}][cost]" value="${netUnitCost}">
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
          <td>
              <input type="number" class="form-control discount-input"
                  name="products[${productId}][discount]" value="0" min="0" style="width:100px">
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
        document
            .querySelectorAll(".qty-input, .discount-input")
            .forEach((input) => {
                input.addEventListener("input", function () {
                    let row = this.closest("tr");
                    let qty =
                        parseInt(row.querySelector(".qty-input").value) || 1;
                    let unitCost =
                        parseFloat(
                            row
                                .querySelector(".qty-input")
                                .getAttribute("data-cost")
                        ) || 0;
                    let discount =
                        parseFloat(
                            row.querySelector(".discount-input").value
                        ) || 0;

                    let subtotal = unitCost * qty - discount;
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
        let discount =
            parseFloat(row.querySelector(".discount-input").value) || 0;
        let netUnitCost = parseFloat(
            row.querySelector(".qty-input").dataset.cost
        );

        // Calculate subtotal after discount
        let subtotal = netUnitCost * qty - discount;
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
        let discount =
            parseFloat(document.getElementById("inputDiscount").value) || 0;
        let shipping =
            parseFloat(document.getElementById("inputShipping").value) || 0;

        // Apply discount and add shipping cost
        grandTotal = subtotalSum - discount + shipping;

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

    /// Manage Due for sale page
    function updateDueAmount() {
        let grandTotal =
            parseFloat(
                document.querySelector("input[name='grand_total']").value
            ) || 0;
        let paidAmount =
            parseFloat(
                document.querySelector("input[name='paid_amount']").value
            ) || 0;
        // new add full paid functionality
        let fullPaidAmount =
            parseFloat(
                document.querySelector("input[name='full_paid']").value
            ) || 0;

        if (paidAmount < 0) {
            paidAmount = 0;
            document.querySelector("input[name='paid_amount']").value = 0;
        }
        // new add full paid functionality
        if (fullPaidAmount < 0) {
            fullPaidAmount = 0;
            document.querySelector("input[name='full_paid']").value = 0;
        }

        // calculate due amount
        // let dueAmount = grandTotal - paidAmount;

        // new add full paid functionality
        let dueAmount = grandTotal - (paidAmount + fullPaidAmount);

        if (dueAmount < 0) {
            dueAmount = 0;
        }
        document.getElementById(
            "dueAmount"
        ).textContent = `Rp ${dueAmount.toFixed(0)}`;
        document.querySelector("input[name='due_amount']").value =
            dueAmount.toFixed(0);
    }

    // Event listeners for discount and shipping input change
    document
        .getElementById("inputDiscount")
        .addEventListener("input", updateGrandTotal);
    document
        .getElementById("inputShipping")
        .addEventListener("input", updateGrandTotal);
    document
        .querySelector("input[name='paid_amount']")
        .addEventListener("input", updateDueAmount);
    // new add full paid functionality
    document
        .querySelector("input[name='full_paid']")
        .addEventListener("input", updateDueAmount);

    /// Start Modal

    // this is modal, instead to html
    let modal = document.createElement("div");
    modal.id = "customModal";
    modal.style.position = "fixed";
    modal.style.top = "0";
    modal.style.left = "0";
    modal.style.width = "100%";
    modal.style.height = "100%";
    modal.style.backgroundColor = "rgba(0, 0, 0, 0.5)";
    modal.style.display = "none";
    modal.style.justifyContent = "center";
    modal.style.alignItems = "center";
    modal.style.zIndex = "1000";

    modal.innerHTML = `
      <div style="background: white; padding: 20px; border-radius: 5px; width: 400px;">
          <h3 id="modalTitle"></h3>
          <label>Harga Produk: <span class="text-danger">*</span></label>
          <input type="text" id="modalPrice" class="form-control" />

          <label>Tipe Diskon: <span class="text-danger">*</span></label>
          <select id="modalDiscountType" class="form-control">
              <option value="">Pilih Diskon</option>
              <option value="fixed">Tetap</option>
              <option value="percentage">Persen</option>
          </select>

          <label>Diskon: <span class="text-danger">*</span></label>
          <input type="text" id="modalDiscount" class="form-control" value="0" />

          <div style="margin-top: 15px; text-align: right;">
              <button id="closeModal" class="btn btn-secondary">Batal</button>
              <button id="saveChanges" class="btn btn-primary">Simpan</button>
          </div>
      </div>
  `;

    document.body.appendChild(modal);

    // Function to show modal
    function showModal(productName, productPrice) {
        document.getElementById("modalTitle").innerText = productName;
        document.getElementById("modalPrice").value = "Rp " + productPrice;
        modal.style.display = "flex";
    }

    // Event listener to open modal
    document.addEventListener("click", function (event) {
        if (event.target.closest(".edit-discount-btn")) {
            let button = event.target.closest(".edit-discount-btn");
            let productId = button.getAttribute("data-id");
            let productName = button.getAttribute("data-name");
            let productPrice = button.getAttribute("data-cost");

            // Set modal values
            document.getElementById("modalTitle").innerText = productName;
            document.getElementById("modalPrice").value = "Rp " + productPrice;
            modal.setAttribute("data-id", productId); // Store productId in modal

            // Show modal
            modal.style.display = "flex";
        }
    });

    // Close modal event
    document
        .getElementById("closeModal")
        .addEventListener("click", function () {
            modal.style.display = "none";
        });

    // Save changes event
    document
        .getElementById("saveChanges")
        .addEventListener("click", function () {
            let updatedPrice = parseFloat(
                document.getElementById("modalPrice").value.replace("Rp ", "")
            );
            let discountValue =
                parseFloat(document.getElementById("modalDiscount").value) || 0;
            let discountType =
                document.getElementById("modalDiscountType").value;
            let productId = modal.getAttribute("data-id");
            let row = document.querySelector(`tr[data-id="${productId}"]`);

            if (row) {
                let priceCell = row.querySelector("td:nth-child(2)");
                let qtyInput = row.querySelector(".qty-input");
                let discountInput = row.querySelector(".discount-input");
                let subtotalCell = row.querySelector(".subtotal");

                // Update price in table
                priceCell.innerText = updatedPrice.toFixed(0);
                qtyInput.setAttribute("data-cost", updatedPrice);

                // Set discount value
                discountInput.value = discountValue.toFixed(0);

                // Apply discount calculation
                let qty = parseFloat(qtyInput.value);
                let discountAmount =
                    discountType === "percentage"
                        ? updatedPrice * qty * (discountValue / 100)
                        : discountValue;
                let subtotal = updatedPrice * qty - discountAmount;

                subtotalCell.innerText = subtotal.toFixed(0);

                modal.style.display = "none"; // Close modal
                updateGrandTotal();
            }
        });

    // Event listeners for discount and shipping input change
    document
        .getElementById("inputDiscount")
        .addEventListener("input", updateGrandTotal);
    document
        .getElementById("inputShipping")
        .addEventListener("input", updateGrandTotal);

    document
        .getElementById("inputDiscount")
        .addEventListener("input", function () {
            document.getElementById("displayDiscount").textContent =
                this.value || 0;
        });
    document
        .getElementById("inputShipping")
        .addEventListener("input", function () {
            document.getElementById("shippingDisplay").textContent =
                this.value || 0;
        });

    // --- Perbaikan tombol Lunas ---
    const tombolLunas = document.getElementById("btn-lunas");
    const paidAmountInput = document.querySelector("input[name='paid_amount']");
    const grandTotalInput = document.querySelector("input[name='grand_total']");

    if (tombolLunas && paidAmountInput && grandTotalInput) {
        tombolLunas.addEventListener("click", function () {
            // Ambil nilai numerik dari grand_total
            const grandTotalValue = grandTotalInput.value;

            // Set nilai tersebut ke input paid_amount
            paidAmountInput.value = grandTotalValue;

            // Trigger event 'input' agar updateDueAmount berjalan
            paidAmountInput.dispatchEvent(
                new Event("input", { bubbles: true })
            );
        });
    }
});
