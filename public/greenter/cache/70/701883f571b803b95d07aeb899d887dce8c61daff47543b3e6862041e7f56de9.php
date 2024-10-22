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

/* despatch.html.twig */
class __TwigTemplate_862763b1a2143d5abe5f3815e564c59cacdd2fc22342ea6b50db574c6ab8b7e3 extends Template
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
        $this->loadTemplate("assets/style.css", "despatch.html.twig", 5)->display($context);
        echo "td{padding: 3px;}
    </style>
</head>
<body class=\"white-bg\">
";
        // line 9
        $context["cp"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 9, $this->source); })()), "company", [], "any", false, false, false, 9);
        // line 10
        $context["name"] = $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 10, $this->source); })()), "tipoDoc", [], "any", false, false, false, 10), "01");
        // line 11
        echo "<table width=\"100%\">
    <tbody><tr>
        <td style=\"padding:30px; !important\">
            <table width=\"100%\" height=\"200px\" border=\"0\" aling=\"center\" cellpadding=\"0\" cellspacing=\"0\">
                <tbody><tr>
                    <td width=\"50%\" height=\"90\" align=\"center\">
                        <span><img src=\"";
        // line 17
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\ImageFilter')->toBase64(twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 17, $this->source); })()), "system", [], "any", false, false, false, 17), "logo", [], "any", false, false, false, 17)), "html", null, true);
        echo "\" height=\"80\" style=\"text-align:center\" border=\"0\"></span>
                    </td>
                    <td width=\"5%\" height=\"40\" align=\"center\"></td>
                    <td width=\"45%\" rowspan=\"2\" valign=\"bottom\" style=\"padding-left:0\">
                        <div class=\"tabla_borde\">
                            <table width=\"100%\" border=\"0\" height=\"200\" cellpadding=\"6\" cellspacing=\"0\">
                                <tbody><tr>
                                    <td align=\"center\">
                                        <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:29px\" text-align=\"center\">";
        // line 25
        echo twig_escape_filter($this->env, (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 25, $this->source); })()), "html", null, true);
        echo "</span>
                                        <br>
                                        <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:19px\" text-align=\"center\">E L E C T R Ó N I C A</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <span style=\"font-size:15px\" text-align=\"center\">R.U.C.: ";
        // line 32
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 32, $this->source); })()), "ruc", [], "any", false, false, false, 32), "html", null, true);
        echo "</span>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"center\">
                                        <span style=\"font-size:24px\">";
        // line 37
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 37, $this->source); })()), "serie", [], "any", false, false, false, 37), "html", null, true);
        echo "-";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 37, $this->source); })()), "correlativo", [], "any", false, false, false, 37), "html", null, true);
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
        // line 50
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 50, $this->source); })()), "razonSocial", [], "any", false, false, false, 50), "html", null, true);
        echo "</span></strong>
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\">
                                        <strong>Dirección: </strong>";
        // line 55
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["cp"]) || array_key_exists("cp", $context) ? $context["cp"] : (function () { throw new RuntimeError('Variable "cp" does not exist.', 55, $this->source); })()), "address", [], "any", false, false, false, 55), "direccion", [], "any", false, false, false, 55), "html", null, true);
        echo "
                                    </td>
                                </tr>
                                <tr>
                                    <td align=\"left\">
                                        ";
        // line 60
        echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 60, $this->source); })()), "user", [], "any", false, false, false, 60), "header", [], "any", false, false, false, 60);
        echo "
                                    </td>
                                </tr>
                                </tbody></table>
                        </div>
                    </td>
                </tr>
                </tbody></table>
            <br>
            <div class=\"tabla_borde\">
                ";
        // line 70
        $context["cl"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 70, $this->source); })()), "destinatario", [], "any", false, false, false, 70);
        // line 71
        echo "                <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
                    <tbody>
                    <tr>
                        <td colspan=\"2\">DESTINATARIO</td>
                    </tr>
                    <tr class=\"border_top\">
                        <td width=\"60%\" align=\"left\"><strong>Razón Social:</strong>  ";
        // line 77
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 77, $this->source); })()), "rznSocial", [], "any", false, false, false, 77), "html", null, true);
        echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>";
        // line 78
        echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 78, $this->source); })()), "tipoDoc", [], "any", false, false, false, 78), "06"), "html", null, true);
        echo ":</strong>  ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 78, $this->source); })()), "numDoc", [], "any", false, false, false, 78), "html", null, true);
        echo "</td>
                    </tr>
                    <tr>
                        <td width=\"40%\" align=\"left\" colspan=\"2\"><strong>Dirección:</strong>  ";
        // line 81
        if (twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 81, $this->source); })()), "address", [], "any", false, false, false, 81)) {
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["cl"]) || array_key_exists("cl", $context) ? $context["cl"] : (function () { throw new RuntimeError('Variable "cl" does not exist.', 81, $this->source); })()), "address", [], "any", false, false, false, 81), "direccion", [], "any", false, false, false, 81), "html", null, true);
        }
        echo "</td>
                    </tr>
                    </tbody></table>
            </div><br>
            <div class=\"tabla_borde\">
                ";
        // line 86
        $context["cl"] = twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 86, $this->source); })()), "destinatario", [], "any", false, false, false, 86);
        // line 87
        echo "                <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
                    <tbody>
                    <tr>
                        <td colspan=\"2\">ENVIO</td>
                    </tr>
                    <tr class=\"border_top\">
                        <td width=\"60%\" align=\"left\">
                            <strong>Fecha Emisión:</strong>  ";
        // line 94
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 94, $this->source); })()), "fechaEmision", [], "any", false, false, false, 94), "d/m/Y"), "html", null, true);
        echo "
                        </td>
                        <td width=\"40%\" align=\"left\"><strong>Fecha Inicio de Traslado:</strong>  ";
        // line 96
        echo twig_escape_filter($this->env, twig_date_format_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 96, $this->source); })()), "envio", [], "any", false, false, false, 96), "fecTraslado", [], "any", false, false, false, 96), "d/m/Y"), "html", null, true);
        echo " </td>
                    </tr>
                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Motivo Traslado:</strong>  ";
        // line 99
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 99, $this->source); })()), "envio", [], "any", false, false, false, 99), "desTraslado", [], "any", false, false, false, 99), "html", null, true);
        echo " </td>
                        <td width=\"40%\" align=\"left\"><strong>Modalidad de Transporte:</strong>  ";
        // line 100
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 100, $this->source); })()), "envio", [], "any", false, false, false, 100), "modTraslado", [], "any", false, false, false, 100), "html", null, true);
        echo " </td>
                    </tr>
                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Peso Bruto Total (";
        // line 103
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 103, $this->source); })()), "envio", [], "any", false, false, false, 103), "undPesoTotal", [], "any", false, false, false, 103), "html", null, true);
        echo "):</strong>  ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 103, $this->source); })()), "envio", [], "any", false, false, false, 103), "pesoTotal", [], "any", false, false, false, 103), "html", null, true);
        echo "% </td>
                        <td width=\"40%\">";
        // line 104
        if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 104, $this->source); })()), "envio", [], "any", false, false, false, 104), "numBultos", [], "any", false, false, false, 104)) {
            echo "<strong>Número de Bultos:</strong>  ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 104, $this->source); })()), "envio", [], "any", false, false, false, 104), "numBultos", [], "any", false, false, false, 104), "html", null, true);
        }
        echo "</td>
                    </tr>
                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>P. Partida:</strong>  ";
        // line 107
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 107, $this->source); })()), "envio", [], "any", false, false, false, 107), "partida", [], "any", false, false, false, 107), "ubigueo", [], "any", false, false, false, 107), "html", null, true);
        echo " - ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 107, $this->source); })()), "envio", [], "any", false, false, false, 107), "partida", [], "any", false, false, false, 107), "direccion", [], "any", false, false, false, 107), "html", null, true);
        echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>P. Llegada: </strong>  ";
        // line 108
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 108, $this->source); })()), "envio", [], "any", false, false, false, 108), "llegada", [], "any", false, false, false, 108), "ubigueo", [], "any", false, false, false, 108), "html", null, true);
        echo " - ";
        echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 108, $this->source); })()), "envio", [], "any", false, false, false, 108), "llegada", [], "any", false, false, false, 108), "direccion", [], "any", false, false, false, 108), "html", null, true);
        echo "</td>
                    </tr>
                    </tbody></table>
            </div><br>
            ";
        // line 112
        $context["tr"] = twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 112, $this->source); })()), "envio", [], "any", false, false, false, 112), "transportista", [], "any", false, false, false, 112);
        // line 113
        echo "            ";
        if ((isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 113, $this->source); })())) {
            // line 114
            echo "            <div class=\"tabla_borde\">
                <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
                    <tbody>
                    <tr>
                        <td colspan=\"2\">TRANSPORTE</td>
                    </tr>
                    <tr class=\"border_top\">
                        <td width=\"60%\" align=\"left\"><strong>Razón Social:</strong>  ";
            // line 121
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 121, $this->source); })()), "rznSocial", [], "any", false, false, false, 121), "html", null, true);
            echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>";
            // line 122
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 122, $this->source); })()), "tipoDoc", [], "any", false, false, false, 122), "06"), "html", null, true);
            echo ":</strong>  ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 122, $this->source); })()), "numDoc", [], "any", false, false, false, 122), "html", null, true);
            echo "</td>
                    </tr>
                    <tr>
                        <td width=\"60%\" align=\"left\"><strong>Vehiculo:</strong>  ";
            // line 125
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 125, $this->source); })()), "placa", [], "any", false, false, false, 125), "html", null, true);
            echo "</td>
                        <td width=\"40%\" align=\"left\"><strong>Conductor:</strong>  ";
            // line 126
            echo twig_escape_filter($this->env, $this->env->getRuntime('Greenter\Report\Filter\DocumentFilter')->getValueCatalog(twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 126, $this->source); })()), "choferTipoDoc", [], "any", false, false, false, 126), "06"), "html", null, true);
            echo " ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["tr"]) || array_key_exists("tr", $context) ? $context["tr"] : (function () { throw new RuntimeError('Variable "tr" does not exist.', 126, $this->source); })()), "choferDoc", [], "any", false, false, false, 126), "html", null, true);
            echo "</td>
                    </tr>
                    </tbody></table>
            </div><br>
            ";
        }
        // line 131
        echo "            <div class=\"tabla_borde\">
                <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
                    <tbody>
                    <tr>
                        <td align=\"center\" class=\"bold\">Item</td>
                        <td align=\"center\" class=\"bold\">Código</td>
                        <td align=\"center\" class=\"bold\" width=\"300px\">Descripción</td>
                        <td align=\"center\" class=\"bold\">Unidad</td>
                        <td align=\"center\" class=\"bold\">Cantidad</td>
                    </tr>
                        ";
        // line 141
        $context['_parent'] = $context;
        $context['_seq'] = twig_ensure_traversable(twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 141, $this->source); })()), "details", [], "any", false, false, false, 141));
        $context['loop'] = [
          'parent' => $context['_parent'],
          'index0' => 0,
          'index'  => 1,
          'first'  => true,
        ];
        if (is_array($context['_seq']) || (is_object($context['_seq']) && $context['_seq'] instanceof \Countable)) {
            $length = count($context['_seq']);
            $context['loop']['revindex0'] = $length - 1;
            $context['loop']['revindex'] = $length;
            $context['loop']['length'] = $length;
            $context['loop']['last'] = 1 === $length;
        }
        foreach ($context['_seq'] as $context["_key"] => $context["det"]) {
            // line 142
            echo "                        <tr class=\"border_top\">
                            <td align=\"center\">";
            // line 143
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["loop"], "index", [], "any", false, false, false, 143), "html", null, true);
            echo "</td>
                            <td align=\"center\">";
            // line 144
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "codigo", [], "any", false, false, false, 144), "html", null, true);
            echo "</td>
                            <td align=\"center\">";
            // line 145
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "descripcion", [], "any", false, false, false, 145), "html", null, true);
            echo "</td>
                            <td align=\"center\">";
            // line 146
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "unidad", [], "any", false, false, false, 146), "html", null, true);
            echo "</td>
                            <td align=\"center\">";
            // line 147
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, $context["det"], "cantidad", [], "any", false, false, false, 147), "html", null, true);
            echo "</td>
                        </tr>
                        ";
            ++$context['loop']['index0'];
            ++$context['loop']['index'];
            $context['loop']['first'] = false;
            if (isset($context['loop']['length'])) {
                --$context['loop']['revindex0'];
                --$context['loop']['revindex'];
                $context['loop']['last'] = 0 === $context['loop']['revindex0'];
            }
        }
        $_parent = $context['_parent'];
        unset($context['_seq'], $context['_iterated'], $context['_key'], $context['det'], $context['_parent'], $context['loop']);
        $context = array_intersect_key($context, $_parent) + $_parent;
        // line 150
        echo "                    </tbody>
                </table></div>
            <table width=\"100%\" border=\"0\" cellpadding=\"0\" cellspacing=\"0\">
                <tbody><tr>
                    <td width=\"50%\" valign=\"top\">
                        <table width=\"100%\" border=\"0\" cellpadding=\"5\" cellspacing=\"0\">
                            <tbody>
                            <tr>
                                <td colspan=\"4\">
                                ";
        // line 159
        if (twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 159, $this->source); })()), "observacion", [], "any", false, false, false, 159)) {
            // line 160
            echo "                                    <br><br>
                                    <span style=\"font-family:Tahoma, Geneva, sans-serif; font-size:12px\" text-align=\"center\"><strong>Observaciones</strong></span>
                                    <br>
                                    <p>";
            // line 163
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 163, $this->source); })()), "observacion", [], "any", false, false, false, 163), "html", null, true);
            echo "</p>
                                ";
        }
        // line 165
        echo "                                </td>
                            </tr>
                            </tbody>
                        </table>
                    </td>
                    <td width=\"50%\" valign=\"top\"></td>
                </tr>
                </tbody></table>
            ";
        // line 173
        if ((array_key_exists("max_items", $context) && (1 === twig_compare(twig_length_filter($this->env, twig_get_attribute($this->env, $this->source, (isset($context["doc"]) || array_key_exists("doc", $context) ? $context["doc"] : (function () { throw new RuntimeError('Variable "doc" does not exist.', 173, $this->source); })()), "details", [], "any", false, false, false, 173)), (isset($context["max_items"]) || array_key_exists("max_items", $context) ? $context["max_items"] : (function () { throw new RuntimeError('Variable "max_items" does not exist.', 173, $this->source); })()))))) {
            // line 174
            echo "                <div style=\"page-break-after:always;\"></div>
            ";
        }
        // line 176
        echo "            <div>
            <table>
                <tbody>
                <tr><td width=\"100%\">
                    <blockquote>
                        ";
        // line 181
        if (twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "user", [], "any", false, true, false, 181), "footer", [], "any", true, true, false, 181)) {
            // line 182
            echo "                            ";
            echo twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 182, $this->source); })()), "user", [], "any", false, false, false, 182), "footer", [], "any", false, false, false, 182);
            echo "
                        ";
        }
        // line 184
        echo "                        ";
        if ((twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, ($context["params"] ?? null), "system", [], "any", false, true, false, 184), "hash", [], "any", true, true, false, 184) && twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 184, $this->source); })()), "system", [], "any", false, false, false, 184), "hash", [], "any", false, false, false, 184))) {
            // line 185
            echo "                            <strong>Resumen:</strong>   ";
            echo twig_escape_filter($this->env, twig_get_attribute($this->env, $this->source, twig_get_attribute($this->env, $this->source, (isset($context["params"]) || array_key_exists("params", $context) ? $context["params"] : (function () { throw new RuntimeError('Variable "params" does not exist.', 185, $this->source); })()), "system", [], "any", false, false, false, 185), "hash", [], "any", false, false, false, 185), "html", null, true);
            echo "<br>
                        ";
        }
        // line 187
        echo "                        <span>Representación Impresa de la ";
        echo twig_escape_filter($this->env, (isset($context["name"]) || array_key_exists("name", $context) ? $context["name"] : (function () { throw new RuntimeError('Variable "name" does not exist.', 187, $this->source); })()), "html", null, true);
        echo " ELECTRÓNICA.</span>
                    </blockquote>
                    </td>
                </tr>
                </tbody></table>
            </div>
        </td>
    </tr>
    </tbody></table>
