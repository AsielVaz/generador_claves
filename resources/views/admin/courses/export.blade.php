<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Inscritos {{ $course->title }}</title>
</head>
<body>
    <table border="1">
        <thead>
            <tr>
                <th colspan="7">Reporte de inscritos - {{ $course->title }}</th>
            </tr>
            <tr>
                <th>Alumno</th>
                <th>Correo</th>
                <th>Fecha inscripcion</th>
                <th>Costo curso</th>
                <th>Pagado</th>
                <th>Pendiente</th>
                <th>Estado</th>
            </tr>
        </thead>
        <tbody>
            @forelse ($rows as $row)
                <tr>
                    <td>{{ $row['user']->name }}</td>
                    <td>{{ $row['user']->email }}</td>
                    <td>{{ $row['enrolled_at'] ? \Illuminate\Support\Carbon::parse($row['enrolled_at'])->format('d/m/Y H:i') : 'Sin fecha' }}</td>
                    <td>{{ number_format($row['course_cost'], 2, '.', '') }}</td>
                    <td>{{ number_format($row['paid_total'], 2, '.', '') }}</td>
                    <td>{{ number_format($row['remaining'], 2, '.', '') }}</td>
                    <td>{{ $row['status'] }}</td>
                </tr>
            @empty
                <tr>
                    <td colspan="7">Este curso aun no tiene alumnos inscritos.</td>
                </tr>
            @endforelse
        </tbody>
    </table>
</body>
</html>
