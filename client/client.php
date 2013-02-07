<?php
$client= new GearmanClient();
$client->addServer();

# Send reverse job
do
{
  $result = $client->doNormal("feed_url", "http://rss.detik.com/");
  
  # Check for various return packets and errors.
  switch($client->returnCode())
  {
    case GEARMAN_WORK_DATA:
      echo "Data: $result\n";
      break;
    case GEARMAN_WORK_STATUS:
      list($numerator, $denominator)= $client->doStatus();
      echo "Status: $numerator/$denominator complete\n";
      break;
    case GEARMAN_WORK_FAIL:
      echo "Failed\n";
      exit;
    case GEARMAN_SUCCESS:
      echo "success send job\n";  
      break;
    default:
      echo "RET: " . $client->returnCode() . "\n";
      exit;
  }
}
while($client->returnCode() != GEARMAN_SUCCESS);

?>