</body></html>";
    }

    public function getTemplateName()
    {
        return "despatch.html.twig";
    }

    public function isTraitable()
    {
        return false;
    }

    public function getDebugInfo()
    {
        return array (  400 => 187,  394 => 185,  391 => 184,  385 => 182,  383 => 181,  376 => 176,  372 => 174,  370 => 173,  360 => 165,  355 => 163,  350 => 160,  348 => 159,  337 => 150,  320 => 147,  316 => 146,  312 => 145,  308 => 144,  304 => 143,  301 => 142,  284 => 141,  272 => 131,  262 => 126,  258 => 125,  250 => 122,  246 => 121,  237 => 114,  234 => 113,  232 => 112,  223 => 108,  217 => 107,  208 => 104,  202 => 103,  196 => 100,  192 => 99,  186 => 96,  181 => 94,  172 => 87,  170 => 86,  160 => 81,  152 => 78,  148 => 77,  140 => 71,  138 => 70,  125 => 60,  117 => 55,  109 => 50,  91 => 37,  83 => 32,  73 => 25,  62 => 17,  54 => 11,  52 => 10,  50 => 9,  43 => 5,  37 => 1,);
    }

    public function getSourceContext()
    {
        return new Source("", "despatch.html.twig", "C:\\xampp\\htdocs\\facturacion\\vendor\\greenter\\report\\src\\Report\\Templates\\despatch.html.twig");
    }
}
