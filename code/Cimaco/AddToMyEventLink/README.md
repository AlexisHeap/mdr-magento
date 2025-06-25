# AddToMyEventLink Magento 2 Module

## 📦 About

This module is part of the MDR integration for Magento, where users can add products by clicking the 'Agregar a mi mesa de regalos' button in the PDP. It opens a modal where they must select an event associated with their logged-in account.

## 📁 Installation

Export or clone the `code/Cimaco/AddToMyEventLink` directory from this repository and paste it inside of your app directory.

Where:
- `Cimaco` is the **vendor name**
- `AddToMyEventLink` is the **module name**

3. Enable the module:

```bash
bin/magento module:enable Cimaco_AddToMyEventLink
bin/magento setup:upgrade
bin/magento cache:flush
```

(Optional, if not in developer mode)
```bash
bin/magento setup:di:compile
```

Conclusion:
So, Your module should reside in this exact path:
`app/code/Cimaco/AddToMyEventLink`



