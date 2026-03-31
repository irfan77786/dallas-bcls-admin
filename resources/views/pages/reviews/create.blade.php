<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Submit a Review</title>

    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- FontAwesome for star icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- jQuery Bar Rating -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/jquery-bar-rating/1.2.2/themes/fontawesome-stars.css">

    <style>
        body {
            background-color: #f8f9fa;
        }
        .container {
            max-width: 600px;
            margin-top: 50px;
        }
        .card {
            padding: 20px;
            border-radius: 10px;
        }
        .br-theme-fontawesome-stars .br-widget a {
            font-size: 1.8rem;
        }
        .logo-img {
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>
<body>
    <div class="container">
        <!-- Logo -->
        <div class="logo-img">
            <img height="50" src="https://vivamedhospital.com/wp-content/uploads/2024/08/logo.png" class="header-brand-img" title="RADMIN">
        </div>

        <div class="card shadow-sm">
            <h2 class="text-center mb-4">Submit a Review</h2>

            <!-- Success Message -->
            @if(session('success'))
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    {{ session('success') }}
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <!-- Error Messages -->
            @if ($errors->any())
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <ul class="mb-0">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            @endif

            <form action="{{ route('review.store') }}" method="POST">
                @csrf

                <!-- Employee Selection -->
                <div class="mb-3">
                    <label for="employee_id" class="form-label">Select Employee</label>
                    <select name="employee_id" id="employee_id" class="form-select" required>
                        <option value="" disabled selected>-- Select Employee --</option>
                        @foreach ($employees as $employee)
                            <option value="{{ $employee->id }}">{{ $employee->name }} - {{ $employee->position }}</option>
                        @endforeach
                    </select>
                </div>

                <!-- Star Rating -->
                <div class="mb-3">
                    <label for="rating" class="form-label">Rating</label>
                    <select id="rating" name="rating" class="form-select" required>
                        <option value="1">1 - Poor</option>
                        <option value="2">2 - Fair</option>
                        <option value="3">3 - Good</option>
                        <option value="4">4 - Very Good</option>
                        <option value="5">5 - Excellent</option>
                    </select>
                </div>

                <!-- Comment Box -->
                <div class="mb-3">
                    <label for="comment" class="form-label">Comment (Optional)</label>
                    <textarea name="comment" id="comment" class="form-control" rows="4" maxlength="500"></textarea>
                </div>

                <!-- Submit Button -->
                <button type="submit" class="btn btn-primary w-100">Submit Review</button>
            </form>
        </div>
    </div>

    <!-- Bootstrap & jQuery Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery-bar-rating/1.2.2/jquery.barrating.min.js"></script>

    <script>
        $(document).ready(function() {
            $('#rating').barrating({
                theme: 'fontawesome-stars',
                showSelectedRating: true
            });
        });
    </script>
</body>
</html>
