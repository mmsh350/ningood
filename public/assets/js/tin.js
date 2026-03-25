$("#verifyNIN").on("click", function (event) {
    event.preventDefault();

    let data = new FormData(this.form);
    let validationInfo = document.getElementById("validation-info");
    let download = document.getElementById("download");
    $("#errorMsg").hide();

    var preloader = $(".page-loading");

    function showLoader() {
        preloader.addClass("active").show();
    }

    function hideLoader() {
        preloader.removeClass("active");
        setTimeout(function () {
            preloader.hide();
        }, 1000);
    }

    $.ajax({
        type: "post",
        url: "/user/tin-retrieve",
        dataType: "json",
        data,
        processData: false,
        contentType: false,
        cache: false,
        beforeSend: function () {
            showLoader();
            $("#download").hide();
        },
        success: function (result) {
            hideLoader();

            if (result && result.status === "success") {
                let displayContent = "";

                if (result.entity === "corporate") {
                    // Corporate entity display
                    const typeMapping = {
                        1: "BN",
                        2: "RC",
                    };

                    const typeDescription =
                        typeMapping[result.data.type] || "RC";

                    displayContent = `
                      <div class="card shadow-sm">
                        <div class="card-body">
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-building"></i> (${typeDescription}) Number </small>
                                <h6 class="fw-bold text-dark">${result.data.rc}</h6>
                              </div>
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-building-check"></i> Company Name</small>
                                <h6 class="fw-bold text-dark">${result.data.company_name}</h6>
                              </div>
                            </div>
                          </div>

                          <hr class="my-3">

                          <div class="alert alert-secondary border-0 bg-light-secondary p-3 mb-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <i class="bi bi-check-circle-fill text-secondary" style="font-size: 2rem;"></i>
                              </div>
                              <div class="flex-grow-1">
                                <small class="text-muted d-block mb-1"><i class="bi bi-receipt"></i> Tax ID Generated</small>
                                <h5 class="fw-bold text-primary mb-0" style="font-family: 'Courier New', monospace; letter-spacing: 1px;">
                                  <span id="tax_id">${result.data.tax_id}</span>
                                </h5>
                                  <h6 class="fw-bold text-dark"><span hidden id="entityType">${result.entity}</span></h6>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    `;
                } else {
                    // Individual entity display (existing code)
                    displayContent = `
                      <div class="card shadow-sm">
                        <div class="card-body">
                          <div class="row mb-4">
                            <div class="col-md-6">
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-person-badge"></i> NIN</small>
                                <h6 class="fw-bold text-dark"><span id="tax_id">${
                                    result.data.tax_id
                                }</span></h6>
                                  <h6 class="fw-bold text-dark"><span hidden id="entityType">${
                                      result.entity
                                  }</span></h6>
                              </div>
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-person"></i> First Name</small>
                                <h6 class="fw-bold text-dark">${
                                    result.data.firstName
                                }</h6>
                              </div>
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-person"></i> Surname</small>
                                <h6 class="fw-bold text-dark">${
                                    result.data.lastName
                                }</h6>
                              </div>
                            </div>
                            <div class="col-md-6">
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-calendar-event"></i> Date of Birth</small>
                                <h6 class="fw-bold text-dark">${
                                    result.data.dateOfBirth
                                }</h6>
                              </div>
                              <div class="mb-3">
                                <small class="text-muted d-block mb-1"><i class="bi bi-geo-alt"></i> Tax Residency</small>
                                <h6 class="fw-bold text-dark">${
                                    result.data.tax_residency || "N/A"
                                }</h6>
                              </div>
                            </div>
                          </div>

                          <hr class="my-3">

                          <div class="alert alert-secondary border-0 bg-light-secondary p-3 mb-0">
                            <div class="d-flex align-items-center">
                              <div class="me-3">
                                <i class="bi bi-check-circle-fill text-dark" style="font-size: 2rem;"></i>
                              </div>
                              <div class="flex-grow-1">
                                <small class="text-muted d-block mb-1"><i class="bi bi-receipt"></i> Tax ID Generated</small>
                                <h5 class="fw-bold text-dark mb-0" style="font-family: 'Courier New', monospace; letter-spacing: 1px;">
                                  ${result.data.tax_id}
                                </h5>
                              </div>
                            </div>
                          </div>
                        </div>
                      </div>
                    `;
                }

                validationInfo.innerHTML = displayContent;
                $("#download").show();
            } else {
                hideLoader();

                $("#errorMsg").show();
                $("#message").html("Invalid Response");

                setTimeout(function () {
                    $("#errorMsg").fadeOut();
                }, 30000);
            }
        },
        error: function (data) {
            hideLoader();
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg").show();
                $("#message").html(value);
            });
            setTimeout(function () {
                $("#errorMsg").fadeOut();
            }, 30000);
        },
    });
});

$("#download_id").on("click", function (event) {
    let getNIN = $("#tax_id").html();
    let getEntityType = $("#entityType").text();

    fetch("/user/tinSlip/" + getNIN + "/" + getEntityType, {
        method: "GET",
        headers: {
            "Content-Type": "application/json",
            "X-CSRF-TOKEN": "{{ csrf_token() }}",
        },
    })
        .then((response) => {
            if (response.ok) {
                // Extract filename from Content-Disposition header
                const contentDisposition = response.headers.get(
                    "Content-Disposition"
                );
                let filename = "document.pdf";
                if (
                    contentDisposition &&
                    contentDisposition.indexOf("attachment") !== -1
                ) {
                    const filenameRegex =
                        /filename[^;=\n]*=((['"]).*?\2|[^;\n]*)/;
                    const matches = filenameRegex.exec(contentDisposition);
                    if (matches != null && matches[1]) {
                        filename = matches[1].replace(/['"]/g, "");
                    }
                }
                return response.blob().then((blob) => ({ blob, filename }));
            } else {
                return response.json().then((data) => {
                    // Handle errors
                    $.each(data.errors, function (key, value) {
                        $("#errorMsg2").show();
                        $("#message2").html(value);
                    });
                    setTimeout(function () {
                        $("#errorMsg2").hide();
                    }, 5000);
                });
            }
        })
        .then(({ blob, filename }) => {
            if (blob) {
                // Create a link element, use it to download the blob with the extracted filename
                const url = window.URL.createObjectURL(blob);
                const a = document.createElement("a");
                a.href = url;
                a.download = filename; // Use the extracted filename
                document.body.appendChild(a);
                a.click();
                a.remove();
                window.URL.revokeObjectURL(url);
            }
        })
        .catch((error) => {
            console.error("Error:", error);
            // Handle errors
            $.each(data.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        });
});
