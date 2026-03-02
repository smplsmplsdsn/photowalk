<?php
function generateHandle(): string {
  return bin2hex(random_bytes(4));
}