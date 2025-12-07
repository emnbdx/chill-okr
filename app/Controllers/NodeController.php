<?php
namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;

class NodeController {
  public function index(Request $req, Response $res): Response {
    return view($res, "nodes/index");
  }
}
