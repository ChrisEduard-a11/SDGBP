<?php
    // Incluir el archivo PHP que recupera los datos
    include('../acciones/get_data.php');
    ?>
    <script>
        var ctx = document.getElementById('attendanceChart').getContext('2d');
        var attendanceChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: ['Asistio', 'Llego Tarde', 'No Asistio'],
                datasets: [{
                    label: 'Asistencia',
                    data: [
                        attendanceData['Asistio'] || 0,
                        attendanceData['Llego Tarde'] || 0,
                        attendanceData['No Asistio'] || 0
                    ],
                    backgroundColor: [
                        'rgba(75, 192, 192, 0.2)',
                        'rgba(255, 206, 86, 0.2)',
                        'rgba(255, 99, 132, 0.2)'
                    ],
                    borderColor: [
                        'rgba(75, 192, 192, 1)',
                        'rgba(255, 206, 86, 1)',
                        'rgba(255, 99, 132, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                scales: {
                    y: {
                        beginAtZero: true
                    }
                }
            }
        });
    </script>
   