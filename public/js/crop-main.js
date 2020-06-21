window.onload = function() {
    "use strict";

    var Cropper = window.Cropper;
    var URL = window.URL || window.webkitURL;
    var container = document.querySelector(".img-container");
    var image = container.getElementsByTagName("img").item(0);
    var download = document.getElementById("download");
    var actions = document.getElementById("actions");
    var dataX = document.getElementById("dataX");
    var dataY = document.getElementById("dataY");
    var dataHeight = document.getElementById("dataHeight");
    var dataWidth = document.getElementById("dataWidth");
    var dataRotate = document.getElementById("dataRotate");
    var dataScaleX = document.getElementById("dataScaleX");
    var dataScaleY = document.getElementById("dataScaleY");
    var options = {
        viewMode: 1,
        dragMode: "move",
        aspectRatio: NaN,
        checkCrossOrigin: false,
        preview: ".img-preview",
        responsive: true,
        modal: true,
        guides: true,
        center: true,
        highlight: true,
        background: false,
        autoCrop: true,
        autoCropArea: 1,
        movable: false,
        zoomable: false,
        scalable: true,
        zoomOnTouch: false,
        zoomOnWheel: false,
        cropBoxMovable: true,
        cropBoxResizable: true,
        toggleDragModeOnDblclick: false,
        preview: ".img-preview",
        ready: function(e) {
            console.log(e.type);
        },
        cropstart: function(e) {
            console.log(e.type, e.detail.action);
        },
        cropmove: function(e) {
            console.log(e.type, e.detail.action);
        },
        cropend: function(e) {
            console.log(e.type, e.detail.action);
        },
        crop: function(e) {
            var data = e.detail;

            console.log(e.type);
            dataX.value = Math.round(data.x);
            dataY.value = Math.round(data.y);
            dataHeight.value = Math.round(data.height);
            dataWidth.value = Math.round(data.width);
            dataRotate.value =
                typeof data.rotate !== "undefined" ? data.rotate : "";
        }
    };
    var cropper = new Cropper(image, options);
    var originalImageURL = image.src;
    var uploadedImageType = "image/jpeg";
    var uploadedImageName = document.getElementById("pictureid").value;
    var uploadedImageURL;

    // Tooltip
    $('[data-toggle="tooltip"]').tooltip();

    // Buttons
    if (!document.createElement("canvas").getContext) {
        $('button[data-method="getCroppedCanvas"]').prop("disabled", true);
    }

    if (
        typeof document.createElement("cropper").style.transition ===
        "undefined"
    ) {
        $('button[data-method="rotate"]').prop("disabled", true);
        $('button[data-method="scale"]').prop("disabled", true);
    }

    // Download
    if (typeof download.download === "undefined") {
        download.className += " disabled";
        download.title = "Your browser does not support download";
    }

    // Options
    actions.querySelector(".docs-toggles").onchange = function(event) {
        var e = event || window.event;
        var target = e.target || e.srcElement;
        var cropBoxData;
        var canvasData;
        var isCheckbox;
        var isRadio;

        if (!cropper) {
            return;
        }

        if (target.tagName.toLowerCase() === "label") {
            target = target.querySelector("input");
        }

        isCheckbox = target.type === "checkbox";
        isRadio = target.type === "radio";

        if (isCheckbox || isRadio) {
            if (isCheckbox) {
                options[target.name] = target.checked;
                cropBoxData = cropper.getCropBoxData();
                canvasData = cropper.getCanvasData();

                options.ready = function() {
                    console.log("ready");
                    cropper
                        .setCropBoxData(cropBoxData)
                        .setCanvasData(canvasData);
                };
            } else {
                options[target.name] = target.value;
                options.ready = function() {
                    console.log("ready");
                };
            }

            // Restart
            cropper.destroy();
            cropper = new Cropper(image, options);
        }
    };

    // Methods
    actions.querySelector(".docs-buttons").onclick = function(event) {
        var e = event || window.event;
        var target = e.target || e.srcElement;
        var cropped;
        var result;
        var input;
        var data;

        if (!cropper) {
            return;
        }

        while (target !== this) {
            if (target.getAttribute("data-method")) {
                break;
            }

            target = target.parentNode;
        }

        if (
            target === this ||
            target.disabled ||
            target.className.indexOf("disabled") > -1
        ) {
            return;
        }

        data = {
            method: target.getAttribute("data-method"),
            target: target.getAttribute("data-target"),
            option: target.getAttribute("data-option") || undefined,
            secondOption: target.getAttribute("data-second-option") || undefined
        };

        cropped = cropper.cropped;

        if (data.method) {
            if (typeof data.target !== "undefined") {
                input = document.querySelector(data.target);

                if (
                    !target.hasAttribute("data-option") &&
                    data.target &&
                    input
                ) {
                    try {
                        data.option = JSON.parse(input.value);
                    } catch (e) {
                        console.log(e.message);
                    }
                }
            }

            switch (data.method) {
                case "rotate":
                    if (cropped && options.viewMode > 0) {
                        cropper.clear();
                    }

                    break;

                case "getCroppedCanvas":
                    try {
                        data.option = JSON.parse(data.option);
                    } catch (e) {
                        console.log(e.message);
                    }

                    if (uploadedImageType === "image/jpeg") {
                        if (!data.option) {
                            data.option = {};
                        }

                        data.option.fillColor = "#fff";
                    }

                    break;
            }

            result = cropper[data.method](data.option, data.secondOption);

            switch (data.method) {
                case "rotate":
                    if (cropped && options.viewMode > 0) {
                        cropper.crop();
                    }

                    break;

                case "scaleX":
                case "scaleY":
                    target.setAttribute("data-option", -data.option);
                    break;

                case "getCroppedCanvas":
                    if (result) {
                        // Bootstrap's Modal
                        $("#getCroppedCanvasModal")
                            .modal()
                            .find(".modal-body")
                            .html(result);

                        if (!download.disabled) {
                            download.download = uploadedImageName;
                            download.href = result.toDataURL(uploadedImageType);
                        }
                    }

                    break;

                case "destroy":
                    cropper = null;

                    if (uploadedImageURL) {
                        URL.revokeObjectURL(uploadedImageURL);
                        uploadedImageURL = "";
                        image.src = originalImageURL;
                    }

                    break;
            }

            if (typeof result === "object" && result !== cropper && input) {
                try {
                    input.value = JSON.stringify(result);
                } catch (e) {
                    console.log(e.message);
                }
            }
        }
    };

    document.body.onkeydown = function(event) {
        var e = event || window.event;

        if (e.target !== this || !cropper || this.scrollTop > 300) {
            return;
        }

        switch (e.keyCode) {
            case 37:
                e.preventDefault();
                cropper.move(-1, 0);
                break;

            case 38:
                e.preventDefault();
                cropper.move(0, -1);
                break;

            case 39:
                e.preventDefault();
                cropper.move(1, 0);
                break;

            case 40:
                e.preventDefault();
                cropper.move(0, 1);
                break;
        }
    };
};
