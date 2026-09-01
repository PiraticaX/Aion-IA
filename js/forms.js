/*
 * ============================================================
 * AION-IA - Form handling
 * ============================================================
 *
 * All form submissions are handled by PHP endpoints
 * running on the AION-IA Hostinger server.
 *
 * NO credentials are stored in this file.
 * ============================================================
 */


var AION_ENDPOINTS = {

    contact:
        "https://www.aion-ia.in/api/contact.php",

    careers:
        "https://www.aion-ia.in/api/careers-application.php",

    whitepaperAccess:
        "https://www.aion-ia.in/api/whitepaper-access.php"

};



/*
 * ============================================================
 * Generic JSON form handler
 * ============================================================
 */

function aionHandleJsonForm(
    form,
    endpoint,
    successMessage
) {

    form.addEventListener(
        "submit",
        function(e) {

            e.preventDefault();


            var statusEl =
                form.querySelector(
                    ".form-status"
                );


            var submitButton =
                form.querySelector(
                    '[type="submit"]'
                );


            /*
             * Browser validation.
             */

            if (!form.checkValidity()) {

                form.reportValidity();

                return;
            }


            /*
             * Convert form data to JSON.
             */

            var data = {};


            new FormData(form).forEach(
                function(value, key) {

                    data[key] = value;

                }
            );


            /*
             * UI state.
             */

            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.setAttribute(
                    "aria-busy",
                    "true"
                );

            }


            if (statusEl) {

                statusEl.textContent =
                    "Sending...";

                statusEl.className =
                    "form-status is-sending";

            }


            /*
             * Send request.
             */

            fetch(
                endpoint,
                {

                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json"

                    },

                    body:
                        JSON.stringify(data)

                }
            )


            /*
             * Parse response.
             */

            .then(
                function(response) {

                    return response
                        .json()
                        .catch(
                            function() {

                                throw new Error(
                                    "The server returned an invalid response."
                                );

                            }
                        )
                        .then(
                            function(result) {

                                if (
                                    !response.ok ||
                                    !result.success
                                ) {

                                    throw new Error(
                                        result.message ||
                                        "Submission failed."
                                    );

                                }


                                return result;

                            }
                        );

                }
            )


            /*
             * Success.
             */

            .then(
                function(result) {

                    if (statusEl) {

                        statusEl.textContent =
                            result.message ||
                            successMessage;

                        statusEl.className =
                            "form-status is-success";

                    }


                    form.reset();

                }
            )


            /*
             * Error.
             */

            .catch(
                function(error) {

                    console.error(
                        "AION-IA form error:",
                        error
                    );


                    if (statusEl) {

                        statusEl.textContent =
                            error.message ||
                            "Unable to submit the form. Please try again.";

                        statusEl.className =
                            "form-status is-error";

                    }

                }
            )


            /*
             * Restore button.
             */

            .finally(
                function() {

                    if (submitButton) {

                        submitButton.disabled =
                            false;

                        submitButton.removeAttribute(
                            "aria-busy"
                        );

                    }

                }
            );

        }
    );

}



/*
 * ============================================================
 * Careers form
 *
 * Uses multipart/form-data because a resume
 * is uploaded.
 * ============================================================
 */

function aionHandleCareersForm(form) {

    form.addEventListener(
        "submit",
        function(e) {

            e.preventDefault();


            var statusEl =
                form.querySelector(
                    ".form-status"
                );


            var submitButton =
                form.querySelector(
                    '[type="submit"]'
                );


            /*
             * Browser validation.
             */

            if (!form.checkValidity()) {

                form.reportValidity();

                return;
            }


            /*
             * FormData automatically includes
             * the resume file.
             */

            var data =
                new FormData(form);


            /*
             * UI state.
             */

            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.setAttribute(
                    "aria-busy",
                    "true"
                );

            }


            if (statusEl) {

                statusEl.textContent =
                    "Submitting application...";

                statusEl.className =
                    "form-status is-sending";

            }


            /*
             * Upload.
             */

            fetch(
                AION_ENDPOINTS.careers,
                {

                    method: "POST",

                    headers: {

                        "Accept":
                            "application/json"

                    },

                    body: data

                }
            )


            /*
             * Parse server response.
             */

            .then(
                function(response) {

                    return response
                        .json()
                        .catch(
                            function() {

                                throw new Error(
                                    "The server returned an invalid response."
                                );

                            }
                        )
                        .then(
                            function(result) {

                                if (
                                    !response.ok ||
                                    !result.success
                                ) {

                                    throw new Error(
                                        result.message ||
                                        "Application submission failed."
                                    );

                                }


                                return result;

                            }
                        );

                }
            )


            /*
             * Success.
             */

            .then(
                function(result) {

                    if (statusEl) {

                        statusEl.textContent =
                            result.message ||
                            "Application received.";

                        statusEl.className =
                            "form-status is-success";

                    }


                    form.reset();

                }
            )


            /*
             * Error.
             */

            .catch(
                function(error) {

                    console.error(
                        "AION-IA careers error:",
                        error
                    );


                    if (statusEl) {

                        statusEl.textContent =
                            error.message ||
                            "Unable to submit your application. Please try again.";

                        statusEl.className =
                            "form-status is-error";

                    }

                }
            )


            /*
             * Restore button.
             */

            .finally(
                function() {

                    if (submitButton) {

                        submitButton.disabled =
                            false;

                        submitButton.removeAttribute(
                            "aria-busy"
                        );

                    }

                }
            );

        }
    );

}



