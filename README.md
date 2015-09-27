# DynamicQuery-for-PHP
Dynamic SQL query access class for php; shortcuts to do advanced queries; uses Pixie

### Methods

Coming soon; see class

### Example Usage

```
$db = new \DynamicQuery\DynamicQuery('mysql', 'test', 'localhost', 'root', 'theotherworld');
$db->query('
CREATE TABLE test
(
id int NOT NULL AUTO_INCREMENT,
name           text      NOT NULL,
age            int       NOT NULL,
address        varchar(50)
PRIMARY KEY (id)
)');
$rows = $db->test->insert([
	'name'=>'last',
	'age'=>'12',
	'address'=>'123 test'
]);
$rows = $db->test->findAllDynamic('age', 12);
$rows[0]->address = 'asdfasdfasdfasdfasdf11111111111111';
$rows[0]([
	'address'=>'12o3592532354235'
]);
$rows[0]->save();
print_r($db->test->findAll('age', 12));
```
