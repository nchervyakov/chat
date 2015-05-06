Конфигурирование push-сервера для чата.
=======================================

## Конфигурирование в Ubuntu

### 1. Установить Erlang

```
> sudo apt-get install erlang
```

### 2. Устанавливаем RabbitMQ

Описание процесса установки есть здесь: [https://www.rabbitmq.com/install-debian.html]

В целом, должно хватить этой команды:
```
> sudo apt-get install rabbitmq-server
```

### 3. Установка Node.JS 

Описание процесса установки есть здесь: [https://github.com/joyent/node/wiki/Installing-Node.js-via-package-manager#debian-and-ubuntu-based-linux-distributions]

Сама установка:
```
> curl -sL https://deb.nodesource.com/setup | sudo bash -
> sudo apt-get install -y nodejs
> sudo apt-get install -y npm
```

А также для установки компилируемых расширений node.js:
```
> apt-get install -y build-essential 
```

### 4. Устанавливаем зависимости проекта Node.js.

После клонирования проекта push-сервера:
```
> git clone https://github.com/nchervyakov/chat-push-server
```

...Необходимо установить зависимости:
```
> sudo npm install
```



## Конфигурирование в Windows

### 1. Установить Erlang

Идём по ссылке [http://www.erlang.org/download.html] и устанавливаем последнюю версию Erlang для нашей версии Windows (32 или 64 bit). 

### 2. Устанавливаем RabbitMQ

Идём по ссылке [https://www.rabbitmq.com/download.html] и устанавливаем последнюю версию RabbitMQ для Windows.

В большинстве случаев сервис установится и сконфигурируется автоматически а также будет запускаться автоматом при старте Windows.
Но могут быть и проблемы: 

* Если имя пользователя Windows содержит не-ASCII символы, сервис не запустится.
    В моём случае:
        - Добавил к переменной среды `Path`: `;C:\Program Files (x86)\RabbitMQ Server\rabbitmq_server-3.5.1\sbin` для доступа к bat-файлам.
        - Сделал символическую ссылку с директории `C:\Users\Николай_2\AppData\Roaming\RabbitMQ` на `C:\RabbitMQ` (для избегания не-ASCII символов) 
        - Установил переменную окружения: `RABBITMQ_BASE=C:\RabbitMQ`
        - Сделал символическую ссылку с директории `C:\Users\Николай_2` на `C:\NickHome` (для избегания не-ASCII символов)   
        - В файле `C:\Program Files (x86)\RabbitMQ Server\rabbitmq_server-3.5.1\sbin\rabbitmq-server.bat` после `@echo off` 
            на следующей строке добавил `set HOMEPATH=/NickHome`. Хотя диск `C:/` и не указан, он потом используется.
              
После чего сервис успешно запустился. Если сервис не был запущен из-за ошибки, то надо сделать одно из двух:
    * перезагрузить компьютер
    * Перейти в `Панель управления\Все элементы панели управления\Администрирование`, запустить **Службы** и там запустить RabbitMQ.
    
### 3. Установка Node.JS 

Необходимо перейти по ссылке [https://nodejs.org/] и там нажать **Install**. Далее скачать и следовать инструкциям.

### 4. Устанавливаем VisualC++ Express.

Необходима для компиляции некоторых модулей Node.js (например Socket.IO).
Берём здесь [https://www.visualstudio.com/products/visual-studio-community-vs] и устанавливаем.

### 5. Устанавливаем зависимости проекта Node.js.

Просто установить из командной строки не получилось, но найден практически гарантированный способ.
    * Запускаем VisualC++ Express
    * Заходим в `Tools > Visual Studio Command Prompt`
    * Переходим в папку проекта node.js и запускаем установку:
        ```
        >cd D:\projects\chat-push-server
        >"c:\Program Files\nodejs\npm.cmd" install 
        ```





        
        

