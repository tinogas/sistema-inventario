<?php
/**
 * XlsxWriter — Generador de archivos .xlsx puro PHP (sin ZipArchive, sin Composer).
 * Usa gzdeflate() + implementación manual del formato ZIP para crear el paquete OOXML.
 *
 * Características:
 *   - Filas agrupadas con OutlineLevel (grupos +/-)
 *   - Grupos CONTRAÍDOS por defecto al abrir el archivo
 *   - Estilos: cabecera gris+negrita, negrita, número 3 decimales
 *   - Múltiples hojas
 *
 * Uso:
 *   $xw = new XlsxWriter();
 *   $xw->addSheet('Stock');
 *   $xw->writeHeader(['Código', 'Producto', 'Total']);
 *   $xw->writeRow(['MUE001', 'Muelle Toyota', 8], 0, true);   // nivel 0, collapsed=true
 *   $xw->writeRow(['', 'Hermosillo', 5],           1);         // nivel 1 (detalle, oculto)
 *   $xw->download('stock.xlsx');
 */
class XlsxWriter
{
    private array $sheets   = [];
    private int   $sheetIdx = -1;

    // ── API pública ──────────────────────────────────────────────────

    public function addSheet(string $nombre): void
    {
        $this->sheets[] = ['nombre' => $nombre, 'rows' => [], 'maxLevel' => 0];
        $this->sheetIdx = count($this->sheets) - 1;
    }

    /** Fila de encabezado (negrita + fondo gris, siempre nivel 0) */
    public function writeHeader(array $cols): void
    {
        $this->sheets[$this->sheetIdx]['rows'][] = [
            'cells'     => $cols,
            'level'     => 0,
            'collapsed' => false,
            'hidden'    => false,
            'bold'      => true,
            'header'    => true,
        ];
    }

    /**
     * Fila de datos.
     * @param int  $level     0 = resumen, 1+ = detalle (contraíble)
     * @param bool $collapsed true en fila nivel 0: su grupo de nivel 1 empieza contraído
     * @param bool $bold      Negrita
     */
    public function writeRow(array $cells, int $level = 0, bool $collapsed = false, bool $bold = false): void
    {
        if ($level > $this->sheets[$this->sheetIdx]['maxLevel']) {
            $this->sheets[$this->sheetIdx]['maxLevel'] = $level;
        }
        $this->sheets[$this->sheetIdx]['rows'][] = [
            'cells'     => $cells,
            'level'     => $level,
            'collapsed' => $collapsed && $level === 0,
            'hidden'    => $level > 0,   // las filas de detalle empiezan ocultas
            'bold'      => $bold && !($level === 0 && $bold === false),
            'header'    => false,
        ];
    }

