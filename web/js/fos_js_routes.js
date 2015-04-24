fos.Router.setData({"base_url":"","routes":{"user_profile_show":{"tokens":[["text","\/profile\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"fos_user_profile_show":{"tokens":[["text","\/profile\/"]],"defaults":[],"requirements":{"_method":"GET"},"hosttokens":[]},"sonata_user_profile_show":{"tokens":[["text","\/profile\/"]],"defaults":[],"requirements":{"_method":"GET"},"hosttokens":[]},"admin_app_user_show":{"tokens":[["text","\/show"],["variable","\/","[^\/]++","id"],["text","\/admin\/app\/user"]],"defaults":[],"requirements":[],"hosttokens":[]},"chat":{"tokens":[["text","\/chat\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"chat_show":{"tokens":[["variable","\/","\\d+","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"companion_id":"\\d+"},"hosttokens":[]},"chat_add_message":{"tokens":[["text","\/add-message"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"chat_check_can_add_message":{"tokens":[["text","\/check-can-add-message"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"GET"},"hosttokens":[]},"chat_add_image_message":{"tokens":[["text","\/add-image-message"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"POST|GET"},"hosttokens":[]},"chat_agree_to_pay":{"tokens":[["text","\/agree-to-pay"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"chat_get_new_messages":{"tokens":[["text","\/new-messages"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"GET"},"hosttokens":[]},"chat_get_previous_messages":{"tokens":[["text","\/previous-messages"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"GET"},"hosttokens":[]},"chat_mark_messages_read":{"tokens":[["text","\/mark-messages-read"],["variable","\/","[^\/]++","companion_id"],["text","\/chat"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"coins_add":{"tokens":[["text","\/coins\/add"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"homepage":{"tokens":[["text","\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"profile_show":{"tokens":[["text","\/profile\/"]],"defaults":[],"requirements":[],"hosttokens":[]},"profile_photos":{"tokens":[["text","\/profile\/photos"]],"defaults":[],"requirements":[],"hosttokens":[]},"profile_add_photo":{"tokens":[["text","\/profile\/add-photo"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"profile_delete_photo":{"tokens":[["variable","\/","[^\/]++","photo"],["text","\/profile\/delete-photo"]],"defaults":[],"requirements":{"_method":"POST"},"hosttokens":[]},"search_index":{"tokens":[["text","\/search"]],"defaults":[],"requirements":[],"hosttokens":[]},"user_show":{"tokens":[["variable","\/","[^\/]++","user_id"],["text","\/user"]],"defaults":[],"requirements":[],"hosttokens":[]}},"prefix":"","host":"localhost","scheme":"http"});