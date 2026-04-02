<?php

namespace App\Support;

use Illuminate\Support\Arr;

class ExcelWorkbookBuilder
{
    public function build(string $workbookTitle, array $sheets): string
    {
        $createdAt = now()->format('Y-m-d\TH:i:s\Z');
        $worksheetsXml = collect($sheets)
            ->map(fn (array $sheet) => $this->renderWorksheet($sheet))
            ->implode('');

        return <<<XML
<?xml version="1.0" encoding="UTF-8"?>
<?mso-application progid="Excel.Sheet"?>
<Workbook xmlns="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:o="urn:schemas-microsoft-com:office:office"
 xmlns:x="urn:schemas-microsoft-com:office:excel"
 xmlns:ss="urn:schemas-microsoft-com:office:spreadsheet"
 xmlns:html="http://www.w3.org/TR/REC-html40">
 <DocumentProperties xmlns="urn:schemas-microsoft-com:office:office">
  <Author>{$this->escape('Global Voice CRM')}</Author>
  <LastAuthor>{$this->escape('Global Voice CRM')}</LastAuthor>
  <Created>{$this->escape($createdAt)}</Created>
  <Company>{$this->escape('Global Voice')}</Company>
  <Version>16.00</Version>
  <Title>{$this->escape($workbookTitle)}</Title>
 </DocumentProperties>
 <ExcelWorkbook xmlns="urn:schemas-microsoft-com:office:excel">
  <WindowHeight>12540</WindowHeight>
  <WindowWidth>22455</WindowWidth>
  <ProtectStructure>False</ProtectStructure>
  <ProtectWindows>False</ProtectWindows>
 </ExcelWorkbook>
 {$this->renderStyles()}
 {$worksheetsXml}
</Workbook>
XML;
    }

    private function renderStyles(): string
    {
        return <<<XML
<Styles>
 <Style ss:ID="Default" ss:Name="Normal">
  <Alignment ss:Vertical="Top"/>
  <Borders/>
  <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#0F172A"/>
  <Interior/>
  <NumberFormat/>
  <Protection/>
 </Style>
 <Style ss:ID="title">
  <Alignment ss:Horizontal="Left" ss:Vertical="Center"/>
  <Font ss:FontName="Calibri" ss:Size="14" ss:Bold="1" ss:Color="#FFFFFF"/>
  <Interior ss:Color="#0F3D62" ss:Pattern="Solid"/>
 </Style>
 <Style ss:ID="meta">
  <Alignment ss:Vertical="Center"/>
  <Font ss:FontName="Calibri" ss:Size="10" ss:Color="#475569"/>
  <Interior ss:Color="#E2E8F0" ss:Pattern="Solid"/>
 </Style>
 <Style ss:ID="header">
  <Alignment ss:Horizontal="Center" ss:Vertical="Center" ss:WrapText="1"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#CBD5E1"/>
  </Borders>
  <Font ss:FontName="Calibri" ss:Size="10" ss:Bold="1" ss:Color="#0F172A"/>
  <Interior ss:Color="#DCEAF7" ss:Pattern="Solid"/>
 </Style>
 <Style ss:ID="cell">
  <Alignment ss:Vertical="Top"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
  </Borders>
 </Style>
 <Style ss:ID="wrap">
  <Alignment ss:Vertical="Top" ss:WrapText="1"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
  </Borders>
 </Style>
 <Style ss:ID="integer">
  <Alignment ss:Horizontal="Right" ss:Vertical="Top"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
  </Borders>
  <NumberFormat ss:Format="0"/>
 </Style>
 <Style ss:ID="money">
  <Alignment ss:Horizontal="Right" ss:Vertical="Top"/>
  <Borders>
   <Border ss:Position="Bottom" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Left" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Right" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
   <Border ss:Position="Top" ss:LineStyle="Continuous" ss:Weight="1" ss:Color="#E2E8F0"/>
  </Borders>
  <NumberFormat ss:Format="#,##0.00"/>
 </Style>
</Styles>
XML;
    }

