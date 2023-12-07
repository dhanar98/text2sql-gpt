<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    <script src="https://code.jquery.com/jquery-3.6.4.min.js"></script>
    @vite('resources/css/app.css')
    @vite('resources/css/prism.css')
    @vite('resources/js/prism.js')

</head>

<body class="antialiased bg-gray-100 selection:bg-red-500 selection:text-white">

    <div class="min-h-screen flex justify-center items-center">
        <div class="max-w-4xl w-full p-6 lg:p-8 bg-white rounded-md shadow-md">

            <div class="grid grid-cols-2 md:grid-cols-2 gap-6">
                <div class="mb-4 md:mb-0 md:col-span-2">
                    <!-- Text Area: Human Prompt -->
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-600">Human Prompt</label>
                        <textarea id="human-prompt" class="w-full px-3 py-2 border rounded-md"></textarea>
                    </div>

                    <!-- Text Area: SQL Result -->
                    <div class="mb-4 md:mb-0">
                        <label class="block mb-2 text-sm font-medium text-gray-600">SQL Result</label>
                        <pre style="display:-webkit-box; overflow: visible; padding: 0.5em; background: rgb(29, 29, 29); color: inherit; max-height: none; height: auto; overflow-wrap: break-word; flex: 1 1 0%; border-radius: 0.5rem;">
                            <code id="sql-result" class="language-sql" style="white-space: pre;">
                            </code>
                        </pre>
                    </div>
                </div>

                <!-- Second Column -->
                <div class="mb-4 md:mb-0 md:col-span-2">
                    <div class="mb-4">
                        <label class="block mb-2 text-sm font-medium text-gray-600">List of Tables</label>
                        <select id="list-of-table" class="w-full px-3 py-2 border rounded-md">
                            @foreach ($fetchedDbTableNames as $tableName)
                            <option value="{{ $tableName }}">{{ ucfirst(str_replace('_', ' ', $tableName)) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block mb-2 text-sm font-medium text-gray-600">Create SQL Statement</label>
                        <pre style="display:-webkit-box; overflow: visible; padding: 0.5em; background: rgb(29, 29, 29); color: inherit; max-height: none; height: auto; overflow-wrap: break-word; flex: 1 1 0%; border-radius: 0.5rem;">
                            <code id="fetched-sql" class="language-sql" style="white-space: pre;">
                            </code>
                        </pre>
                    </div>
                </div>

                <div class="w-full">
                    <!-- Convert to SQL Button -->
                    <div class="w-full md:w-1/2">
                        <button id="convert-to-sql-btn" class="bg-blue-500 text-white px-4 py-2 rounded-md w-full">Convert to SQL</button>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        $(document).ready(function() {
            function makeAjaxRequest(endpointUrl, method, requestData, successCallback, errorCallback) {
                const csrfToken = $('meta[name="csrf-token"]').attr('content');

                $.ajax({
                    url: endpointUrl,
                    method: method,
                    data: requestData,
                    dataType: 'json',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    },
                    success: successCallback,
                    error: errorCallback
                });
            }

            function handleAjaxSuccess(response, resultElementId) {
                const {
                    status,
                    data,
                    message
                } = response;
                if (status === 'SUCCESS' && data) {
                    const resultText = $.trim(data.translatedTextToSQL || data.fetchedSqlStatement);
                    $(`#${resultElementId}`).text(resultText);
                    Swal.fire({
                        icon: "success",
                        title: "SUCCESS",
                        html: "<b>"+ message + "</b>",
                    });
                    Prism.highlightElement($(`#${resultElementId}`)[0]);
                }
            }

            function getCreateQuery(tableName) {
                const endpointUrl = "{{ route('getcreatestatement') }}";
                const requestData = {
                    tableName
                };

                makeAjaxRequest(endpointUrl, 'GET', requestData, function(response) {
                    handleAjaxSuccess(response, 'fetched-sql');
                }, function(error) {
                    Swal.fire({
                        icon: "error",
                        title: "ERROR",
                        text: error.responseJSON.message,
                    });
                });
            }

            function convertToSQL() {
                const endpointUrl = "{{ route('getsqlstatement') }}";
                const humanPrompt = $('#human-prompt').val();
                const createSqlStatement = $('#fetched-sql').val();

                const requestData = {
                    humanPrompt,
                    createSqlStatement
                };

                makeAjaxRequest(endpointUrl, 'POST', requestData, function(response) {
                    handleAjaxSuccess(response, 'sql-result');
                }, function(error) {
                    Swal.fire({
                        icon: "error",
                        title: "ERROR",
                        text: error.responseJSON.message,
                    });
                });
            }

            // Event handlers
            $('#convert-to-sql-btn').click(function() {
                convertToSQL();
            });

            $('#list-of-table').change(function() {
                getCreateQuery($(this).val());
            });
        });
    </script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</body>


</html>