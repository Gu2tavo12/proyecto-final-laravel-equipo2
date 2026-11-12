<?php

namespace App\Http;

final class ReportePdf
{
    private const INICIO_CONTENIDO = 736;

    private const LIMITE_INFERIOR = 62;

    /** @param list<array{string, string, iterable<array{string, int}>, 3?: string}> $secciones */
    public function generar(array $secciones): string
    {
        $encabezado = "0.11 0.22 0.37 rg\n"
            .$this->texto('Reporte de empleo y pasantías', 48, 782, 19, 'F2')
            .$this->texto('Caso 7', 48, 761);
        $paginas = [];
        $pagina = $encabezado;
        $altura = self::INICIO_CONTENIDO;

        foreach ($secciones as $seccion) {
            [$titulo, $columna, $filas] = $seccion;

            if ($altura < self::LIMITE_INFERIOR + 100) {
                $paginas[] = $pagina;
                $pagina = $encabezado;
                $altura = self::INICIO_CONTENIDO;
            }

            $pagina .= $this->encabezadoSeccion($titulo, $columna, $altura);
            $altura -= 26;
            $sinFilas = true;

            foreach ($filas as [$etiqueta, $valor]) {
                $sinFilas = false;
                $lineas = explode("\n", wordwrap($this->codificar($etiqueta), 68, "\n", true));
                $altoFila = count($lineas) * 14 + 4;

                if ($altura - $altoFila < self::LIMITE_INFERIOR) {
                    $paginas[] = $pagina;
                    $pagina = $encabezado.$this->encabezadoSeccion($titulo, $columna, self::INICIO_CONTENIDO);
                    $altura = self::INICIO_CONTENIDO - 26;
                }

                $pagina .= $this->numero((string) $valor, $altura);

                foreach ($lineas as $linea) {
                    $pagina .= $this->textoCodificado($linea, 48, $altura, 10, 'F1');
                    $altura -= 14;
                }

                $altura -= 4;
            }

            if ($sinFilas) {
                $pagina .= $this->texto($seccion[3] ?? '', 48, $altura);
                $altura -= 18;
            }

            $altura -= 24;
        }

        $paginas[] = $pagina;

        return $this->documento($paginas);
    }

    private function encabezadoSeccion(string $titulo, string $columna, int $altura): string
    {
        $borde = $altura - 9;

        return $this->texto($titulo, 48, $altura, 13, 'F2')
            .$this->numero($columna, $altura)
            ."0.76 0.80 0.85 RG 48 $borde m 547 $borde l S\n";
    }

    private function numero(string $texto, int $altura): string
    {
        return $this->texto($texto, 547 - strlen($texto) * 6, $altura);
    }

    private function texto(string $texto, int $x, int $y, int $tamano = 10, string $fuente = 'F1'): string
    {
        return $this->textoCodificado($this->codificar($texto), $x, $y, $tamano, $fuente);
    }

    private function codificar(string $texto): string
    {
        $texto = preg_replace('/[\p{Z}\p{Cc}]+/u', ' ', $texto) ?? '';

        return mb_convert_encoding(trim($texto), 'Windows-1252', 'UTF-8');
    }

    private function textoCodificado(string $texto, int $x, int $y, int $tamano, string $fuente): string
    {
        $literal = strtr($texto, ['\\' => '\\\\', '(' => '\\(', ')' => '\\)']);

        return "BT /$fuente $tamano Tf $x $y Td ($literal) Tj ET\n";
    }

    /** @param non-empty-list<string> $paginas */
    private function documento(array $paginas): string
    {
        $objetos = [
            1 => '<< /Type /Catalog /Pages 2 0 R >>',
            2 => '',
            3 => '<< /Type /Font /Subtype /Type1 /BaseFont /Courier /Encoding /WinAnsiEncoding >>',
            4 => '<< /Type /Font /Subtype /Type1 /BaseFont /Helvetica-Bold /Encoding /WinAnsiEncoding >>',
        ];
        $referencias = [];

        foreach ($paginas as $indice => $pagina) {
            $pagina .= $this->texto('Página '.($indice + 1).' de '.count($paginas), 48, 35);
            $objetoPagina = count($objetos) + 1;
            $objetoContenido = $objetoPagina + 1;
            $referencias[] = "$objetoPagina 0 R";
            $objetos[$objetoPagina] = '<< /Type /Page /Parent 2 0 R /MediaBox [0 0 595 842] '
                ."/Resources << /Font << /F1 3 0 R /F2 4 0 R >> >> /Contents $objetoContenido 0 R >>";
            $objetos[$objetoContenido] = '<< /Length '.strlen($pagina)." >>\nstream\n".$pagina.'endstream';
        }

        $objetos[2] = '<< /Type /Pages /Kids ['.implode(' ', $referencias).'] /Count '.count($paginas).' >>';
        $pdf = "%PDF-1.4\n%\xE2\xE3\xCF\xD3\n";
        $cantidad = count($objetos) + 1;
        $tabla = "xref\n0 $cantidad\n0000000000 65535 f \n";

        foreach ($objetos as $numero => $objeto) {
            $tabla .= sprintf("%010d 00000 n \n", strlen($pdf));
            $pdf .= "$numero 0 obj\n$objeto\nendobj\n";
        }

        return $pdf.$tabla."trailer\n<< /Size $cantidad /Root 1 0 R >>\nstartxref\n".strlen($pdf)."\n%%EOF\n";
    }
}
