<?php
    require_once("Connection.php");

    abstract Class Model {
        //connection to dynamodb
        protected $connection;

        public function __construct()
        {
            $this->connection = new Connection();
        }

        public function __destruct()
        {
            $this->connection = null;
        }

        //get a record from a table based on primary key
        //on success, return json string of result
        //on failure, return null
        public function read($tableName, $key)
        {
            $result = $this->connection->getClient()->getItem([
                //json object making up key
                'Key' => $key,

                //table name of table to get item from
                'TableName' => $tableName,
            ]);

            $marshalledResult = $result['Item'];

            if ($marshalledResult != null)
            {
                //return unmarshalled result
                return $this->connection->getMarshaler()->unmarshalJson($marshalledResult);
            }
            
            return null;
        }

        //query a table
        //on success, return json string of results
        //on failure, return null
        public function query($tableName, $key)
        {
            //SOURCE: AWS, no date a
            //ACCESSED FROM: https://docs.aws.amazon.com/code-library/latest/ug/dynamodb_example_dynamodb_Query_section.html
            $expressionAttributeValues = [];
            $expressionAttributeNames = [];
            $keyConditionExpression = "";
            $index = 1;
            
            foreach ($key as $name => $value) {
                $keyConditionExpression .= "#" . array_key_first($value) . " = :v$index,";
                $expressionAttributeNames["#" . array_key_first($value)] = array_key_first($value);
                $hold = array_pop($value);
                $expressionAttributeValues[":v$index"] = [
                    array_key_first($hold) => array_pop($hold),
                ];
            }

            $keyConditionExpression = substr($keyConditionExpression, 0, -1);
            $query = [
                'ExpressionAttributeValues' => $expressionAttributeValues,
                'ExpressionAttributeNames' => $expressionAttributeNames,
                'KeyConditionExpression' => $keyConditionExpression,
                'TableName' => $tableName,
            ];

            $result = $this->connection->getClient()->query($query);

            $marshalledResult = $result['Items'];

            //return unmarshalled result
            if ($marshalledResult != null)
            {
                $returnVal = array();

                foreach ($marshalledResult as $item)
                {
                    array_push($returnVal, json_decode($this->connection->getMarshaler()->unmarshalJson($item))); 
                }

                return json_encode($returnVal, JSON_OBJECT_AS_ARRAY);
            }
            
            
            return null;
        }

        //create a record
        //on success, return true
        //on failure, return false
        public function put($tableName, $data)
        {
            $result = $this->connection->getClient()->putItem([
                'Item' => $data,

                //table name of table to get item from
                'TableName' => $tableName
            ]);

            $metaData = $result['@metadata'];

            if ($metaData["statusCode"] == 200)
            {
                return true;
            }
            
            return false;
        }

        //update a record
        //on success, return true
        //on failure, return false
        public function update($tableName, $key, $attributeNames, $attributeValues, $updateExpression)
        {
            $result = $this->connection->getClient()->updateItem([
                'Key' => $key,
                'TableName' => $tableName,
                'UpdateExpression' => $updateExpression,
                'ExpressionAttributeNames' => $attributeNames,
                'ExpressionAttributeValues' => $attributeValues,
            ]);

            $metaData = $result['@metadata'];

            if ($metaData["statusCode"] == 200)
            {
                return true;
            }
            
            return false;
        }

        //delete a record
        //on success, return true
        //on failure, return false
        public function delete($tableName, $key)
        {
            $result = $this->connection->getClient()->deleteItem([
                //json object making up key
                'Key' => $key,

                //table name of table to get item from
                'TableName' => $tableName,
            ]);

            $metaData = $result['@metadata'];

            if ($metaData["statusCode"] == 200)
            {
                return true;
            }
            
            return false;
        }
    }

?>