/*
 * ============================================================
 * Whitepaper access gate
 * ============================================================
 */

function aionInitWhitepaperGate() {

    var overlay =
        document.getElementById(
            "wpGate"
        );


    var library =
        document.getElementById(
            "wpLibrary"
        );


    var form =
        document.getElementById(
            "wpGateForm"
        );


    /*
     * Not on whitepaper page.
     */

    if (
        !overlay ||
        !library ||
        !form
    ) {

        return;

    }


    /*
     * Check session.
     */

    var unlocked =
        sessionStorage.getItem(
            "aion_wp_access"
        ) === "true";


    if (unlocked) {

        overlay.classList.remove(
            "is-open"
        );


        library.removeAttribute(
            "hidden"
        );

    }
    else {

        overlay.classList.add(
            "is-open"
        );

    }



    /*
     * Submit access request.
     */

    form.addEventListener(
        "submit",
        function(e) {

            e.preventDefault();


            var statusEl =
                form.querySelector(
                    ".form-status"
                );


            var submitButton =
                form.querySelector(
                    '[type="submit"]'
                );


            /*
             * Browser validation.
             */

            if (!form.checkValidity()) {

                form.reportValidity();

                return;
            }


            /*
             * Convert to JSON.
             */

            var data = {};


            new FormData(form).forEach(
                function(value, key) {

                    data[key] = value;

                }
            );


            /*
             * UI state.
             */

            if (submitButton) {

                submitButton.disabled =
                    true;

                submitButton.setAttribute(
                    "aria-busy",
                    "true"
                );

            }


            if (statusEl) {

                statusEl.textContent =
                    "Processing request...";

                statusEl.className =
                    "form-status is-sending";

            }


            /*
             * Send request.
             */

            fetch(
                AION_ENDPOINTS.whitepaperAccess,
                {

                    method: "POST",

                    headers: {

                        "Content-Type":
                            "application/json",

                        "Accept":
                            "application/json"

                    },

                    body:
                        JSON.stringify(data)

                }
            )


            /*
             * Parse response.
             */

            .then(
                function(response) {

                    return response
                        .json()
                        .catch(
                            function() {

                                throw new Error(
                                    "The server returned an invalid response."
                                );

                            }
                        )
                        .then(
                            function(result) {

                                if (
                                    !response.ok ||
                                    !result.success
                                ) {

                                    throw new Error(
                                        result.message ||
                                        "Unable to process request."
                                    );

                                }


                                return result;

                            }
                        );

                }
            )


            /*
             * Successful access.
             */

            .then(
                function(result) {

                    /*
                     * IMPORTANT:
                     *
                     * Only unlock the library AFTER
                     * the backend confirms success.
                     */

                    sessionStorage.setItem(
                        "aion_wp_access",
                        "true"
                    );


                    overlay.classList.remove(
                        "is-open"
                    );


                    library.removeAttribute(
                        "hidden"
                    );


                    if (statusEl) {

                        statusEl.textContent =
                            result.message ||
                            "Access granted.";

                        statusEl.className =
                            "form-status is-success";

                    }


                    form.reset();

                }
            )


            /*
             * Failure.
             */

            .catch(
                function(error) {

                    console.error(
                        "AION-IA whitepaper error:",
                        error
                    );


                    if (statusEl) {

                        statusEl.textContent =
                            error.message ||
                            "Unable to process your request. Please try again.";

                        statusEl.className =
                            "form-status is-error";

                    }

                }
            )


            /*
             * Restore button.
             */

            .finally(
                function() {

                    if (submitButton) {

                        submitButton.disabled =
                            false;

                        submitButton.removeAttribute(
                            "aria-busy"
                        );

                    }

                }
            );

        }
    );

}



/*
 * ============================================================
 * Initialise all forms
 * ============================================================
 */

document.addEventListener(
    "DOMContentLoaded",
    function() {


        /*
         * Contact
         */

        var contactForm =
            document.getElementById(
                "contactForm"
            );


        if (contactForm) {

            aionHandleJsonForm(
                contactForm,
                AION_ENDPOINTS.contact,
                "Message sent. AION-IA will respond shortly."
            );

        }



        /*
         * Careers
         */

        var careersForm =
            document.getElementById(
                "careersForm"
            );


        if (careersForm) {

            aionHandleCareersForm(
                careersForm
            );

        }



        /*
         * Whitepaper gate
         */

        aionInitWhitepaperGate();

    }
);