    /** Descarga el archivo xlsx */
    public function download(string $filename): void
    {
        $filename = preg_replace('/\.(xlsx?|xls)$/i', '', $filename) . '.xlsx';
        $data     = $this->buildZip();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . rawurlencode($filename) . '"');
        header('Content-Length: ' . strlen($data));
        header('Cache-Control: max-age=0');
        echo $data;
        exit;
    }

    // ── Construcción del paquete ZIP ─────────────────────────────────

    private function buildZip(): string
    {
        if (empty($this->sheets)) {
            $this->addSheet('Hoja1');
        }

        // Shared strings
        $allStrings = [];
        $strMap     = [];
        foreach ($this->sheets as &$sheet) {
            foreach ($sheet['rows'] as &$row) {
                foreach ($row['cells'] as &$cell) {
                    if (!is_numeric($cell) && $cell !== null && $cell !== '') {
                        $v = (string)$cell;
                        if (!isset($strMap[$v])) {
                            $strMap[$v]   = count($allStrings);
                            $allStrings[] = $v;
                        }
                    }
                }
            }
        }
        unset($sheet, $row, $cell);

        $n = count($this->sheets);
        $files = [];
        $files['[Content_Types].xml']        = $this->xmlContentTypes($n);
        $files['_rels/.rels']                = $this->xmlRels();
        $files['docProps/app.xml']           = $this->xmlApp();
        $files['xl/workbook.xml']            = $this->xmlWorkbook();
        $files['xl/_rels/workbook.xml.rels'] = $this->xmlWorkbookRels($n);
        $files['xl/styles.xml']              = $this->xmlStyles();
        $files['xl/sharedStrings.xml']       = $this->xmlSharedStrings($allStrings);

        foreach ($this->sheets as $i => $sheet) {
            $files["xl/worksheets/sheet{$i}.xml"] = $this->xmlSheet($sheet, $strMap);
        }

        return $this->zipFiles($files);
    }

    // ── Escritura de archivos XML del paquete OOXML ──────────────────

    private function xmlContentTypes(int $n): string
    {
        $sheets = '';
        for ($i = 0; $i < $n; $i++) {
            $sheets .= '<Override PartName="/xl/worksheets/sheet' . $i . '.xml"'
                . ' ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">'
            . '<Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>'
            . '<Default Extension="xml"  ContentType="application/xml"/>'
            . '<Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>'
            . '<Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>'
            . '<Override PartName="/xl/sharedStrings.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sharedStrings+xml"/>'
            . $sheets
            . '</Types>';
    }

    private function xmlRels(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . '<Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>'
            . '</Relationships>';
    }

    private function xmlApp(): string
    {
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Properties xmlns="http://schemas.openxmlformats.org/officeDocument/2006/extended-properties">'
            . '<Application>Inventario Taller</Application></Properties>';
    }

    private function xmlWorkbook(): string
    {
        $sheetEls = '';
        foreach ($this->sheets as $i => $s) {
            $sheetEls .= '<sheet name="' . htmlspecialchars($s['nombre'], ENT_XML1)
                . '" sheetId="' . ($i + 1) . '" r:id="rId' . ($i + 1) . '"/>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">'
            . '<bookViews><workbookView xWindow="0" yWindow="0" windowWidth="16384" windowHeight="8192"/></bookViews>'
            . '<sheets>' . $sheetEls . '</sheets></workbook>';
    }

    private function xmlWorkbookRels(int $n): string
    {
        $rels = '';
        for ($i = 0; $i < $n; $i++) {
            $rels .= '<Relationship Id="rId' . ($i + 1) . '"'
                . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet"'
                . ' Target="worksheets/sheet' . $i . '.xml"/>';
        }
        $rels .= '<Relationship Id="rId' . ($n + 1) . '"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/sharedStrings"'
            . ' Target="sharedStrings.xml"/>';
        $rels .= '<Relationship Id="rId' . ($n + 2) . '"'
            . ' Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles"'
            . ' Target="styles.xml"/>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">'
            . $rels . '</Relationships>';
    }

    private function xmlStyles(): string
    {
        // numFmts: 164 = 3 decimales, 165 = 2 decimales moneda
        $numFmts = '<numFmts count="2">'
            . '<numFmt numFmtId="164" formatCode="#,##0.000"/>'
            . '<numFmt numFmtId="165" formatCode="#,##0.00"/>'
            . '</numFmts>';
        // fonts: 0=normal, 1=bold
        $fonts = '<fonts count="2">'
            . '<font><sz val="10"/><name val="Calibri"/></font>'
            . '<font><b/><sz val="10"/><name val="Calibri"/></font>'
            . '</fonts>';
        // fills: 0=none, 1=gray125(reserved), 2=solid gray header
        $fills = '<fills count="3">'
            . '<fill><patternFill patternType="none"/></fill>'
            . '<fill><patternFill patternType="gray125"/></fill>'
            . '<fill><patternFill patternType="solid"><fgColor rgb="FFD9D9D9"/><bgColor indexed="64"/></patternFill></fill>'
            . '</fills>';
        $borders = '<borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>';
        // xfs: 0=normal 1=header(bold+gray) 2=bold 3=num3dec
        $cellStyleXfs = '<cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>';
        $cellXfs = '<cellXfs count="4">'
            . '<xf numFmtId="0"   fontId="0" fillId="0" borderId="0" xfId="0"/>'
            . '<xf numFmtId="0"   fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>'
            . '<xf numFmtId="0"   fontId="1" fillId="0" borderId="0" xfId="0" applyFont="1"/>'
            . '<xf numFmtId="164" fontId="0" fillId="0" borderId="0" xfId="0" applyNumberFormat="1"/>'
            . '</cellXfs>';
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $numFmts . $fonts . $fills . $borders . $cellStyleXfs . $cellXfs
            . '</styleSheet>';
    }

    private function xmlSharedStrings(array $strings): string
    {
        $n  = count($strings);
        $si = '';
        foreach ($strings as $s) {
            $si .= '<si><t xml:space="preserve">' . htmlspecialchars($s, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></si>';
        }
        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<sst xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"'
            . ' count="' . $n . '" uniqueCount="' . $n . '">' . $si . '</sst>';
    }

    private function xmlSheet(array $sheet, array $strMap): string
    {
        $maxLevel = (int)$sheet['maxLevel'];

        // sheetFormatPr con outlineLevelRow activa los botones +/- en filas
        $fmtPr = $maxLevel > 0
            ? '<sheetFormatPr defaultRowHeight="15" outlineLevelRow="' . $maxLevel . '"/>'
            : '<sheetFormatPr defaultRowHeight="15"/>';

        // outlinePr: summaryBelow="0" → el resumen está ARRIBA (lo que queremos)
        // showOutlineSymbols="1" → fuerza que aparezcan los botones +/-
        $sheetViews = '<sheetViews><sheetView workbookViewId="0">'
            . ($maxLevel > 0 ? '<outlinePr summaryBelow="0" summaryRight="0"/>' : '')
            . '</sheetView></sheetViews>';

        $rowsXml = '';
        foreach ($sheet['rows'] as $ri => $row) {
            $rNum      = $ri + 1;
            $level     = (int)$row['level'];
            $isHeader  = $row['header'];
            $isBold    = $row['bold'] || $isHeader;

            // Atributos de fila
            $rowAttrs  = ' r="' . $rNum . '"';
            if ($level > 0)        $rowAttrs .= ' outlineLevel="' . $level . '"';
            if ($row['hidden'])    $rowAttrs .= ' hidden="1"';
            if ($row['collapsed']) $rowAttrs .= ' collapsed="1"';

            // Estilo de celda según contexto
            $cellStyle = $isHeader ? 1 : ($isBold ? 2 : 0);

            $cellsXml = '';
            foreach ($row['cells'] as $ci => $val) {
                $col = $this->colLetter($ci);
                $ref = $col . $rNum;
                $sAttr = $cellStyle ? ' s="' . $cellStyle . '"' : '';

                if ($val === null || $val === '') {
                    $cellsXml .= '<c r="' . $ref . '"' . $sAttr . '/>';
                } elseif (is_numeric($val)) {
                    // Números: usar estilo 3 (formato 3 decimales) si no es header/bold
                    $numStyle = $isHeader || $isBold ? $cellStyle : 3;
                    $nAttr    = $numStyle ? ' s="' . $numStyle . '"' : '';
                    $cellsXml .= '<c r="' . $ref . '"' . $nAttr . '><v>' . htmlspecialchars((string)$val, ENT_XML1) . '</v></c>';
                } else {
                    // String compartido
                    $v = (string)$val;
                    if (isset($strMap[$v])) {
                        $cellsXml .= '<c r="' . $ref . '" t="s"' . $sAttr . '><v>' . $strMap[$v] . '</v></c>';
                    } else {
                        // Inline string (fallback)
                        $cellsXml .= '<c r="' . $ref . '" t="inlineStr"' . $sAttr . '><is><t>'
                            . htmlspecialchars($v, ENT_XML1 | ENT_QUOTES, 'UTF-8') . '</t></is></c>';
                    }
                }
            }
            $rowsXml .= '<row' . $rowAttrs . '>' . $cellsXml . '</row>';
        }

        return '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            . '<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            . $sheetViews
            . $fmtPr
            . '<sheetData>' . $rowsXml . '</sheetData>'
            . '</worksheet>';
    }

    // ── ZIP manual (no requiere ZipArchive) ──────────────────────────

    private function zipFiles(array $files): string
    {
        $localParts = [];
        $centralDir = '';
        $offset     = 0;

        foreach ($files as $name => $content) {
            // Comprimir con deflate raw
            $compressed = gzdeflate($content, 6);
            $crc32      = crc32($content);
            $sizeComp   = strlen($compressed);
            $sizeOrig   = strlen($content);
            $nameLen    = strlen($name);

            // Local file header (30 bytes + name + data)
            $local = pack('VvvvvvVVVvv',
                0x04034b50,  // signature
                20,          // version needed
                0,           // general purpose bit flag
                8,           // compression: deflate
                0,           // last mod time
                0,           // last mod date
                $crc32,
                $sizeComp,
                $sizeOrig,
                $nameLen,
                0            // extra field length
            ) . $name . $compressed;

            $localParts[] = $local;

            // Central directory entry (46 bytes + name)
            $centralDir .= pack('VvvvvvvVVVvvvvvVV',
                0x02014b50,  // signature
                20,          // version made by
                20,          // version needed
                0,           // general purpose bit flag
                8,           // compression: deflate
                0,           // last mod time
                0,           // last mod date
                $crc32,
                $sizeComp,
                $sizeOrig,
                $nameLen,
                0,           // extra field length
                0,           // file comment length
                0,           // disk number start
                0,           // internal file attributes
                0,           // external file attributes
                $offset      // offset of local header
            ) . $name;

            $offset += strlen($local);
        }

        $numFiles   = count($files);
        $cdSize     = strlen($centralDir);
        $localData  = implode('', $localParts);

        // End of central directory record (22 bytes)
        $eocd = pack('VvvvvVVv',
            0x06054b50,  // signature
            0,           // disk number
            0,           // disk with central dir
            $numFiles,   // entries on this disk
            $numFiles,   // total entries
            $cdSize,     // central directory size
            $offset,     // central directory offset
            0            // zip comment length
        );

        return $localData . $centralDir . $eocd;
    }

    // ── Utilidades ───────────────────────────────────────────────────

    private function colLetter(int $idx): string
    {
        $letter = '';
        $idx++;
        while ($idx > 0) {
            $rem    = ($idx - 1) % 26;
            $letter = chr(65 + $rem) . $letter;
            $idx    = (int)(($idx - 1) / 26);
        }
        return $letter;
    }
}
