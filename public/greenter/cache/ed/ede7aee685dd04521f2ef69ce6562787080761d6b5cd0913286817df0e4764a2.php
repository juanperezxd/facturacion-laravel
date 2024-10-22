<?php

use Twig\Environment;
use Twig\Error\LoaderError;
use Twig\Error\RuntimeError;
use Twig\Extension\SandboxExtension;
use Twig\Markup;
use Twig\Sandbox\SecurityError;
use Twig\Sandbox\SecurityNotAllowedTagError;
use Twig\Sandbox\SecurityNotAllowedFilterError;
use Twig\Sandbox\SecurityNotAllowedFunctionError;
use Twig\Source;
use Twig\Template;

/* invoice.html.twig */
class __TwigTemplate_84c37c237887af8260ac847744ef0bab56ca694d17f753fc21eeef182edaab10 extends Template
{
    private $source;
    private $macros = [];

    public function __construct(Environment $env)
    {
        parent::__construct($env);

        $this->source = $this->getSourceContext();

        $this->parent = false;

        $this->blocks = [
        ];
    }

    protected function doDisplay(array $context, array $blocks = [])
    {
        $macros = $this->macros;
        // line 1
        echo "<html>
<head>
    <meta http-equiv=\"Content-Type\" content=\"text/html; charset=UTF-8\">
    <style type=\"text/css\">
        ";
        // line 5
        $this->loadTemplate("assets/style.css", "invoice.html.twig", 5)->display($context);
        // line 6
        echo "    </style>
</head>
<body class=\"white-bg\">
";
        // line 9
        $context["cp"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 9, $this->source); })()), "company", [], "any", false, false, false, 9);
        // line 10
        $context["isNota"] = twig_in_filter(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 10, $this->source); })()), "tipoDoc", [], "any", false, false, false, 10), [0 => "07", 1 => "08"]);
        // line 11
        $context["isAnticipo"] = (twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "totalAnticipos", [], "any", true, true, false, 11) && (1 === twig_compare(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 11, $this->source); })()), "totalAnticipos", [], "any", false, false, false, 11), 0)));
        // line 12
        $context["name"] = $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 12, $this->source); })()), "tipoDoc", [], "any", false, false, false, 12), "01");
        // line 13
        echo "<table width=\"100%\" style=\"page-break-inside:avoid;page-break-after: avoid;\">
    <tbody><tr>
        <td style=\"padding:30px; !important\">
            <table width=\"100%\" height=\"200px\" border=\"0\" aling=\"center\" cellpadding=\"0\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                <tbody><tr>
                    <td width=\"50%\" height=\"90\" align=\"center\">
                        <span><img src=\"";
        // line 19
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\ImageFilter')->toBase64(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 19, $this->source); })()), "system", [], "any", false, false, false, 19), "logo", [], "any", false, false, false, 19)), "html", null, true);
        echo "\" height=\"80\" style=\"text-align:center\" border=\"0\"></span>
                    </td>
                    <td width=\"5%\" height=\"40\" align=\"center\"></td>
                    <td width=\"45%\" rowspan=\"2\" valign=\"bottom\" style=\"padding-left:0\">
                        <div class=\"tabla_borde\">
                            <table width=\"100%\" border=\"0\" height=\"200\" cellpadding=\"6\" cellspacing=\"0\">
                                <tbody><tr>
                                    <td align=\"center\">
                                        <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:29px\" text-align=\"center\">";
        // line 27
        echo twig_escape_filter($this->env, (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 27, $this->source); })()), "html", null, true);
        echo "</span>
                                        <br>
                                        <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:19px\" text-align=\"center\">E L E C T R Ó N I C A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <span style=\"font-size:15px\" text-align=\"center\">R.U.C.: ";
        // line 34
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 34, $this->source); })()), "ruc", [], "any", false, false, false, 34), "html", null, true);
        echo "</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <span style=\"font-size:24px\">";
        // line 39
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 39, $this->source); })()), "serie", [], "any", false, false, false, 39), "html", null, true);
        echo "-";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 39, $this->source); })()), "correlativo", [], "any", false, false, false, 39), "html", null, true);
        echo "</span>
                                    </td>
                                </tr>
                                </tbody></table>
                        </div>
                    </td>
                </tr>
                <tr>
                    <td valign=\"bottom\" style=\"padding-left:0\">
                        <div class=\"tabla_borde\">
                            <table width=\"96%\" height=\"100%\" border=\"0\" border-radius=\"\" cellpadding=\"9\" cellspacing=\"0\">
                                <tbody><tr>
                                    <td align=\"center\">
                                        <strong><span style=\"font-size:15px\">";
        // line 52
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 52, $this->source); })()), "razonSocial", [], "any", false, false, false, 52), "html", null, true);
        echo "</span></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\">
                                        <strong>Dirección: </strong>";
        // line 57
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 57, $this->source); })()), "address", [], "any", false, false, false, 57), "direccion", [], "any", false, false, false, 57), "html", null, true);
        echo "
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\">
                                        ";
        // line 62
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 62, $this->source); })()), "user", [], "any", false, false, false, 62), "header", [], "any", false, false, false, 62);
        echo "
                                    </td>
                                </tr>
                                </tbody></table>
                        </div>
                    </td>
                </tr>
                </tbody></table>
            <div class=\"tabla_borde\">
                ";
        // line 71
        $context["cl"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 71, $this->source); })()), "client", [], "any", false, false, false, 71);
        // line 72
        echo "                <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;font-size:10px !important;\">
                    <tbody><tr>
                        <td width=\"60%\" align=\"left\"><strong>Razón Social:</strong>  ";
        // line 74
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 74, $this->source); })()), "rznSocial", [], "any", false, false, false, 74), "html", null, true);
        echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>";
        // line 75
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 75, $this->source); })()), "tipoDoc", [], "any", false, false, false, 75), "06"), "html", null, true);
        echo ":</strong>  ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 75, $this->source); })()), "numDoc", [], "any", false, false, false, 75), "html", null, true);
        echo "</td>
                    </tr>
                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Dirección: </strong>  ";
        // line 78
        if (twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 78, $this->source); })()), "address", [], "any", false, false, false, 78)) {
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 78, $this->source); })()), "address", [], "any", false, false, false, 78), "direccion", [], "any", false, false, false, 78), "html", null, true);
        }
        echo "</td>
                        <td width=\"40%\" align=\"left\">
                            <strong>Fecha Emisión: </strong>  ";
        // line 80
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 80, $this->source); })()), "fechaEmision", [], "any", false, false, false, 80), "d/m/Y"), "html", null, true);
        echo "
                            ";
        // line 81
        if ((0 !== twig_compare(twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 81, $this->source); })()), "fechaEmision", [], "any", false, false, false, 81), "H:i:s"), "00:00:00"))) {
            echo " ";
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 81, $this->source); })()), "fechaEmision", [], "any", false, false, false, 81), "H:i:s"), "html", null, true);
            echo " ";
        }
        // line 82
        echo "                            ";
        if ((twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "fecVencimiento", [], "any", true, true, false, 82) && twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 82, $this->source); })()), "fecVencimiento", [], "any", false, false, false, 82))) {
            // line 83
            echo "                            <br><br><strong>Fecha Vencimiento: </strong>  ";
            echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 83, $this->source); })()), "fecVencimiento", [], "any", false, false, false, 83), "d/m/Y"), "html", null, true);
            echo "
                            ";
        }
        // line 85
        echo "                        </td>
                    </tr>
                    ";
        // line 87
        if ((isset($context["isNota"]) || array_key_exists("isNota", $context) ? $context["isNota"] : (function () { throw new RuntimeError('Variable "isNota" does not exist.', 87, $this->source); })())) {
            // line 88
            echo "                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Tipo Doc. Ref.: </strong>  ";
            // line 89
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 89, $this->source); })()), "tipDocAfectado", [], "any", false, false, false, 89), "01"), "html", null, true);
            echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>Documento Ref.: </strong>  ";
            // line 90
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 90, $this->source); })()), "numDocfectado", [], "any", false, false, false, 90), "html", null, true);
            echo "</td>
                    </tr>
                    ";
        }
        // line 93
        echo "                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Tipo Moneda: </strong>  ";
        // line 94
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 94, $this->source); })()), "tipoMoneda", [], "any", false, false, false, 94), "021"), "html", null, true);
        echo " </td>
                        <td width=\"40%\" align=\"left\">";
        // line 95
        if ((twig_get_attribute($this->env, $this->source, ($context["doc"] ?? null), "compra", [], "any", true, true, false, 95) && twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 95, $this->source); })()), "compra", [], "any", false, false, false, 95))) {
            echo "<strong>O/C: </strong>  ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 95, $this->source); })()), "compra", [], "any", false, false, false, 95), "html", null, true);
        }
        echo "</td>
                    </tr>
                    ";
        // line 97
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 97, $this->source); })()), "guias", [], "any", false, false, false, 97)) {
            // line 98
            echo "                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Guias: </strong>
                        ";
            // line 100
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 100, $this->source); })()), "guias", [], "any", false, false, false, 100));
            foreach ($context['_seq'] as $context["_key"] => $context["guia"]) {
                // line 101
                echo "                            ";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["guia"], "nroDoc", [], "any", false, false, false, 101), "html", null, true);
                echo "&nbsp;&nbsp;
                        ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['guia'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 102
            echo "</td>
                        <td width=\"40%\"></td>
                    </tr>
                    ";
        }
        // line 106
        echo "                    </tbody></table>
            </div><br>
            ";
        // line 108
        $context["moneda"] = $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 108, $this->source); })()), "tipoMoneda", [], "any", false, false, false, 108), "02");
        // line 109
        echo "            <div class=\"tabla_borde\">
                <table width=\"100%\" border=\"0\" cellpadding=\"2\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;font-size:10px !important\">
                    <tbody>
                        <tr>
                            <td align=\"center\" class=\"bold\">Cantidad</td>
                            <td align=\"center\" class=\"bold\">Código</td>
                            <td align=\"center\" class=\"bold\">Descripción</td>
                            <td style=\"height: 35px;\" align=\"center\" class=\"bold\">V. Unitario</td>
                            <td align=\"center\" class=\"bold\">V. Total</td>
                        </tr>
                        ";
        // line 119
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 119, $this->source); })()), "details", [], "any", false, false, false, 119));
        foreach ($context['_seq'] as $context["_key"] => $context["det"]) {
            // line 120
            echo "                        <tr class=\"border_top\">
                            <td align=\"center\">
                                ";
            // line 122
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, $context["det"], "cantidad", [], "any", false, false, false, 122)), "html", null, true);
            echo "
                                ";
            // line 123
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "unidad", [], "any", false, false, false, 123), "html", null, true);
            echo "
                            </td>
                            <td align=\"center\">
                                ";
            // line 126
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "codProducto", [], "any", false, false, false, 126), "html", null, true);
            echo "
                            </td>
                            <td align=\"center\" width=\"300px\">
                                <span>";
            // line 129
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "descripcion", [], "any", false, false, false, 129), "html", null, true);
            echo "</span><br>
                            </td>
                            <td align=\"center\">
                                ";
            // line 132
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 132, $this->source); })()), "html", null, true);
            echo "
                                ";
            // line 133
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, $context["det"], "mtoValorUnitario", [], "any", false, false, false, 133)), "html", null, true);
            echo "
                            </td>
                            <td align=\"center\">
                                ";
            // line 136
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 136, $this->source); })()), "html", null, true);
            echo "
                                ";
            // line 137
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, $context["det"], "mtoValorVenta", [], "any", false, false, false, 137)), "html", null, true);
            echo "
                            </td>
                        </tr>
                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['det'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 141
        echo "                    </tbody>
                </table></div>
            <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                <tbody><tr>
                    <td width=\"50%\" valign=\"top\">
                        <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                            <tbody>
                            <tr>
                                <td colspan=\"4\">
                                    <br>
                                    <br>
                                    <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:12px\" text-align=\"center\"><strong>";
        // line 152
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\ResolveFilter')->getValueLegend(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 152, $this->source); })()), "legends", [], "any", false, false, false, 152), "1000"), "html", null, true);
        echo "</strong></span>
                                    <br>
                                    <br>
                                    <strong>Información Adicional</strong>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                            <tbody>
                            <tr >
                                <td width=\"30%\" style=\"font-size: 10px;\">
                                    LEYENDA:
                                </td>
                                <td width=\"70%\" style=\"font-size: 10px;\">
                                    <p>
                                        ";
        // line 168
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 168, $this->source); })()), "legends", [], "any", false, false, false, 168));
        foreach ($context['_seq'] as $context["_key"] => $context["leg"]) {
            // line 169
            echo "                                        ";
            if ((0 !== twig_compare(twig_get_attribute($this->env, $this->source, $context["leg"], "code", [], "any", false, false, false, 169), "1000"))) {
                // line 170
                echo "                                            ";
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["leg"], "value", [], "any", false, false, false, 170), "html", null, true);
                echo "<br>
                                        ";
            }
            // line 172
            echo "                                        ";
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['leg'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 173
        echo "                                    </p>
                                </td>
                            </tr>
                            ";
        // line 176
        if ((isset($context["isNota"]) || array_key_exists("isNota", $context) ? $context["isNota"] : (function () { throw new RuntimeError('Variable "isNota" does not exist.', 176, $this->source); })())) {
            // line 177
            echo "                            <tr>
                                <td width=\"30%\" style=\"font-size: 10px;\">
                                    MOTIVO DE EMISIÓN:
                                </td>
                                <td width=\"70%\" style=\"font-size: 10px;\">
                                    ";
            // line 182
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 182, $this->source); })()), "desMotivo", [], "any", false, false, false, 182), "html", null, true);
            echo "
                                </td>
                            </tr>
                            ";
        }
        // line 186
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "user", [], "any", false, true, false, 186), "extras", [], "any", true, true, false, 186)) {
            // line 187
            echo "                                ";
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 187, $this->source); })()), "user", [], "any", false, false, false, 187), "extras", [], "any", false, false, false, 187));
            foreach ($context['_seq'] as $context["_key"] => $context["item"]) {
                // line 188
                echo "                                    <tr>
                                        <td width=\"30%\" style=\"font-size: 10px;\">
                                            ";
                // line 190
                echo twig_escape_filter($this->env, twig_upper_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "name", [], "any", false, false, false, 190)), "html", null, true);
                echo ":
                                        </td>
                                        <td width=\"70%\" style=\"font-size: 10px;\">
                                            ";
                // line 193
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["item"], "value", [], "any", false, false, false, 193), "html", null, true);
                echo "
                                        </td>
                                    </tr>
                                ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['item'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 197
            echo "                            ";
        }
        // line 198
        echo "                            </tbody>
                        </table>
                        ";
        // line 200
        if ((isset($context["isAnticipo"]) || array_key_exists("isAnticipo", $context) ? $context["isAnticipo"] : (function () { throw new RuntimeError('Variable "isAnticipo" does not exist.', 200, $this->source); })())) {
            // line 201
            echo "                        <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                            <tbody>
                            <tr>
                                <td>
                                    <br>
                                    <strong>Anticipo</strong>
                                    <br>
                                </td>
                            </tr>
                            </tbody>
                        </table>
                        <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\" style=\"font-size: 10px;\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                            <tbody>
                            <tr>
                                <td width=\"30%\"><b>Nro. Doc.</b></td>
                                <td width=\"70%\"><b>Total</b></td>
                            </tr>
                            ";
            // line 218
            $context['_parent'] = $context;
            $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 218, $this->source); })()), "anticipos", [], "any", false, false, false, 218));
            foreach ($context['_seq'] as $context["_key"] => $context["atp"]) {
                // line 219
                echo "                            <tr class=\"border_top\">
                                <td width=\"30%\">";
                // line 220
                echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["atp"], "nroDocRel", [], "any", false, false, false, 220), "html", null, true);
                echo "</td>
                                <td width=\"70%\">";
                // line 221
                echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 221, $this->source); })()), "html", null, true);
                echo " ";
                echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, $context["atp"], "total", [], "any", false, false, false, 221)), "html", null, true);
                echo "</td>
                            </tr>
                            ";
            }
            $_parent = $context['_parent'];
            unset($context['_seq'], $context['_iterated'], $context['_key'], $context['atp'], $context['_parent'], $context['loop']);
            $context = array_intersect_key($context, $_parent) + $_parent;
            // line 224
            echo "                            </tbody>
                        </table>
                        ";
        }
        // line 227
        echo "                    </td>
                    <td width=\"50%\" valign=\"top\">
                        <br>
                        <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" class=\"table table-valores-totales\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                            <tbody>
                            ";
        // line 232
        if ((isset($context["isAnticipo"]) || array_key_exists("isAnticipo", $context) ? $context["isAnticipo"] : (function () { throw new RuntimeError('Variable "isAnticipo" does not exist.', 232, $this->source); })())) {
            // line 233
            echo "                                <tr>
                                    <td align=\"right\"><strong>Total Anticipo:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 235
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 235, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 235, $this->source); })()), "totalAnticipos", [], "any", false, false, false, 235)), "html", null, true);
            echo "</span></td>
                                </tr>
                            ";
        }
        // line 238
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 238, $this->source); })()), "mtoOperGravadas", [], "any", false, false, false, 238)) {
            // line 239
            echo "                            <tr>
                                <td align=\"right\"><strong>Op. Gravadas:</strong></td>
                                <td width=\"120\" align=\"right\"><span>";
            // line 241
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 241, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 241, $this->source); })()), "mtoOperGravadas", [], "any", false, false, false, 241)), "html", null, true);
            echo "</span></td>
                            </tr>
                            ";
        }
        // line 244
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 244, $this->source); })()), "mtoOperInafectas", [], "any", false, false, false, 244)) {
            // line 245
            echo "                            <tr>
                                <td align=\"right\"><strong>Op. Inafectas:</strong></td>
                                <td width=\"120\" align=\"right\"><span>";
            // line 247
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 247, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 247, $this->source); })()), "mtoOperInafectas", [], "any", false, false, false, 247)), "html", null, true);
            echo "</span></td>
                            </tr>
                            ";
        }
        // line 250
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 250, $this->source); })()), "mtoOperExoneradas", [], "any", false, false, false, 250)) {
            // line 251
            echo "                            <tr >
                                <td align=\"right\"><strong>Op. Exoneradas:</strong></td>
                                <td width=\"120\" align=\"right\"><span>";
            // line 253
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 253, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 253, $this->source); })()), "mtoOperExoneradas", [], "any", false, false, false, 253)), "html", null, true);
            echo "</span></td>
                            </tr>
                            ";
        }
        // line 256
        echo "                            <tr>
                                <td align=\"right\"><strong>I.G.V.";
        // line 257
        if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "user", [], "any", false, true, false, 257), "numIGV", [], "any", true, true, false, 257)) {
            echo " ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 257, $this->source); })()), "user", [], "any", false, false, false, 257), "numIGV", [], "any", false, false, false, 257), "html", null, true);
            echo "%";
        }
        echo ":</strong></td>
                                <td width=\"120\" align=\"right\"><span>";
        // line 258
        echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 258, $this->source); })()), "html", null, true);
        echo "  ";
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 258, $this->source); })()), "mtoIGV", [], "any", false, false, false, 258)), "html", null, true);
        echo "</span></td>
                            </tr>
                            ";
        // line 260
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 260, $this->source); })()), "mtoISC", [], "any", false, false, false, 260)) {
            // line 261
            echo "                            <tr>
                                <td align=\"right\"><strong>I.S.C.:</strong></td>
                                <td width=\"120\" align=\"right\"><span>";
            // line 263
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 263, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 263, $this->source); })()), "mtoISC", [], "any", false, false, false, 263)), "html", null, true);
            echo "</span></td>
                            </tr>
                            ";
        }
        // line 266
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 266, $this->source); })()), "sumOtrosCargos", [], "any", false, false, false, 266)) {
            // line 267
            echo "                                <tr>
                                    <td align=\"right\"><strong>Otros Cargos:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 269
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 269, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 269, $this->source); })()), "sumOtrosCargos", [], "any", false, false, false, 269)), "html", null, true);
            echo "</span></td>
                                </tr>
                            ";
        }
        // line 272
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 272, $this->source); })()), "icbper", [], "any", false, false, false, 272)) {
            // line 273
            echo "                                <tr>
                                    <td align=\"right\"><strong>I.C.B.P.E.R.:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 275
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 275, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 275, $this->source); })()), "icbper", [], "any", false, false, false, 275)), "html", null, true);
            echo "</span></td>
                                </tr>
                            ";
        }
        // line 278
        echo "                            ";
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 278, $this->source); })()), "mtoOtrosTributos", [], "any", false, false, false, 278)) {
            // line 279
            echo "                                <tr>
                                    <td align=\"right\"><strong>Otros Tributos:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 281
            echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 281, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 281, $this->source); })()), "mtoOtrosTributos", [], "any", false, false, false, 281)), "html", null, true);
            echo "</span></td>
                                </tr>
                            ";
        }
        // line 284
        echo "                            <tr>
                                <td align=\"right\"><strong>Precio Venta:</strong></td>
                                <td width=\"120\" align=\"right\"><span id=\"ride-importeTotal\" class=\"ride-importeTotal\">";
        // line 286
        echo twig_escape_filter($this->env, (isset($context["moneda"]) || array_key_exists("moneda", $context) ? $context["moneda"] : (function () { throw new RuntimeError('Variable "moneda" does not exist.', 286, $this->source); })()), "html", null, true);
        echo "  ";
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 286, $this->source); })()), "mtoImpVenta", [], "any", false, false, false, 286)), "html", null, true);
        echo "</span></td>
                            </tr>
                            ";
        // line 288
        if ((twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 288, $this->source); })()), "perception", [], "any", false, false, false, 288) && twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 288, $this->source); })()), "perception", [], "any", false, false, false, 288), "mto", [], "any", false, false, false, 288))) {
            // line 289
            echo "                                ";
            $context["perc"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 289, $this->source); })()), "perception", [], "any", false, false, false, 289);
            // line 290
            echo "                                ";
            $context["soles"] = $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog("PEN", "02");
            // line 291
            echo "                                <tr>
                                    <td align=\"right\"><strong>Percepción:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 293
            echo twig_escape_filter($this->env, (isset($context["soles"]) || array_key_exists("soles", $context) ? $context["soles"] : (function () { throw new RuntimeError('Variable "soles" does not exist.', 293, $this->source); })()), "html", null, true);
            echo "  ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["perc"]) || array_key_exists("perc", $context) ? $context["perc"] : (function () { throw new RuntimeError('Variable "perc" does not exist.', 293, $this->source); })()), "mto", [], "any", false, false, false, 293)), "html", null, true);
            echo "</span></td>
                                </tr>
                                <tr>
                                    <td align=\"right\"><strong>Total a Pagar:</strong></td>
                                    <td width=\"120\" align=\"right\"><span>";
            // line 297
            echo twig_escape_filter($this->env, (isset($context["soles"]) || array_key_exists("soles", $context) ? $context["soles"] : (function () { throw new RuntimeError('Variable "soles" does not exist.', 297, $this->source); })()), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\FormatFilter')->number(twig_get_attribute($this->env, $this->source, (isset($context["perc"]) || array_key_exists("perc", $context) ? $context["perc"] : (function () { throw new RuntimeError('Variable "perc" does not exist.', 297, $this->source); })()), "mtoTotal", [], "any", false, false, false, 297)), "html", null, true);
            echo "</span></td>
                                </tr>
                            ";
        }
        // line 300
        echo "                            </tbody>
                        </table>
                    </td>
                </tr>
                </tbody></table>
            <br>
            <br>
            <!-- ";
        // line 307
        if ((array_key_exists("max_items", $context) && (1 === twig_compare(twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 307, $this->source); })()), "details", [], "any", false, false, false, 307)), (isset($context["max_items"]) || array_key_exists("max_items", $context) ? $context["max_items"] : (function () { throw new RuntimeError('Variable "max_items" does not exist.', 307, $this->source); })()))))) {
            // line 308
            echo "            
            ";
        }
        // line 309
        echo " -->
            <!-- <div style=\"page-break-after:always;\"></div> -->
            <div>
                <hr style=\"display: block; height: 1px; border: 0; border-top: 1px solid #666; margin: 20px 0; padding: 0;\"><table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\" style=\"page-break-inside:avoid; page-break-after:avoid;\">
                    <tbody><tr>
                        <td width=\"85%\">
                            <blockquote>
                                ";
        // line 316
        if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "user", [], "any", false, true, false, 316), "footer", [], "any", true, true, false, 316)) {
            // line 317
            echo "                                    ";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 317, $this->source); })()), "user", [], "any", false, false, false, 317), "footer", [], "any", false, false, false, 317);
            echo "
                                ";
        }
        // line 319
        echo "                                ";
        if ((twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "system", [], "any", false, true, false, 319), "hash", [], "any", true, true, false, 319) && twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 319, $this->source); })()), "system", [], "any", false, false, false, 319), "hash", [], "any", false, false, false, 319))) {
            // line 320
            echo "                                    <strong>Resumen:</strong>   ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 320, $this->source); })()), "system", [], "any", false, false, false, 320), "hash", [], "any", false, false, false, 320), "html", null, true);
            echo "<br>
                                ";
        }
        // line 322
        echo "                                <span>Representación Impresa de la ";
        echo twig_escape_filter($this->env, (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 322, $this->source); })()), "html", null, true);
        echo " ELECTRÓNICA.</span>
                            </blockquote>
                        </td>
                        <td width=\"15%\" align=\"right\">
                            <img src=\"";
        // line 326
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\ImageFilter')->toBase64($this->env->getRuntime('Greenter\Report\Render\QrRender')->getImage((isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 326, $this->source); })())), "svg+xml"), "html", null, true);
        echo "\" alt=\"Qr Image\">
                        </td>
                    </tr>
                    </tbody></table>
            </div>
            <div style=\"page-break-after:always;\"></div>
        </td>
    </tr>
    </tbody></table>
