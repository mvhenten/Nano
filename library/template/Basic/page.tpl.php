<?php echo $this->getDoctype() ?>
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
<head>
    <title><?php echo $this->getTitle() ?></title>
    <?php
        foreach( $this->getHeadSection() as $value ){
            echo $value . "\n";
        }
    ?>
</head>
<body>
    <!-- Insert your content here -->
</body>
</html>
