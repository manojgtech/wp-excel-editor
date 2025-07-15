# Excel Processing WordPress Plugin

एक modern और user-friendly WordPress plugin जो Excel files को upload, process और display करने के साथ-साथ comments editing की advanced functionality प्रदान करता है।

## 🚀 Features

### Core Features
- **Excel File Upload**: .xls और .xlsx files को admin panel से upload करें
- **Data Processing**: PhpSpreadsheet library का उपयोग करके Excel data को process करें
- **Database Storage**: Processed data को WordPress database में store करें
- **Comments Editing**: Interactive popup dialog के साथ comments edit करें

### Security & Authentication
- **WordPress Authentication**: केवल WordPress users login कर सकते हैं
- **Role-Based Access**: केवल `excel_editor` role वाले users को access
- **AJAX Security**: Nonce verification और input sanitization
- **Permission Control**: Granular permissions for different actions

### Modern Design Features
- **Responsive Design**: Mobile और desktop devices पर perfect display
- **Modern UI/UX**: Gradient backgrounds, smooth animations, और professional styling
- **Interactive Elements**: Hover effects, loading states, और visual feedback
- **Accessibility**: Keyboard shortcuts और screen reader support

### Enhanced Functionality
- **Search & Filter**: Real-time search functionality
- **Status Indicators**: Visual indicators for records with/without comments
- **Character Count**: Textarea में character count display
- **Auto-save**: Optional auto-save functionality
- **Export**: CSV export functionality
- **Bulk Actions**: Multiple records selection और actions

## 📋 Requirements

- WordPress 5.0+
- PHP 7.4+
- MySQL 5.6+
- Composer (for dependencies)

## 🛠 Installation

1. **Plugin Upload**:
   ```bash
   # Plugin folder को wp-content/plugins/ में copy करें
   cp -r excel-processing /path/to/wordpress/wp-content/plugins/
   ```

2. **Dependencies Install**:
   ```bash
   cd wp-content/plugins/excel-processing
   composer install
   ```

3. **WordPress Activation**:
   - WordPress admin panel में जाएं
   - Plugins > Installed Plugins
   - "Excel Processing Plugin" को activate करें

4. **Database Setup**:
   - Plugin automatically database table create करेगा
   - Custom user role "excel_editor" भी create होगा

## 🔧 Configuration

### User Management
1. **Excel Editor Users Create करें**:
   - WordPress admin में **Users > Add New** पर जाएं
   - User details भरें (username, email, password)
   - Role को **"Excel Editor"** set करें
   - **"Add New User"** पर click करें

2. **Excel Editor Permissions**:
   - ✅ Comments editing page access
   - ✅ Beebe, AEC, और Epic comments edit कर सकते हैं
   - ❌ Billing Notes edit नहीं कर सकते (read-only)
   - ❌ Admin panel access नहीं
   - ❌ Records upload या delete नहीं कर सकते

### Admin Settings
1. **Excel Processing** menu में जाएं
2. **Settings** page पर जाएं
3. User management guidelines देखें
4. Excel file requirements और shortcode usage जानें

## 📖 Usage

### Excel File Upload
1. Admin panel में **Excel Processing** menu पर click करें
2. **Choose File** button से Excel file select करें
3. **Upload Excel** button पर click करें
4. Success message देखें

### Comments Editing Access
1. **Shareable Link** copy करें: `/beebe-invoices`
2. Link share करें authorized users के साथ
3. Users WordPress credentials से login करें
4. केवल `excel_editor` role वाले users access कर सकते हैं
5. Comments edit करने के लिए **Edit** button पर click करें

### Frontend Display
Shortcode का उपयोग करें:
```
[excel_data]
```

**Features**:
- ✅ Responsive table design
- ✅ Horizontal scrolling on mobile
- ✅ Clean date format (date only, no time)
- ✅ Hover effects और modern styling
- ✅ Comment editing functionality

## 🎨 Design Features

### Modern UI Elements
- **Gradient Backgrounds**: Beautiful purple-blue gradients
- **Card-based Layout**: Clean, modern card design
- **Smooth Animations**: CSS transitions और hover effects
- **Professional Typography**: Modern font stack

### Interactive Components
- **Edit Buttons**: Hover effects के साथ modern edit buttons
- **Status Indicators**: Color-coded status dots
- **Loading States**: Spinner animations
- **Success/Error Messages**: Styled notification messages

### Responsive Design
- **Mobile-First**: Mobile devices पर optimized
- **Flexible Tables**: Horizontal scroll के साथ responsive tables
- **Touch-Friendly**: Touch devices के लिए optimized buttons

