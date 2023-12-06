<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Laravel</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=figtree:400,600&display=swap" rel="stylesheet" />
    @vite('resources/css/app.css')

</head>

<body class="antialiased bg-gray-100 selection:bg-red-500 selection:text-white">

    <div class="min-h-screen flex justify-center items-center">
        <div class="max-w-4xl w-full p-6 lg:p-8 bg-white rounded-md shadow-md">

            <div class="flex flex-wrap">
                <!-- Text Area: Human Prompt -->
                <div class="w-full mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-600">Human Prompt</label>
                    <textarea id="human-prompt" class="w-full px-3 py-2 border rounded-md"></textarea>
                </div>

                <!-- Text Area: SQL Result -->
                <div class="w-full mb-4">
                    <label class="block mb-2 text-sm font-medium text-gray-600">SQL Result</label>
                    <textarea id="sql-result" class="w-full px-3 py-2 border rounded-md language-sql line-numbers"></textarea>
                </div>

                <!-- List of Table Dropdown and Convert Button Container -->
                <div class="w-full flex justify-center items-center mb-4">
                    <!-- Dropdown: List of Tables -->
                    <div class="w-50 pr-2">
                        <label class="block mb-2 text-sm font-medium text-gray-600">List of Tables</label>
                        <select id="list-of-table" class="w-full px-3 py-2 border rounded-md">
                        @foreach ($fetchedDbTableNames as $tableName)
                            <option value="{{ $tableName }}">{{ ucfirst(str_replace('_', ' ', $tableName)) }}</option>
                        @endforeach
                        </select>
                    </div>
                </div>
                <div class="w-full">
                    <!-- Convert to SQL Button -->
                    <div class="w-1/2 pl-2">
                        <button id="convert-to-sql-btn" class="bg-blue-500 text-white px-4 py-2 rounded-md w-50">Convert to SQL</button>
                    </div>
                </div>

                <!-- Text Area: Create SQL Statement -->
                <div class="w-full">
                    <label class="block mb-2 text-sm font-medium text-gray-600">Create SQL Statement</label>
                    <textarea id="create-sql-statement" class="w-full px-3 py-2 border rounded-md language-sql line-numbers"></textarea>
                </div>
            </div>
        </div>
    </div>

</body>


</html>