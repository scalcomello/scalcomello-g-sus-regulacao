<?php
require '../../vendor/autoload.php';
require '../../includes/dbconnect.php';

use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;
use PhpOffice\PhpSpreadsheet\Style\Font;
use PhpOffice\PhpSpreadsheet\Cell\DataType;

if (!isset($_POST['ids']) || empty($_POST['ids'])) {
    die("Nenhum registro selecionado.");
}

$ids = implode(",", array_map('intval', $_POST['ids']));

$spreadsheet = new Spreadsheet();
$sheet = $spreadsheet->getActiveSheet();

// Definindo os cabeçalhos das colunas
$sheet->setCellValue('A1', 'Horário');
$sheet->setCellValue('B1', 'Nome');
$sheet->setCellValue('C1', 'Data de Nascimento');
$sheet->setCellValue('D1', 'Idade');
$sheet->setCellValue('E1', 'CNS');
$sheet->setCellValue('F1', 'Procedimento');
$sheet->setCellValue('G1', 'Ultrassonografia'); // Alteração do título da coluna

// Aplicando estilo em negrito para os cabeçalhos
$sheet->getStyle('A1:G1')->applyFromArray([
    'font' => ['bold' => true],
    'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
    'borders' => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
    'fill' => [
        'fillType' => Fill::FILL_SOLID,
        'startColor' => ['rgb' => 'D9EAD3']
    ]
]);

// Consulta SQL para buscar os registros selecionados
$sql = "SELECT s.hora_agendamento, c.no_cidadao, c.dt_nascimento, c.nu_cns, 
               p.procedimento, s.tipo_procedimento,
               TIMESTAMPDIFF(YEAR, c.dt_nascimento, CURDATE()) AS idade
        FROM solicitacao s
        JOIN tb_cidadao c ON s.cidadao_id = c.id_cidadao
        JOIN procedimento p ON s.procedimento_id = p.idProcedimento
        WHERE s.idSolicitacao IN ($ids)";

$result = $conn->query($sql);
$rowIndex = 2;

while ($row = $result->fetch_assoc()) {
    // Preenchendo as células com os dados
    $sheet->setCellValue('A' . $rowIndex, $row['hora_agendamento']);
    $sheet->setCellValue('B' . $rowIndex, $row['no_cidadao']);
    $sheet->setCellValue('C' . $rowIndex, date('d/m/Y', strtotime($row['dt_nascimento'])));
    $sheet->setCellValue('D' . $rowIndex, $row['idade']); // Coluna de idade calculada
    $sheet->setCellValueExplicit('E' . $rowIndex, $row['nu_cns'], DataType::TYPE_STRING); // CNS como string para evitar notação científica
    $sheet->setCellValue('F' . $rowIndex, $row['procedimento']);
    $sheet->setCellValue('G' . $rowIndex, $row['tipo_procedimento']);
    $rowIndex++;
}

// Definindo largura automática para as colunas
foreach (range('A', 'G') as $col) {
    $sheet->getColumnDimension($col)->setAutoSize(true);
}

// Configurações de download do arquivo Excel
header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
header('Content-Disposition: attachment; filename="dados_agendados.xlsx"');

$writer = new Xlsx($spreadsheet);
$writer->save('php://output');
exit;
?>