    private function renderWorksheet(array $sheet): string
    {
        $name = $this->sanitizeSheetName((string) Arr::get($sheet, 'name', 'Sheet'));
        $title = (string) Arr::get($sheet, 'title', $name);
        $subtitle = (string) Arr::get($sheet, 'subtitle', '');
        $headings = Arr::get($sheet, 'headings', []);
        $columns = Arr::get($sheet, 'columns', []);
        $rows = Arr::get($sheet, 'rows', []);

        $headingCount = max(count($headings), 1);
        $columnXml = collect($columns)
            ->map(fn ($width) => '<Column ss:AutoFitWidth="0" ss:Width="' . (float) $width . '"/>')
            ->implode('');

        $titleRow = $this->renderMergedRow($title, 'title', $headingCount);
        $subtitleRow = $this->renderMergedRow($subtitle ?: 'Generatsiya qilingan sana: ' . now()->format('Y-m-d H:i:s'), 'meta', $headingCount);
        $headingRow = $this->renderHeadingRow($headings);
        $bodyRows = collect($rows)->map(fn ($row) => $this->renderDataRow($row))->implode('');
        $totalRows = count($rows) + 3;
        $autoFilter = '<AutoFilter x:Range="R3C1:R' . max($totalRows, 3) . 'C' . $headingCount . '" xmlns="urn:schemas-microsoft-com:office:excel"/>';

        return <<<XML
<Worksheet ss:Name="{$this->escape($name)}">
 <Table ss:ExpandedColumnCount="{$headingCount}" ss:ExpandedRowCount="{$totalRows}">
  {$columnXml}
  {$titleRow}
  {$subtitleRow}
  {$headingRow}
  {$bodyRows}
 </Table>
 {$autoFilter}
 <WorksheetOptions xmlns="urn:schemas-microsoft-com:office:excel">
  <FreezePanes/>
  <FrozenNoSplit/>
  <SplitHorizontal>3</SplitHorizontal>
  <TopRowBottomPane>3</TopRowBottomPane>
  <ActivePane>2</ActivePane>
  <ProtectObjects>False</ProtectObjects>
  <ProtectScenarios>False</ProtectScenarios>
 </WorksheetOptions>
</Worksheet>
XML;
    }

    private function renderMergedRow(string $value, string $styleId, int $columnCount): string
    {
        $mergeAcross = max($columnCount - 1, 0);

        return '<Row ss:AutoFitHeight="0" ss:Height="24"><Cell ss:StyleID="' . $styleId . '" ss:MergeAcross="' . $mergeAcross . '"><Data ss:Type="String">' . $this->escape($value) . '</Data></Cell></Row>';
    }

    private function renderHeadingRow(array $headings): string
    {
        $cells = collect($headings)
            ->map(fn ($heading) => '<Cell ss:StyleID="header"><Data ss:Type="String">' . $this->escape((string) $heading) . '</Data></Cell>')
            ->implode('');

        return '<Row ss:AutoFitHeight="1">' . $cells . '</Row>';
    }

    private function renderDataRow(array $row): string
    {
        $cells = collect($row)->map(fn ($cell) => $this->renderCell($cell))->implode('');

        return '<Row ss:AutoFitHeight="1">' . $cells . '</Row>';
    }

    private function renderCell(mixed $cell): string
    {
        if (! is_array($cell) || ! array_key_exists('value', $cell)) {
            $cell = [
                'value' => $cell,
                'type' => is_int($cell) || is_float($cell) ? 'Number' : 'String',
                'style' => is_int($cell) ? 'integer' : (is_float($cell) ? 'money' : 'cell'),
            ];
        }

        $value = $cell['value'] ?? '';
        $type = $cell['type'] ?? 'String';
        $style = $cell['style'] ?? 'cell';

        if ($type === 'Number' && ($value === '' || $value === null)) {
            $type = 'String';
            $value = '';
            $style = 'cell';
        }

        return '<Cell ss:StyleID="' . $this->escape($style) . '"><Data ss:Type="' . $this->escape($type) . '">' . $this->escape((string) $value) . '</Data></Cell>';
    }

    private function sanitizeSheetName(string $value): string
    {
        $value = preg_replace('/[\\\\\\/?*\\[\\]:]/', ' ', $value) ?: 'Sheet';
        $value = trim($value);

        return mb_substr($value !== '' ? $value : 'Sheet', 0, 31);
    }

    private function escape(string $value): string
    {
        $value = preg_replace('/[^\P{C}\n\r\t]+/u', '', $value) ?? '';

        return htmlspecialchars($value, ENT_QUOTES | ENT_XML1, 'UTF-8');
    }
}
