ketwaroo/package-info
======================

This is sort of a reflection tool for the composer package we're working in.

Most common usage would be if you need to determine the base path of your package.
Or if you have a project that spans multiple sub packages.

# Usage. Of sorts.

```php

namespace Vendor/Package;

use Ketwaroo/PackageInfo

class SomeClass{

    public function readStaticCsvData(){

        // say you need to read some data located in /vendor/package/data/mydata.csv;
        // instead of mucking about with relative paths,

        $root = PackageInfo::whereAmI($this);

        $csv = file($root.'/mydata.csv');

        // ..etc

    }
}
```

# TODO.

unit tests. maybe.
figure out what else this could be useful for.




