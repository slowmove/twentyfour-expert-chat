## Plugin information
***
  Plugin Name: TwentyFour Expert Chat 

  Description: Create "One to All" chat for your site

  Version: 1.0

  Author: Erik Johansson

  Author URI: http://24hr.se

  License: GPL2

## License
  This program is free software; you can redistribute it and/or modify
  it under the terms of the GNU General Public License, version 2, as
  published by the Free Software Foundation.

  This program is distributed in the hope that it will be useful,
  but WITHOUT ANY WARRANTY; without even the implied warranty of
  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
  GNU General Public License for more details.

  You should have received a copy of the GNU General Public License
  along with this program; if not, write to the Free Software
  Foundation, Inc., 51 Franklin St, Fifth Floor, Boston, MA  02110-1301  USA
  
## Usage
```php
global $blog_id;
$chat = new ExpertChat($blog_id);
$chat->render_chat_box();
```