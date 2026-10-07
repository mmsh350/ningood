$("#verifyBVN").on("click", function (event) {

    const getMimeType = (base64) => {
        if (!base64) return 'image/jpeg';
        if (base64.startsWith('iVBORw0KGgo')) return 'image/png';
        if (base64.startsWith('/9j/')) return 'image/jpeg';
        if (base64.startsWith('R0lGOD')) return 'image/gif';
        return 'image/jpeg';
    };

    event.preventDefault();

    let data = new FormData(this.form);
    let validationInfo = document.getElementById("validation-info");
    let download = document.getElementById("download");

    var preloader = $('.page-loading');

    function showLoader() {
        if (typeof showGlobalLoader === 'function') {
            showGlobalLoader();
        }
    }

    function hideLoader() {
        if (typeof hideGlobalLoader === 'function') {
            hideGlobalLoader();
        }
    }

    $.ajax({
        type: "post",
        url: "/user/bvn-retrieve",
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
            $("#loader").hide();
            hideLoader();

            let bvnData = result.data || {};
            const rawPhoto = bvnData.photo || bvnData.image || bvnData.base64Image || '';
            const mimeType = getMimeType(rawPhoto);
            const imgSrc = rawPhoto ? (rawPhoto.startsWith('data:') ? rawPhoto : `data:${mimeType};base64, ${rawPhoto}`) : '';
            const bvnNumber = bvnData.bvn || bvnData.idNumber || '';
            const firstName = bvnData.firstName || '';
            const surname = bvnData.lastName || bvnData.surname || '';
            const middleName = bvnData.middleName || '';
            const phoneNo = bvnData.phoneNumber || bvnData.phoneNumber1 || bvnData.phoneno || '';
            const gender = bvnData.gender || '';

            validationInfo.innerHTML = `
            <div class="border border-light">
   <div class="table-responsive">
      <table class="table">
         <thead >
            <tr>
               <th style="border: none ! important;" width="20%"></th>
               <th style="border: none ! important;"></th>
               <th style="border: none ! important;"></th>
               <th style="border: none ! important;"></th>
            </tr>
         </thead>
         <tbody>
            <tr>
               <th scope="row" rowspan="9">
                  <img class="rounded" src="${imgSrc}" alt="User Image" style="width: 250px; height: 250px;">
               </th>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">BVN</th>
               <td style="text-align:left" ><span id="bvnno" >${bvnNumber}</span>
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">FirstName</th>
               <td  style="text-align:left">${firstName}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Surname</th>
               <td  style="text-align:left">${surname}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Middle Name</th>
               <td  style="text-align:left">${middleName}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Phone No</th>
               <td  style="text-align:left">${phoneNo}
               </td>
            </tr>
            <tr>
               <th scope="row" style="text-align:right; border: none ! important;">Gender</th>
               <td  style="text-align:left">${gender}
               </td>
            </tr>

         </tbody>
      </table>
   </div>
</div>
            `;
            $("#download").show();
        },
        error: function (data) {
            $("#loader").hide();
            hideLoader();
            if (data && data.responseJSON && data.responseJSON.errors) {
                $.each(data.responseJSON.errors, function (key, value) {
                    $("#errorMsg").show();
                    $("#message").html(value);
                });
            } else if (data && data.responseJSON && data.responseJSON.message) {
                $("#errorMsg").show();
                $("#message").html(data.responseJSON.message);
            }
            setTimeout(function () {
                $("#errorMsg").hide();
            }, 5000);
        },
    });
});

$("#freeSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();
    $.ajax({
        type: "get",
        url: "/user/standardBVN/" + getBVN,
        dataType: "json",
        processData: false,
        contentType: false,
        cache: false,
        success: function (response) {
            if (response.view) {
                var newWindow = window.open("", "_blank");
                newWindow.document.write(response.view);
                newWindow.document.close();
            } else {
                console.error("No view content received");
            }
        },
        error: function (data) {
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        },
    });
});

$("#paidSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();
    $.ajax({
        type: "get",
        url: "/user/premiumBVN/" + getBVN,
        dataType: "json",
        processData: false,
        contentType: false,
        cache: false,
        success: function (response) {
            if (response.view) {
                var newWindow = window.open("", "_blank");
                newWindow.document.write(response.view);
                newWindow.document.close();
            } else {
                console.error("No view content received");
            }
        },
        error: function (data) {
            $.each(data.responseJSON.errors, function (key, value) {
                $("#errorMsg2").show();
                $("#message2").html(value);
            });
            setTimeout(function () {
                $("#errorMsg2").hide();
            }, 5000);
        },
    });
});
$("#plasticSlip").on("click", function (event) {
    let getBVN = $("#bvnno").html();

    fetch("/user/plasticBVN/" + getBVN, {
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
                let filename = "document.pdf"; // Default filename if not found in headers
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
