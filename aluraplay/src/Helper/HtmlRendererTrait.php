<?php

namespace Alura\Mvc\Helper;

trait HtmlRendererTrait
{
  private function addErrorMessage(string $errorMessage): void
  {
    $_SESSION['error_message'] = $errorMessage;
  }
}


