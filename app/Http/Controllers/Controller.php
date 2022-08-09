<?php

namespace App\Http\Controllers;

use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Bus\DispatchesJobs;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

class Controller extends BaseController
{
  use AuthorizesRequests, DispatchesJobs, ValidatesRequests;

  public function leerMatriz()
  {
    $manejadorArchivo = fopen('habitacion.txt', 'r');
    if ($manejadorArchivo) {
      while (($linea = fgets($manejadorArchivo)) !== false) {
        $m[] = explode(',', str_replace("\r\n", "", $linea));
      }

      fclose($manejadorArchivo);
    } else {
      die('No se pudo abrir el archivo');
    }
    $this->iluminarHabitacion($m);
  }
  function imprimirHabitacion($m)
  {
    foreach ($m as $fila) {
      foreach ($fila as $numero) {
        echo ($numero . '&nbsp;&nbsp;&nbsp;');
      }
      echo '<br/>';
    }
    echo '<br/>';
  }
  function obtenerZonasOscurasCantidad($m)
  {
    //$this->imprimirHabitacion($m);
    $filas = count($m);
    $columnas = count($m[0]);

    for ($f = 0; $f < $filas; $f++) {
      for ($c = 0; $c < $columnas; $c++) {
        if ($m[$f][$c] == 0) {
          return true;
        }
      }
    }

    return false;
  }

  function obtenerFocosCantidad($m)
  {
    $filas = count($m);
    $columnas = count($m[0]);
    $focosCantidad = 0;

    for ($f = 0; $f < $filas; $f++) {
      for ($c = 0; $c < $columnas; $c++) {
        if ($m[$f][$c] == 2) {
          $focosCantidad++;
        }
      }
    }

    return $focosCantidad;
  }

  function iluminarHabitacion($m)
  {
    $filas = count($m);
    $columnas = count($m[0]);

    while ($this->obtenerZonasOscurasCantidad($m)) {
      $segmentosIluminadosPorPared = [];
      $mayor = [];



      for ($f = 0; $f < $filas; $f++) {
        //echo count($mayor);
        for ($c = 0; $c < $columnas; $c++) {
          $paredIndice = ($f . ',' . $c);
          $segmentosIluminadosPorPared[$paredIndice] = 0;

          if ($m[$f][$c] != 0) {
            continue;
          }

          //Buscar hacia arriba
          if ($f > 0) {
            for ($fAux = ($f - 1); $fAux > -1; $fAux--) {
              if ($m[$fAux][$c] == 1) {
                break;
              }

              if ($m[$fAux][$c] == 0) {
                $segmentosIluminadosPorPared[$paredIndice]++;
              }
            }
          }


          //Buscar hacia la derecha
          if ($c < ($columnas - 1)) {
            for ($cAux = ($c + 1); $cAux < $columnas; $cAux++) {
              if ($m[$f][$cAux] == 1) {
                break;
              }

              if ($m[$f][$cAux] == 0) {
                $segmentosIluminadosPorPared[$paredIndice]++;
              }
            }
          }

          //Buscar hacia abajo
          if ($f < ($filas - 1)) {
            for ($fAux = ($f + 1); $fAux < $filas; $fAux++) {
              if ($m[$fAux][$c] == 1) {
                break;
              }

              if ($m[$fAux][$c] == 0) {
                $segmentosIluminadosPorPared[$paredIndice]++;
              }
            }
          }

          //Buscar hacia la izquierda
          if ($c > 0) {
            for ($cAux = ($c - 1); $cAux > -1; $cAux--) {
              if ($m[$f][$cAux] == 1) {
                break;
              }

              if ($m[$f][$cAux] == 0) {
                $segmentosIluminadosPorPared[$paredIndice]++;
              }
            }
          }
          if (count($mayor)) { //No se ha definido el mayor
            if ($mayor['valor'] < $segmentosIluminadosPorPared[$paredIndice]) {
              
              
              $mayor = [
                'indice' => $paredIndice,
                'valor' => $segmentosIluminadosPorPared[$paredIndice],
              ];
            }
          } else {
            $mayor = [
              'indice' => $paredIndice,
              'valor' => $segmentosIluminadosPorPared[$paredIndice],
            ];
          }
        }
      }
      //echo (json_encode($segmentosIluminadosPorPared) . '<br/><br/>');
      echo "Siguiente foco a colocar: " . json_encode($mayor) . '<br/>';



      $paredMayorIndiceSegmentos = explode(',', $mayor['indice']);
      $paredMayorFila = intval($paredMayorIndiceSegmentos[0]);
      //echo json_encode($paredMayorIndiceSegmentos) . '<br/>';
      $paredMayorColumna = intval($paredMayorIndiceSegmentos[1]);
      $m[$paredMayorFila][$paredMayorColumna] = 2;

      //Buscar hacia arriba
      if ($paredMayorFila > 0) {
        for ($fAux = ($paredMayorFila - 1); $fAux > -1; $fAux--) {
          if ($m[$fAux][$paredMayorColumna] == 1) {
            break;
          }

          if ($m[$fAux][$paredMayorColumna] == 0) {
            $m[$fAux][$paredMayorColumna] = '*';
          }
        }
      }

      //Buscar hacia la derecha
      if ($paredMayorColumna < ($columnas - 1)) {
        for ($cAux = ($paredMayorColumna + 1); $cAux < $columnas; $cAux++) {
          if ($m[$paredMayorFila][$cAux] == 1) {
            break;
          }

          if ($m[$paredMayorFila][$cAux] == 0) {
            $m[$paredMayorFila][$cAux] = '*';
          }
        }
      }

      //Buscar hacia abajo
      if ($paredMayorFila < ($filas - 1)) {
        for ($fAux = ($paredMayorFila + 1); $fAux < $filas; $fAux++) {
          if ($m[$fAux][$paredMayorColumna] == 1) {
            break;
          }

          if ($m[$fAux][$paredMayorColumna] == 0) {
            $m[$fAux][$paredMayorColumna] = '*';
          }
        }
      }

      //Buscar hacia la izquierda
      if ($paredMayorColumna > 0) {
        for ($cAux = ($paredMayorColumna - 1); $cAux > -1; $cAux--) {
          if ($m[$paredMayorFila][$cAux] == 1) {
            break;
          }

          if ($m[$paredMayorFila][$cAux] == 0) {
            $m[$paredMayorFila][$cAux] = '*';
          }
        }
      }

       $this->imprimirHabitacion($m);
    }

     echo "Cantidad total de bombillos: " . $this->obtenerFocosCantidad($m);

     
  }
}
