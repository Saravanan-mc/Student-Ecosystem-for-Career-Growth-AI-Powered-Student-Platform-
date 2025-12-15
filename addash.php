
<?php
include 'admin.php';
?>



<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Student Performance Report</title>
    <!-- Tailwind CSS CDN -->
    <script src="https://cdn.tailwindcss.com"></script>
    <!-- Google Fonts - Inter -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">
    <!-- Font Awesome for Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css">
    <style>
        /* Custom styles for Inter font */
        body {
            font-family: 'Inter', sans-serif;
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
            overflow-x: hidden; /* Prevent horizontal scroll due to animations */
        }

        /* Custom Keyframe Animations */
        @keyframes fadeInScale {
            from {
                opacity: 0;
                transform: scale(0.95) translateY(20px);
            }
            to {
                opacity: 1;
                transform: scale(1) translateY(0);
            }
        }

        @keyframes slideInDownBounce {
            from {
                opacity: 0;
                transform: translateY(-50px);
            }
            60% {
                opacity: 1;
                transform: translateY(10px);
            }
            100% {
                transform: translateY(0);
            }
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @keyframes backgroundPan {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        @keyframes rotateIn {
            from {
                opacity: 0;
                transform: rotate(-90deg) scale(0.5);
            }
            to {
                opacity: 1;
                transform: rotate(0deg) scale(1);
            }
        }

        @keyframes popIn {
            from {
                opacity: 0;
                transform: scale(0.5);
            }
            to {
                opacity: 1;
                transform: scale(1);
            }
        }

        /* Apply animations */
        .animate-fadeInScale {
            animation: fadeInScale 0.8s ease-out forwards;
        }

        .animate-slideInDownBounce {
            animation: slideInDownBounce 1s ease-out forwards;
        }

        .animate-fadeInUp {
            animation: fadeInUp 0.7s ease-out forwards;
        }

        .animate-backgroundPan {
            background-size: 200% 200%;
            animation: backgroundPan 15s linear infinite;
        }

        .animate-rotateIn {
            animation: rotateIn 0.6s ease-out forwards;
        }

        .animate-popIn {
            animation: popIn 0.5s cubic-bezier(0.68, -0.55, 0.27, 1.55) forwards; /* Bounce effect */
        }

        /* Delay for sequential animations */
        .animate-delay-100 { animation-delay: 0.1s; }
        .animate-delay-200 { animation-delay: 0.2s; }
        .animate-delay-300 { animation-delay: 0.3s; }
        .animate-delay-400 { animation-delay: 0.4s; }
        .animate-delay-500 { animation-delay: 0.5s; }
        .animate-delay-600 { animation-delay: 0.6s; }
        .animate-delay-700 { animation-delay: 0.7s; }
        .animate-delay-800 { animation-delay: 0.8s; }
        .animate-delay-900 { animation-delay: 0.9s; }
        .animate-delay-1000 { animation-delay: 1s; }
        .animate-delay-1100 { animation-delay: 1.1s; }
        .animate-delay-1200 { animation-delay: 1.2s; }
        .animate-delay-1300 { animation-delay: 1.3s; }
        .animate-delay-1400 { animation-delay: 1.4s; }

        /* Custom hover effects for links */
        .link-hover-effect {
            position: relative;
            display: inline-flex; /* Use flex to align icon and text */
            align-items: center;
            overflow: hidden;
        }
        .link-hover-effect::after {
            content: '';
            position: absolute;
            bottom: 0;
            left: 0;
            width: 100%;
            height: 2px;
            background-color: currentColor; /* Matches text color */
            transform: translateX(-100%);
            transition: transform 0.3s ease-out;
        }
        .link-hover-effect:hover::after {
            transform: translateX(0);
        }

        #rr{
            margin-left:300px;
        }
    </style>
</head>
<body id="rr"  class="bg-gradient-to-br from-green-100 via-lime-100 to-emerald-100 min-h-screen flex items-center justify-center p-4 sm:p-6 lg:p-8 animate-backgroundPan">
    <div class="bg-white p-6 sm:p-8 md:p-10 rounded-3xl shadow-2xl w-full max-w-4xl border border-gray-200 transform transition-all duration-300 ease-in-out hover:scale-[1.01] hover:shadow-3xl animate-fadeInScale">
        <h1 class="text-4xl font-extrabold text-gray-900 mb-8 text-center leading-tight animate-slideInDownBounce">
            <i class="fas fa-graduation-cap text-emerald-600 mr-4 animate-rotateIn animate-delay-300"></i>
            Student Performance Report
            <i class="fas fa-chart-line text-emerald-600 ml-4 animate-rotateIn animate-delay-300"></i>
        </h1>

        <div class="overflow-x-auto rounded-2xl border border-gray-200 shadow-lg animate-fadeIn animate-delay-700">
            <table class="min-w-full bg-white">
                <thead class="bg-gradient-to-r from-green-700 to-emerald-800 text-white shadow-md">
                    <tr>
                        <th class="py-4 px-6 text-left text-sm font-semibold uppercase tracking-wider rounded-tl-2xl">Student Name</th>
                        <th class="py-4 px-6 text-left text-sm font-semibold uppercase tracking-wider">Roll Number</th>
                        <th class="py-4 px-6 text-left text-sm font-semibold uppercase tracking-wider">User ID</th>
                        <th class="py-4 px-6 text-left text-sm font-semibold uppercase tracking-wider rounded-tr-2xl">Data Link</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-100">
                    <!-- Saravanan M's Row -->
                    <tr class="hover:bg-green-50 transition duration-200 ease-in-out hover:shadow-md hover:scale-[1.01] transform origin-center animate-fadeInUp animate-delay-800">
                        <td class="py-4 px-6 whitespace-nowrap text-base text-gray-800">
                            <a href="#student-details-saravanan" class="text-emerald-700 hover:text-emerald-900 font-medium transition duration-200 ease-in-out link-hover-effect">Saravanan M</a>
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-base text-gray-700">7376221ec294</td>
                        <td class="py-4 px-6 whitespace-nowrap text-base text-gray-700">
                            <a href="student-details.html?userId=1" class="text-emerald-700 hover:text-emerald-900 font-medium transition duration-200 ease-in-out link-hover-effect">1</a>
                        </td>
                        <td class="py-4 px-6 whitespace-nowrap text-base text-gray-700">
                            <a href="addatareport.php" class="text-emerald-700 hover:text-emerald-900 font-medium transition duration-200 ease-in-out link-hover-effect">
                                View Data <i class="fas fa-external-link-alt text-sm ml-2 animate-popIn animate-delay-1000"></i>
                            </a>
                        </td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</body>
</html>
