<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title inertia>{{ config('app.name', 'Laravel') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />
        <!-- Include stylesheet -->
        <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.2.3/dist/css/bootstrap.min.css" rel="stylesheet">
        <style type="text/css">
        	*{
        		font-family: "Arial";
        	}
        	table, th, td {
			  border: 1px solid black;
			  padding:5px;
			}
        </style>
    </head>
    <body class="font-sans antialiased container">
		<table class="table table-bordered">
			<thead>
				<th scope="col">Item #</th>
				<th scope="col">Item Name</th>
				<th scope="col">MSRP</th>
				<th scope="col">Quantity</th>
				<th scope="col">$ Total</th>
			</thead>
			<tbody>
				@foreach($data as $d)
					<tr>
						<td>{{$d['item']}}</td>
						<td>{{$d['description']}}</td>
						<td>{{$d['msrp']}}</td>
						<td>{{$d['quantity']}}</td>
						<td>{{$d['totalMsrp']}}</td>
					</tr>
				@endforeach
			</tbody>
		</table>
	</body>
</html>