### User Interface Improvements
- **Header Section**: User info और logout button के साथ
- **User Guide**: Comprehensive admin guide
- **Settings Page**: Organized configuration sections
- **Visual Feedback**: Better loading और success states

## 🔧 Technical Details

### File Structure
```
excel-processing/
├── assets/
│   ├── css/
│   │   ├── excel-processing-admin.css
│   │   └── excel-processing-frontend.css
│   └── js/
│       ├── excel-processing-admin.js
│       ├── excel-processing-frontend.js
│       └── excel-processing-popup.js
├── includes/
│   ├── admin-functions.php
│   ├── class-excel-data-table.php
│   └── frontend-functions.php
├── vendor/
├── composer.json
└── excel-processing-plugin.php
```

### Database Schema
```sql
CREATE TABLE wp_excel_data (
    id mediumint(9) NOT NULL AUTO_INCREMENT,
    invoice_id varchar(50) DEFAULT '' NOT NULL,
    dispatch_id varchar(50) DEFAULT '' NOT NULL,
    date_time_of_service datetime DEFAULT NULL,
    patient_full_name varchar(100) DEFAULT '' NOT NULL,
    billable_service_code varchar(50) DEFAULT '' NOT NULL,
    origin_name varchar(100) DEFAULT '' NOT NULL,
    origin_address text DEFAULT '' NOT NULL,
    destination_name varchar(100) DEFAULT '' NOT NULL,
    destination_address text DEFAULT '' NOT NULL,
    mileage float DEFAULT 0 NOT NULL,
    price_invoiced float DEFAULT 0 NOT NULL,
    amount_received float DEFAULT 0 NOT NULL,
    current_amount_due float DEFAULT 0 NOT NULL,
    billing_notes text DEFAULT '' NOT NULL,
    beebe_comments text DEFAULT '' NOT NULL,
    aec_comments text DEFAULT '' NOT NULL,
    epic_comments text DEFAULT '' NOT NULL,
    PRIMARY KEY (id)
);
```

### AJAX Endpoints
- `excel_processing_update_comments`: Comments update करने के लिए
- `excel_processing_get_comments`: Comments fetch करने के लिए
- `excel_processing_delete`: Records delete करने के लिए

### Security Features
- **WordPress Authentication**: Native WordPress login system
- **Role-Based Access**: `excel_editor` role verification
- **Nonce Verification**: AJAX security
- **Input Sanitization**: Data validation और sanitization
- **Permission Checks**: Granular permission control

## 🔒 Security

### Authentication
- WordPress native authentication system
- No custom password protection
- Secure session management

### Access Control
- Role-based permissions
- `excel_editor` role required for comments editing
- Admin access for full functionality

### Data Protection
- Input sanitization
- SQL injection prevention
- XSS protection
- CSRF protection via nonces

## 🐛 Troubleshooting

### Common Issues

**Access Denied Error**:
- User doesn't have "Excel Editor" role
- Solution: Assign correct role in Users > Edit User

**Login Loop**:
- Browser cache issues
- Solution: Clear browser cache and cookies

**Table Not Loading**:
- No Excel data uploaded
- Solution: Upload Excel file first

**Comments Not Saving**:
- User permissions issue
- Network connection problem
- Solution: Check user role and network

**Shortcode Not Working**:
- Plugin not activated
- Solution: Activate plugin in WordPress admin

### Debug Mode
Enable WordPress debug mode for detailed error messages:
```php
// wp-config.php में add करें
define('WP_DEBUG', true);
define('WP_DEBUG_LOG', true);
```

## 📝 Changelog

### Version 1.4
- ✅ WordPress authentication implementation
- ✅ Role-based access control
- ✅ Improved user interface design
- ✅ Comprehensive user guide
- ✅ Enhanced shortcode functionality
- ✅ Better responsive design
- ✅ Security improvements

### Version 1.3
- ✅ Modern UI/UX design
- ✅ Interactive comment editing
- ✅ Responsive table design
- ✅ Advanced search functionality

### Version 1.2
- ✅ Excel file processing
- ✅ Database integration
- ✅ Basic admin interface

### Version 1.1
- ✅ Initial plugin structure
- ✅ Basic functionality

## 🤝 Support

Plugin के बारे में कोई questions या issues हैं तो:

1. **Documentation**: इस README file को पढ़ें
2. **User Guide**: Admin panel में User Guide section देखें
3. **Settings**: Settings page में troubleshooting tips देखें

## 📄 License

This plugin is developed for internal use. All rights reserved.

---

**Note**: यह plugin WordPress के latest standards के अनुसार develop किया गया है और security best practices का पालन करता है। 