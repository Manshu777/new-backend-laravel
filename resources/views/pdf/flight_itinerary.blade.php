@php
    // Custom directive to escape LaTeX special characters
    Blade::directive('latexescape', function ($expression) {
        return "<?php echo str_replace(
            ['\\', '&', '%', '$', '#', '_', '{', '}', '~', '^'],
            ['\\\\', '\\&', '\\%', '\\$', '\\#', '\\_', '\\{', '\\}', '\\textasciitilde{}', '\\textasciicircum{}'],
            $expression
        ); ?>";
    });
@endphp
% Document class and basic setup
\documentclass[a4paper,12pt]{article}
\usepackage[utf8]{inputenc}
\usepackage[T1]{fontenc}
\usepackage{lmodern}
\usepackage{geometry}
\usepackage{booktabs}
\usepackage{enumitem}
\usepackage{xcolor}
\usepackage{datetime}
\geometry{margin=1in}

% Define colors for styling
\definecolor{headerblue}{RGB}{0, 114, 230}

% Customize section headers
\usepackage{sectsty}
\sectionfont{\color{headerblue}\large\bfseries}
\subsectionfont{\color{headerblue}\normalsize\bfseries}

% Format the date
\newdateformat{itinerarydate}{\THEDAY\ \monthname[\THEMONTH]\ \THEYEAR}

% Begin document
\begin{document}

% Header
\begin{center}
    \textbf{\Large NextGenTrip} \\[0.2cm]
    \normalsize Flight Invoice \\[0.1cm]
    \small PNR: @latexescape($ticket['pnr'] ?? 'N/A') \quad Booking ID: @latexescape($ticket['booking_id'] ?? 'N/A')
\end{center}
\vspace{0.5cm}

% Booking Details
\section*{Booking Details}
\begin{tabular}{p{0.45\textwidth} p{0.45\textwidth}}
    \toprule
    \textbf{Detail} & \textbf{Value} \\
    \midrule
    PNR & @latexescape($ticket['pnr'] ?? 'N/A') \\
    Booking ID & @latexescape($ticket['booking_id'] ?? 'N/A') \\
    Booking Date & @latexescape($ticket['date_of_booking'] ?? 'N/A') \\
    Invoice Number & @latexescape($ticket['invoice_no'] ?? 'N/A') \\
    Invoice Amount & @latexescape((isset($ticket['passengers'][0]['Fare']['Currency']) ? $ticket['passengers'][0]['Fare']['Currency'] : 'INR')) @latexescape($ticket['total_fare'] ?? '0.00') \\
    \bottomrule
\end{tabular}

% Flight Details
\section*{Flight Itinerary}
@php
    // Pre-process complex expressions to avoid syntax errors
    $departureTime = isset($ticket['segments'][0]['Origin']['DepTime'])
        ? \Carbon\Carbon::parse($ticket['segments'][0]['Origin']['DepTime'])->format('d F Y, H:i')
        : now()->format('d F Y, H:i');
    $arrivalTime = isset($ticket['segments'][0]['Destination']['ArrTime'])
        ? \Carbon\Carbon::parse($ticket['segments'][0]['Destination']['ArrTime'])->format('d F Y, H:i')
        : now()->format('d F Y, H:i');
    $duration = isset($ticket['segments'][0]['Duration'])
        ? floor($ticket['segments'][0]['Duration'] / 60) . ' hours ' . ($ticket['segments'][0]['Duration'] % 60) . ' minutes'
        : 'N/A';
@endphp
\begin{tabular}{p{0.45\textwidth} p{0.45\textwidth}}
    \toprule
    \textbf{Detail} & \textbf{Value} \\
    \midrule
    Flight & @latexescape($ticket['flight_name'] ?? 'N/A') (@latexescape($ticket['flight_number'] ?? 'N/A')) \\
    Route & @latexescape($ticket['full_route'] ?? 'N/A') \\
    Departure & @latexescape($departureTime) (Terminal @latexescape($ticket['segments'][0]['Origin']['Airport']['Terminal'] ?? 'N/A')) \\
    Arrival & @latexescape($arrivalTime) (Terminal @latexescape($ticket['segments'][0]['Destination']['Airport']['Terminal'] ?? 'N/A')) \\
    Duration & @latexescape($duration) \\
    Baggage & @latexescape($ticket['segments'][0]['Baggage'] ?? 'N/A') (Cabin: @latexescape($ticket['segments'][0]['CabinBaggage'] ?? 'N/A')) \\
    \bottomrule
\end{tabular}

% Passenger Details
\section*{Passenger Details}
@forelse ($ticket['passengers'] ?? [] as $passenger)
    @php
        // Combine passenger name for cleaner output
        $passengerName = trim(($passenger['Title'] ?? '') . ' ' . ($passenger['FirstName'] ?? '') . ' ' . ($passenger['LastName'] ?? ''));
    @endphp
    \begin{tabular}{p{0.45\textwidth} p{0.45\textwidth}}
        \toprule
        \textbf{Detail} & \textbf{Value} \\
        \midrule
        Name & @latexescape($passengerName ?: 'N/A') \\
        Passport Number & @latexescape($passenger['PassportNo'] ?? 'N/A') \\
        Contact Number & @latexescape($passenger['ContactNo'] ?? 'N/A') \\
        Email & @latexescape($passenger['Email'] ?? 'N/A') \\
        \bottomrule
    \end{tabular}
@empty
    \begin{tabular}{p{0.45\textwidth} p{0.45\textwidth}}
        \toprule
        \textbf{Detail} & \textbf{Value} \\
        \midrule
        Name & N/A \\
        Passport Number & N/A \\
        Contact Number & N/A \\
        Email & N/A \\
        \bottomrule
    \end{tabular}
@endforelse

% Fare Breakdown
\section*{Fare Breakdown}
@php
    // Pre-process fare data to avoid undefined index errors
    $currency = isset($ticket['passengers'][0]['Fare']['Currency']) ? $ticket['passengers'][0]['Fare']['Currency'] : 'INR';
    $baseFare = isset($ticket['passengers'][0]['Fare']['BaseFare']) ? $ticket['passengers'][0]['Fare']['BaseFare'] : '0.00';
    $tax = isset($ticket['passengers'][0]['Fare']['Tax']) ? $ticket['passengers'][0]['Fare']['Tax'] : '0.00';
    $otherCharges = isset($ticket['passengers'][0]['Fare']['OtherCharges']) ? $ticket['passengers'][0]['Fare']['OtherCharges'] : '0.00';
    $totalFare = isset($ticket['passengers'][0]['Fare']['PublishedFare']) ? $ticket['passengers'][0]['Fare']['PublishedFare'] : '0.00';
@endphp
\begin{tabular}{p{0.45\textwidth} p{0.45\textwidth}}
    \toprule
    \textbf{Item} & \textbf{Amount (@latexescape($currency))} \\
    \midrule
    Base Fare & @latexescape($baseFare) \\
    Taxes & @latexescape($tax) \\
    Other Charges & @latexescape($otherCharges) \\
    \midrule
    Total Fare & @latexescape($totalFare) \\
    \bottomrule
\end{tabular}

% Footer
\vspace{1cm}
\begin{center}
    \small For assistance, contact NextGenTrip at support@nextgentrip.com or call @latexescape($ticket['airline_toll_free_no'] ?? 'N/A'). \\
    Thank you for choosing us!
\end{center}

\end{document}