<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Tabel</title>
</head>

<body>
    <h1>Data Tabel</h1>
    <table border="1">
        <tr>
            <th>empno</th>
            <th>datin</th>
            <th>timin</th>
            <th>datot</th>
            <th>timot</th>
        </tr>
        @foreach($data as $row)
        <tr>
            <td>{{ $row->empno }}</td>
            <td>{{ $row->datin }}</td>
            <td>{{ $row->timin }}</td>
            <td>{{ $row->datot }}</td>
            <td>{{ $row->timot }}</td>
        </tr>
        @endforeach
    </table>
</body>

</html>