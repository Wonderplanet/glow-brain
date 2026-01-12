  get "users/profiles", to: "users#get"
  get "users/search", to: "users#search"
  patch "users/recover_ap", to: "users#recover_ap"
  delete "users/delete", to: "users#delete"
