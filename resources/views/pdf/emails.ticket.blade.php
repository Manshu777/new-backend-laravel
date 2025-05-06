<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Flight Ticket</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-100 font-sans">
    <div class="max-w-2xl mx-auto mt-10 bg-white shadow-lg rounded-lg overflow-hidden">
        <!-- Header with logo -->
        <div class="bg-blue-600 p-4 flex items-center justify-between">
            <img src="https://via.placeholder.com/100x40?text=LOGO" alt="Logo" class="h-10">
            <h2 class="text-white text-xl font-semibold">Flight Ticket</h2>
        </div>

        <!-- Ticket details -->
        <div class="p-6 space-y-4">
            <div class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <p class="text-gray-500">PNR</p>
                    <p class="font-medium">{{ $ticket['pnr'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Booking ID</p>
                    <p class="font-medium">{{ $ticket['booking_id'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Passenger Name</p>
                    <p class="font-medium">{{ $ticket['user_name'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Email</p>
                    <p class="font-medium">{{ $ticket['username'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Phone Number</p>
                    <p class="font-medium">{{ $ticket['phone_number'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Flight</p>
                    <p class="font-medium">{{ $ticket['flight_name'] }} ({{ $ticket['flight_number'] }})</p>
                </div>
                <div>
                    <p class="text-gray-500">Departure</p>
                    <p class="font-medium">{{ $ticket['departure_from'] }} at {{ $ticket['flight_date'] }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Arrival</p>
                    <p class="font-medium">{{ $ticket['arrival_to'] }} at {{ $ticket['return_date'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Total Fare</p>
                    <p class="font-medium text-green-600">INR {{ number_format($ticket['total_fare'], 2) }}</p>
                </div>
                <div>
                    <p class="text-gray-500">Booking Date</p>
                    <p class="font-medium">{{ $ticket['date_of_booking'] }}</p>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
