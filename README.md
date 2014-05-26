Muchacuba SMS Queue
===================

This library is an implementation of `cubalider/sms-queue` using `doctrine/orm`
for persistence.

```
// $em is an already created entity manager

$messageManager = new MessageManager($em);
$bulkManager = new BulkManager($em);

$messages1 = array(
    new Message("Message 1.1"),
    new Message("Message 1.2"),
    new Message("Message 1.3")
);

$messageManager->push($messages1);

$messages2 = array(
    new Message("Message 2.1"),
    new Message("Message 2.2"),
    new Message("Message 2.3")
);

$messageManager->push($messages2);

$bulk = $bulkManager->pop();
$messages = $messageManager->pop($bulk, 2);
// $messages is array(["Message 1.1"], ["Message 1.2"])

$messages = $messageManager->pop($bulk, 2);
// $messages is array(["Message 1.3"])

$messages = $messageManager->pop($bulk, 2);
// $messages is false

$bulk = $bulkManager->pop();
$messages = $messageManager->pop($bulk);
// $messages is array(["Message 2.1"], ["Message 2.2"], ["Message 2.3"])
```