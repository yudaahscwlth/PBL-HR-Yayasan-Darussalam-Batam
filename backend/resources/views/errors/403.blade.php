<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Error Page</title>
  <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-50 flex items-center justify-center min-h-screen">
  <div class="text-center">
    <!-- ERROR CODE -->
    <h1 class="text-9xl font-bold text-blue-600">403</h1>
    
    <!-- ERROR TITLE -->
    <p class="text-2xl mt-4 font-semibold">Error: Page Not Found</p>
    
    <!-- ERROR DESCRIPTION -->
    <p class="mt-2 text-gray-600">Sorry, the page you're looking for cannot be accessed.</p>

    <!-- BUTTONS -->
    <div class="mt-6 flex justify-center space-x-4">
      <button 
        onclick="history.back()" 
        class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">
        Go Back
      </button>
    </div>
  </div>
</body>
</html>