</body></html>
";
    }

    public function getTemplateName()
    {
        return "invoice.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  703 => 326,  695 => 322,  689 => 320,  686 => 319,  680 => 317,  678 => 316,  669 => 309,  665 => 308,  663 => 307,  654 => 300,  646 => 297,  637 => 293,  633 => 291,  630 => 290,  627 => 289,  625 => 288,  618 => 286,  614 => 284,  606 => 281,  602 => 279,  599 => 278,  591 => 275,  587 => 273,  584 => 272,  576 => 269,  572 => 267,  569 => 266,  561 => 263,  557 => 261,  555 => 260,  548 => 258,  540 => 257,  537 => 256,  529 => 253,  525 => 251,  522 => 250,  514 => 247,  510 => 245,  507 => 244,  499 => 241,  495 => 239,  492 => 238,  484 => 235,  480 => 233,  478 => 232,  471 => 227,  466 => 224,  455 => 221,  451 => 220,  448 => 219,  444 => 218,  425 => 201,  423 => 200,  419 => 198,  416 => 197,  406 => 193,  400 => 190,  396 => 188,  391 => 187,  388 => 186,  381 => 182,  374 => 177,  372 => 176,  367 => 173,  361 => 172,  355 => 170,  352 => 169,  348 => 168,  329 => 152,  316 => 141,  306 => 137,  302 => 136,  296 => 133,  292 => 132,  286 => 129,  280 => 126,  274 => 123,  270 => 122,  266 => 120,  262 => 119,  250 => 109,  248 => 108,  244 => 106,  238 => 102,  229 => 101,  225 => 100,  221 => 98,  219 => 97,  211 => 95,  207 => 94,  204 => 93,  198 => 90,  194 => 89,  191 => 88,  189 => 87,  185 => 85,  179 => 83,  176 => 82,  170 => 81,  166 => 80,  159 => 78,  151 => 75,  147 => 74,  143 => 72,  141 => 71,  129 => 62,  121 => 57,  113 => 52,  95 => 39,  87 => 34,  77 => 27,  66 => 19,  58 => 13,  56 => 12,  54 => 11,  52 => 10,  50 => 9,  45 => 6,  43 => 5,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "invoice.html.twig", "C:\\xampp\\htdocs\\apimultiserviciovel-copia\\vendor\\greenter\\report\\src\\Report\\Templates\\invoice.html.twig");
    }
}
