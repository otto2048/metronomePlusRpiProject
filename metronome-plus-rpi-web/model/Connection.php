<?php
    //get sdk
    require_once($_SERVER['DOCUMENT_ROOT']."/cmp408/metronome-plus-rpi-web/model/vendor/autoload.php");

    use Aws\DynamoDb\Exception\DynamoDbException;
    use Aws\DynamoDb\Marshaler;

    use Aws\DynamoDb\DynamoDbClient;

    use Aws\Sqs\SqsClient; 
    use Aws\Exception\AwsException;

    Class Connection {

        private $region;

        private $dynamodbClient;
        private $marshaler;

        public function __construct()
        {
            $this->region = "us-east-1";
            
            //create dynamodb connection
            $this->dynamodbClient = DynamoDbClient::factory(array(
                'region' => $this->region,
                'version' => 'latest'
            ));

            $this->marshaler = new Marshaler();
        }

        public function getClient()
        {
            return $this->dynamodbClient;
        }

        public function getMarshaler()
        {
            return $this->marshaler;
        }
    }
?>